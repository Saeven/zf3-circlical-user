<?php

namespace CirclicalUser\Provider;

interface RoleProviderInterface
{
    public function getAllRoles() : array;
}