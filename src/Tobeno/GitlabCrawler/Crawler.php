<?php


namespace Tobeno\GitlabCrawler;


use Gitlab\Client;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Crawler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var \Gitlab\Api\Repositories
     */
    private $repositoriesApi;

    /**
     * @var \Gitlab\Api\Projects
     */
    private $projectsApi;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * Crawler constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->repositoriesApi = $client->api('repositories');
        $this->projectsApi = $client->api('projects');
    }

    /**
     * @param string $expression
     * @return array
     */
    public function crawl(string $expression): array
    {
        $projects = $this->matchProjects($expression);

        $crawledFiles = [];

        foreach ($projects as $project) {
            $projectId = (int)$project['id'];

            $projectName = $project['path_with_namespace'];

            $this->log('Found project ' . $projectName . ' (ID: ' . $projectId . ')');

            $branch = $this->matchBranch($projectId, $expression);

            $file = null;

            if ($branch) {
                $branchName = $branch['name'];

                $this->log('Found branch ' . $branchName);


                $file = $this->matchFile($projectId, $branchName, $expression);
            }

            if ($file) {
                if ($file['encoding'] !== 'base64') {
                    throw new \RuntimeException('Unknown file encoding ' . $file['encoding'] . ' found.');
                }

                $filePath = $file['file_path'];

                $this->log('Found file ' . $filePath);

                $crawledFile = new CrawledFile();
                $crawledFile->setProjectId($projectId);
                $crawledFile->setProjectName($projectName);
                $crawledFile->setBranchName($branch['name']);
                $crawledFile->setPath($filePath);
                $crawledFile->setContents(base64_decode($file['content']));

                $crawledFiles[] = $crawledFile;
            }
        }

        return $crawledFiles;
    }

    /**
     * @return array
     */
    private function getProjects(): array
    {
        $cacheKey = 'projects';

        if (!isset($this->cache[$cacheKey])) {
            $projects = (function (array $projects) {
                foreach ($projects as $project) {
                    yield $project['path_with_namespace'] => $project;
                }
            })($this->projectsApi->accessible());

            $this->cache[$cacheKey] = iterator_to_array($projects);
        }

        return $this->cache[$cacheKey];
    }

    /**
     * @param int $projectId
     * @return array
     */
    private function getBranches(int $projectId): array
    {
        $cacheKey = 'branches:' . $projectId;

        if (!isset($this->cache[$cacheKey])) {
            $branches = (function (array $branches) {
                foreach ($branches as $branch) {
                    yield $branch['name'] => $branch;
                }
            })($this->repositoriesApi->branches($projectId));

            $this->cache[$cacheKey] = iterator_to_array($branches);
        }

        return $this->cache[$cacheKey];
    }

    /**
     * @param int $projectId
     * @param null|string $ref
     * @return array
     */
    private function getTree(int $projectId, ?string $ref): array
    {
        $cacheKey = 'tree:' . $projectId . ':' . $ref;

        if (!isset($this->cache[$cacheKey])) {
            $branches = (function (array $tree) {
                foreach ($tree as $item) {
                    yield $item['path'] => $item;
                }
            })($this->repositoriesApi->tree($projectId, [
                'ref' => $ref
            ]));

            $this->cache[$cacheKey] = iterator_to_array($branches);
        }

        return $this->cache[$cacheKey];
    }

    /**
     * @param string $expression
     * @return array
     */
    private function matchProjects(string $expression): array
    {
        $fileParts = explode(':', $expression);
        if (count($fileParts) !== 3) {
            throw new \InvalidArgumentException('Invalid file given.');
        }

        $projects = $this->match($fileParts[0], $this->getProjects());

        return $projects;
    }

    /**
     * @param int $projectId
     * @param string $expression
     * @return array|null
     */
    private function matchBranch(int $projectId, string $expression): ?array
    {
        $fileParts = explode(':', $expression);
        if (count($fileParts) !== 3) {
            throw new \InvalidArgumentException('Invalid file given.');
        }

        $branch = $this->matchOne($fileParts[1], $this->getBranches($projectId));

        return $branch;
    }

    /**
     * @param int $projectId
     * @param string $ref
     * @param string $expression
     * @return array|null
     */
    private function matchFile(int $projectId, string $ref, string $expression): ?array
    {
        $matchedFile = null;

        $fileParts = explode(':', $expression);
        if (count($fileParts) !== 3) {
            throw new \InvalidArgumentException('Invalid file given.');
        }

        $treeItem = $this->matchOne($fileParts[2], $this->getTree($projectId, $ref));

        if ($treeItem) {
            $matchedFile = $this->repositoriesApi->getFile($projectId, $treeItem['path'], $ref);
        }

        return $matchedFile;
    }

    /**
     * @param string $pattern
     * @param array $set
     * @return array|null
     */
    private function matchOne(string $pattern, array $set): ?array
    {
        $matches = $this->match($pattern, $set);

        return $matches ? reset($matches) : null;
    }

    /**
     * @param string $pattern
     * @param array $set
     * @return array
     */
    private function match(string $pattern, array $set)
    {
        $matches = [];

        $setKeys = array_keys($set);

        $patterns = explode('|', $pattern);
        foreach ($patterns as $singlePattern) {
            $singlePattern = str_replace('*', '.*', preg_quote($singlePattern));

            foreach ($setKeys as $setKey) {
                if (preg_match('~' . $singlePattern . '~', $setKey)) {
                    $match = $set[$setKey];

                    $matches[] = $match;
                }
            }
        }

        return $matches;
    }

    /**
     * @param string $message
     */
    public function log(string $message): void
    {
        if ($this->logger) {
            $this->logger->debug('Gitlab crawler: ' . $message);
        }
    }

}