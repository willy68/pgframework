<?php

namespace Framework\Twig;

use Framework\Session\FlashService;
use Twig\TwigFunction;

class FlashExtension extends \Twig\Extension\AbstractExtension
{

  /**
   * Undocumented variable
   *
   * @var FlashService
   */
    private $flashservice;

    /**
     * FlashExtension constructor.
     * @param FlashService $flashservice
     */
    public function __construct(FlashService $flashservice)
    {
        $this->flashservice = $flashservice;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('flash', [$this, 'getFlash'])
        ];
    }

  /**
   * Undocumented function
   *
   * @param string $type
   * @return string|null
   */
    public function getFlash(string $type): ?string
    {
        return $this->flashservice->get($type);
    }
}
