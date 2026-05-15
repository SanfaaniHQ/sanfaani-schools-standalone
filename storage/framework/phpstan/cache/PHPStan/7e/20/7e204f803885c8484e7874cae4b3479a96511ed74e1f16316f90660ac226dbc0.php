<?php declare(strict_types = 1);

// odsl-C:\laragon\www\sanfaani-schools\app\Models\GradingScale.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\GradingScale
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.0-8.3.30-98f6aa39ebd4eb0891a6075566ef58b0acd5ae796e7715cb825876ff545c0415',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\GradingScale',
        'filename' => 'C:/laragon/www/sanfaani-schools/app/Models/GradingScale.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\GradingScale',
    'shortName' => 'GradingScale',
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
    'endLine' => 35,
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
        'declaringClassName' => 'App\\Models\\GradingScale',
        'implementingClassName' => 'App\\Models\\GradingScale',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'school_id\', \'name\', \'min_score\', \'max_score\', \'grade\', \'remark\', \'is_pass\', \'sort_order\', \'status\']',
          'attributes' => 
          array (
            'startLine' => 13,
            'endLine' => 23,
            'startTokenPos' => 43,
            'startFilePos' => 262,
            'endTokenPos' => 72,
            'endFilePos' => 441,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 13,
        'endLine' => 23,
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
        'declaringClassName' => 'App\\Models\\GradingScale',
        'implementingClassName' => 'App\\Models\\GradingScale',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'min_score\' => \'decimal:2\', \'max_score\' => \'decimal:2\', \'is_pass\' => \'boolean\']',
          'attributes' => 
          array (
            'startLine' => 25,
            'endLine' => 29,
            'startTokenPos' => 81,
            'startFilePos' => 468,
            'endTokenPos' => 104,
            'endFilePos' => 578,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 25,
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
        'declaringClassName' => 'App\\Models\\GradingScale',
        'implementingClassName' => 'App\\Models\\GradingScale',
        'currentClassName' => 'App\\Models\\GradingScale',
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