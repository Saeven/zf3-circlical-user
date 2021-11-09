<?php

declare(strict_types=1);

namespace CirclicalUser\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryType;

/**
 * You can use this as a convenience method for cases where you would like to have public/private UUID situations.
 * UUIDs are great for anti-scrape identifiers, but if you are storing them as char-36 they become very expensive
 * primary keys from a performance perspective.
 *
 * This pattern, is a common in-the-field pattern to keep keys lightweight, yet give you a unique public UUID that
 * can be used to keep the scrapers at bay if you need public object identifier.
 */
trait SecretIdPublicUuidTrait
{
    /**
     * The unique auto incremented primary key.
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     *
     * @var int|null
     */
    protected $id;

    /**
     * The internal primary identity key.
     *
     * @ORM\Column(type="uuid_binary", unique=true);
     *
     * @var UuidBinaryType
     */
    protected $uuid;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidBinaryType
    {
        return $this->uuid;
    }
}
