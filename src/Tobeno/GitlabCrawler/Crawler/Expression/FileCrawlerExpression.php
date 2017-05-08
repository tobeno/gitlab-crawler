<?php


namespace Tobeno\GitlabCrawler\Crawler\Expression;


class FileCrawlerExpression
{
    const FALLBACK_SEPARATOR = '|';
    const PART_SEPARATOR = ':';
    /**
     * @var \string[]
     */
    private $projects;
    /**
     * @var \string[]
     */
    private $branches;
    /**
     * @var \string[]
     */
    private $files;

    /**
     * FileCrawlerExpression constructor.
     * @param string[] $projects
     * @param string[] $branches
     * @param string[] $files
     */
    public function __construct(
        array $projects,
        array $branches,
        array $files
    ) {

        $this->projects = $projects;
        $this->branches = $branches;
        $this->files = $files;
    }

    /**
     * @return \string[]
     */
    public function getProjects(): array
    {
        return $this->projects;
    }

    /**
     * @return \string[]
     */
    public function getBranches(): array
    {
        return $this->branches;
    }

    /**
     * @return \string[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return implode(
            self::PART_SEPARATOR,
            [
                implode(self::FALLBACK_SEPARATOR, $this->projects),
                implode(self::FALLBACK_SEPARATOR, $this->branches),
                implode(self::FALLBACK_SEPARATOR, $this->files),
            ]
        );
    }

    /**
     * @param string|string[] $projects
     * @param string|string[] $branches
     * @param string|string[] $files
     * @return self
     */
    public static function create(
        $projects,
        $branches,
        $files
    ) {
        $projects = self::preparePart($projects);
        $branches = self::preparePart($branches);
        $files = self::preparePart($files);

        return new self($projects, $branches, $files);
    }

    /**
     * @param string $expression
     * @return self
     */
    public static function parse(string $expression)
    {
        $expressionParts = explode(self::PART_SEPARATOR, $expression);

        if (count($expressionParts) !== 3) {
            throw new \InvalidArgumentException('Invalid expression "'.$expression.'" given.');
        }

        return self::create($expressionParts[0], $expressionParts[1], $expressionParts[2]);
    }

    /**
     * @param string|string[] $part
     * @return array
     */
    private static function preparePart($part): array
    {
        if (is_string($part)) {
            $part = explode(self::FALLBACK_SEPARATOR, $part);
        }

        return $part;
    }
}