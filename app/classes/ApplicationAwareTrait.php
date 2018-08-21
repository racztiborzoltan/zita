<?php
declare(strict_types=1);

namespace Zita\TestProject;

use Zita\ApplicationAwareTrait as MainApplicationAwareTrait;

/**
 *
 * Application aware trait
 *
 * @method \Zita\TestProject\Application setApplication(\Zita\TestProject\Application $application)
 * @method \Zita\TestProject\Application getApplication()
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
trait ApplicationAwareTrait
{
    use MainApplicationAwareTrait;
}