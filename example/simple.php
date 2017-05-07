<?php
require __DIR__ . '/../vendor/autoload.php';

$rootPath = realpath(__DIR__ . '/..');

$localPath = $rootPath . DIRECTORY_SEPARATOR . 'local';

$previousCwd = getcwd();

chdir($rootPath);

$config = require __DIR__ . '/../config.local.php';

$client = new \Gitlab\Client($config['gitlab_api_url']);
$client->authenticate($config['gitlab_api_token'], \Gitlab\Client::AUTH_URL_TOKEN);

$logger = new \Monolog\Logger('app');
$logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::DEBUG));

$crawler = new \Tobeno\GitlabCrawler\Crawler($client);
$crawler->setLogger($logger);

$files = $crawler->crawl('tobeno/test*:master:README.md');

chdir($previousCwd);