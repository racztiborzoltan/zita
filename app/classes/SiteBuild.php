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
     * @return bool Was the operation successful?
     */
    public function copyFile($relative_file_path): bool
    {
        $source_full_path = $this->getSourceDirectory() . '/' . $relative_file_path;
        $destination_full_path = $this->getDestinationDirectory() . '/' . $relative_file_path;

        if (!is_file($source_full_path)) {
            throw new \InvalidArgumentException('File not found in source directory: ' . $relative_file_path);
        }

        if (!is_file($destination_full_path) || filemtime($source_full_path) > filemtime($destination_full_path) ) {
            if (!is_dir(dirname($destination_full_path))) {
                mkdir(dirname($destination_full_path), 0777, true);
            }
            return copy($source_full_path, $destination_full_path);
        }
        return true;
    }

    /**
     * Copy directory from source to destination
     *
     * @param string $relative_directory_path
     * @return bool Was the operation successful?
     */
    public function copyDirectory($relative_directory_path): bool
    {
        $source_dir_path = $this->getSourceDirectory() . '/' . $relative_directory_path;
        $destination_dir_path = $this->getDestinationDirectory() . '/' . $relative_directory_path;

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

            $this->copyFile($relative_directory_path . '/' . (str_replace($source_dir_path, '', $source_file->getPathname())));
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
            $relative_path = $relative_directory_path . '/' . (str_replace($destination_dir_path, '', $destination_file->getPathname()));
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

        return true;
    }
}
