<?php

namespace App\Exceptions;

use RuntimeException;

class MailConfigurationException extends RuntimeException
{
    public function __construct(
        public readonly string $category,
        string $message
    ) {
        parent::__construct($message);
    }
}
