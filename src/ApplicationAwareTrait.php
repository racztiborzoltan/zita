<?php
namespace Zita;

/**
 *
 * Application aware trait
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
trait ApplicationAwareTrait
{

    /**
     * @var \Zita\Application
     */
    private $_application = null;

    /**
     * Returns an application object if exists, otherwise an exception is thrown
     *
     * @throws \LogicException
     * @return \Zita\Application
     */
    public function getApplication(): \Zita\Application
    {
        if (empty($this->_application)) {
            throw new \LogicException('Application object not found. Use the following method before: ->setApplication()');
        }
        return $this->_application;
    }

    /**
     * Set an application object
     *
     * @param \Zita\Application $application
     * @return \Zita\ApplicationAwareTrait
     */
    public function setApplication(\Zita\Application $application)
    {
        $this->_application = $application;
        return $this;
    }
}