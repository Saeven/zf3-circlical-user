<?php

declare(strict_types=1);

namespace CirclicalUser\Mapper;

use CirclicalUser\Entity\UserAuthenticationLog;

class UserAuthenticationLogMapper extends AbstractDoctrineMapper
{
    protected string $entityName = UserAuthenticationLog::class;
}
