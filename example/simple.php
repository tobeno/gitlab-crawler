<?php
require __DIR__ . '/../vendor/autoload.php';

$rootPath = realpath(__DIR__ . '/..');

$localPath = $rootPath . '/local';
$cachePath = $localPath . '/cache';

$previousCwd = getcwd();

chdir($rootPath);

$config = require $rootPath . '/config.local.php';

$cache = new \Symfony\Component\Cache\Adapter\FilesystemAdapter('', 0, $cachePath);

$client = new \Gitlab\Client($config['gitlab_api_url']);
$client->authenticate($config['gitlab_api_token'], \Gitlab\Client::AUTH_URL_TOKEN);

$logger = new \Monolog\Logger('app');
$logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::DEBUG));

$crawler = new \Tobeno\GitlabCrawler\Crawler($client, $cache);
$crawler->setLogger($logger);

$files = $crawler->crawl('tobeno/test*:master:README.md');

chdir($previousCwd);