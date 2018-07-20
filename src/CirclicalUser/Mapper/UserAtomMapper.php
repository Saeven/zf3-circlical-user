<?php

namespace CirclicalUser\Mapper;

use CirclicalUser\Provider\UserInterface as User;
use CirclicalUser\Entity\UserAtom;


/**
 * Class UserAtomMapper
 *
 * A convenience class, this lets you drop nuggets of information to support a user with an easy-to-use API.
 *
 * @package CirclicalUser\Mapper
 */
class UserAtomMapper extends AbstractDoctrineMapper
{
    protected $entityName = UserAtom::class;

    /**
     * Get an atomic piece of user data
     *
     * @param User $user
     * @param      $key
     * @param bool $detachFromEntityManager
     *
     * @return UserAtom|null
     */
    public function getAtom(User $user, $key, bool $detachFromEntityManager = true)
    {
        if ($atom = $this->getRepository()->findOneBy(['user_id' => $user->getId(), 'key' => $key])) {
            if ($detachFromEntityManager) {
                $this->getEntityManager()->detach($atom);
            }

            return $atom;
        }

        return null;
    }

    public function deleteAtom(UserAtom $atom)
    {
        $this->getEntityManager()->remove($atom);
    }

    /**
     * Set a particular atom on a user
     *
     * @param User $user
     * @param      $key
     * @param      $value
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function setAtom(User $user, $key, $value)
    {

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare('INSERT INTO users_atoms ( user_id, `key`, `value`) VALUES( ?, ?, ? ) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)');

        $user_id = $user->getId();
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $key);
        $stmt->bindParam(3, $value);
        $stmt->execute();
    }

    /**
     * Key-value pair search.
     *
     * @param string $key
     * @param string $value
     *
     * @return array
     */
    public function search(string $key, string $value)
    {
        return $this->getRepository()->findBy(['key' => $key, 'value' => $value]);
    }


}