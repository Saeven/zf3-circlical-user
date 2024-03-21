<?php

declare(strict_types=1);

namespace CirclicalUser\Service;

use CirclicalUser\Entity\UserResetToken;
use CirclicalUser\Exception\AuthenticationDataException;
use CirclicalUser\Exception\AuthenticationHashException;
use CirclicalUser\Exception\BadPasswordException;
use CirclicalUser\Exception\EmailUsernameTakenException;
use CirclicalUser\Exception\InvalidResetTokenException;
use CirclicalUser\Exception\MismatchedEmailsException;
use CirclicalUser\Exception\NoSuchUserException;
use CirclicalUser\Exception\PasswordResetProhibitedException;
use CirclicalUser\Exception\PersistedUserRequiredException;
use CirclicalUser\Exception\TooManyRecoveryAttemptsException;
use CirclicalUser\Exception\UsernameTakenException;
use CirclicalUser\Exception\UserWithoutAuthenticationRecordException;
use CirclicalUser\Exception\WeakPasswordException;
use CirclicalUser\Provider\AuthenticationProviderInterface;
use CirclicalUser\Provider\AuthenticationRecordInterface;
use CirclicalUser\Provider\PasswordCheckerInterface;
use CirclicalUser\Provider\UserInterface as User;
use CirclicalUser\Provider\UserProviderInterface;
use CirclicalUser\Provider\UserResetTokenInterface;
use CirclicalUser\Provider\UserResetTokenProviderInterface;
use Exception;
use Laminas\Http\PhpEnvironment\RemoteAddress;
use LogicException;
use ParagonIE\Halite\Alerts\InvalidKey;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;

use function array_filter;
use function array_values;
use function base64_decode;
use function base64_encode;
use function count;
use function explode;
use function filter_var;
use function hash_equals;
use function hash_hmac;
use function is_numeric;
use function password_hash;
use function password_needs_rehash;
use function password_verify;
use function session_get_cookie_params;
use function setcookie;
use function str_contains;
use function strpos;
use function substr_count;
use function time;
use function trim;

use const FILTER_VALIDATE_EMAIL;
use const PASSWORD_DEFAULT;

/**
 * Cookie-based authentication service that gives the option of using transient sessions.  It also allows
 * you to log in using email or username.  Note, if you permit an auth model where you allow users to register
 * emails as usernames, it's your responsibility to trigger the username change when the email change occurs.
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
     * Non http-only cookie that contains timestamp, can be used for things like JS-detection of inactivity
     * against session expiry
     */
    public const COOKIE_TIMESTAMP = '_sessiont';

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
     * Configure the amount of time added to 'now', for cookie expiry
     */
    private int $authenticationCookieDuration;

    /**
     * @param ?UserResetTokenProviderInterface $resetTokenProvider If not null, permit password reset
     * @param string $systemEncryptionKey The raw material of a Halite-generated encryption key, stored in config.
     * @param bool $transient True if cookies should expire at the end of the session (zero value, for expiry)
     * @param bool $secure True if cookies should be marked as 'Secure', enforced as 'true' in production by this service's Factory
     * @param PasswordCheckerInterface $passwordChecker Optional, a password checker implementation
     * @param bool $validateFingerprint If password reset is enabled, do we validate the browser fingerprint?
     * @param bool $validateIp If password reset is enabled, do we validate the user IP address?
     * @param string $sameSite Should be one of 'None', 'Lax' or 'Strict'.
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
        string $sameSite,
        int $authenticationCookieDuration
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
        $this->authenticationCookieDuration = $authenticationCookieDuration;
    }

    /**
     * Check to see if a user is logged in
     */
    public function hasIdentity(): bool
    {
        return $this->getIdentity() !== null;
    }

    /**
     * Authenticate a user
     */
    private function setIdentity(User $user): void
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
     * @throws BadPasswordException Thrown when the password doesn't work.
     * @throws NoSuchUserException Thrown when the user can't be identified.
     * @throws UserWithoutAuthenticationRecordException If a user was found by email, yet had no matching authentication record.
     * @throws AuthenticationDataException Can be thrown if your database isn't properly structured, e.g. distinct emails.
     */
    public function authenticate(string $username, string $password): User
    {
        $auth = $this->authenticationProvider->findByUsername($username);

        if (!$auth && filter_var($username, FILTER_VALIDATE_EMAIL)) {
            if ($user = $this->userProvider->findByEmail($username)) {
                if (!$auth = $user->getAuthenticationRecord()) {
                    throw new UserWithoutAuthenticationRecordException();
                }

                // should never happen, but here to protect implementations
                if ($auth->getUser() !== $user) {
                    throw new AuthenticationDataException();
                }
            }
        }

        if (!$auth) {
            throw new NoSuchUserException();
        }

        if (password_verify($password, $auth->getHash())) {
            // might have been discovered earlier
            $user = $auth->getUser();
            $this->resetAuthenticationKey($auth);
            $this->setSessionCookies($auth);
            $this->setIdentity($user);

            if (password_needs_rehash($auth->getHash(), PASSWORD_DEFAULT)) {
                $auth->setHash(password_hash($password, PASSWORD_DEFAULT));
                $this->authenticationProvider->update($auth);
            }

            return $user;
        }

        throw new BadPasswordException();
    }

    /**
     * Change an auth record username given a user id and a new username. Note that it may throw a NoSuchUserException when the user's authentication records couldn't be found
     *
     * @throws NoSuchUserException
     * @throws UsernameTakenException
     * @throws UserWithoutAuthenticationRecordException
     */
    public function changeUsername(User $user, string $newUsername): AuthenticationRecordInterface
    {
        $auth = $user->getAuthenticationRecord();

        if (!$auth) {
            throw new UserWithoutAuthenticationRecordException();
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
     * @throws InvalidKey
     */
    private function setSessionCookies(AuthenticationRecordInterface $authentication): void
    {
        $systemKey = new EncryptionKey($this->systemEncryptionKey);
        $sessionKey = new HiddenString($authentication->getRawSessionKey());
        $userKey = new EncryptionKey($sessionKey);
        $hashCookieName = hash_hmac('sha256', $sessionKey->getString() . $authentication->getUsername(), (string) $systemKey);
        $userTuple = base64_encode(Crypto::encrypt(new HiddenString($authentication->getUserId() . ':' . $hashCookieName), $systemKey));
        $hashCookieContents = base64_encode(Crypto::encrypt(new HiddenString(time() . ':' . $authentication->getUserId() . ':' . $authentication->getUsername()), $userKey));

        $expiry = $this->transient ? 0 : time() + $this->authenticationCookieDuration;

        //
        // 1 - Set the cookie that contains the user ID, and hash cookie name
        //
        $this->setCookie(
            self::COOKIE_USER,
            $userTuple,
            $expiry
        );

        //
        // 2 - Set the cookie with random name, that contains a verification hash, that's a function of the switching session key
        //
        $this->setCookie(
            self::COOKIE_HASH_PREFIX . $hashCookieName,
            $hashCookieContents,
            $expiry
        );

        //
        // 3 - Set the sign cookie, that acts as a safeguard against tampering
        //
        $this->setCookie(
            self::COOKIE_VERIFY_A,
            hash_hmac('sha256', $userTuple, (string) $systemKey),
            $expiry
        );

        //
        // 4 - Set a sign cookie for the hashCookie's values
        //
        $this->setCookie(
            self::COOKIE_VERIFY_B,
            hash_hmac('sha256', $hashCookieContents, (string) $userKey),
            $expiry
        );

        //
        // 5 - Set the timeout cookie
        //
        $this->setCookie(
            self::COOKIE_TIMESTAMP,
            (string) $expiry,
            $expiry,
            false
        );
    }

    /**
     * Set a cookie with values defined by configuration
     */
    private function setCookie(string $name, string $value, int $expiry, bool $httpOnly = true): void
    {
        $sessionParameters = session_get_cookie_params();
        setcookie(
            $name,
            $value,
            [
                'expires' => $expiry,
                'path' => '/',
                'domain' => (string) $sessionParameters['domain'],
                'secure' => $this->secure,
                'httponly' => $httpOnly,
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
     * @see self::setSessionCookies
     *
     * @throws InvalidKey
     */
    public function getIdentity(): ?User
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
            hash_hmac('sha256', $_COOKIE[self::COOKIE_USER], (string) $systemKey),
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
            $userTuple = Crypto::decrypt(base64_decode($_COOKIE[self::COOKIE_USER]), $systemKey)->getString();

            if (strpos($userTuple, ':') === false) {
                throw new LogicException();
            }

            // paranoid, make sure we have everything we need
            $explodedUserTuple = @explode(":", $userTuple, 2);
            if (count($explodedUserTuple) !== 2) {
                throw new AuthenticationDataException();
            }

            @[$cookieUserId, $hashCookieSuffix] = $explodedUserTuple;

            /** @psalm-suppress PossiblyNullArgument */
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
                hash_hmac('sha256', $_COOKIE[$hashCookieName], (string) $userKey),
                $_COOKIE[self::COOKIE_VERIFY_B]
            );

            if (!$hashPass) {
                throw new AuthenticationHashException();
            }

            //
            // 3. Decrypt the hash cookie with the user key
            //
            $hashedCookieContents = Crypto::decrypt(base64_decode($_COOKIE[$hashCookieName]), $userKey)->getString();
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
            $user = $auth->getUser();
            $this->setIdentity($user);

            // these could be limited duration cookies, in which case, they must be renewed
            if ($this->authenticationCookieDuration !== 0) {
                $this->setSessionCookies($auth);
            }

            return $this->identity;
        } catch (Exception $x) {
            $this->purgeHashCookies();
        }

        return null;
    }

    /**
     * Remove all hash cookies, potentially saving one
     */
    private function purgeHashCookies(?string $skipCookie = null): void
    {
        $sp = session_get_cookie_params();
        $killTime = time() - 3600;
        foreach ($_COOKIE as $cookieName => $value) {
            if ($cookieName !== $skipCookie && str_contains($cookieName, self::COOKIE_HASH_PREFIX)) {
                setcookie($cookieName, '', $killTime, '/', $sp['domain'], false, true);
            }
        }
    }

    /**
     * @param User $user Used by some password checkers to provide better checking
     * @throws WeakPasswordException
     */
    private function enforcePasswordStrength(string $password, User $user): void
    {
        $userData = array_values(array_filter(array_values((array) $user), 'is_string'));
        if (!$this->passwordChecker->isStrongPassword($password, $userData)) {
            throw new WeakPasswordException();
        }
    }

    /**
     * Reset this user's password
     *
     * @param User $user The user to whom this password gets assigned
     * @param string $newPassword Cleartext password that's being hashed
     * @throws NoSuchUserException
     * @throws WeakPasswordException
     */
    public function resetPassword(User $user, string $newPassword): void
    {
        $this->enforcePasswordStrength($newPassword, $user);

        $auth = $user->getAuthenticationRecord();
        if (!$auth) {
            throw new UserWithoutAuthenticationRecordException();
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $auth->setHash($hash);
        $this->resetAuthenticationKey($auth);
        $this->authenticationProvider->update($auth);
    }

    /**
     * Validate user password
     *
     * @param User $user The user to validate password for
     * @param string $password Cleartext password that'w will be verified
     * @throws PersistedUserRequiredException
     * @throws UserWithoutAuthenticationRecordException
     */
    public function verifyPassword(User $user, string $password): bool
    {
        if (!$user->getId()) {
            throw new PersistedUserRequiredException("Your user must have an ID before you can create auth records with it");
        }

        $auth = $user->getAuthenticationRecord();
        if (!$auth) {
            throw new UserWithoutAuthenticationRecordException();
        }

        return password_verify($password, $auth->getHash());
    }

    /**
     * Register a new user into the auth tables, and, log them in. Essentially calls registerAuthenticationRecord
     * and then stores the necessary cookies and identity into the service.
     *
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

        if ($user->getAuthenticationRecord() !== null) {
            throw new LogicException("This user already has an existing authentication record");
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
            $user,
            $username,
            $hash,
            KeyFactory::generateEncryptionKey()->getRawKeyMaterial()
        );
        $user->setAuthenticationRecord($auth);
        $this->authenticationProvider->save($auth);

        return $auth;
    }

    /**
     * Resalt a user's authentication table salt
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
    public function clearIdentity(): void
    {
        if ($user = $this->getIdentity()) {
            if ($auth = $user->getAuthenticationRecord()) {
                $this->resetAuthenticationKey($auth);
            }
        }

        $sp = session_get_cookie_params();
        $killTime = time() - 3600;
        foreach ([self::COOKIE_USER, self::COOKIE_VERIFY_A, self::COOKIE_VERIFY_B, self::COOKIE_TIMESTAMP] as $cookieName) {
            setcookie($cookieName, '', $killTime, '/', $sp['domain'], false, true);
        }

        $this->identity = null;
    }

    /**
     * Forgot-password mechanisms are a potential back door; but they're needed.  This only takes care
     * of hash generation.
     *
     * @throws NoSuchUserException
     * @throws PasswordResetProhibitedException
     * @throws TooManyRecoveryAttemptsException
     */
    public function createRecoveryToken(User $user): UserResetToken
    {
        if (!$this->resetTokenProvider) {
            throw new PasswordResetProhibitedException('The configuration currently prohibits the resetting of passwords!');
        }

        $auth = $user->getAuthenticationRecord();
        if (!$auth) {
            throw new UserWithoutAuthenticationRecordException();
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
     * @throws WeakPasswordException
     */
    public function changePasswordWithRecoveryToken(User $user, int $tokenId, string $token, string $newPassword): void
    {
        if (!$this->resetTokenProvider) {
            throw new PasswordResetProhibitedException('The configuration currently prohibits the resetting of passwords!');
        }

        $auth = $user->getAuthenticationRecord();
        if (!$auth) {
            throw new UserWithoutAuthenticationRecordException();
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

    /**
     * Allow the override of the authentication cookies' durations.  Though this should be set via factory,
     * this is to help with cache/database-based configurations that may need it.
     */
    public function setAuthenticationCookieDuration(int $authenticationCookieDuration): void
    {
        $this->authenticationCookieDuration = $authenticationCookieDuration;
    }
}
