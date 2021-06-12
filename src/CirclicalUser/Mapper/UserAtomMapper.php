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
     */
    public function getAtom(User $user, string $key, bool $detachFromEntityManager = true): ?UserAtom
    {
        if ($atom = $this->getRepository()->findOneBy(['user' => $user, 'key' => $key])) {
            if ($detachFromEntityManager) {
                $this->getEntityManager()->detach($atom);
            }

            return $atom;
        }

        return null;
    }

    /**
     * Key-value pair search.
     */
    public function search(string $key, string $value): array
    {
        return $this->getRepository()->findBy(['key' => $key, 'value' => $value]);
    }
}
