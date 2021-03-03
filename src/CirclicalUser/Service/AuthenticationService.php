<?php

namespace CirclicalUser\Service;

use CirclicalUser\Entity\UserResetToken;
use CirclicalUser\Exception\AuthenticationDataException;
use CirclicalUser\Exception\AuthenticationHashException;
use CirclicalUser\Exception\InvalidResetTokenException;
use CirclicalUser\Exception\PasswordResetProhibitedException;
use CirclicalUser\Exception\PersistedUserRequiredException;
use CirclicalUser\Exception\TooManyRecoveryAttemptsException;
use CirclicalUser\Exception\WeakPasswordException;
use CirclicalUser\Provider\AuthenticationProviderInterface;
use CirclicalUser\Provider\AuthenticationRecordInterface;
use CirclicalUser\Provider\PasswordCheckerInterface;
use CirclicalUser\Provider\UserInterface as User;
use CirclicalUser\Exception\BadPasswordException;
use CirclicalUser\Exception\EmailUsernameTakenException;
use CirclicalUser\Exception\MismatchedEmailsException;
use CirclicalUser\Exception\NoSuchUserException;
use CirclicalUser\Exception\UsernameTakenException;
use CirclicalUser\Provider\UserProviderInterface;
use CirclicalUser\Provider\UserResetTokenInterface;
use CirclicalUser\Provider\UserResetTokenProviderInterface;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use Zend\Http\PhpEnvironment\RemoteAddress;

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
    public const COOKIE_USER = '_sessiona';

    /**
     * SHA256 hmac combination that verifies COOKIE_VERIFY_A
     */
    public const COOKIE_VERIFY_A = '_sessionb';

    /**
     * SHA256 hmac combination that verifies a randomly generated cookie
     */
    public const COOKIE_VERIFY_B = '_sessionc';

    /**
     * Prefix for hash cookies, mmm.
     */
    public const COOKIE_HASH_PREFIX = '__cu';

    /**
     * Stores the user identity after having been authenticated.
     */
    private ?User $identity;

    private AuthenticationProviderInterface $authenticationProvider;

    private UserProviderInterface $userProvider;

    /**
     * A config-defined key that's used to encrypt ID cookie
     */
    private HiddenString $systemEncryptionKey;

    /**
     * Should the cookie expire at the end of the session?
     */
    private bool $transient;

    /**
     * Should the cookie be marked as secure?
     */
    private bool $secure;

    private PasswordCheckerInterface $passwordChecker;

    private ?UserResetTokenProviderInterface $resetTokenProvider;

    /**
     * If password reset is enabled, do we validate the browser fingerprint?
     */
    private bool $validateFingerprint;

    /**
     * If password reset is enabled, do we validate the user IP address?
     */
    private bool $validateIp;

    /**
     * Samesite cookie attribute
     */
    private string $sameSite;


    /**
     * AuthenticationService constructor.
     *
     * @param AuthenticationProviderInterface  $authenticationProvider
     * @param UserProviderInterface            $userProvider
     * @param ?UserResetTokenProviderInterface $resetTokenProvider  If not null, permit password reset
     * @param string                           $systemEncryptionKey The raw material of a Halite-generated encryption key, stored in config.
     * @param bool                             $transient           True if cookies should expire at the end of the session (zero value, for expiry)
     * @param bool                             $secure              True if cookies should be marked as 'Secure', enforced as 'true' in production by this service's Factory
     * @param PasswordCheckerInterface         $passwordChecker     Optional, a password checker implementation
     * @param bool                             $validateFingerprint If password reset is enabled, do we validate the browser fingerprint?
     * @param bool                             $validateIp          If password reset is enabled, do we validate the user IP address?
     * @param string                           $sameSite            Should be one of 'None', 'Lax' or 'Strict'.
     */
    public function __construct(
        AuthenticationProviderInterface $authenticationProvider,
        UserProviderInterface $userProvider,
        ?UserResetTokenProviderInterface $resetTokenProvider,
        string $systemEncryptionKey,
        bool $transient,
        bool $secure,
        PasswordCheckerInterface $passwordChecker,
        bool $validateFingerprint,
        bool $validateIp,
        string $sameSite
    ) {
        $this->identity = null;
        $this->authenticationProvider = $authenticationProvider;
        $this->userProvider = $userProvider;
        $this->systemEncryptionKey = new HiddenString($systemEncryptionKey);
        $this->transient = $transient;
        $this->secure = $secure;
        $this->passwordChecker = $passwordChecker;
        $this->resetTokenProvider = $resetTokenProvider;
        $this->validateFingerprint = $validateFingerprint;
        $this->validateIp = $validateIp;
        $this->sameSite = $sameSite;
    }

    /**
     * Check to see if a user is logged in
     * @return bool
     */
    public function hasIdentity(): bool
    {
        return $this->getIdentity() !== null;
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
     * Implemented for fringe cases where you need conditional behavior.  Recommend relying
     * on the configuration files wherever possible.
     */
    public function setValidateFingerprint(bool $validateFingerprint): void
    {
        $this->validateFingerprint = $validateFingerprint;
    }

    /**
     * Implemented for fringe cases where you need conditional behavior.  Recommend relying
     * on the configuration files wherever possible.
     */
    public function setValidateIp(bool $validateIp): void
    {
        $this->validateIp = $validateIp;
    }


    public function getPasswordChecker(): PasswordCheckerInterface
    {
        return $this->passwordChecker;
    }

    /**
     * Passed in by a successful form submission, should set proper auth cookies if the identity verifies.
     * The login should work with both username, and email address.
     *
     * @throws BadPasswordException Thrown when the password doesn't work
     * @throws NoSuchUserException Thrown when the user can't be identified
     */
    public function authenticate(string $username, string $password): User
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
            // might have been discovered earlier
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
            }

            throw new NoSuchUserException();
        }

        throw new BadPasswordException();
    }


    /**
     * Change an auth record username given a user id and a new username.
     * Note - in this case username is email.
     *
     * @throws NoSuchUserException Thrown when the user's authentication records couldn't be found
     * @throws UsernameTakenException
     */
    public function changeUsername(User $user, string $newUsername): AuthenticationRecordInterface
    {
        $auth = $this->authenticationProvider->findByUserId($user->getId());

        if (!$auth) {
            throw new NoSuchUserException();
        }

        // check to see if already taken
        if ($otherAuth = $this->authenticationProvider->findByUsername($newUsername)) {
            if ($auth === $otherAuth) {
                return $auth;
            }

            throw new UsernameTakenException();
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
        $sessionKey = new HiddenString($authentication->getRawSessionKey());
        $userKey = new EncryptionKey($sessionKey);
        $hashCookieName = hash_hmac('sha256', $sessionKey . $authentication->getUsername(), $systemKey);
        $userTuple = base64_encode(Crypto::encrypt(new HiddenString($authentication->getUserId() . ':' . $hashCookieName), $systemKey));
        $hashCookieContents = base64_encode(Crypto::encrypt(new HiddenString(time() . ':' . $authentication->getUserId() . ':' . $authentication->getUsername()), $userKey));

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
     */
    private function setCookie(string $name, string $value)
    {
        $expiry = $this->transient ? 0 : (time() + 2629743);
        $sessionParameters = session_get_cookie_params();
        setcookie(
            $name,
            $value,
            [
                'expires' => $expiry,
                'path' => '/',
                'domain' => $sessionParameters['domain'],
                'secure' => $this->secure,
                'httponly' => true,
                'samesite' => $this->sameSite,
            ]
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
     * @return User|null
     * @see self::setSessionCookies
     */
    public function getIdentity()
    {
        if ($this->identity) {
            return $this->identity;
        }

        if (!isset($_COOKIE[self::COOKIE_VERIFY_A], $_COOKIE[self::COOKIE_VERIFY_B], $_COOKIE[self::COOKIE_USER])) {
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
                throw new \LogicException();
            }

            // paranoid, make sure we have everything we need
            @list($cookieUserId, $hashCookieSuffix) = @explode(":", $userTuple, 2);
            if (!is_numeric($cookieUserId) || !trim($hashCookieSuffix)) {
                throw new AuthenticationDataException();
            }

            if (!($auth = $this->authenticationProvider->findByUserId($cookieUserId))) {
                throw new NoSuchUserException();
            }

            $hashCookieName = self::COOKIE_HASH_PREFIX . $hashCookieSuffix;

            //
            // 2. Check the hashCookie for corroborating data
            //
            if (!isset($_COOKIE[$hashCookieName])) {
                throw new AuthenticationDataException();
            }

            $userKey = new EncryptionKey(new HiddenString($auth->getRawSessionKey()));
            $hashPass = hash_equals(
                hash_hmac('sha256', $_COOKIE[$hashCookieName], $userKey),
                $_COOKIE[self::COOKIE_VERIFY_B]
            );

            if (!$hashPass) {
                throw new AuthenticationHashException();
            }

            //
            // 3. Decrypt the hash cookie with the user key
            //
            $hashedCookieContents = Crypto::decrypt(base64_decode($_COOKIE[$hashCookieName]), $userKey);
            if (!(substr_count($hashedCookieContents, ':') === 2)) {
                throw new AuthenticationDataException();
            }

            [, $hashedUserId, $hashedUsername] = explode(':', $hashedCookieContents);
            if ($hashedUserId !== $cookieUserId) {
                throw new AuthenticationHashException();
            }

            if ($hashedUsername !== $auth->getUsername()) {
                throw new AuthenticationHashException();
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
        $killTime = time() - 3600;
        foreach ($_COOKIE as $cookieName => $value) {
            if ($cookieName !== $skipCookie && strpos($cookieName, self::COOKIE_HASH_PREFIX) !== false) {
                setcookie($cookieName, '', $killTime, '/', $sp['domain'], false, true);
            }
        }
    }

    /**
     * @param string $password
     * @param User   $user Used by some password checkers to provide better checking
     *
     * @throws WeakPasswordException
     */
    private function enforcePasswordStrength(string $password, User $user)
    {
        $userData = array_values(array_filter(array_values((array)$user), 'is_string'));
        if (!$this->passwordChecker->isStrongPassword($password, $userData)) {
            throw new WeakPasswordException();
        }
    }


    /**
     * Reset this user's password
     *
     * @param User   $user        The user to whom this password gets assigned
     * @param string $newPassword Cleartext password that's being hashed
     *
     * @throws NoSuchUserException
     * @throws WeakPasswordException
     */
    public function resetPassword(User $user, string $newPassword)
    {
        $this->enforcePasswordStrength($newPassword, $user);

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
     * Validate user password
     *
     * @param User   $user     The user to validate password for
     * @param string $password Cleartext password that'w will be verified
     *
     * @return bool
     *
     * @throws NoSuchUserException
     */
    public function verifyPassword(User $user, string $password): bool
    {
        $auth = $this->authenticationProvider->findByUserId($user->getId());
        if (!$auth) {
            throw new NoSuchUserException();
        }

        return password_verify($password, $auth->getHash());
    }


    /**
     * Register a new user into the auth tables, and, log them in. Essentially calls registerAuthenticationRecord
     * and then stores the necessary cookies and identity into the service.
     *
     * @param User   $user
     * @param string $username
     * @param string $password
     *
     * @return AuthenticationRecordInterface
     * @throws PersistedUserRequiredException
     */
    public function create(User $user, string $username, string $password): AuthenticationRecordInterface
    {
        $this->enforcePasswordStrength($password, $user);

        $auth = $this->registerAuthenticationRecord($user, $username, $password);
        $this->setSessionCookies($auth);
        $this->setIdentity($user);

        return $auth;
    }


    /**
     * Very similar to create, except that it won't log the user in.  This was created to satisfy circumstances where
     * you are creating users from an admin panel for example.  This function is also used by create.
     *
     * Note, this method does not check password strength!
     *
     * @param User   $user
     * @param string $username
     * @param string $password
     *
     * @return AuthenticationRecordInterface
     * @throws EmailUsernameTakenException
     * @throws MismatchedEmailsException
     * @throws PersistedUserRequiredException
     * @throws UsernameTakenException
     */
    public function registerAuthenticationRecord(User $user, string $username, string $password): AuthenticationRecordInterface
    {
        if (!$user->getId()) {
            throw new PersistedUserRequiredException("Your user must have an ID before you can create auth records with it");
        }

        if ($this->authenticationProvider->findByUsername($username)) {
            throw new UsernameTakenException();
        }

        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            if ($user->getEmail() !== $username) {
                throw new MismatchedEmailsException();
            }

            if ($emailUser = $this->userProvider->findByEmail($username)) {
                if ($emailUser !== $user) {
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
        $auth->setRawSessionKey($key->getRawKeyMaterial());
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
        $killTime = time() - 3600;
        foreach ([self::COOKIE_USER, self::COOKIE_VERIFY_A, self::COOKIE_VERIFY_B] as $cookieName) {
            setcookie($cookieName, '', $killTime, '/', $sp['domain'], false, true);
        }

        $this->identity = null;
    }

    /**
     * Forgot-password mechanisms are a potential back door; but they're needed.  This only takes care
     * of hash generation.
     *
     * @param User $user
     *
     * @return UserResetToken
     * @throws NoSuchUserException
     * @throws PasswordResetProhibitedException
     * @throws TooManyRecoveryAttemptsException
     */
    public function createRecoveryToken(User $user): UserResetToken
    {
        if (!$this->resetTokenProvider) {
            throw new PasswordResetProhibitedException('The configuration currently prohibits the resetting of passwords!');
        }

        $auth = $this->authenticationProvider->findByUserId($user->getId());
        if (!$auth) {
            throw new NoSuchUserException();
        }

        if ($this->resetTokenProvider->getRequestCount($auth) > 5) {
            throw new TooManyRecoveryAttemptsException();
        }

        $this->resetTokenProvider->invalidateUnusedTokens($auth);

        $remote = new RemoteAddress();
        $remote->setUseProxy(true);
        $token = new UserResetToken($auth, $remote->getIpAddress());
        $this->resetTokenProvider->save($token);

        return $token;
    }


    /**
     * @throws InvalidResetTokenException
     * @throws NoSuchUserException
     * @throws PasswordResetProhibitedException
     * @throws \CirclicalUser\Exception\WeakPasswordException
     */
    public function changePasswordWithRecoveryToken(User $user, int $tokenId, string $token, string $newPassword)
    {
        if (!$this->resetTokenProvider) {
            throw new PasswordResetProhibitedException('The configuration currently prohibits the resetting of passwords!');
        }

        $auth = $this->authenticationProvider->findByUserId($user->getId());
        if (!$auth) {
            throw new NoSuchUserException();
        }

        $remote = new RemoteAddress();
        $remote->setUseProxy(true);

        $resetToken = $this->resetTokenProvider->get($tokenId);
        if (!$resetToken) {
            throw new InvalidResetTokenException();
        }

        if (!$resetToken->isValid($auth, $token, $remote->getIpAddress(), $this->validateFingerprint, $this->validateIp)) {
            throw new InvalidResetTokenException();
        }

        $this->resetPassword($user, $newPassword);
        $resetToken->setStatus(UserResetTokenInterface::STATUS_USED);
        $this->resetTokenProvider->update($resetToken);
    }
}
