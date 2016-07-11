<?php

namespace CirclicalUser\Provider;

interface RoleInterface
{
    public function getId() : int;

    public function getName() : string;

    public function getParent();
}