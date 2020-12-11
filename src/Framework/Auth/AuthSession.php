<?php

namespace Framework\Auth;

use Framework\Auth;
use Framework\Auth\User;
use Framework\Session\SessionInterface;
use Framework\Auth\Repository\UserRepository;

class AuthSession implements Auth
{
    /**
     * Cookie options
     *
     * @var array
     */
    private $options = [
        'sessionName' => 'auth.user',
        'field' => 'username'
    ];

    /**
     * 
     *
     * @var SessionInterface
     */
    private $session;

    /**
     * 
     *
     * @var User
     */
    private $user;

    /**
     * 
     *
     * @var UserRepository
     */
    protected $userRepository;

    public function __construct(
        SessionInterface $session,
        UserRepository $userRepository,
        array $options = []
    )
    {
        $this->session = $session;
        $this->userRepository = $userRepository;
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
    }

    /**
     *
     * @param string $username
     * @param string $password
     * @return User|null
     */
    public function login(string $username, string $password): ?User
    {
        if (empty($username) || empty($password)) {
            return null;
        }

        /** @var User $user */
        $user = $this->userRepository->getUser($this->options['field'], $username);
        if ($user && password_verify($password, $user->getPassword())) {
            $this->setUser($user);
            return $user;
        }
        return null;
    }

    /**
     *
     * @return void
     */
    public function logout(): void
    {
        $this->session->delete($this->options['sessionName']);
        $this->user = null;
    }

    public function getUser(): ?User
    {
        $userId = $this->session->get($this->options['sessionName']);

        if ($userId) {
            if ($this->user && (int) $this->user->getId() === (int) $userId) {
                return $this->user;
            }
            try {
                $this->user = $this->userRepository->getUser('id', $userId);
                return $this->user;
            } catch (\Exception $e) {
                $this->session->delete($this->options['sessionName']);
                return null;
            }
        }
        return null;    
    }

    /**
     * 
     * @param User $user
     * @return Auth
     */
    public function setUser(User $user): Auth
    {
        $this->session->set($this->options['sessionName'], $user->getId());
        $this->user = $user;
        return $this;
    }
}
