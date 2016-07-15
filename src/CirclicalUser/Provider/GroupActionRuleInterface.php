<?php

namespace CirclicalUser\Provider;


interface GroupActionRuleInterface
{
    public function getResourceClass() : string;

    public function getResourceId();

    public function getRole();

    public function getActions() : array;

    public function addAction($action);

    public function removeAction($action);

}