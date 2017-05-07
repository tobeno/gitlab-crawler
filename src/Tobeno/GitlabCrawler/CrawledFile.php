<?php


namespace Tobeno\GitlabCrawler;


class CrawledFile
{
    /**
     * @var int|null
     */
    private $projectId;

    /**
     * @var string|null
     */
    private $projectName;

    /**
     * @var string|null
     */
    private $branchName;

    /**
     * @var string|null
     */
    private $path;

    /**
     * @var string|null
     */
    private $contents;

    /**
     * @return int|null
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * @param int|null $projectId
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * @return null|string
     */
    public function getProjectName()
    {
        return $this->projectName;
    }

    /**
     * @param null|string $projectName
     */
    public function setProjectName($projectName)
    {
        $this->projectName = $projectName;
    }

    /**
     * @return null|string
     */
    public function getBranchName()
    {
        return $this->branchName;
    }

    /**
     * @param null|string $branchName
     */
    public function setBranchName($branchName)
    {
        $this->branchName = $branchName;
    }

    /**
     * @return null|string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param null|string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return null|string
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * @param null|string $contents
     */
    public function setContents($contents)
    {
        $this->contents = $contents;
    }


}