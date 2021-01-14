<?php

namespace Framework\Auth\RememberMe;

use RuntimeException;
use Framework\Auth\User;
use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Psr\Http\Message\ServerRequestInterface;
use Framework\Auth\Repository\UserRepositoryInterface;
use Framework\Auth\Repository\TokenRepositoryInterface;

class AbstractRememberMeDatabase //implements RememberMeInterface
{
    use RememberMeCookieAwareTraits;

    /**
     * Algo de hashage
     *
     * @var string
     */
    private $algo = 'sha256';

    /**
     * Clef privée de hashage
     *
     * @var string
     */
    private $salt;

    /**
     * Cookie options
     *
     * @var array
     */
    protected $options = [
        'name' => 'auth_login',
        'field' => 'username',
        'lifetime' => 3600 * 24 * 3,
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'httpOnly' => true,
        'samesite' => null,
    ];

    /**
     * User repository
     *
     * @var UserRepositoryInterface
     */
    protected $userRepository;
    /**
     * Token Repository
     *
     * @var TokenRepositoryInterface
     */
    private $tokenRepository;

    /**
     * Constructeur: ajoute l'option pour le nom du cookie du mot de passe aléatoire
     *
     * @param UserRepositoryInterface $userRepository
     * @param TokenRepositoryInterface $tokenRepository
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        TokenRepositoryInterface $tokenRepository
    ) {
        $this->userRepository = $userRepository;
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Crée un cookie d'authentification,
     * un token en base données et un cookie avec un mot de passe aléatoire
     *
     * @param ResponseInterface $response
     * @param User $user
     * @return ResponseInterface
     */
    public function onLogin(ResponseInterface $response, User $user): ResponseInterface
    {

        $series = base64_encode(random_bytes(64));
        $randomPassword = base64_encode(random_bytes(64));

        //["series', 'credential', 'random_password', 'expiration_date']
        $this->tokenRepository->saveToken(
            [
                'series' => $series,
                'credential' => $user->getUsername(),
                'random_password' => $randomPassword,
                'expiration_date' => new \DateTime()
            ]
        );

        // Create random password cookie [$series, $username, $randomPassword]
        $cookie = SetCookie::create($this->options['name'])
            ->withValue($this->encodeCookie([$series, $user->getUsername(), $randomPassword]))
            ->withExpires(time() +  $this->options['lifetime'])
            ->withPath($this->options['path'])
            ->withDomain($this->options['domain'])
            ->withSecure($this->options['secure'])
            ->withHttpOnly($this->options['httpOnly']);
        return FigResponseCookies::set($response, $cookie);
    }

    /**
     * Connecte l'utilisateur automatiquement avec le cookie reçu de la requète et
     * vérifie le token en base de données s'il est valide
     *
     * @param ServerRequestInterface $request
     * @return User|null
     */
    public function autoLogin(ServerRequestInterface $request): ?User
    {
        $cookie = FigRequestCookies::get($request, $this->options['name']);
        if (!$cookie->getValue()) {
            return null;
        }

        $cookieParts = $this->decodeCookie($cookie->getValue());
        if (3 !== \count($cookieParts)) {
            return null;
        }

        [$series, $username, $randomPassword] = $cookieParts;
        $token = $this->tokenRepository->getToken($series);

        $user = $this->userRepository->getUser($this->options['field'], $token->getCredential());
        if ($user) {

            $authenticate = true;
            //password corrupted
            if (!hash_equals($token->getRandomPassword(), $randomPassword)) {
                $authenticate = false;
            }
            // expiration outdated
            if ($token->getExpirationDate()->getTimestamp() + $this->options['lifetime'] < time()) {
                $authenticate = false;
            }
            // Remove token from database
            if (!$authenticate) {
                $this->tokenRepository->deleteToken($token->getId());
                return null;
            }

            //["series', 'credential', 'random_password', 'expiration_date']
            $this->tokenRepository->updateToken(['expiration_date' => new \DateTime()], $token->getId());

        }
        return $user;
    }

    /**
     * Déconnecte l'utilisateur et invalide le cookie dans la response et
     * marque le token en base de données expiré
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function onLogout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $cookie = FigRequestCookies::get($request, $this->options['name']);
        if ($cookie->getValue()) {

            $cookieParts = $this->decodeCookie($cookie->getValue());
            if (3 === \count($cookieParts)) {
    
                [$series] = $cookieParts;
    
                $token = $this->tokenRepository->getToken($series);
    
                if ($token) {
                    // Delete token from database
                    $this->tokenRepository->deleteToken($token->getId());
                }
                // Delete cookie
                $cookiePassword = SetCookie::create($this->options['name'])
                    ->withValue('')
                    ->withExpires(time() - 3600)
                    ->withPath($this->options['path'])
                    ->withDomain($this->options['domain'])
                    ->withSecure($this->options['secure'])
                    ->withHttpOnly($this->options['httpOnly']);
                $response = FigResponseCookies::set($response, $cookiePassword);
            }
        }
        return $response;
    }

    /**
     * Renouvelle la date d'expiration du cookie dans la response et le token en base de données
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function resume(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Get $credential
        $cookie = FigRequestCookies::get($request, $this->options['name']);
        if ($cookie->getValue()) {
            // Set new random password cookie
            $cookie = SetCookie::create($this->options['name'])
                ->withValue($cookie->getValue())
                ->withExpires(time() +  $this->options['lifetime'])
                ->withPath($this->options['path'])
                ->withDomain($this->options['domain'])
                ->withSecure($this->options['secure'])
                ->withHttpOnly($this->options['httpOnly']);
            $response = FigResponseCookies::set($response, $cookie);
        }
        return $response;
    }

    /**
     * Modifie le tableau d'options du cookie
     *
     * @param array $options
     * @return AbstractRememberMeDatabase
     */
    public function setOptions(array $options = []): AbstractRememberMeDatabase
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        return $this;
    }
}
