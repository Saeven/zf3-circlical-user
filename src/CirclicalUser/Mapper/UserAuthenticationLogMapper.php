<?php

namespace CirclicalUser\Mapper;


/**
 * Class UserAuthenticationLogMapper
 *
 * Log when users authenticate
 *
 * @package CirclicalUser\Mapper
 */

use CirclicalUser\Entity\UserAuthenticationLog;

class UserAuthenticationLogMapper extends AbstractDoctrineMapper
{
    protected $entityName = UserAuthenticationLog::class;

}