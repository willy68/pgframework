<?php

namespace Framework\Auth\RememberMe;

use Framework\Auth\User;
use Dflydev\FigCookies\SetCookie;
use Framework\Auth\ForbiddenException;
use Psr\Http\Message\ResponseInterface;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Psr\Http\Message\ServerRequestInterface;
use Framework\Auth\Repository\UserRepositoryInterface;

class AbstractRememberMe // implements RememberMeInterface
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
     * User repository
     *
     * @var UserRepositoryInterface
     */
    protected $userRepository;

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

    public function __construct(
        UserRepositoryInterface $userRepository,
        string $salt = ''
    ) {
        $this->userRepository = $userRepository;
        $this->salt = $salt;
    }

    /**
     * Crée un cookie d'authentification
     *
     * @param ResponseInterface $response
     * @param User $user
     * @return ResponseInterface
     */
    public function onLogin(ResponseInterface $response, User $user): ResponseInterface
    {

        $cookieValue = $this->getCookieHash(
            $user->getUsername(),
            $user->getPassword(),
            get_class($user),
            time() + $this->options['lifetime'],
            $this->salt
        );

        $cookie = SetCookie::create($this->options['name'])
            ->withValue($cookieValue)
            ->withExpires(time() + $this->options['lifetime'])
            ->withPath($this->options['path'])
            ->withDomain($this->options['domain'])
            ->withSecure($this->options['secure'])
            ->withHttpOnly($this->options['httpOnly']);
        return FigResponseCookies::set($response, $cookie);
    }

    /**
     * Connecte l'utilisateur automatiquement avec le cookie reçu de la requète
     *
     * @param ServerRequestInterface $request
     * @return User|null
     */
    public function autoLogin(ServerRequestInterface $request): ?User
    {
        $cookie = FigRequestCookies::get($request, $this->options['name']);
        if (($cookieValue = $cookie->getValue())) {
            $cookieParts = $this->decodeCookie($cookieValue);

            if (4 !== \count($cookieParts)) {
                throw new ForbiddenException('The cookie is invalid.');
            }

            [$username, $userClass, $expires, $hash] = $cookieParts;

            if (false === $username = base64_decode($username, true)) {
                throw new ForbiddenException('$username contains a character from outside the base64 alphabet.');
            }

            $user = $this->userRepository->getUser($this->options['field'], $username);

            if (true === hash_equals(hash_hmac($this->algo, $username . $user->getPassword() . $userClass . $expires, $this->salt), $hash)) {
                return $user;
            }
        }
        return null;
    }

    /**
     * Déconnecte l'utilisateur et invalide le cookie dans la response
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function onLogout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $cookie = FigRequestCookies::get($request, $this->options['name']);
        if ($cookie->getValue()) {
            $cookie = SetCookie::create($this->options['name'])
                ->withValue('')
                ->withExpires(time() - 3600)
                ->withPath($this->options['path'])
                ->withDomain($this->options['domain'])
                ->withSecure($this->options['secure'])
                ->withHttpOnly($this->options['httpOnly']);
            $response = FigResponseCookies::set($response, $cookie);
        }
        return $response;
    }

    /**
     * Renouvelle la date d'expiration du cookie dans la response
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function resume(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $cookie = FigRequestCookies::get($request, $this->options['name']);
        if ($cookie->getValue()) {
            $setCookie = SetCookie::create($this->options['name'])
                ->withValue($cookie->getValue())
                ->withExpires(time() + $this->options['lifetime'])
                ->withPath($this->options['path'])
                ->withDomain($this->options['domain'])
                ->withSecure($this->options['secure'])
                ->withHttpOnly($this->options['httpOnly']);
            $response = FigResponseCookies::set($response, $setCookie);
        }
        return $response;
    }

    /**
     * Modifie le tableau d'options du cookie
     *
     * @param array $options
     * @return self
     */
    public function setOptions(array $options = []): self
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        return $this;
    }

    /**
     * Initialise l'algorithme de cryptage si null vaut sha256 par défaut
     *
     * @param string|null $algo
     * @return self
     */
    public function setAlgo(?string $algo = null): self
    {
        if (!is_null($algo)) {
            $availableAlgorithms = hash_algos();
            if (!in_array($algo, $availableAlgorithms, true)) {
                throw new \RuntimeException(sprintf(
                    'The hash type `%s` was not found. Available algorithms are: %s',
                    $algo,
                    implode(', ', $availableAlgorithms)
                ));
            }
            $this->algo = strtolower($algo);
        }
        return $this;
    }

    protected function getCookieHash(
        string $credential,
        string $password,
        string $userClass,
        int $expires
    ): string {
        return $this->encodeCookie([
            base64_encode($credential),
            $userClass,
            $expires,
            hash_hmac($this->algo, $credential . $password . $userClass . $expires, $this->salt)
        ]);
    }
}
