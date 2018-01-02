<?php


namespace CirclicalUser\Provider;


interface CookieNameProviderInterface
{
    /**
     * User cookie, which is verified by VerificationCookieName, and contains the name of a randomly generated cookie
     */
    public function getUserCookieName(): string;

    /**
     * SHA256 hmac combination that verifies UserCookieName
     */
    public function getVerificationCookieName(): string;

    /**
     * SHA256 hmac combination that verifies a randomly generated cookie
     */
    public function getRedundancyCookieName(): string;

    /**
     * Prefix for hash cookies, mmm.
     */
    public function getHashPrefixCookieName(): string;
}