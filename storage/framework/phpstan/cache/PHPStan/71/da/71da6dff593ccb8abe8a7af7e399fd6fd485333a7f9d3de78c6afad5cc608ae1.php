<?php declare(strict_types = 1);

// odsl-C:\laragon\www\sanfaani-schools\app\Models\SchoolResultAccessPolicyRule.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\SchoolResultAccessPolicyRule
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.0-8.3.30-97e3a04aa2ff10ba58d6aeecca8c1e3addb3d65d6ad7a074bb7a076dd3be8eb2',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\SchoolResultAccessPolicyRule',
        'filename' => 'C:/laragon/www/sanfaani-schools/app/Models/SchoolResultAccessPolicyRule.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\SchoolResultAccessPolicyRule',
    'shortName' => 'SchoolResultAccessPolicyRule',
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
    'endLine' => 55,
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
        'declaringClassName' => 'App\\Models\\SchoolResultAccessPolicyRule',
        'implementingClassName' => 'App\\Models\\SchoolResultAccessPolicyRule',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'school_result_access_policy_id\', \'academic_session_id\', \'term_id\', \'result_type\', \'access_scope\', \'max_access_per_student\', \'max_access_per_card\', \'requires_scratch_card\', \'allows_parent_payment\', \'allows_school_paid_access\', \'allows_pdf_download\', \'status\', \'starts_at\', \'ends_at\', \'metadata\']',
          'attributes' => 
          array (
            'startLine' => 13,
            'endLine' => 29,
            'startTokenPos' => 43,
            'startFilePos' => 278,
            'endTokenPos' => 90,
            'endFilePos' => 700,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 13,
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
      'casts' => 
      array (
        'declaringClassName' => 'App\\Models\\SchoolResultAccessPolicyRule',
        'implementingClassName' => 'App\\Models\\SchoolResultAccessPolicyRule',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'requires_scratch_card\' => \'boolean\', \'allows_parent_payment\' => \'boolean\', \'allows_school_paid_access\' => \'boolean\', \'allows_pdf_download\' => \'boolean\', \'starts_at\' => \'datetime\', \'ends_at\' => \'datetime\', \'metadata\' => \'array\']',
          'attributes' => 
          array (
            'startLine' => 31,
            'endLine' => 39,
            'startTokenPos' => 99,
            'startFilePos' => 727,
            'endTokenPos' => 150,
            'endFilePos' => 1018,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 31,
        'endLine' => 39,
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
      'policy' => 
      array (
        'name' => 'policy',
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
        'declaringClassName' => 'App\\Models\\SchoolResultAccessPolicyRule',
        'implementingClassName' => 'App\\Models\\SchoolResultAccessPolicyRule',
        'currentClassName' => 'App\\Models\\SchoolResultAccessPolicyRule',
        'aliasName' => NULL,
      ),
      'academicSession' => 
      array (
        'name' => 'academicSession',
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
        'startLine' => 46,
        'endLine' => 49,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\SchoolResultAccessPolicyRule',
        'implementingClassName' => 'App\\Models\\SchoolResultAccessPolicyRule',
        'currentClassName' => 'App\\Models\\SchoolResultAccessPolicyRule',
        'aliasName' => NULL,
      ),
      'term' => 
      array (
        'name' => 'term',
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
        'startLine' => 51,
        'endLine' => 54,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\SchoolResultAccessPolicyRule',
        'implementingClassName' => 'App\\Models\\SchoolResultAccessPolicyRule',
        'currentClassName' => 'App\\Models\\SchoolResultAccessPolicyRule',
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