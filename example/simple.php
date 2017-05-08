<?php

namespace Tobeno\GitlabCrawler;

/**
 * @var \Gitlab\Client $client
 * @var string $cachePath
 * @var array $config
 */
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Tobeno\GitlabCrawler\Crawler\Expression\FileCrawlerExpression;
use Tobeno\GitlabCrawler\Crawler\FileCrawler;

require __DIR__.'/setup.php';

$logger = new Logger('app');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$cache = new FilesystemAdapter('', 0, $cachePath);
//$cache->clear();

$crawler = new FileCrawler($client, $cache);
$crawler->setLogger($logger);

$expression = FileCrawlerExpression::create(
    $config['gitlab_project'],
    $config['gitlab_branch'],
    'composer.json'
);

$files = $crawler->crawl($expression);

foreach ($files as $file) {
    echo $file->getProjectName().': '.$file->getContents().PHP_EOL;
}