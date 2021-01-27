<?php

namespace CirclicalUser\Validator;

use CirclicalUser\Provider\PasswordCheckerInterface;
use Zend\Validator\AbstractValidator;

class PasswordValidator extends AbstractValidator
{
    public const WEAK_PASSWORD = 'weakPassword';

    protected $messageTemplates = [
        self::WEAK_PASSWORD => 'That password is common, or could be easily guessed or generated. Please create a stronger password.',
    ];

    private $passwordChecker;
    private $options;

    public function __construct(PasswordCheckerInterface $passwordChecker, array $options = null)
    {
        $this->passwordChecker = $passwordChecker;
        $this->options = $options;

        parent::__construct($options);
    }

    public function isValid($value, $context = null): bool
    {
        $userData = [];
        if (is_array($context) && is_array($this->options) && isset($this->options['user_data']) && is_array($this->options['user_data'])) {
            foreach ($this->options['user_data'] as $key) {
                if (is_string($context[$key])) {
                    $userData[] = $context[$key];
                }
            }
        }

        if (!$this->passwordChecker->isStrongPassword($value, $userData)) {
            $this->error(self::WEAK_PASSWORD);

            return false;
        }

        return true;
    }
}
