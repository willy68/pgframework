<?php

namespace Framework\Auth\RememberMe;

use Framework\Auth\Repository\UserRepositoryInterface;

abstract class AbstractRememberMe
{
    use RememberMeCookieAwareTraits;

    /**
     * Algo de hashage
     *
     * @var string
     */
    protected $algo = 'sha256';

    /**
     * Clef privée de hashage
     *
     * @var string
     */
    protected $salt;

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
}
