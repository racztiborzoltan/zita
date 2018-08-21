<?php
namespace Zita;

/**
 * Application aware interface
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
interface ApplicationAwareInterface
{

    /**
     * Returns an application object
     *
     * @return \Zita\Application
     */
    public function getApplication(): \Zita\Application;

    /**
     * Set an application object
     *
     * @param \Zita\Application $application
     */
    public function setApplication(\Zita\Application $application);
}