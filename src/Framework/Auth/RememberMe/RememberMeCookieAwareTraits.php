<?php

namespace Framework\Auth\RememberMe;

trait RememberMeCookieAwareTraits
{
    // Can't use const!
    private $delimiter = ':';
    
    /**
     * Get the cookie parts
     *
     * @param string $cookie
     * @return array
     */
    protected function decodeCookie(string $cookie): array
    {
        $cookieParts = explode($this->delimiter, base64_decode($cookie));
        return $cookieParts;
    }

    /**
     * Encode the cookie parts
     *
     * @param array $cookieParts
     * @return string
     */
    protected function encodeCookie(array $cookieParts): string 
    {
        return base64_encode(implode($this->delimiter, $cookieParts));
    }
}
