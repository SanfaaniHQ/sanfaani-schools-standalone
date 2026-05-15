<?php declare(strict_types = 1);

// odsl-C:\laragon\www\sanfaani-schools\app\Models\ClassSubjectAssignment.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\ClassSubjectAssignment
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.0-8.3.30-e61a2cd006e9e906cabf96438a2577732c2d95dce2c60e9210ede0abbd90cb59',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\ClassSubjectAssignment',
        'filename' => 'C:/laragon/www/sanfaani-schools/app/Models/ClassSubjectAssignment.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\ClassSubjectAssignment',
    'shortName' => 'ClassSubjectAssignment',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 10,
    'endLine' => 67,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Database\\Eloquent\\Model',
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
      0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
      1 => 'Illuminate\\Database\\Eloquent\\SoftDeletes',
    ),
    'immediateConstants' => 
    array (
      'TYPES' => 
      array (
        'declaringClassName' => 'App\\Models\\ClassSubjectAssignment',
        'implementingClassName' => 'App\\Models\\ClassSubjectAssignment',
        'name' => 'TYPES',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '[\'core\', \'elective\', \'optional\', \'religious\', \'vocational\', \'custom\']',
          'attributes' => 
          array (
            'startLine' => 15,
            'endLine' => 22,
            'startTokenPos' => 55,
            'startFilePos' => 338,
            'endTokenPos' => 75,
            'endFilePos' => 461,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 15,
        'endLine' => 22,
        'startColumn' => 5,
        'endColumn' => 6,
      ),
    ),
    'immediateProperties' => 
    array (
      'fillable' => 
      array (
        'declaringClassName' => 'App\\Models\\ClassSubjectAssignment',
        'implementingClassName' => 'App\\Models\\ClassSubjectAssignment',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'school_id\', \'school_class_id\', \'subject_id\', \'academic_session_id\', \'term_id\', \'assignment_type\', \'is_elective\', \'is_required\', \'status\', \'metadata\']',
          'attributes' => 
          array (
            'startLine' => 24,
            'endLine' => 35,
            'startTokenPos' => 84,
            'startFilePos' => 491,
            'endTokenPos' => 116,
            'endFilePos' => 728,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 24,
        'endLine' => 35,
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
        'declaringClassName' => 'App\\Models\\ClassSubjectAssignment',
        'implementingClassName' => 'App\\Models\\ClassSubjectAssignment',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'is_elective\' => \'boolean\', \'is_required\' => \'boolean\', \'metadata\' => \'array\']',
          'attributes' => 
          array (
            'startLine' => 37,
            'endLine' => 41,
            'startTokenPos' => 125,
            'startFilePos' => 755,
            'endTokenPos' => 148,
            'endFilePos' => 864,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 37,
        'endLine' => 41,
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
        'startLine' => 43,
        'endLine' => 46,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\ClassSubjectAssignment',
        'implementingClassName' => 'App\\Models\\ClassSubjectAssignment',
        'currentClassName' => 'App\\Models\\ClassSubjectAssignment',
        'aliasName' => NULL,
      ),
      'schoolClass' => 
      array (
        'name' => 'schoolClass',
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
        'startLine' => 48,
        'endLine' => 51,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\ClassSubjectAssignment',
        'implementingClassName' => 'App\\Models\\ClassSubjectAssignment',
        'currentClassName' => 'App\\Models\\ClassSubjectAssignment',
        'aliasName' => NULL,
      ),
      'subject' => 
      array (
        'name' => 'subject',
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
        'startLine' => 53,
        'endLine' => 56,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\ClassSubjectAssignment',
        'implementingClassName' => 'App\\Models\\ClassSubjectAssignment',
        'currentClassName' => 'App\\Models\\ClassSubjectAssignment',
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
        'startLine' => 58,
        'endLine' => 61,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\ClassSubjectAssignment',
        'implementingClassName' => 'App\\Models\\ClassSubjectAssignment',
        'currentClassName' => 'App\\Models\\ClassSubjectAssignment',
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
        'startLine' => 63,
        'endLine' => 66,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\ClassSubjectAssignment',
        'implementingClassName' => 'App\\Models\\ClassSubjectAssignment',
        'currentClassName' => 'App\\Models\\ClassSubjectAssignment',
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