<?php
declare(strict_types = 1);
namespace Zita\TestProject;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zita\ApplicationAwareInterface;
use Zita\ApplicationAwareTrait;

/**
 * Default request handler
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
class RequestHandler implements RequestHandlerInterface, ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->getApplication()->getResponse();
        return $response;
    }
}
