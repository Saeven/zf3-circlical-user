<?php

namespace Spec\CirclicalUser\Service;

use CirclicalUser\Entity\Authentication;
use CirclicalUser\Entity\UserResetToken;
use CirclicalUser\Exception\InvalidResetTokenException;
use CirclicalUser\Exception\PasswordResetProhibitedException;
use CirclicalUser\Exception\PersistedUserRequiredException;
use CirclicalUser\Exception\TooManyRecoveryAttemptsException;
use CirclicalUser\Exception\WeakPasswordException;
use CirclicalUser\Mapper\UserResetTokenMapper;
use CirclicalUser\Provider\AuthenticationRecordInterface;
use CirclicalUser\Provider\UserInterface as User;
use CirclicalUser\Exception\BadPasswordException;
use CirclicalUser\Exception\EmailUsernameTakenException;
use CirclicalUser\Exception\MismatchedEmailsException;
use CirclicalUser\Exception\NoSuchUserException;
use CirclicalUser\Exception\UsernameTakenException;
use CirclicalUser\Mapper\AuthenticationMapper;
use CirclicalUser\Mapper\UserMapper;
use CirclicalUser\Provider\UserResetTokenInterface;
use CirclicalUser\Provider\UserResetTokenProviderInterface;
use CirclicalUser\Service\AuthenticationService;
use CirclicalUser\Service\PasswordChecker\Passwdqc;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AuthenticationServiceSpec extends ObjectBehavior
{

    private $systemEncryptionKey;
    private $authenticationData;

    public function let(AuthenticationMapper $authenticationMapper, UserMapper $userMapper, User $user, UserResetTokenMapper $tokenMapper)
    {
        $hash = password_hash('abc', PASSWORD_DEFAULT);
        $key = KeyFactory::generateEncryptionKey();

        $authenticationData = new Authentication(1, 'userA', $hash, $key->getRawKeyMaterial());
        $this->authenticationData = $authenticationData;
        $orphanAuthData = new Authentication(2, 'orphan', $hash, $key->getRawKeyMaterial());

        $authenticationMapper->findByUsername(Argument::any())->willReturn(null);
        $authenticationMapper->findByUsername('userA')->willReturn($authenticationData);
        $authenticationMapper->findByUsername('orphan')->willReturn($orphanAuthData);
        $authenticationMapper->findByUserId(Argument::any())->willReturn(null);
        $authenticationMapper->findByUserId(1)->willReturn($authenticationData);

        $authenticationMapper->update($authenticationData)->willReturn(true);


        $user->getId()->willReturn(1);

        $userMapper->findByEmail(Argument::any())->willReturn(null);
        $userMapper->findByEmail('alex@circlical.com')->willReturn($user);
        $userMapper->getUser(Argument::any())->willReturn(null);
        $userMapper->getUser(1)->willReturn($user);

        $this->systemEncryptionKey = KeyFactory::generateEncryptionKey();
        $this->beConstructedWith($authenticationMapper, $userMapper, $tokenMapper, $this->systemEncryptionKey->getRawKeyMaterial(), false, false, null, true, true);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('CirclicalUser\Service\AuthenticationService');
    }

    public function it_returns_null_when_nobody_is_there()
    {
        $this->hasIdentity()->shouldBe(false);
    }

    public function it_returns_true_when_someone_is_there()
    {
        $this->authenticate('userA', 'abc');
        $this->hasIdentity()->shouldBe(true);
    }

    public function it_has_a_private_set_identity_function()
    {
        $this->shouldThrow(\Exception::class)->during('setIdentity');
    }

    public function it_checks_via_email_second($authenticationMapper, $userMapper)
    {
        $authenticationMapper->findByUserId(1)->shouldBeCalled();
        $user = $this->authenticate('alex@circlical.com', 'abc');
        $user->getId()->shouldBe(1);
    }

    public function it_checks_via_username_first($authenticationMapper, $userMapper)
    {
        $userMapper->findByEmail()->shouldNotBeCalled();
        $user = $this->authenticate('userA', 'abc');
        $user->getId()->shouldBe(1);
    }

    public function it_fails_bad_passwords_on_username_checks($authenticationMapper, $userMapper)
    {
        $this->shouldThrow(BadPasswordException::class)->during('authenticate', ['userA', 'def']);
    }

    public function it_fails_bad_users_on_username_checks($authenticationMapper, $userMapper)
    {
        $this->shouldThrow(NoSuchUserException::class)->during('authenticate', ['userB', 'def']);
    }

    public function it_fails_bad_passwords_on_email_checks($authenticationMapper, $userMapper)
    {
        $this->shouldThrow(BadPasswordException::class)->during('authenticate', ['alex@circlical.com', 'def']);
    }

    public function it_fails_bad_users_on_email_checks($authenticationMapper, $userMapper)
    {
        $this->shouldThrow(NoSuchUserException::class)->during('authenticate', ['unknown@circlical.com', 'def']);
    }

    public function it_fails_on_orphaned_user_records($authenticationMapper, $userMapper)
    {
        $authenticationMapper->findByUserId()->shouldNotBeCalled();
        $userMapper->findByEmail()->shouldNotBeCalled();
        $this->shouldThrow(NoSuchUserException::class)->during('authenticate', ['orphan', 'abc']);
    }

    public function it_permits_username_changes($authenticationMapper, $userMapper, $user)
    {
        $this->changeUsername($user, 'newUsername');
    }

    public function it_does_nothing_for_same_username_changes($authenticationMapper, $userMapper, $user)
    {
        $this->changeUsername($user, 'userA');
    }

    public function it_declines_username_changes_for_missing_users($authenticationMapper, $userMapper, User $user3)
    {
        $user3->getId()->willReturn(3);
        $this->shouldThrow(NoSuchUserException::class)->during('changeUsername', [$user3, 'newUsername']);
    }

    public function it_declines_username_changes_when_usernames_are_taken($authenticationMapper, $userMapper, User $user1)
    {
        $user1->getId()->willReturn(1);
        $this->shouldThrow(UsernameTakenException::class)->during('changeUsername', [$user1, 'orphan']);
    }

    public function it_verify_user_password($authenticationMapper, $userMapper, $user)
    {
        $this->verifyPassword($user, 'abc')->shouldBe(true);
    }

    public function it_failed_verify_bad_user_password($authenticationMapper, $userMapper, $user)
    {
        $this->verifyPassword($user, 'xyz')->shouldBe(false);
    }

    public function it_fails_verify_non_existing_user($authenticationMapper, $userMapper, User $user4)
    {
        $user4->getId()->willReturn(4);
        $this->shouldThrow(NoSuchUserException::class)->during('verifyPassword', [$user4, 'abc']);
    }

    public function it_returns_authenticated_identities()
    {
        $this->authenticate('userA', 'abc');
        $user = $this->getIdentity();
        $user->shouldBeAnInstanceOf(User::class);
        $user->getId()->shouldBe(1);
    }

    public function it_authenticates_with_cookies()
    {
        $systemKey = $this->systemEncryptionKey;
        $userKey = new EncryptionKey(new HiddenString($this->authenticationData->getSessionKey()));
        $hashCookieName = hash_hmac('sha256', $this->authenticationData->getSessionKey() . $this->authenticationData->getUsername(), $systemKey);
        $userTuple = base64_encode(Crypto::encrypt(new HiddenString($this->authenticationData->getUserId() . ":" . $hashCookieName), $systemKey));
        $hashCookieContents = base64_encode(Crypto::encrypt(new HiddenString(time() . ':' . $this->authenticationData->getUserId() . ':' . $this->authenticationData->getUsername()), $userKey));

        $_COOKIE[AuthenticationService::COOKIE_USER] = $userTuple;
        $_COOKIE[AuthenticationService::COOKIE_HASH_PREFIX . $hashCookieName] = $hashCookieContents;
        $_COOKIE[AuthenticationService::COOKIE_VERIFY_A] = hash_hmac('sha256', $userTuple, $systemKey);
        $_COOKIE[AuthenticationService::COOKIE_VERIFY_B] = hash_hmac('sha256', $hashCookieContents, $userKey);

        $this->getIdentity()->shouldBeAnInstanceOf(User::class);
    }

    public function it_fails_when_the_random_hash_cookie_is_bad()
    {
        $systemKey = $this->systemEncryptionKey;
        $userKey = new EncryptionKey(new HiddenString($this->authenticationData->getSessionKey()));
        $hashCookieName = hash_hmac('sha256', $this->authenticationData->getSessionKey() . $this->authenticationData->getUsername(), $systemKey);
        $userTuple = base64_encode(Crypto::encrypt(new HiddenString($this->authenticationData->getUserId() . ":" . $hashCookieName), $systemKey));
        $hashCookieContents = base64_encode(Crypto::encrypt(new HiddenString(time() . ':' . $this->authenticationData->getUserId() . ':' . $this->authenticationData->getUsername()), $userKey));

        $_COOKIE[AuthenticationService::COOKIE_USER] = $userTuple;
        $_COOKIE[AuthenticationService::COOKIE_HASH_PREFIX . $hashCookieName] = 'g14gdf';
        $_COOKIE[AuthenticationService::COOKIE_VERIFY_A] = hash_hmac('sha256', $userTuple, $systemKey);
        $_COOKIE[AuthenticationService::COOKIE_VERIFY_B] = hash_hmac('sha256', $hashCookieContents, $userKey);

        $this->getIdentity()->shouldBe(null);
    }

    public function it_bails_when_the_user_tuple_is_not_well_formatted()
    {
        $systemKey = $this->systemEncryptionKey;
        $userKey = new EncryptionKey(new HiddenString($this->authenticationData->getSessionKey()));
        $hashCookieName = hash_hmac('sha256', $this->authenticationData->getSessionKey() . $this->authenticationData->getUsername(), $systemKey);
        $userTuple = base64_encode(Crypto::encrypt(new HiddenString('tanqueray'), $systemKey));
        $hashCookieContents = base64_encode(Crypto::encrypt(new HiddenString(time() . ':' . $this->authenticationData->getUserId() . ':' . $this->authenticationData->getUsername()), $userKey));

        $_COOKIE[AuthenticationService::COOKIE_USER] = $userTuple;
        $_COOKIE[AuthenticationService::COOKIE_HASH_PREFIX . $hashCookieName] = 'g14gdf';
        $_COOKIE[AuthenticationService::COOKIE_VERIFY_A] = hash_hmac('sha256', $userTuple, $systemKey);
        $_COOKIE[AuthenticationService::COOKIE_VERIFY_B] = hash_hmac('sha256', $hashCookieContents, $userKey);

        $this->getIdentity()->shouldBe(null);
    }

    public function it_bails_when_the_user_tuple_contains_well_formatted_garbage()
    {
        $systemKey = $this->systemEncryptionKey;
        $userKey = new EncryptionKey(new HiddenString($this->authenticationData->getSessionKey()));
        $hashCookieName = hash_hmac('sha256', $this->authenticationData->getSessionKey() . $this->authenticationData->getUsername(), $systemKey);
        $userTuple = base64_encode(Crypto::encrypt(new HiddenString('A' . ":" . $hashCookieName), $systemKey));
        $hashCookieContents = base64_encode(Crypto::encrypt(new HiddenString(time() . ':' . $this->authenticationData->getUserId() . ':' . $this->authenticationData->getUsername()), $userKey));

        $_COOKIE[AuthenticationService::COOKIE_USER] = $userTuple;
        $_COOKIE[AuthenticationService::COOKIE_HASH_PREFIX . $hashCookieName] = 'g14gdf';
        $_COOKIE[AuthenticationService::COOKIE_VERIFY_A] = hash_hmac('sha256', $userTuple, $systemKey);
        $_COOKIE[AuthenticationService::COOKIE_VERIFY_B] = hash_hmac('sha256', $hashCookieContents, $userKey);

        $this->getIdentity()->shouldBe(null);
    }

    public function it_bails_when_the_user_tuple_contains_an_impossible_user_id()
    {
        $systemKey = $this->systemEncryptionKey;
        $userKey = new EncryptionKey(new HiddenString($this->authenticationData->getSessionKey()));
        $hashCookieName = hash_hmac('sha256', $this->authenticationData->getSessionKey() . $this->authenticationData->getUsername(), $systemKey);
        $userTuple = base64_encode(Crypto::encrypt(new HiddenString(15234 . ":" . $hashCookieName), $systemKey));
        $hashCookieContents = base64_encode(Crypto::encrypt(new HiddenString(time() . ':' . $this->authenticationData->getUserId() . ':' . $this->authenticationData->getUsername()), $userKey));

        $_COOKIE[AuthenticationService::COOKIE_USER] = $userTuple;
        $_COOKIE[AuthenticationService::COOKIE_HASH_PREFIX . $hashCookieName] = 'g14gdf';
        $_COOKIE[AuthenticationService::COOKIE_VERIFY_A] = hash_hmac('sha256', $userTuple, $systemKey);
        $_COOKIE[AuthenticationService::COOKIE_VERIFY_B] = hash_hmac('sha256', $hashCookieContents, $userKey);

        $this->getIdentity()->shouldBe(null);
    }

    public function it_bails_when_the_hash_cookie_is_not_well_formatted()
    {
        $systemKey = $this->systemEncryptionKey;
        $userKey = new EncryptionKey(new HiddenString($this->authenticationData->getSessionKey()));
        $hashCookieName = hash_hmac('sha256', $this->authenticationData->getSessionKey() . $this->authenticationData->getUsername(), $systemKey);
        $userTuple = base64_encode(Crypto::encrypt(new HiddenString($this->authenticationData->getUserId() . ":" . $hashCookieName), $systemKey));
        $hashCookieContents = base64_encode(Crypto::encrypt(new HiddenString($this->authenticationData->getUserId() . ':' . $this->authenticationData->getUsername()), $userKey));

        $_COOKIE[AuthenticationService::COOKIE_USER] = $userTuple;
        $_COOKIE[AuthenticationService::COOKIE_HASH_PREFIX . $hashCookieName] = 'g14gdf';
        $_COOKIE[AuthenticationService::COOKIE_VERIFY_A] = hash_hmac('sha256', $userTuple, $systemKey);
        $_COOKIE[AuthenticationService::COOKIE_VERIFY_B] = hash_hmac('sha256', $hashCookieContents, $userKey);

        $this->getIdentity()->shouldBe(null);
    }

    private function ninja_power_combinations($array)
    {
        $results = [[]];

        foreach ($array as $element) {
            foreach ($results as $combination) {
                $results[] = array_merge([$element], $combination);
            }
        }

        return $results;
    }

    public function it_fails_when_any_cookies_are_missing()
    {
        $systemKey = $this->systemEncryptionKey;
        $userKey = new EncryptionKey(new HiddenString($this->authenticationData->getSessionKey()));
        $hashCookieName = hash_hmac('sha256', $this->authenticationData->getSessionKey() . $this->authenticationData->getUsername(), $systemKey);
        $userTuple = base64_encode(Crypto::encrypt(new HiddenString($this->authenticationData->getUserId() . ":" . $hashCookieName), $systemKey));
        $hashCookieContents = base64_encode(Crypto::encrypt(new HiddenString(time() . ':' . $this->authenticationData->getUserId() . ':' . $this->authenticationData->getUsername()), $userKey));

        $cookies = [
            AuthenticationService::COOKIE_USER => $userTuple,
            AuthenticationService::COOKIE_HASH_PREFIX . $hashCookieName => $hashCookieContents,
            AuthenticationService::COOKIE_VERIFY_A => hash_hmac('sha256', $userTuple, $systemKey),
            AuthenticationService::COOKIE_VERIFY_B => hash_hmac('sha256', $hashCookieContents, $userKey),
        ];

        $cookieTypes = array_keys($cookies);
        $results = $this->ninja_power_combinations($cookieTypes);

        foreach ($results as $combinations) {
            $comboCount = count($combinations);
            if ($comboCount != 0 && $comboCount < 4) {

                foreach ($cookieTypes as $c) {
                    unset($_COOKIE[$c]);
                }

                foreach ($combinations as $cookieName) {
                    $_COOKIE[$cookieName] = $cookies[$cookieName];
                }

                $this->getIdentity()->shouldBe(null);
            }
        }
    }

    public function it_fails_when_the_user_verify_hash_is_bad($authenticationMapper)
    {
        $systemKey = $this->systemEncryptionKey;
        $userKey = new EncryptionKey(new HiddenString($this->authenticationData->getSessionKey()));
        $hashCookieName = hash_hmac('sha256', $this->authenticationData->getSessionKey() . $this->authenticationData->getUsername(), $systemKey);
        $userTuple = base64_encode(Crypto::encrypt(new HiddenString($this->authenticationData->getUserId() . ":" . $hashCookieName), $systemKey));
        $hashCookieContents = base64_encode(Crypto::encrypt(new HiddenString(time() . ':' . $this->authenticationData->getUserId() . ':' . $this->authenticationData->getUsername()), $userKey));

        $_COOKIE[AuthenticationService::COOKIE_USER] = $userTuple;
        $_COOKIE[AuthenticationService::COOKIE_HASH_PREFIX . $hashCookieName] = $hashCookieContents;
        $_COOKIE[AuthenticationService::COOKIE_VERIFY_A] = hash_hmac('sha256', $userTuple, $systemKey) . 'a';
        $_COOKIE[AuthenticationService::COOKIE_VERIFY_B] = hash_hmac('sha256', $hashCookieContents, $userKey);

        $authenticationMapper->findByUserId(Argument::any())->shouldNotBeCalled();
        $this->getIdentity()->shouldBe(null);
    }

    public function it_fails_when_the_random_verify_hash_is_bad($authenticationMapper, $userMapper)
    {
        $systemKey = $this->systemEncryptionKey;
        $userKey = new EncryptionKey(new HiddenString($this->authenticationData->getSessionKey()));
        $hashCookieName = hash_hmac('sha256', $this->authenticationData->getSessionKey() . $this->authenticationData->getUsername(), $systemKey);
        $userTuple = base64_encode(Crypto::encrypt(new HiddenString($this->authenticationData->getUserId() . ":" . $hashCookieName), $systemKey));
        $hashCookieContents = base64_encode(Crypto::encrypt(new HiddenString(time() . ':' . $this->authenticationData->getUserId() . ':' . $this->authenticationData->getUsername()), $userKey));

        $_COOKIE[AuthenticationService::COOKIE_USER] = $userTuple;
        $_COOKIE[AuthenticationService::COOKIE_HASH_PREFIX . $hashCookieName] = $hashCookieContents;
        $_COOKIE[AuthenticationService::COOKIE_VERIFY_A] = hash_hmac('sha256', $userTuple, $systemKey);
        $_COOKIE[AuthenticationService::COOKIE_VERIFY_B] = hash_hmac('sha256', $hashCookieContents, $userKey) . 'b';

        $userMapper->getUser(Argument::any())->shouldNotBeCalled();
        $this->getIdentity()->shouldBe(null);
    }

    public function it_fails_when_the_user_tuple_and_random_verify_do_not_match($authenticationMapper, $userMapper)
    {
        $systemKey = $this->systemEncryptionKey;
        $userKey = new EncryptionKey(new HiddenString($this->authenticationData->getSessionKey()));
        $hashCookieName = hash_hmac('sha256', $this->authenticationData->getSessionKey() . $this->authenticationData->getUsername(), $systemKey);
        $userTuple = base64_encode(Crypto::encrypt(new HiddenString($this->authenticationData->getUserId() . ":" . $hashCookieName), $systemKey));
        $hashCookieContents = base64_encode(Crypto::encrypt(new HiddenString(time() . ':' . 2 . ':' . $this->authenticationData->getUsername()), $userKey));

        $_COOKIE[AuthenticationService::COOKIE_USER] = $userTuple;
        $_COOKIE[AuthenticationService::COOKIE_HASH_PREFIX . $hashCookieName] = $hashCookieContents;
        $_COOKIE[AuthenticationService::COOKIE_VERIFY_A] = hash_hmac('sha256', $userTuple, $systemKey);
        $_COOKIE[AuthenticationService::COOKIE_VERIFY_B] = hash_hmac('sha256', $hashCookieContents, $userKey) . 'b';

        $userMapper->getUser(Argument::any())->shouldNotBeCalled();
        $this->getIdentity()->shouldBe(null);
    }

    public function it_resets_passwords($authenticationMapper, $user)
    {
        $authenticationMapper->findByUserId(1)->shouldBeCalled();
        $authenticationMapper->update($this->authenticationData)->shouldBeCalled();
        $this->resetPassword($user, 'efg');
    }

    public function it_wont_reset_passwords_when_users_do_not_exist(User $user5)
    {
        $user5->getId()->willReturn(5);
        $this->shouldThrow(NoSuchUserException::class)->during('resetPassword', [$user5, 'efg']);
    }

    public function it_can_create_new_auth_records($authenticationMapper, User $user5, AuthenticationRecordInterface $newAuth)
    {
        $newAuth->getSessionKey()->willReturn(KeyFactory::generateEncryptionKey()->getRawKeyMaterial());
        $newAuth->getUsername()->willReturn('email');
        $newAuth->getUserId()->willReturn(5);
        $user5->getId()->willReturn(5);
        $authenticationMapper->save(Argument::type(AuthenticationRecordInterface::class))->shouldBeCalled();
        $authenticationMapper->create(Argument::type('integer'), Argument::type('string'), Argument::type('string'), Argument::type('string'))->willReturn($newAuth);
        $this->create($user5, 'userC', 'beestring')->shouldBeAnInstanceOf(AuthenticationRecordInterface::class);
    }

    public function it_wont_overwrite_existing_auth_on_create($authenticationMapper, User $user5)
    {
        $user5->getId()->willReturn(5);
        $this->shouldThrow(UsernameTakenException::class)->during('create', [$user5, 'userA', 'razorblades']);
    }

    public function it_wont_create_auth_when_email_usernames_belong_to_user_records($authenticationMapper, User $user5)
    {

        $user5->getId()->willReturn(5);
        $user5->getEmail()->willReturn('alex@circlical.com');
        $this->shouldThrow(EmailUsernameTakenException::class)->during('create', [$user5, 'alex@circlical.com', 'pepperspray']);
    }

    public function it_does_not_permit_mismatched_emails(User $user6)
    {
        $user6->getId()->willReturn(1);
        $user6->getEmail()->willReturn('a@b.com');
        $this->shouldThrow(MismatchedEmailsException::class)->during('create', [$user6, 'b@b.com', 'alphabet']);
    }

    public function it_can_clear_identity()
    {
        $this->authenticate('userA', 'abc');
        $this->clearIdentity();
        $this->getIdentity()->shouldBe(null);
    }

    public function it_requires_that_users_have_id_during_creation(User $otherUser)
    {
        $otherUser->getId()->willReturn(null);
        $this->shouldThrow(PersistedUserRequiredException::class)->during('create', [$otherUser, 'whoami', 'nobody']);
    }

    public function it_will_create_new_auth_records_with_strong_passwords($authenticationMapper, User $user5, AuthenticationRecordInterface $newAuth, $userMapper, $tokenMapper)
    {
        $this->beConstructedWith($authenticationMapper, $userMapper, $tokenMapper, $this->systemEncryptionKey->getRawKeyMaterial(), false, false, new Passwdqc(), true, true);

        $newAuth->getSessionKey()->willReturn(KeyFactory::generateEncryptionKey()->getRawKeyMaterial());
        $newAuth->getUsername()->willReturn('email');
        $newAuth->getUserId()->willReturn(5);
        $user5->getId()->willReturn(5);

        $authenticationMapper->save(Argument::type(AuthenticationRecordInterface::class))->shouldBeCalled();
        $authenticationMapper->create(Argument::type('integer'), Argument::type('string'), Argument::type('string'), Argument::type('string'))->willReturn($newAuth);
        $this->create($user5, 'userC', 'beestring')->shouldBeAnInstanceOf(AuthenticationRecordInterface::class);
    }

    public function it_wont_create_new_auth_records_with_weak_passwords($authenticationMapper, User $user5, AuthenticationRecordInterface $newAuth, $userMapper, $tokenMapper)
    {
        $this->beConstructedWith($authenticationMapper, $userMapper, $tokenMapper, $this->systemEncryptionKey->getRawKeyMaterial(), false, false, new Passwdqc(), true, true);

        $newAuth->getSessionKey()->willReturn(KeyFactory::generateEncryptionKey()->getRawKeyMaterial());
        $newAuth->getUsername()->willReturn('email');
        $newAuth->getUserId()->willReturn(5);
        $user5->getId()->willReturn(5);

        $this->shouldThrow(WeakPasswordException::class)->during('create', [$user5, 'userC', '123456']);
    }

    public function it_creates_forgot_password_hashes(User $user, $tokenMapper)
    {
        $tokenMapper->getRequestCount($this->authenticationData)->willReturn(0);
        $tokenMapper->invalidateUnusedTokens($this->authenticationData)->shouldBeCalled();
        $tokenMapper->save(Argument::any())->shouldBeCalled();
        $this->createRecoveryToken($user)->shouldBeAnInstanceOf(UserResetToken::class);
    }

    public function it_will_refuse_to_accept_too_many_forgot_password_requests(User $user, $tokenMapper)
    {
        $tokenMapper->getRequestCount($this->authenticationData)->willReturn(50);
        $this->shouldThrow(TooManyRecoveryAttemptsException::class)->during('createRecoveryToken', [$user]);
    }

    public function it_wont_create_recovery_tokens_for_authless_users(User $who)
    {
        $who->getId()->willReturn(789);
        $this->shouldThrow(NoSuchUserException::class)->during('createRecoveryToken', [$who]);
    }

    public function it_fails_to_create_tokens_when_password_changes_are_prohibited($authenticationMapper, $userMapper, $tokenMapper, $user)
    {
        $this->beConstructedWith($authenticationMapper, $userMapper, null, $this->systemEncryptionKey->getRawKeyMaterial(), false, false, null, true, true);
        $this->shouldThrow(PasswordResetProhibitedException::class)->during('createRecoveryToken', [$user]);
    }

    public function it_bails_on_password_changes_if_no_provider_is_set($authenticationMapper, $userMapper, $tokenMapper, $user)
    {
        $this->beConstructedWith($authenticationMapper, $userMapper, null, $this->systemEncryptionKey->getRawKeyMaterial(), false, false, null, true, true);
        $this->shouldThrow(PasswordResetProhibitedException::class)->during('changePasswordWithRecoveryToken', [$user, 123, 'string', 'string']);
    }

    public function it_bails_on_password_changes_for_authless_users(User $who)
    {
        $who->getId()->willReturn(789);
        $this->shouldThrow(NoSuchUserException::class)->during('changePasswordWithRecoveryToken', [$who, 123, 'string', 'string']);
    }

    public function it_bails_on_password_changes_with_bad_reset_token_ids($user, $tokenMapper)
    {
        $tokenMapper->get(123)->willReturn(null);
        $this->shouldThrow(InvalidResetTokenException::class)->during('changePasswordWithRecoveryToken', [$user, 123, 'string', 'string']);
    }

    public function it_modifies_storage_when_password_changes_succeed(User $user, UserResetToken $token, $tokenMapper)
    {
        $token->isValid(Argument::any(), Argument::any(), Argument::any(), true, true)->willReturn(true);
        $tokenMapper->get(1)->willReturn($token);
        $token->setStatus(UserResetTokenInterface::STATUS_USED)->shouldBeCalled();
        $tokenMapper->update($token)->shouldBeCalled();
        $this->changePasswordWithRecoveryToken($user, 1, 'tokenstring', 'newpassword');
    }

    public function it_throws_exceptions_when_tokens_are_invalid(User $user, UserResetToken $token, $tokenMapper)
    {
        $token->isValid(Argument::any(), Argument::any(), Argument::any(), true, true)->willReturn(false);
        $tokenMapper->get(1)->willReturn($token);
        $this->shouldThrow(InvalidResetTokenException::class)->during('changePasswordWithRecoveryToken', [$user, 1, 'tokenstring', 'newpassword']);
    }


}
