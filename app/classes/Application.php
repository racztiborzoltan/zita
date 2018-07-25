<?php
declare(strict_types=1);

namespace Zita\TestProject;

class Application extends \Zita\Application
{

    /**
     * Name of base dir servce in container object
     *
     * @var string
     */
    const SERVICE_NAME_BASE_DIR = 'base_dir';

    /**
     * Name of sitebuild servce in container object
     *
     * @var string
     */
    const SERVICE_NAME_SITEBUILD = 'sitebuild';

    public function getBaseDir(): string
    {
        return $this->getContainer()->get(static::SERVICE_NAME_BASE_DIR);
    }
}
