<?php declare(strict_types = 1);

// odsl-C:\laragon\www\sanfaani-schools\app\Models\SupportThreadEvent.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\SupportThreadEvent
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.0-8.3.30-2d0d45b779d81f96827b7f72939d958af1bf26060222c0584403109e35e442a8',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\SupportThreadEvent',
        'filename' => 'C:/laragon/www/sanfaani-schools/app/Models/SupportThreadEvent.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\SupportThreadEvent',
    'shortName' => 'SupportThreadEvent',
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
    'endLine' => 45,
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
        'declaringClassName' => 'App\\Models\\SupportThreadEvent',
        'implementingClassName' => 'App\\Models\\SupportThreadEvent',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'support_thread_id\', \'school_id\', \'actor_id\', \'actor_role\', \'event_type\', \'title\', \'body\', \'old_values\', \'new_values\', \'metadata\', \'occurred_at\']',
          'attributes' => 
          array (
            'startLine' => 10,
            'endLine' => 22,
            'startTokenPos' => 33,
            'startFilePos' => 192,
            'endTokenPos' => 68,
            'endFilePos' => 432,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 10,
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
      'casts' => 
      array (
        'declaringClassName' => 'App\\Models\\SupportThreadEvent',
        'implementingClassName' => 'App\\Models\\SupportThreadEvent',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'old_values\' => \'array\', \'new_values\' => \'array\', \'metadata\' => \'array\', \'occurred_at\' => \'datetime\']',
          'attributes' => 
          array (
            'startLine' => 24,
            'endLine' => 29,
            'startTokenPos' => 77,
            'startFilePos' => 459,
            'endTokenPos' => 107,
            'endFilePos' => 599,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 24,
        'endLine' => 29,
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
      'thread' => 
      array (
        'name' => 'thread',
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
        'startLine' => 31,
        'endLine' => 34,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\SupportThreadEvent',
        'implementingClassName' => 'App\\Models\\SupportThreadEvent',
        'currentClassName' => 'App\\Models\\SupportThreadEvent',
        'aliasName' => NULL,
      ),
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
        'startLine' => 36,
        'endLine' => 39,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\SupportThreadEvent',
        'implementingClassName' => 'App\\Models\\SupportThreadEvent',
        'currentClassName' => 'App\\Models\\SupportThreadEvent',
        'aliasName' => NULL,
      ),
      'actor' => 
      array (
        'name' => 'actor',
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
        'startLine' => 41,
        'endLine' => 44,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\SupportThreadEvent',
        'implementingClassName' => 'App\\Models\\SupportThreadEvent',
        'currentClassName' => 'App\\Models\\SupportThreadEvent',
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