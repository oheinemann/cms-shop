<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$envFile = getenv('PATH_SHOP') . '/app/etc/env.php';

// Include the existing env.php file (from the production server)
$config = include $envFile;


// Change MAGE_MODE to developer
if (isset($config['MAGE_MODE'])) {
    $config['MAGE_MODE'] = 'developer';
}


// Change frontend cache to use Redis
if (isset($config['cache']['frontend']['default']['backend_options']['remote_backend_options']['server'])) {
    $config['cache']['frontend']['default']['backend_options']['remote_backend_options']['server'] = 'redis';
}


// Change queue to use RabbitMQ
if (isset($config['queue']['amqp']['host'])) {
    $config['queue']['amqp']['host'] = 'rabbitmq';
}
if (isset($config['queue']['amqp']['user'])) {
    $config['queue']['amqp']['user'] = 'rabbitmq';
}
if (isset($config['queue']['amqp']['password'])) {
    $config['queue']['amqp']['password'] = 'rabbitmq';
}


// Change default database connection to use db
if (isset($config['db']['connection']['default']['host'])) {
    $config['db']['connection']['default']['host'] = 'db';
}
if (isset($config['db']['connection']['default']['username'])) {
    $config['db']['connection']['default']['username'] = 'db';
}
if (isset($config['db']['connection']['default']['dbname'])) {
    $config['db']['connection']['default']['dbname'] = getenv('DATABASE_SHOP');
}
if (isset($config['db']['connection']['default']['password'])) {
    $config['db']['connection']['default']['password'] = 'db';
}


// Change indexer database connection to use db
if (isset($config['db']['connection']['indexer']['host'])) {
    $config['db']['connection']['indexer']['host'] = 'db';
}
if (isset($config['db']['connection']['indexer']['username'])) {
    $config['db']['connection']['indexer']['username'] = 'db';
}
if (isset($config['db']['connection']['indexer']['dbname'])) {
    $config['db']['connection']['indexer']['dbname'] = getenv('DATABASE_SHOP');
}
if (isset($config['db']['connection']['indexer']['password'])) {
    $config['db']['connection']['indexer']['password'] = 'db';
}


// Change session cache to use Redis
if (isset($config['session']['redis']['host'])) {
    $config['session']['redis']['host'] = 'redis';
}


// Change search engine to use OpenSearch
if (isset($config['system']['default']['catalog']['search']['opensearch_server_hostname'])) {
    $config['system']['default']['catalog']['search']['opensearch_server_hostname'] = 'opensearch';
    $config['system']['default']['catalog']['search']['opensearch_username'] = 'admin';
}


// Change base url to use DDEV project url
if (isset($config['system']['default']['web']['unsecure']['base_url'])) {
    $config['system']['default']['web']['unsecure']['base_url'] = 'http://' . getenv('DOMAIN_SHOP') . '/';
}
if (isset($config['system']['default']['web']['secure']['base_url'])) {
    $config['system']['default']['web']['secure']['base_url'] = 'https://' . getenv('DOMAIN_SHOP') . '/';
}


// Save the updated config back to env.php
$newContent = "<?php\nreturn " . var_export($config, true) . ";\n";
file_put_contents($envFile, $newContent);
