<?php

namespace CirclicalUser\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CirclicalUser\Entity\UserAtom
 *
 * @ORM\Entity
 * @ORM\Table(name="users_atoms",indexes={
 *    @ORM\Index(name="lookup_idx", columns={"key", "value"}),
 * });
 */
class UserAtom
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false, options={"unsigned"=true})
     */
    private $user_id;


    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string", length=255, name="`key`")
     */
    private $key;


    /**
     * @var string
     * @ORM\Column(type="string", length=255, name="`value`")
     */
    private $value;

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

}