<?php
declare(strict_types=1);

namespace Zita\TestProject;

class SiteBuild
{

    private $_source_directory = null;

    private $_destination_directory = null;

    public function setSourceDirectory(string $source_directory): self
    {
        $this->_source_directory = realpath($source_directory);
        return $this;
    }

    public function getSourceDirectory(): string
    {
        if (empty($this->_source_directory)) {
            throw new \LogicException('Source directory is empty! Use before the following method: ->setDestinationDirectory()');
        }
        if (!is_dir($this->_source_directory)) {
            throw new \LogicException('Source directory is not valid directory!');
        }
        return $this->_source_directory;
    }

    public function setDestinationDirectory(string $destination_directory): self
    {
        $this->_destination_directory = realpath($destination_directory);
        return $this;
    }

    public function getDestinationDirectory(): string
    {
        if (empty($this->_destination_directory)) {
            throw new \LogicException('Destination directory is empty! Use before the following method: ->setDestinationDirectory()');
        }
        if (!is_dir($this->_destination_directory)) {
            throw new \LogicException('Destination directory is not valid directory!');
        }
        return $this->_destination_directory;
    }

    /**
     * Copy from source directory to destination directory
     *
     * @param string $relative_file_path
     */
    public function copyFile($relative_file_path)
    {
        $source_full_path = $this->getSourceDirectory() . '/' . $relative_file_path;
        $destination_full_path = $this->getDestinationDirectory() . '/' . $relative_file_path;

        if (!is_file($source_full_path)) {
            throw new \LogicException('File not found in source directory');
        }

        if (!is_dir(dirname($destination_full_path))) {
            mkdir(dirname($destination_full_path), 0777, true);
        }
        return copy($source_full_path, $destination_full_path);
    }
}
