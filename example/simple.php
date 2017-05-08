<?php
namespace Tobeno\GitlabCrawler;

/**
 * @var \Gitlab\Client $client
 * @var string $cachePath
 */
use Monolog\Handler\StreamHandler;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Tobeno\GitlabCrawler\Crawler\Expression\FileCrawlerExpression;
use Tobeno\GitlabCrawler\Crawler\FileCrawler;

require __DIR__.'/setup.php';

$logger = new \Monolog\Logger('app');
$logger->pushHandler(new StreamHandler('php://stdout', \Monolog\Logger::DEBUG));

$cache = new FilesystemAdapter('', 0, $cachePath);
//$cache->clear();

$crawler = new FileCrawler($client, $cache);
$crawler->setLogger($logger);

$expression = FileCrawlerExpression::create(
    'tobeno/test*',
    'master',
    ['composer.json', 'application/composer.json']
);

$files = $crawler->crawl($expression);

$composerDefinitions = [];
foreach ($files as $file) {
    $composerDefinitions[$file->getProjectName()] = json_decode($file->getContents(), true);
}

var_dump($composerDefinitions);