<?php

declare(strict_types=1);

namespace CirclicalUser\Entity;

use CirclicalUser\Exception\InvalidResetTokenException;
use CirclicalUser\Exception\InvalidResetTokenFingerprintException;
use CirclicalUser\Exception\InvalidResetTokenIpAddressException;
use CirclicalUser\Exception\MismatchedResetTokenException;
use CirclicalUser\Provider\AuthenticationRecordInterface;
use CirclicalUser\Provider\UserResetTokenInterface;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use InvalidArgumentException;
use JsonException;
use ParagonIE\Halite\Alerts\CannotPerformOperation;
use ParagonIE\Halite\Alerts\InvalidDigestLength;
use ParagonIE\Halite\Alerts\InvalidKey;
use ParagonIE\Halite\Alerts\InvalidMessage;
use ParagonIE\Halite\Alerts\InvalidType;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;
use SodiumException;

use function base64_decode;
use function base64_encode;
use function implode;
use function in_array;
use function json_decode;
use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * A password-reset token.  This is the thing that you would exchange in a forgot-password email
 * that the user can later consume to trigger a password change.
 */
#[ORM\Entity, ORM\Table(name: "users_auth_reset")]
class UserResetToken implements UserResetTokenInterface
{
    #[ORM\Id, ORM\Column(type: "integer", options: ['unsigned' => true]), ORM\GeneratedValue(strategy: "AUTO")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Authentication::class)]
    #[ORM\JoinColumn(name: 'auth_user_id', referencedColumnName: 'user_id', onDelete: 'CASCADE')]
    private AuthenticationRecordInterface $authentication;

    #[ORM\Column(type: "string", length: 2048)]
    private string $token;

    #[ORM\Column(type: "datetime_immutable")]
    private DateTimeImmutable $request_time;

    #[ORM\Column(type: "string", length: 46, options: ['fixed' => true])]
    private string $request_ip_address;

    #[ORM\Column(type: "integer", options: ['default' => 0])]
    private int $status;

    /**
     * @throws InvalidType
     * @throws InvalidDigestLength
     * @throws SodiumException
     * @throws JsonException
     * @throws InvalidKey
     * @throws InvalidMessage
     * @throws CannotPerformOperation
     * @throws Exception
     */
    public function __construct(AuthenticationRecordInterface $authentication, string $requestingIpAddress)
    {
        $this->authentication = $authentication;
        $this->request_time = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $this->request_ip_address = $requestingIpAddress;
        $this->status = UserResetTokenInterface::STATUS_UNUSED;

        $fingerprint = $this->getFingerprint();

        $key = new EncryptionKey(new HiddenString($authentication->getRawSessionKey()));
        $this->token = base64_encode(
            Crypto::encrypt(
                new HiddenString(
                    json_encode([
                        'fingerprint' => $fingerprint,
                        'timestamp' => $this->request_time->format('U'),
                        'userId' => $authentication->getUserId(),
                    ], JSON_THROW_ON_ERROR)
                ),
                $key
            )
        );
    }

    public function getFingerprint(): string
    {
        return implode(
            ':',
            [
                $_SERVER['HTTP_USER_AGENT'] ?? 'na',
                $_SERVER['HTTP_ACCEPT'] ?? 'na',
                $_SERVER['HTTP_ACCEPT_CHARSET'] ?? 'na',
                $_SERVER['HTTP_ACCEPT_ENCODING'] ?? 'na',
                $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'na',
            ]
        );
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function setStatus(int $status): void
    {
        if (!in_array($status, [UserResetTokenInterface::STATUS_UNUSED, UserResetTokenInterface::STATUS_INVALID, UserResetTokenInterface::STATUS_USED], true)) {
            throw new InvalidArgumentException("An invalid status is being set!");
        }
        $this->status = $status;
    }

    /**
     * @throws InvalidResetTokenIpAddressException
     * @throws InvalidResetTokenException
     * @throws InvalidResetTokenFingerprintException
     * @throws MismatchedResetTokenException
     */
    public function isValid(
        AuthenticationRecordInterface $authenticationRecord,
        string $checkToken,
        string $requestingIpAddress,
        bool $validateFingerprint,
        bool $validateIp
    ): bool {
        if ($this->token !== $checkToken) {
            return false;
        }

        // this token is for someone else...
        if ($authenticationRecord !== $this->authentication) {
            throw new MismatchedResetTokenException();
        }

        try {
            $encryptedJson = @base64_decode($checkToken);
            $sessionKey = new HiddenString($authenticationRecord->getRawSessionKey());
            $key = new EncryptionKey($sessionKey);
            $jsonString = Crypto::decrypt($encryptedJson, $key)->getString();
        } catch (Exception $x) {
            throw new InvalidResetTokenException();
        }

        try {
            $json = @json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);
            if (!isset($json['fingerprint'], $json['timestamp'], $json['userId'])) {
                throw new InvalidResetTokenException();
            }

            if ($validateFingerprint && $json['fingerprint'] !== $this->getFingerprint()) {
                throw new InvalidResetTokenFingerprintException();
            }

            if ($validateIp && $requestingIpAddress !== $this->request_ip_address) {
                throw new InvalidResetTokenIpAddressException();
            }

            if ($json['userId'] !== $authenticationRecord->getUserId()) {
                throw new InvalidResetTokenException();
            }

            return true;
        } catch (JsonException $exception) {
        }

        return false;
    }
}
