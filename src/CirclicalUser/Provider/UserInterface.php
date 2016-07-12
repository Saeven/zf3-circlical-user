<?php

namespace CirclicalUser\Provider;

interface UserInterface
{
    public function getId();

    public function getRoles();

    public function getEmail();
}