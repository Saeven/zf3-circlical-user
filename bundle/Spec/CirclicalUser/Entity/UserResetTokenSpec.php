<?php

namespace Spec\CirclicalUser\Entity;

use CirclicalUser\Entity\Authentication;
use CirclicalUser\Entity\UserResetToken;
use CirclicalUser\Exception\InvalidResetTokenException;
use CirclicalUser\Exception\InvalidResetTokenFingerprintException;
use CirclicalUser\Exception\InvalidResetTokenIpAddressException;
use CirclicalUser\Provider\AuthenticationRecordInterface;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\Halite\KeyFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserResetTokenSpec extends ObjectBehavior
{
    private $testTime;

    private $rawKeyMaterial;

    function let(AuthenticationRecordInterface $authenticationRecord)
    {
        $_SERVER['HTTP_USER_AGENT'] = 1;
        $_SERVER['HTTP_ACCEPT'] = 1;
        $_SERVER['HTTP_ACCEPT_CHARSET'] = 1;
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 1;
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 1;

        $this->rawKeyMaterial = KeyFactory::generateEncryptionKey()->getRawKeyMaterial();
        $authenticationRecord->getSessionKey()->willReturn($this->rawKeyMaterial);
        $authenticationRecord->getUserId()->willReturn(1);
        $this->beConstructedWith($authenticationRecord, '10.10.1.1');


        $property = new \ReflectionProperty(UserResetToken::class, 'request_time');
        $property->setAccessible(true);
        $this->testTime = $property->getValue($this->getWrappedObject());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserResetToken::class);
    }

    function it_fails_validity_check_with_a_bad_token(AuthenticationRecordInterface $authenticationRecord)
    {
        $this->isValid($authenticationRecord, 'bad', '10.10.1.1', false, false)->shouldBe(false);
    }

    function it_dies_when_authrecords_are_mismatched(AuthenticationRecordInterface $newAuth)
    {
        $property = new \ReflectionProperty(UserResetToken::class, 'token');
        $property->setAccessible(true);
        $token = $property->getValue($this->getWrappedObject());
        $this->shouldThrow(InvalidResetTokenException::class)->during(
            'isValid',
            [$newAuth, $token, '10.10.1.1', false, false]
        );
    }

    function it_dies_when_the_session_key_is_invalid_despite_the_rest_being_ok(AuthenticationRecordInterface $authenticationRecord)
    {
        $property = new \ReflectionProperty(UserResetToken::class, 'token');
        $property->setAccessible(true);
        $token = $property->getValue($this->getWrappedObject());

        // the authentication record was manipulated during the operation
        $authenticationRecord->getSessionKey()->willReturn(KeyFactory::generateEncryptionKey()->getRawKeyMaterial());

        $this->shouldThrow(InvalidResetTokenException::class)->during(
            'isValid',
            [$authenticationRecord, $token, '10.10.1.1', false, false]
        );
    }

    function it_can_validate_ip(AuthenticationRecordInterface $authenticationRecord)
    {
        $property = new \ReflectionProperty(UserResetToken::class, 'token');
        $property->setAccessible(true);
        $token = $property->getValue($this->getWrappedObject());

        $this->shouldThrow(InvalidResetTokenIpAddressException::class)->during(
            'isValid',
            [$authenticationRecord, $token, '5.5.5.5', false, true]
        );
    }

    function it_can_validate_fingerprint(AuthenticationRecordInterface $authenticationRecord)
    {
        $_SERVER['HTTP_USER_AGENT'] = 2;
        $_SERVER['HTTP_ACCEPT'] = 2;
        $_SERVER['HTTP_ACCEPT_CHARSET'] = 2;
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 2;
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 2;

        $property = new \ReflectionProperty(UserResetToken::class, 'token');
        $property->setAccessible(true);
        $token = $property->getValue($this->getWrappedObject());

        $this->shouldThrow(InvalidResetTokenFingerprintException::class)->during(
            'isValid',
            [$authenticationRecord, $token, '10.10.1.1', true, false]
        );
    }
}
