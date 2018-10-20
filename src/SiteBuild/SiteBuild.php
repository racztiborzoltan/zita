<?php
declare(strict_types=1);

namespace Zita\SiteBuild;

use Psr\Http\Message\UriInterface;

/**
 * SiteBuild class
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
abstract class SiteBuild implements SiteBuildInterface
{

    private $_name = null;

    private $_page_type = null;

    private $_config = [];

    private $_source_directory = null;

    private $_destination_directory = null;

    /**
     * Public URI
     *
     * @var UriInterface
     */
    private $_public_url = null;

    public function setName(string $name)
    {
        $this->_name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->_name;
    }

    public function setPageType(string $page_type)
    {
        $valid_page_types = $this->getValidPageTypes();
        if (!in_array($page_type, $valid_page_types)) {
            $exception = new InvalidPageTypeException('invalid page type: ".$page_type.". Valid page types: ' . implode(', ', $valid_page_types));
            $exception->setSiteBuild($this);
            throw $exception;
        }
        $this->_page_type = $page_type;
        return $this;
    }

    public function getPageType(): string
    {
        return $this->_page_type;
    }

    public function mergeConfig(array $config)
    {
        $this->_config = $this->_array_merge_recursive($this->_config, $config);
        return $this;
    }

    protected function _array_merge_recursive($array1, $array2)
    {
        if (is_array($array1) && is_array($array2)) {
            // If associative numeric indexed array:
            if (in_array(false, array_map('is_numeric', array_keys($array2)))) {
                foreach (array_keys($array2) as $key) {
                    if (isset($array1[$key])) {
                        $array1[$key] = $this->_array_merge_recursive($array1[$key], $array2[$key]);
                    } else {
                        $array1[$key] = $array2[$key];
                    }
                }
                return $array1;
            } else {
                // if full numeric indexed array:
                return $array2;
            }
        }
        return $array2;
    }

    public function setConfig(array $config): array
    {
        $this->_config = $config;
        return $this;
    }

    public function getConfig(): array
    {
        return $this->_config;
    }

    public function setSourceDirectory(string $source_directory)
    {
        $this->_source_directory = $source_directory;
        return $this;
    }

    public function getSourceDirectory(): string
    {
        return $this->_source_directory;
    }

    public function setDestinationDirectory(string $destination_directory)
    {
        $this->_destination_directory = $destination_directory;
        return $this;
    }

    public function getDestinationDirectory(): string
    {
        return $this->_destination_directory;
    }

    public function setPublicUrl(UriInterface $public_url)
    {
        $this->_public_url = $public_url;
        return $this;
    }

    public function getPublicUrl(): UriInterface
    {
        return $this->_public_url;
    }

    /**
     * Copy from source directory to destination directory
     *
     * @param string $source_relative_file_path
     * @return string new relative file path or NULL
     */
    public function copyFile(string $source_relative_file_path): string
    {
        return $this->copyFileWithPathPrefix($source_relative_file_path, '');
    }

    /**
     * Copy from source directory to destination directory with path prefix
     *
     * Example:
     * "assets/css/main.css" copy to "cache/assets/main.css"
     *
     * @param string $source_relative_file_path
     * @param string $path_prefix
     * @return string new relative file path or NULL
     */
    public function copyFileWithPathPrefix(string $source_relative_file_path, string $path_prefix): string
    {
        // If first parameter is absolute url:
        if (preg_match('#^((https?):)?//#', $source_relative_file_path)) {
            return $source_relative_file_path;
        }
        $destination_relative_file_path = $path_prefix . $source_relative_file_path;
        $source_full_path = $this->getSourceDirectory() . '/' . $source_relative_file_path;
        $destination_full_path = $this->getDestinationDirectory() . '/' . $destination_relative_file_path;

        if (!is_file($source_full_path)) {
            throw new \InvalidArgumentException('File not found in source directory: ' . $source_relative_file_path);
        }

        if (!is_file($destination_full_path) || filemtime($source_full_path) > filemtime($destination_full_path) ) {
            if (!is_dir(dirname($destination_full_path))) {
                mkdir(dirname($destination_full_path), 0777, true);
            }
            copy($source_full_path, $destination_full_path);
            return $destination_relative_file_path;
        }
        return $destination_relative_file_path;
    }

    /**
     * Copy directory from source to destination
     *
     * @param string $source_relative_directory_path
     * @return string new relative destination directory path
     */
    public function copyDirectory(string $source_relative_directory_path): string
    {
        return $this->copyDirectoryWithPathPrefix($source_relative_directory_path, '');
    }

    /**
     * Copy directory from source to destination with path prefix
     *
     * Example:
     * // all files in "assets" copy into "cache/assets"
     * $class->copyDirectoryWithPathPrefix('assets', 'cache/');
     *
     * @param string $source_relative_directory_path
     * @param string $path_prefix
     * @return string new relative destination directory path
     */
    public function copyDirectoryWithPathPrefix(string $source_relative_directory_path, string $path_prefix): string
    {
        $destination_relative_directory_path = $path_prefix . $source_relative_directory_path;
        $source_dir_path = $this->getSourceDirectory() . '/' . $source_relative_directory_path;
        $destination_dir_path = $this->getDestinationDirectory() . '/' . $destination_relative_directory_path;

        if (!is_dir($source_dir_path)) {
            throw new \InvalidArgumentException('Directory not found in source directory');
        }

        // copy files to destination:
        $source_files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source_dir_path), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach($source_files as $source_file){
            /**
             * @var \SplFileInfo $source_file
             */
            if (in_array($source_file->getFilename(), ['.', '..']) || $source_file->isDir()) {
                continue;
            }

            $this->copyFileWithPathPrefix($source_relative_directory_path . '/' . (str_replace($source_dir_path, '', $source_file->getPathname())), $path_prefix);
        }
        unset($source_file, $source_files);

        //
        // check "orphaned" files in destination directory:
        //
        $destination_files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($destination_dir_path), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach($destination_files as $destination_file){
            /**
             * @var \SplFileInfo $destination_file
             */
            if (in_array($destination_file->getFilename(), ['.', '..'])) {
                continue;
            }

            $destination_full_path = $destination_file->getPathname();
            $relative_path = $source_relative_directory_path . '/' . (str_replace($destination_dir_path, '', $destination_file->getPathname()));
            $source_full_path = $this->getSourceDirectory() . '/' . $relative_path;

            if (file_exists($destination_full_path) && !file_exists($source_full_path) && $destination_file->isWritable()) {
                if ($destination_file->isDir()) {
                    rmdir($destination_full_path);
                }
                if ($destination_file->isFile()) {
                    unlink($destination_full_path);
                }
            }
        }
        unset($destination_file, $destination_files);

        return $destination_relative_directory_path;
    }

    abstract public function getValidPageTypes(): array;

    abstract public function render(): \Psr\Http\Message\ResponseInterface;
}