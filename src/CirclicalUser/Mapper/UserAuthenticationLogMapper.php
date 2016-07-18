<?php

namespace CirclicalUser\Mapper;


/**
 * Class UserAuthenticationLogMapper
 *
 * Log when users authenticate
 *
 * @package CirclicalUser\Mapper
 */
class UserAuthenticationLogMapper extends AbstractDoctrineMapper
{
    protected $entityName = 'CirclicalUser\Entity\UserAuthenticationLog';

}