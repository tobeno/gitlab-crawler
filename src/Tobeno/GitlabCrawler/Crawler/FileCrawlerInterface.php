<?php

namespace Tobeno\GitlabCrawler\Crawler;

use Tobeno\GitlabCrawler\Result\CrawledFile;
use Tobeno\GitlabCrawler\Expression\FileCrawlerExpression;

interface FileCrawlerInterface
{
    /**
     * @param FileCrawlerExpression|string $expression
     * @return \Traversable|CrawledFile[]
     */
    public function crawl($expression): \Traversable;
}