<?php
namespace Zita\SiteBuild;

use Psr\Http\Message\UriInterface;

/**
 * SiteBuild Exception class for invalid page type
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
interface SiteBuildInterface
{

    /**
     * Set name of sitebuild
     *
     * @param string $name
     */
    public function setName(string $name);

    /**
     * Set name of sitebuild
     *
     * @param string $name
     */
    public function getName(): string;

    /**
     * Set page type
     *
     * @param string $page_type
     * @return self
     */
    public function setPageType(string $page_type);

    /**
     * Returns page type
     *
     * @return string
     */
    public function getPageType(): string;

    /**
     * Merge config values into recursively
     *
     * Supported multidimensional array values:
     *  - scalar values (integer, float, string or boolean)
     *  - full numeric indexed array (ex.: array('item1', 'item2'))
     *  - full associative indexed array (ex.: array('name1' => 'item1', 'name2' => 'item2'))
     *  - object
     *  - resource
     *
     * @param array $config
     * @return self
     */
    public function mergeConfig(array $config);

    /**
     * Overwrite all config values
     *
     * @param array $config
     * @return self
     */
    public function setConfig(array $config): array;

    /**
     * Get all config values
     *
     * @return array
     */
    public function getConfig(): array;

    /**
     * Set source directory of sitebuild
     *
     * @param string $source_directory
     * @return self
     */
    public function setSourceDirectory(string $source_directory);

    /**
     * Returns source directory of sitebuild
     *
     * @return string
     */
    public function getSourceDirectory(): string;

    /**
     * Set destination directory of sitebuild
     *
     * @param string $source_directory
     * @return self
     */
    public function setDestinationDirectory(string $destination_directory);

    /**
     * Returns destination directory of sitebuild
     *
     * @return string
     */
    public function getDestinationDirectory(): string;

    /**
     * Set public URL to sitebuild
     *
     * @param UriInterface $public_url
     * @return self
     */
    public function setPublicUrl(UriInterface $public_url);

    /**
     * Returns public URL to sitebuild
     *
     * @return UriInterface
     */
    public function getPublicUrl(): UriInterface;

    /**
     * Return list of valid page type
     *
     * @return array
     */
    public function getValidPageTypes(): array;

    /**
     * Sitebuild rendering
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function render(): \Psr\Http\Message\ResponseInterface;
}