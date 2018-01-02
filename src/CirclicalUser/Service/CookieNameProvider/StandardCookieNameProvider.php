<?php

namespace CirclicalUser\Service\CookieNameProvider;

use CirclicalUser\Provider\CookieNameProviderInterface;

class StandardCookieNameProvider implements CookieNameProviderInterface
{

    public function getUserCookieName(): string
    {
        return '_sessiona';
    }

    public function getVerificationCookieName(): string
    {
        return '_sessionb';
    }

    public function getRedundancyCookieName(): string
    {
        return '_sessionc';
    }

    public function getHashPrefixCookieName(): string
    {
        return '__cu';
    }
}