<?php

declare(strict_types=1);

namespace CirclicalUser\Validator;

use CirclicalUser\Provider\PasswordCheckerInterface;
use Laminas\Validator\AbstractValidator;

use function is_array;
use function is_string;

class PasswordValidator extends AbstractValidator
{
    public const WEAK_PASSWORD = 'weakPassword';

    protected array $messageTemplates = [
        self::WEAK_PASSWORD => 'That password is common, or could be easily guessed or generated. Please create a stronger password.',
    ];

    private PasswordCheckerInterface $passwordChecker;

    private ?array $options;

    /**
     * @inheritDoc
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function __construct(PasswordCheckerInterface $passwordChecker, ?array $options = null)
    {
        $this->passwordChecker = $passwordChecker;
        $this->options = $options;

        parent::__construct($options);
    }

    public function isValid(mixed $value, ?array $context = null): bool
    {
        $userData = [];
        if (is_array($context) && $this->options && isset($this->options['user_data']) && is_array($this->options['user_data'])) {
            foreach ($this->options['user_data'] as $key) {
                if (is_string($context[$key] ?? null)) {
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
