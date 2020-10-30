<?php

namespace CirclicalUser\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * You can use this as a convenience method for cases where you would like to have public/private UUID situations.
 * UUIDs are great for anti-scrape identifiers, but if you are storing them as char-36 they become very expensive
 * primary keys from a performance perspective.
 *
 * This pattern, is a common in-the-field pattern to keep keys lightweight, yet give you a unique public UUID that
 * can be used to keep the scrapers at bay if you need public object identifier.
 *
 * Trait SecretIdPublicUuidTrait
 * @package CirclicalUser\Entity
 */
trait SecretIdPublicUuidTrait
{
    /**
     * The unique auto incremented primary key.
     *
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * The internal primary identity key.
     *
     * @ORM\Column(type="uuid_binary", unique=true);
     */
    protected $uuid;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid()
    {
        return $this->uuid;
    }
}