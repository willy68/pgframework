<?php

namespace Framework\Auth;

interface TokenInterface
{
    /**
     * get token id
     * 
     * @return int
     */
    public function getId(): int;

    /**
     * get the unique user credential (ex. username or email)
     * 
     * @return string
     */
    public function getCredential(): string;

    /**
     * get the random pasword hash
     *
     * @return string
     */
    public function getRandomPassword(): string;

    /**
     * get the expiration date
     *
     * @return \DateTime
     */
    public function getExpirationDate(): \DateTime;

    /**
     * get is_expired field as bool
     *
     * @return bool
     */
    public function getIsExpired(): bool;
}
