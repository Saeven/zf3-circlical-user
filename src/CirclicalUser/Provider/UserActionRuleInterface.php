<?php

namespace CirclicalUser\Provider;


interface UserActionRuleInterface
{
    public function getResourceClass() : string;

    public function getResourceId();

    public function getUser();

    public function getActions() : array;

    public function addAction($action);

    public function removeAction($action);

}