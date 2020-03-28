<?php

namespace Framework\Response;

use GuzzleHttp\Psr7\Response;

class ResponseRedirect extends Response
{

    /**
     * ResponseRedirect constructor.
     * @param string $url
     */
    public function __construct(string $url)
    {
        parent::__construct(200, ['Location' => $url]);
    }
}
