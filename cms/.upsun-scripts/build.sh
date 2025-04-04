#!/usr/bin/env bash
#found out where we are so we can include other files
DIR="${BASH_SOURCE%/*}"
#if BASH_SOURCE didn't return what we want, try PWD
if [[ ! -d "$DIR" ]]; then DIR="$PWD"; fi
#Include our common.sh
. "${DIR}/common.sh"

export VERSION=1.2.1
wget -nc https://storage.googleapis.com/downloads.webmproject.org/releases/webp/libwebp-${VERSION}-linux-x86-64.tar.gz  -O - | tar -xz -C ${PLATFORM_CACHE_DIR}
mkdir -p /app/.global/bin
cp ${PLATFORM_CACHE_DIR}/libwebp-${VERSION}-linux-x86-64/bin/* /app/.global/bin/

if [ -f "${PLATFORM_APP_DIR}/config/system/settings.php" ]; then
  mv "${PLATFORM_APP_DIR}/config/system/settings.php" "${PLATFORM_APP_DIR}/config/system/settings.FromSource.php"
fi

# There is no way to tell during the build stage if the CMS is already installed, so we'll run the command and then
# determine during the deploy stage whether or not we've installed it already
composer exec typo3 install:setup -- --install-steps-config=install/SetupConfiguration.yaml --no-interaction --skip-extension-setup

# Enable the install tool. Will allow access for 60mins after deployment.
# https://docs.typo3.org/m/typo3/guide-security/8.7/en-us/GuidelinesIntegrators/InstallTool/Index.html
# public/typo3conf is read-only in deploy, so create a symlink to the file in the ${varPath} file mount
# @todo add a link to the docs
if [[ -n ${TYPO3_ENABLE_INSTALL_TOOL+x} ]]; then
  ln -sf "${PLATFORM_APP_DIR}/${varPath}/ENABLE_INSTALL_TOOL" "${PLATFORM_APP_DIR}/config/ENABLE_INSTALL_TOOL"
fi

# this file is created earlier by the installer. It is currently in a read-only area, but the CMS needs to be able to
# write to it later, so we'll rename the original to copy later in deploy, but go ahead and create a symlink to it now
if [ -f "${PLATFORM_APP_DIR}/config/system/settings.php" ]; then
  if [ ! -f "${PLATFORM_APP_DIR}/config/system/settings.FromSource.php" ]; then
    mv "${PLATFORM_APP_DIR}/config/system/settings.php" "${PLATFORM_APP_DIR}/config/system/settings.FromSource.php"
  fi
  ln -sf "${PLATFORM_APP_DIR}/var/settings.php" "${PLATFORM_APP_DIR}/config/system/settings.php"
fi

# This file is created by the installer previously and is deleted by the installer later, once the installation is
# fully complete during deploy (when we have access to the db), but it won't be able to since it's in a read-only
# location so we'll take care of it now
if [ -f "${PLATFORM_APP_DIR}/public/FIRST_INSTALL" ]; then
  rm "${PLATFORM_APP_DIR}/public/FIRST_INSTALL"
fi

# we only want to do this if we're running on a platform environment, and not local development.
if [[ -n ${PLATFORM_TREE_ID+x} && $PLATFORM_TREE_ID =~ $pshTreeIDPttrn ]];then
  # composer install extender for Typo3 creates a bunch of directories and files that are in our file mounts. Since they
  # are in the file mount area, when we mount the file container at deploy, it will shadow any files we've written. This
  # isn't a big deal if we've already installed the CMS but since we dont know that yet, we have to assume it hasnt
  # been installed and then determine if it has during deploy.
  #
  # BUT, if there are dirs with the same name, we need to get rid of them before trying to rename the originals.
  # @todo should we publicly document not to create directories with the same name as a mount + '.' + tmpDirExtension?
  echo "$mountsToSync" | tr ' ' '\n' | while read dir; do
    # if the old one still exists, delete it so we can move the new one to it
    if [ -d "${PLATFORM_APP_DIR}/${dir}.${tmpDirExtension}" ]; then
      rm -rf "${PLATFORM_APP_DIR}/${dir}.${tmpDirExtension}"
    fi
    # Files are written to this location from the composer installer extender, BUT this location is also a file mount which
    # isn't mounted during this stage (build). We have to rename it so we can sync the contents back during the deploy stage
    mv "${PLATFORM_APP_DIR}/${dir}" "${PLATFORM_APP_DIR}/${dir}.${tmpDirExtension}"
  done
fi
