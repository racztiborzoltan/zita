<?php
namespace Zita;

use Psr\Http\Server\MiddlewareInterface;

class MiddlewareGroup implements MiddlewareInterface
{

    use MiddlewareGroupTrait;
}
