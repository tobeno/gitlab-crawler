<?php
require __DIR__ . '/../vendor/autoload.php';

$rootPath = realpath(__DIR__ . '/..');

$localPath = $rootPath . '/local';
$cachePath = $localPath . '/cache';

$config = require $rootPath . '/config.local.php';

$client = new \Gitlab\Client($config['gitlab_api_url']);
$client->authenticate($config['gitlab_api_token'], \Gitlab\Client::AUTH_URL_TOKEN);