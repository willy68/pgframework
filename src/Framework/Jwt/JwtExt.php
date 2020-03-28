<?php

namespace Framework\Jwt;

use Firebase\JWT\JWT as JWT;

class JwtExt extends JWT
{

    /**
     *
     * @param string $jwt
     * @param string $key
     * @param int $exp
     * @return string
     */
    public static function refreshToken(string $jwt, string $key = null, $exp = 3600): string
    {
        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            throw new \UnexpectedValueException('Wrong number of segments');
        }
        list($headb64, $bodyb64, $cryptob64) = $tks;
        if (null === ($header = JWT::jsonDecode(JWT::urlsafeB64Decode($headb64)))) {
            throw new \UnexpectedValueException('Invalid segment encoding');
        }
        if (null === ($payload = JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64)))) {
            throw new \UnexpectedValueException('Invalid segment encoding');
        }

        $algo = $header->alg;
        $timestamp = time();
        $leeway = (isset($payload->nbf) && isset($payload->iat)) ? ($payload->nbf - $payload->iat) : static::$leeway;
        
        
        // Check that this token has been created before 'now'. This prevents
        // using tokens that have been created for later use (and haven't
        // correctly used the nbf claim).
        if (isset($payload->iat)) {
            $payload->iat = $timestamp;
        }
        // Check if the nbf if it is defined. This is the time that the
        // token can actually be used.
        if (isset($payload->nbf)) {
            $payload->nbf = $timestamp + $leeway;
        }

        // Check if this token has expired.
        if (isset($payload->exp)) {
            $payload->exp = $timestamp + $leeway + $exp;
        }

        return JWT::encode($payload, $key, $algo);
    }
}
