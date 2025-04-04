<?php
if (getenv('IS_DDEV_PROJECT') == 'true') {
    $GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive(
        $GLOBALS['TYPO3_CONF_VARS'],
        [
            'BE' => [
                'installToolPassword' => '$argon2i$v=19$m=65536,t=16,p=1$a2pnSGRoZWphWEJwai41MQ$vr/a72ILecmgNDsHHzMU/6qXbOxAgpCi+K5fZ8Kq6s0', // password
            ],
            'DB' => [
                'Connections' => [
                    'Default' => [
                        'dbname' => 'db',
                        'driver' => 'mysqli',
                        'host' => 'db',
                        'password' => 'db',
                        'port' => '3306',
                        'user' => 'db',
                    ],
                ],
            ],
            // This GFX configuration allows processing by installed ImageMagick 6
            'GFX' => [
                'processor' => 'ImageMagick',
                'processor_path' => '/usr/bin/',
                'processor_path_lzw' => '/usr/bin/',
                'processor_effects' => true,
            ],
            // This mail configuration sends all emails to mailpit
            'MAIL' => [
                'transport' => 'smtp',
                'transport_smtp_encrypt' => false,
                'transport_smtp_server' => 'localhost:1025',
            ],
            'SYS' => [
                'trustedHostsPattern' => '.*.*',
                'devIPmask' => '*',
                'displayErrors' => 1,
            ],
        ]
    );
} elseif (getenv('PLATFORM_PROJECT')) {
    $list = [
        'pages' => 3600 * 24 * 7,
        'pagesection' => 3600 * 24 * 7,
        'rootline' => 3600 * 24 * 7,
        'hash' => 3600 * 24 * 7,
        'extbase' => 3600 * 24 * 7,
    ];
    $counter = 10;
    foreach ($list as $key => $lifetime) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$key]['backend'] = \TYPO3\CMS\Core\Cache\Backend\RedisBackend::class;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$key]['options'] = [
            # Database 10 - 15 => Caches
            'database' => $counter++,
            'hostname' => getenv('REDIS_HOST'),
            'port' => (int)getenv('REDIS_PORT'),
            'defaultLifetime' => $lifetime
        ];
    }

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['session']['FE'] = [
        'backend' => \TYPO3\CMS\Core\Session\Backend\RedisSessionBackend::class,
        'options' => [
            'hostname' =>  getenv('REDIS_HOST'),
            # Database 0 => Sessions
            'database' => 0,
            'port' => (int)getenv('REDIS_PORT'),
        ]
    ];

    $GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive(
        $GLOBALS['TYPO3_CONF_VARS'],
        [
            'DB' => [
                'Connections' => [
                    'Default' => [
                        'dbname' => getenv('DATABASE_PATH'),
                        'driver' => 'mysqli',
                        'host' => getenv('DATABASE_HOST'),
                        'password' => getenv('DATABASE_PASSWORD'),
                        'port' => getenv('DATABASE_PORT'),
                        'user' => getenv('DATABASE_USERNAME'),
                    ],
                ],
            ],
        ]
    );
}