<?php declare(strict_types = 1);

// odsl-C:\laragon\www\sanfaani-schools\app\Models\SupportEscalationHistory.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\SupportEscalationHistory
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.0-8.3.30-cde4efa9d10259993eb0debb0882ab0487edde54514ddb32a085d7cabaf4a11e',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\SupportEscalationHistory',
        'filename' => 'C:/laragon/www/sanfaani-schools/app/Models/SupportEscalationHistory.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\SupportEscalationHistory',
    'shortName' => 'SupportEscalationHistory',
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
    'endLine' => 44,
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
        'declaringClassName' => 'App\\Models\\SupportEscalationHistory',
        'implementingClassName' => 'App\\Models\\SupportEscalationHistory',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'support_thread_id\', \'school_id\', \'escalated_by\', \'from_role\', \'to_role\', \'from_level\', \'to_level\', \'reason\', \'escalated_at\', \'metadata\']',
          'attributes' => 
          array (
            'startLine' => 10,
            'endLine' => 21,
            'startTokenPos' => 33,
            'startFilePos' => 198,
            'endTokenPos' => 65,
            'endFilePos' => 422,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 10,
        'endLine' => 21,
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
        'declaringClassName' => 'App\\Models\\SupportEscalationHistory',
        'implementingClassName' => 'App\\Models\\SupportEscalationHistory',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'from_level\' => \'integer\', \'to_level\' => \'integer\', \'escalated_at\' => \'datetime\', \'metadata\' => \'array\']',
          'attributes' => 
          array (
            'startLine' => 23,
            'endLine' => 28,
            'startTokenPos' => 74,
            'startFilePos' => 449,
            'endTokenPos' => 104,
            'endFilePos' => 592,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 23,
        'endLine' => 28,
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
        'startLine' => 30,
        'endLine' => 33,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\SupportEscalationHistory',
        'implementingClassName' => 'App\\Models\\SupportEscalationHistory',
        'currentClassName' => 'App\\Models\\SupportEscalationHistory',
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
        'startLine' => 35,
        'endLine' => 38,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\SupportEscalationHistory',
        'implementingClassName' => 'App\\Models\\SupportEscalationHistory',
        'currentClassName' => 'App\\Models\\SupportEscalationHistory',
        'aliasName' => NULL,
      ),
      'escalatedBy' => 
      array (
        'name' => 'escalatedBy',
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
        'startLine' => 40,
        'endLine' => 43,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\SupportEscalationHistory',
        'implementingClassName' => 'App\\Models\\SupportEscalationHistory',
        'currentClassName' => 'App\\Models\\SupportEscalationHistory',
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