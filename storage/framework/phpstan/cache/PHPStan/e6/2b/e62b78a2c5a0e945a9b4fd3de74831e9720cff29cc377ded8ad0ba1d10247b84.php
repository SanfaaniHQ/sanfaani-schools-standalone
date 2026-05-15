<?php declare(strict_types = 1);

// odsl-C:\laragon\www\sanfaani-schools\app\Models\StudentResult.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\StudentResult
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.0-8.3.30-c323d42982e6b8579acf1b4cf882465f72b84760659367f2fdf308f25a563ad5',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\StudentResult',
        'filename' => 'C:/laragon/www/sanfaani-schools/app/Models/StudentResult.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\StudentResult',
    'shortName' => 'StudentResult',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 11,
    'endLine' => 126,
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
      'STATUSES' => 
      array (
        'declaringClassName' => 'App\\Models\\StudentResult',
        'implementingClassName' => 'App\\Models\\StudentResult',
        'name' => 'STATUSES',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '[\\App\\Enums\\ResultWorkflowStatus::Draft->value, \\App\\Enums\\ResultWorkflowStatus::Submitted->value, \\App\\Enums\\ResultWorkflowStatus::Returned->value, \\App\\Enums\\ResultWorkflowStatus::Reviewed->value, \\App\\Enums\\ResultWorkflowStatus::Approved->value, \\App\\Enums\\ResultWorkflowStatus::Published->value, \\App\\Enums\\ResultWorkflowStatus::Unpublished->value, \\App\\Enums\\ResultWorkflowStatus::Voided->value, \\App\\Enums\\ResultWorkflowStatus::Archived->value]',
          'attributes' => 
          array (
            'startLine' => 16,
            'endLine' => 26,
            'startTokenPos' => 60,
            'startFilePos' => 368,
            'endTokenPos' => 125,
            'endFilePos' => 797,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 16,
        'endLine' => 26,
        'startColumn' => 5,
        'endColumn' => 6,
      ),
    ),
    'immediateProperties' => 
    array (
      'fillable' => 
      array (
        'declaringClassName' => 'App\\Models\\StudentResult',
        'implementingClassName' => 'App\\Models\\StudentResult',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'school_id\', \'student_id\', \'school_class_id\', \'subject_id\', \'academic_session_id\', \'term_id\', \'result_type\', \'ca_score\', \'exam_score\', \'total_score\', \'grade\', \'remark\', \'teacher_remark\', \'officer_remark\', \'admin_remark\', \'status\', \'published_at\', \'published_by\', \'unpublished_at\', \'unpublished_by\', \'unpublish_reason\', \'recorded_by\', \'teacher_result_submission_id\']',
          'attributes' => 
          array (
            'startLine' => 28,
            'endLine' => 52,
            'startTokenPos' => 134,
            'startFilePos' => 827,
            'endTokenPos' => 205,
            'endFilePos' => 1383,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 28,
        'endLine' => 52,
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
        'declaringClassName' => 'App\\Models\\StudentResult',
        'implementingClassName' => 'App\\Models\\StudentResult',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'ca_score\' => \'decimal:2\', \'exam_score\' => \'decimal:2\', \'total_score\' => \'decimal:2\', \'published_at\' => \'datetime\', \'unpublished_at\' => \'datetime\']',
          'attributes' => 
          array (
            'startLine' => 54,
            'endLine' => 60,
            'startTokenPos' => 214,
            'startFilePos' => 1410,
            'endTokenPos' => 251,
            'endFilePos' => 1604,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 54,
        'endLine' => 60,
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
        'startLine' => 62,
        'endLine' => 65,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\StudentResult',
        'implementingClassName' => 'App\\Models\\StudentResult',
        'currentClassName' => 'App\\Models\\StudentResult',
        'aliasName' => NULL,
      ),
      'student' => 
      array (
        'name' => 'student',
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
        'startLine' => 67,
        'endLine' => 70,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\StudentResult',
        'implementingClassName' => 'App\\Models\\StudentResult',
        'currentClassName' => 'App\\Models\\StudentResult',
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
        'startLine' => 72,
        'endLine' => 75,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\StudentResult',
        'implementingClassName' => 'App\\Models\\StudentResult',
        'currentClassName' => 'App\\Models\\StudentResult',
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
        'startLine' => 77,
        'endLine' => 80,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\StudentResult',
        'implementingClassName' => 'App\\Models\\StudentResult',
        'currentClassName' => 'App\\Models\\StudentResult',
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
        'startLine' => 82,
        'endLine' => 85,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\StudentResult',
        'implementingClassName' => 'App\\Models\\StudentResult',
        'currentClassName' => 'App\\Models\\StudentResult',
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
        'startLine' => 87,
        'endLine' => 90,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\StudentResult',
        'implementingClassName' => 'App\\Models\\StudentResult',
        'currentClassName' => 'App\\Models\\StudentResult',
        'aliasName' => NULL,
      ),
      'publishedBy' => 
      array (
        'name' => 'publishedBy',
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
        'startLine' => 92,
        'endLine' => 95,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\StudentResult',
        'implementingClassName' => 'App\\Models\\StudentResult',
        'currentClassName' => 'App\\Models\\StudentResult',
        'aliasName' => NULL,
      ),
      'unpublishedBy' => 
      array (
        'name' => 'unpublishedBy',
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
        'startLine' => 97,
        'endLine' => 100,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\StudentResult',
        'implementingClassName' => 'App\\Models\\StudentResult',
        'currentClassName' => 'App\\Models\\StudentResult',
        'aliasName' => NULL,
      ),
      'recordedBy' => 
      array (
        'name' => 'recordedBy',
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
        'startLine' => 102,
        'endLine' => 105,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\StudentResult',
        'implementingClassName' => 'App\\Models\\StudentResult',
        'currentClassName' => 'App\\Models\\StudentResult',
        'aliasName' => NULL,
      ),
      'teacherResultSubmission' => 
      array (
        'name' => 'teacherResultSubmission',
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
        'startLine' => 107,
        'endLine' => 110,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\StudentResult',
        'implementingClassName' => 'App\\Models\\StudentResult',
        'currentClassName' => 'App\\Models\\StudentResult',
        'aliasName' => NULL,
      ),
      'workflowStatus' => 
      array (
        'name' => 'workflowStatus',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
          'data' => 
          array (
            'types' => 
            array (
              0 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'App\\Enums\\ResultWorkflowStatus',
                  'isIdentifier' => false,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 112,
        'endLine' => 115,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\StudentResult',
        'implementingClassName' => 'App\\Models\\StudentResult',
        'currentClassName' => 'App\\Models\\StudentResult',
        'aliasName' => NULL,
      ),
      'isLockedAfterApproval' => 
      array (
        'name' => 'isLockedAfterApproval',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 117,
        'endLine' => 120,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\StudentResult',
        'implementingClassName' => 'App\\Models\\StudentResult',
        'currentClassName' => 'App\\Models\\StudentResult',
        'aliasName' => NULL,
      ),
      'canTransitionTo' => 
      array (
        'name' => 'canTransitionTo',
        'parameters' => 
        array (
          'target' => 
          array (
            'name' => 'target',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Enums\\ResultWorkflowStatus',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 122,
            'endLine' => 122,
            'startColumn' => 37,
            'endColumn' => 64,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 122,
        'endLine' => 125,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\StudentResult',
        'implementingClassName' => 'App\\Models\\StudentResult',
        'currentClassName' => 'App\\Models\\StudentResult',
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