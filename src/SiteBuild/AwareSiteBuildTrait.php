<?php
namespace Zita\SiteBuild;

/**
 * aware sitebuild trait class
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
trait AwareSiteBuildTrait
{

    /**
     * @var SiteBuild
     */
    private $_sitebuild = null;

    /**
     * Set sitebuild object
     *
     * @param SiteBuild $sitebuild
     * @return self
     */
    public function setSiteBuild(SiteBuild $sitebuild): self
    {
        $this->_sitebuild = $sitebuild;
        return $this;
    }

    /**
     * Returns sitebuild object
     *
     * @param SiteBuild $sitebuild
     * @return self
     */
    public function getSiteBuild(): ?SiteBuild
    {
        return $this->_sitebuild;
    }
}