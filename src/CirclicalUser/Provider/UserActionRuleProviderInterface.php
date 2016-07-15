<?php

namespace CirclicalUser\Provider;


interface UserActionRuleProviderInterface
{

    /**
     * @param               $string
     * @param UserInterface $user
     *
     * @return UserActionRuleInterface
     */
    public function getUserStringActions($string, UserInterface $user);


    /**
     * @param ResourceInterface $resource
     * @param UserInterface     $user
     *
     * @return UserActionRuleInterface
     */
    public function getUserResourceActions(ResourceInterface $resource, UserInterface $user);


    public function update($rule);


    public function create(UserInterface $user, $resourceClass, $resourceId, array $actions) : UserActionRuleInterface;


    public function save($rule);
}