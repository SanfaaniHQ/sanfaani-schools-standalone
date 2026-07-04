<?php

namespace App\Contracts;

interface SchoolAwareMailNotification
{
    public function schoolIdForMail(object $notifiable): ?int;
}
