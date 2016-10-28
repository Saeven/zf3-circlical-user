<?php

namespace CirclicalUser\Service;


use CirclicalUser\Provider\AuthenticationProviderInterface;
use CirclicalUser\Provider\AuthenticationRecordInterface;
use CirclicalUser\Provider\UserInterface as User;
use CirclicalUser\Exception\BadPasswordException;
use CirclicalUser\Exception\EmailUsernameTakenException;
use CirclicalUser\Exception\MismatchedEmailsException;
use CirclicalUser\Exception\NoSuchUserException;
use CirclicalUser\Exception\UsernameTakenException;
use CirclicalUser\Mapper\AuthenticationMapper;
use CirclicalUser\Provider\UserProviderInterface;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;


/**
 * Cookie-based authentication service that gives the option of using transient sessions.  It also allows
 * you to log in using email or username.  Note, if you permit an auth model where you allow users to register
 * emails as usernames, it's your responsibility to trigger the username change when the email change occurs.
 *
 * Class AuthenticationService
 */
class AuthenticationService
{
    /**
     * User cookie, which is verified by COOKIE_VERIFY_A, and contains the name of a randomly generated cookie
     */
    const COOKIE_USER = '_sessiona';

    /**
     * SHA256 hmac combination that verifies COOKIE_VERIFY_A
     */
    const COOKIE_VERIFY_A = '_sessionb';

    /**
     * SHA256 hmac combination that verifies a randomly generated cookie
     */
    const COOKIE_VERIFY_B = '_sessionc';


    /**
     * Prefix for hash cookies, mmm.
     */
    const COOKIE_HASH_PREFIX = '__cu';

    /**
     * Stores the user identity after having been authenticated.
     *
     * @var User
     */
    private $identity;

    /**
     * @var AuthenticationMapper
     */
    private $authenticationProvider;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var string A config-defined key that's used to encrypt ID cookie
     */
    private $systemEncryptionKey;

    /**
     * @var bool Should the cookie expire at the end of the session?
     */
    private $transient;

    /**
     * @var bool Should the cookie be marked as https only?
     */
    private $secure;


    /**
     * AuthenticationService constructor.
     *
     * @param AuthenticationProviderInterface $authenticationProvider
     * @param UserProviderInterface           $userProvider
     * @param string                          $systemEncryptionKey The raw material of a Halite-generated encryption key, stored in config.
     * @param bool                            $transient           True if cookies should expire at the end of the session (zero value, for expiry)
     * @param bool                            $secure              True if cookies should be marked as 'Secure', enforced as 'true' in production by this service's Factory
     */
    public function __construct(AuthenticationProviderInterface $authenticationProvider, UserProviderInterface $userProvider, $systemEncryptionKey, $transient, $secure)
    {
        $this->authenticationProvider = $authenticationProvider;
        $this->userProvider = $userProvider;
        $this->systemEncryptionKey = $systemEncryptionKey;
        $this->transient = $transient;
        $this->secure = $secure;
        $this->identity = null;
    }

    /**
     * Check to see if a user is logged in
     * @return bool
     */
    public function hasIdentity(): bool
    {
        return $this->getIdentity() != null;
    }

    /**
     * Authenticate a user
     *
     * @param User $user
     */
    private function setIdentity(User $user)
    {
        $this->identity = $user;
    }


    /**
     * Passed in by a successful form submission, should set proper auth cookies if the identity verifies.
     * The login should work with both username, and email address.
     *
     * @param $username
     * @param $password
     *
     * @return User
     *
     * @throws BadPasswordException Thrown when the password doesn't work
     * @throws NoSuchUserException Thrown when the user can't be identified
     */
    public function authenticate($username, $password): User
    {
        $auth = $this->authenticationProvider->findByUsername($username);
        $user = null;

        if (!$auth && filter_var($username, FILTER_VALIDATE_EMAIL)) {
            if ($user = $this->userProvider->findByEmail($username)) {
                $auth = $this->authenticationProvider->findByUserId($user->getId());
            }
        }

        if (!$auth) {
            throw new NoSuchUserException();
        }

        if (password_verify($password, $auth->getHash())) {

            if (!$user) {
                $user = $this->userProvider->getUser($auth->getUserId());
            }

            if ($user) {
                $this->resetAuthenticationKey($auth);
                $this->setSessionCookies($auth);
                $this->setIdentity($user);

                if (password_needs_rehash($auth->getHash(), PASSWORD_DEFAULT)) {
                    $auth->setHash(password_hash($password, PASSWORD_DEFAULT));
                    $this->authenticationProvider->update($auth);
                }

                return $user;
            } else {
                throw new NoSuchUserException();
            }
        }

        throw new BadPasswordException();
    }


    /**
     * Change an auth record username given a user id and a new username.
     * Note - in this case username is email.
     *
     * @param User $user
     * @param      $newUsername
     *
     * @return AuthenticationRecordInterface
     * @throws NoSuchUserException Thrown when the user's authentication records couldn't be found
     * @throws UsernameTakenException
     */
    public function changeUsername(User $user, $newUsername): AuthenticationRecordInterface
    {
        /** @var AuthenticationRecordInterface $auth */
        $auth = $this->authenticationProvider->findByUserId($user->getId());

        if (!$auth) {
            throw new NoSuchUserException();
        }

        // check to see if already taken
        if ($otherAuth = $this->authenticationProvider->findByUsername($newUsername)) {
            if ($auth == $otherAuth) {
                return $auth;
            } else {
                throw new UsernameTakenException();
            }
        }

        $auth->setUsername($newUsername);
        $this->authenticationProvider->update($auth);

        return $auth;
    }


    /**
     * Set the auth session cookies that can be used to regenerate the session on subsequent visits
     *
     * @param AuthenticationRecordInterface $authentication
     */
    private function setSessionCookies(AuthenticationRecordInterface $authentication)
    {
        $systemKey = new EncryptionKey($this->systemEncryptionKey);
        $userKey = new EncryptionKey($authentication->getSessionKey());
        $hashCookieName = hash_hmac('sha256', $authentication->getSessionKey() . $authentication->getUsername(), $systemKey);
        $userTuple = base64_encode(Crypto::encrypt($authentication->getUserId() . ":" . $hashCookieName, $systemKey));
        $hashCookieContents = base64_encode(Crypto::encrypt(time() . ':' . $authentication->getUserId() . ':' . $authentication->getUsername(), $userKey));

        //
        // 1 - Set the cookie that contains the user ID, and hash cookie name
        //
        $this->setCookie(
            self::COOKIE_USER,
            $userTuple
        );

        //
        // 2 - Set the cookie with random name, that contains a verification hash, that's a function of the switching session key
        //
        $this->setCookie(
            self::COOKIE_HASH_PREFIX . $hashCookieName,
            $hashCookieContents
        );

        //
        // 3 - Set the sign cookie, that acts as a safeguard against tampering
        //
        $this->setCookie(
            self::COOKIE_VERIFY_A,
            hash_hmac('sha256', $userTuple, $systemKey)
        );

        //
        // 4 - Set a sign cookie for the hashCookie's values
        //
        $this->setCookie(
            self::COOKIE_VERIFY_B,
            hash_hmac('sha256', $hashCookieContents, $userKey)
        );
    }

    /**
     * Set a cookie with values defined by configuration
     *
     * @param $name
     * @param $value
     */
    private function setCookie($name, $value)
    {
        $expiry = $this->transient ? 0 : (time() + 2629743);
        $sessionParameters = session_get_cookie_params();
        setcookie(
            $name,
            $value,
            $expiry,
            '/',
            $sessionParameters['domain'],
            $this->secure,
            true
        );
    }

    /**
     * Rifle through 4 cookies, ensuring that all details line up.  If they do, we accept that the cookies authenticate
     * a specific user.
     *
     * Some notes:
     *
     *  - COOKIE_VERIFY_A is a do-not-decrypt check of COOKIE_USER
     *  - COOKIE_VERIFY_B is a do-not-decrypt check of the random-named-cookie specified by COOKIE_USER
     *  - COOKIE_USER has its contents encrypted by the system key
     *  - the random-named-cookie has its contents encrypted by the user key
     *
     * @see self::setSessionCookies
     * @return User|null
     */
    public function getIdentity()
    {
        if ($this->identity) {
            return $this->identity;
        }

        if (!isset($_COOKIE[self::COOKIE_VERIFY_A])) {
            return null;
        }

        if (!isset($_COOKIE[self::COOKIE_USER])) {
            return null;
        }

        $systemKey = new EncryptionKey($this->systemEncryptionKey);
        $verificationCookie = $_COOKIE[self::COOKIE_VERIFY_A];
        $hashPass = hash_equals(
            hash_hmac('sha256', $_COOKIE[self::COOKIE_USER], $systemKey),
            $verificationCookie
        );

        //
        // 1. Is the verify cookie still equivalent to the user cookie, if so, do not decrypt
        //
        if (!$hashPass) {
            return null;
        }

        //
        // 2. If the user cookie was not tampered with, decrypt its contents with the system key
        //
        try {

            $userTuple = Crypto::decrypt(base64_decode($_COOKIE[self::COOKIE_USER]), $systemKey);

            if (strpos($userTuple, ':') === false) {
                throw new \Exception();
            }

            // paranoid, make sure we have everything we need
            @list($cookieUserId, $hashCookieSuffix) = @explode(":", $userTuple, 2);
            if (!isset($cookieUserId) || !isset($hashCookieSuffix) || !is_numeric($cookieUserId) || !trim($hashCookieSuffix)) {
                throw new \Exception();
            }

            /** @var AuthenticationRecordInterface $auth */
            if (!($auth = $this->authenticationProvider->findByUserId($cookieUserId))) {
                throw new \Exception();
            }

            $hashCookieName = self::COOKIE_HASH_PREFIX . $hashCookieSuffix;

            //
            // 2. Check the hashCookie for corroborating data
            //
            if (!isset($_COOKIE[$hashCookieName])) {
                throw new \Exception();
            }

            $userKey = new EncryptionKey($auth->getSessionKey());
            $hashPass = hash_equals(
                hash_hmac('sha256', $_COOKIE[$hashCookieName], $userKey),
                $_COOKIE[self::COOKIE_VERIFY_B]
            );

            if (!$hashPass) {
                throw new \Exception();
            }

            //
            // 3. Decrypt the hash cookie with the user key
            //
            $hashedCookieContents = Crypto::decrypt(base64_decode($_COOKIE[$hashCookieName]), $userKey);
            if (!substr_count($hashedCookieContents, ':') == 2) {
                throw new \Exception();
            }

            list(, $hashedUserId, $hashedUsername) = explode(':', $hashedCookieContents);
            if ($hashedUserId != $cookieUserId) {
                throw new \Exception();
            }

            if ($hashedUsername != $auth->getUsername()) {
                throw new \Exception();
            }

            $this->purgeHashCookies($hashCookieName);

            //
            // 4. Cookies check out - it's up to the user provider now
            //
            $user = $this->userProvider->getUser($auth->getUserId());
            if ($user) {
                $this->setIdentity($user);

                return $this->identity;
            }

        } catch (\Exception $x) {
            $this->purgeHashCookies();
        }

        return null;
    }

    /**
     * Remove all hash cookies, potentially saving one
     *
     * @param string|null $skipCookie
     */
    private function purgeHashCookies(string $skipCookie = null)
    {
        $sp = session_get_cookie_params();
        foreach ($_COOKIE as $cookieName => $value) {
            if ($cookieName != $skipCookie && strpos($cookieName, self::COOKIE_HASH_PREFIX) !== false) {
                setcookie($cookieName, null, null, '/', $sp['domain'], false, true);
            }
        }
    }


    /**
     * Reset this user's password
     *
     * @param User   $user        The user to whom this password gets assigned
     * @param string $newPassword Cleartext password that's being hashed
     *
     * @throws NoSuchUserException
     */
    public function resetPassword(User $user, $newPassword)
    {
        $auth = $this->authenticationProvider->findByUserId($user->getId());
        if (!$auth) {
            throw new NoSuchUserException();
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $auth->setHash($hash);
        $this->resetAuthenticationKey($auth);
        $this->authenticationProvider->update($auth);
    }


    /**
     * Register a new user into the auth tables, and, log them in
     *
     * @param User   $user
     * @param string $username
     * @param string $password
     *
     * @return AuthenticationRecordInterface
     * @throws EmailUsernameTakenException
     * @throws MismatchedEmailsException
     * @throws UsernameTakenException
     */
    public function create(User $user, $username, $password): AuthenticationRecordInterface
    {
        if ($this->authenticationProvider->findByUsername($username)) {
            throw new UsernameTakenException();
        }

        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            if ($user->getEmail() != $username) {
                throw new MismatchedEmailsException();
            }

            if ($emailUser = $this->userProvider->findByEmail($username)) {
                if ($emailUser != $user) {
                    throw new EmailUsernameTakenException();
                }
            }
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $auth = $this->authenticationProvider->create(
            $user->getId(),
            $username,
            $hash,
            KeyFactory::generateEncryptionKey()->getRawKeyMaterial()
        );

        $this->authenticationProvider->save($auth);
        $this->setSessionCookies($auth);
        $this->setIdentity($user);

        return $auth;
    }


    /**
     * Resalt a user's authentication table salt
     *
     * @param AuthenticationRecordInterface $auth
     *
     * @return AuthenticationRecordInterface
     */
    private function resetAuthenticationKey(AuthenticationRecordInterface $auth): AuthenticationRecordInterface
    {
        $key = KeyFactory::generateEncryptionKey();
        $auth->setSessionKey($key->getRawKeyMaterial());
        $this->authenticationProvider->update($auth);

        return $auth;
    }


    /**
     * Logout.  Reset the user authentication key, and delete all cookies.
     */
    public function clearIdentity()
    {
        if ($user = $this->getIdentity()) {
            $auth = $this->authenticationProvider->findByUserId($user->getId());
            $this->resetAuthenticationKey($auth);
        }

        $sp = session_get_cookie_params();
        foreach ([self::COOKIE_USER, self::COOKIE_VERIFY_A, self::COOKIE_VERIFY_B] as $cookieName) {
            setcookie($cookieName, null, null, '/', $sp['domain'], false, true);
        }

        $this->identity = null;
    }

}