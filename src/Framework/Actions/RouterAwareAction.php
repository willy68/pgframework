<?php

namespace Framework\Actions;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Rajoute des methodes à l'utilisation du router
 */
trait RouterAwareAction
{

  /**
   * Undocumented function
   *
   * @param string $path
   * @param array $params
   * @return ResponseInterface
   */
    public function redirect(string $path, array $params = []): ResponseInterface
    {
        $redirectUri = $this->router->generateUri($path, $params);
        return (new Response())
        ->withStatus(301)
        ->withHeader('Location', $redirectUri);
    }
}
