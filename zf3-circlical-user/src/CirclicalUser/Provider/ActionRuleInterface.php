<?php

namespace CirclicalUser\Provider;


interface ActionRuleInterface
{
    public function getResourceClass() : string;

    public function getResourceId();

    public function getRole();

    public function getActions() : array;

    public function getUserExceptions();

}