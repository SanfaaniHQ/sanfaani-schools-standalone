<?php declare(strict_types = 1);

// odsl-C:\laragon\www\sanfaani-schools\app\Models\LeadOwnershipHistory.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\LeadOwnershipHistory
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.0-8.3.30-859f3215f047ae49ea5521395e78ec2f467eb577a1f190d6aca171ffee11f6ed',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\LeadOwnershipHistory',
        'filename' => 'C:/laragon/www/sanfaani-schools/app/Models/LeadOwnershipHistory.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\LeadOwnershipHistory',
    'shortName' => 'LeadOwnershipHistory',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 8,
    'endLine' => 43,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Database\\Eloquent\\Model',
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
      'fillable' => 
      array (
        'declaringClassName' => 'App\\Models\\LeadOwnershipHistory',
        'implementingClassName' => 'App\\Models\\LeadOwnershipHistory',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'lead_request_id\', \'old_assigned_to\', \'new_assigned_to\', \'changed_by\', \'changed_at\', \'metadata\']',
          'attributes' => 
          array (
            'startLine' => 10,
            'endLine' => 17,
            'startTokenPos' => 33,
            'startFilePos' => 194,
            'endTokenPos' => 53,
            'endFilePos' => 345,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 10,
        'endLine' => 17,
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
        'declaringClassName' => 'App\\Models\\LeadOwnershipHistory',
        'implementingClassName' => 'App\\Models\\LeadOwnershipHistory',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'changed_at\' => \'datetime\', \'metadata\' => \'array\']',
          'attributes' => 
          array (
            'startLine' => 19,
            'endLine' => 22,
            'startTokenPos' => 62,
            'startFilePos' => 372,
            'endTokenPos' => 78,
            'endFilePos' => 445,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 19,
        'endLine' => 22,
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
      'leadRequest' => 
      array (
        'name' => 'leadRequest',
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
        'startLine' => 24,
        'endLine' => 27,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\LeadOwnershipHistory',
        'implementingClassName' => 'App\\Models\\LeadOwnershipHistory',
        'currentClassName' => 'App\\Models\\LeadOwnershipHistory',
        'aliasName' => NULL,
      ),
      'oldOwner' => 
      array (
        'name' => 'oldOwner',
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
        'startLine' => 29,
        'endLine' => 32,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\LeadOwnershipHistory',
        'implementingClassName' => 'App\\Models\\LeadOwnershipHistory',
        'currentClassName' => 'App\\Models\\LeadOwnershipHistory',
        'aliasName' => NULL,
      ),
      'newOwner' => 
      array (
        'name' => 'newOwner',
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
        'startLine' => 34,
        'endLine' => 37,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\LeadOwnershipHistory',
        'implementingClassName' => 'App\\Models\\LeadOwnershipHistory',
        'currentClassName' => 'App\\Models\\LeadOwnershipHistory',
        'aliasName' => NULL,
      ),
      'changedBy' => 
      array (
        'name' => 'changedBy',
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
        'startLine' => 39,
        'endLine' => 42,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\LeadOwnershipHistory',
        'implementingClassName' => 'App\\Models\\LeadOwnershipHistory',
        'currentClassName' => 'App\\Models\\LeadOwnershipHistory',
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