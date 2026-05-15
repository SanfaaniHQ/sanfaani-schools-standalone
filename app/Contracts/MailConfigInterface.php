<?php

namespace App\Contracts;

interface MailConfigInterface
{
    public static function configure(int $schoolId): void;
}
