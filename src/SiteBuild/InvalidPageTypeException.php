<?php
namespace Zita\SiteBuild;

/**
 * SiteBuild Exception class for invalid page type
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
class InvalidPageTypeException extends \Exception
{

    use AwareSiteBuildTrait;
}