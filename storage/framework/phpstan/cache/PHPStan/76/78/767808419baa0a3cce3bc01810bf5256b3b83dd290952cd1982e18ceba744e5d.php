<?php declare(strict_types = 1);

// odsl-C:\laragon\www\sanfaani-schools\app\Models\SchoolSubscription.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\SchoolSubscription
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.0-8.3.30-a4baef279b221eb28a184a38ca453005275947253383010f44d6dc5971e960ad',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\SchoolSubscription',
        'filename' => 'C:/laragon/www/sanfaani-schools/app/Models/SchoolSubscription.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\SchoolSubscription',
    'shortName' => 'SchoolSubscription',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 9,
    'endLine' => 84,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Database\\Eloquent\\Model',
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
      0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
      'fillable' => 
      array (
        'declaringClassName' => 'App\\Models\\SchoolSubscription',
        'implementingClassName' => 'App\\Models\\SchoolSubscription',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'school_id\', \'subscription_plan_id\', \'status\', \'starts_at\', \'ends_at\', \'trial_ends_at\', \'grace_ends_at\', \'billing_cycle\', \'pricing_model\', \'price\', \'currency\', \'student_count\', \'amount_due\', \'amount_paid\', \'payment_status\', \'payment_reference\', \'activated_by\', \'upgraded_from_subscription_id\', \'downgraded_from_subscription_id\', \'superseded_by_subscription_id\', \'plan_name_snapshot\', \'price_snapshot\', \'billing_cycle_snapshot\', \'pricing_model_snapshot\', \'features_snapshot\', \'metadata\']',
          'attributes' => 
          array (
            'startLine' => 13,
            'endLine' => 40,
            'startTokenPos' => 43,
            'startFilePos' => 268,
            'endTokenPos' => 123,
            'endFilePos' => 969,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 13,
        'endLine' => 40,
        'startColumn' => 5,
        'endColumn' => 6,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'casts' => 
      array (
        'declaringClassName' => 'App\\Models\\SchoolSubscription',
        'implementingClassName' => 'App\\Models\\SchoolSubscription',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'starts_at\' => \'datetime\', \'ends_at\' => \'datetime\', \'trial_ends_at\' => \'datetime\', \'grace_ends_at\' => \'datetime\', \'price\' => \'decimal:2\', \'amount_due\' => \'decimal:2\', \'amount_paid\' => \'decimal:2\', \'price_snapshot\' => \'decimal:2\', \'features_snapshot\' => \'array\', \'metadata\' => \'array\']',
          'attributes' => 
          array (
            'startLine' => 42,
            'endLine' => 53,
            'startTokenPos' => 132,
            'startFilePos' => 996,
            'endTokenPos' => 204,
            'endFilePos' => 1367,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 42,
        'endLine' => 53,
        'startColumn' => 5,
        'endColumn' => 6,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
    ),
    'immediateMethods' => 
    array (
      'school' => 
      array (
        'name' => 'school',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 55,
        'endLine' => 58,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\SchoolSubscription',
        'implementingClassName' => 'App\\Models\\SchoolSubscription',
        'currentClassName' => 'App\\Models\\SchoolSubscription',
        'aliasName' => NULL,
      ),
      'subscriptionPlan' => 
      array (
        'name' => 'subscriptionPlan',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 60,
        'endLine' => 63,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\SchoolSubscription',
        'implementingClassName' => 'App\\Models\\SchoolSubscription',
        'currentClassName' => 'App\\Models\\SchoolSubscription',
        'aliasName' => NULL,
      ),
      'activatedBy' => 
      array (
        'name' => 'activatedBy',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 65,
        'endLine' => 68,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\SchoolSubscription',
        'implementingClassName' => 'App\\Models\\SchoolSubscription',
        'currentClassName' => 'App\\Models\\SchoolSubscription',
        'aliasName' => NULL,
      ),
      'upgradedFrom' => 
      array (
        'name' => 'upgradedFrom',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 70,
        'endLine' => 73,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\SchoolSubscription',
        'implementingClassName' => 'App\\Models\\SchoolSubscription',
        'currentClassName' => 'App\\Models\\SchoolSubscription',
        'aliasName' => NULL,
      ),
      'downgradedFrom' => 
      array (
        'name' => 'downgradedFrom',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 75,
        'endLine' => 78,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\SchoolSubscription',
        'implementingClassName' => 'App\\Models\\SchoolSubscription',
        'currentClassName' => 'App\\Models\\SchoolSubscription',
        'aliasName' => NULL,
      ),
      'supersededBy' => 
      array (
        'name' => 'supersededBy',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 80,
        'endLine' => 83,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\SchoolSubscription',
        'implementingClassName' => 'App\\Models\\SchoolSubscription',
        'currentClassName' => 'App\\Models\\SchoolSubscription',
        'aliasName' => NULL,
      ),
    ),
    'traitsData' => 
    array (
      'aliases' => 
      array (
      ),
      'modifiers' => 
      array (
      ),
      'precedences' => 
      array (
      ),
      'hashes' => 
      array (
      ),
    ),
  ),
));