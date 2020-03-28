<?php

namespace Framework\Twig;

use Exception;
use Grafikart\Csrf\CsrfMiddleware;
use Twig\TwigFunction;

class CsrfExtension extends \Twig\Extension\AbstractExtension
{

    /**
     * @var CsrfMiddleware
     */
    private $middleware;

    /**
     * CsrfExtension constructor.
     * @param CsrfMiddleware $middleware
     */
    public function __construct(CsrfMiddleware $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('csrf_input', [$this, 'csrfInput'], ['is_safe' => ['html']])
        ];
    }

    /**
     * @return string
     * @throws Exception
     */
    public function csrfInput()
    {
        return "<input type=\"hidden\" " .
        "name=\"{$this->middleware->getFormKey()}\" " .
        "value=\"{$this->middleware->generateToken()}\"/>";
    }
}
