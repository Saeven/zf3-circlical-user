<?php

namespace CirclicalUser\Provider;

interface AuthenticationRecordInterface
{

    public function setUsername($usernameOrEmail);

    public function getSessionKey() : string;

    public function setSessionKey($rawKey);

    public function getUsername() : string;

    public function getUserId();


}
