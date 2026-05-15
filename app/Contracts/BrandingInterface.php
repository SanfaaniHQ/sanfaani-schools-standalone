<?php

namespace App\Contracts;

use App\Models\School;
use stdClass;

interface BrandingInterface
{
    public function current(): stdClass;

    public function forSchool(?School $school = null): stdClass;
}
