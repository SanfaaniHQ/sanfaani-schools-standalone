<?php declare(strict_types = 1);

// odsl-C:\laragon\www\sanfaani-schools\app\Models\ReportCardSnapshot.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\ReportCardSnapshot
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.0-8.3.30-a2ba83babb77eeaabc8f407181ebd18eae1a97e6bb28b5674f6826fe530553f8',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\ReportCardSnapshot',
        'filename' => 'C:/laragon/www/sanfaani-schools/app/Models/ReportCardSnapshot.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\ReportCardSnapshot',
    'shortName' => 'ReportCardSnapshot',
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
    'endLine' => 161,
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
      'PAYLOAD_SCHEMA_VERSION' => 
      array (
        'declaringClassName' => 'App\\Models\\ReportCardSnapshot',
        'implementingClassName' => 'App\\Models\\ReportCardSnapshot',
        'name' => 'PAYLOAD_SCHEMA_VERSION',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'report_card_snapshot_v1\'',
          'attributes' => 
          array (
            'startLine' => 14,
            'endLine' => 14,
            'startTokenPos' => 50,
            'startFilePos' => 304,
            'endTokenPos' => 50,
            'endFilePos' => 328,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 14,
        'endLine' => 14,
        'startColumn' => 5,
        'endColumn' => 68,
      ),
      'IMMUTABLE_COLUMNS' => 
      array (
        'declaringClassName' => 'App\\Models\\ReportCardSnapshot',
        'implementingClassName' => 'App\\Models\\ReportCardSnapshot',
        'name' => 'IMMUTABLE_COLUMNS',
        'modifiers' => 4,
        'type' => NULL,
        'value' => 
        array (
          'code' => '[\'school_id\', \'student_id\', \'school_class_id\', \'academic_session_id\', \'term_id\', \'result_publication_id\', \'result_verification_id\', \'snapshot_version\', \'snapshot_type\', \'payload_schema_version\', \'result_type\', \'source_status\', \'student_name\', \'admission_number\', \'result_count\', \'total_score\', \'average_score\', \'student_snapshot\', \'school_snapshot\', \'academic_snapshot\', \'result_snapshot\', \'grading_snapshot\', \'settings_snapshot\', \'comments_snapshot\', \'access_snapshot\', \'snapshot_hash\', \'verification_code\', \'generated_by\', \'generated_at\']',
          'attributes' => 
          array (
            'startLine' => 16,
            'endLine' => 46,
            'startTokenPos' => 61,
            'startFilePos' => 370,
            'endTokenPos' => 150,
            'endFilePos' => 1148,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 16,
        'endLine' => 46,
        'startColumn' => 5,
        'endColumn' => 6,
      ),
    ),
    'immediateProperties' => 
    array (
      'fillable' => 
      array (
        'declaringClassName' => 'App\\Models\\ReportCardSnapshot',
        'implementingClassName' => 'App\\Models\\ReportCardSnapshot',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'snapshot_uuid\', \'school_id\', \'student_id\', \'school_class_id\', \'academic_session_id\', \'term_id\', \'result_publication_id\', \'result_verification_id\', \'snapshot_version\', \'snapshot_type\', \'payload_schema_version\', \'result_type\', \'source_status\', \'status\', \'student_name\', \'admission_number\', \'result_count\', \'total_score\', \'average_score\', \'student_snapshot\', \'school_snapshot\', \'academic_snapshot\', \'result_snapshot\', \'grading_snapshot\', \'settings_snapshot\', \'comments_snapshot\', \'access_snapshot\', \'snapshot_hash\', \'verification_code\', \'pdf_disk\', \'pdf_path\', \'pdf_hash\', \'pdf_generated_at\', \'generated_by\', \'generated_at\', \'metadata\']',
          'attributes' => 
          array (
            'startLine' => 48,
            'endLine' => 85,
            'startTokenPos' => 159,
            'startFilePos' => 1178,
            'endTokenPos' => 269,
            'endFilePos' => 2107,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 48,
        'endLine' => 85,
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
        'declaringClassName' => 'App\\Models\\ReportCardSnapshot',
        'implementingClassName' => 'App\\Models\\ReportCardSnapshot',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'snapshot_version\' => \'integer\', \'result_count\' => \'integer\', \'total_score\' => \'decimal:2\', \'average_score\' => \'decimal:2\', \'student_snapshot\' => \'array\', \'school_snapshot\' => \'array\', \'academic_snapshot\' => \'array\', \'result_snapshot\' => \'array\', \'grading_snapshot\' => \'array\', \'settings_snapshot\' => \'array\', \'comments_snapshot\' => \'array\', \'access_snapshot\' => \'array\', \'pdf_generated_at\' => \'datetime\', \'generated_at\' => \'datetime\', \'metadata\' => \'array\']',
          'attributes' => 
          array (
            'startLine' => 87,
            'endLine' => 103,
            'startTokenPos' => 278,
            'startFilePos' => 2134,
            'endTokenPos' => 385,
            'endFilePos' => 2719,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 87,
        'endLine' => 103,
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
      'booted' => 
      array (
        'name' => 'booted',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 105,
        'endLine' => 120,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 18,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\ReportCardSnapshot',
        'implementingClassName' => 'App\\Models\\ReportCardSnapshot',
        'currentClassName' => 'App\\Models\\ReportCardSnapshot',
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
        'declaringClassName' => 'App\\Models\\ReportCardSnapshot',
        'implementingClassName' => 'App\\Models\\ReportCardSnapshot',
        'currentClassName' => 'App\\Models\\ReportCardSnapshot',
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
        'startLine' => 127,
        'endLine' => 130,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\ReportCardSnapshot',
        'implementingClassName' => 'App\\Models\\ReportCardSnapshot',
        'currentClassName' => 'App\\Models\\ReportCardSnapshot',
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
        'startLine' => 132,
        'endLine' => 135,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\ReportCardSnapshot',
        'implementingClassName' => 'App\\Models\\ReportCardSnapshot',
        'currentClassName' => 'App\\Models\\ReportCardSnapshot',
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
        'startLine' => 137,
        'endLine' => 140,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\ReportCardSnapshot',
        'implementingClassName' => 'App\\Models\\ReportCardSnapshot',
        'currentClassName' => 'App\\Models\\ReportCardSnapshot',
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
        'startLine' => 142,
        'endLine' => 145,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\ReportCardSnapshot',
        'implementingClassName' => 'App\\Models\\ReportCardSnapshot',
        'currentClassName' => 'App\\Models\\ReportCardSnapshot',
        'aliasName' => NULL,
      ),
      'resultPublication' => 
      array (
        'name' => 'resultPublication',
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
        'startLine' => 147,
        'endLine' => 150,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\ReportCardSnapshot',
        'implementingClassName' => 'App\\Models\\ReportCardSnapshot',
        'currentClassName' => 'App\\Models\\ReportCardSnapshot',
        'aliasName' => NULL,
      ),
      'resultVerification' => 
      array (
        'name' => 'resultVerification',
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
        'startLine' => 152,
        'endLine' => 155,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\ReportCardSnapshot',
        'implementingClassName' => 'App\\Models\\ReportCardSnapshot',
        'currentClassName' => 'App\\Models\\ReportCardSnapshot',
        'aliasName' => NULL,
      ),
      'generatedBy' => 
      array (
        'name' => 'generatedBy',
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
        'startLine' => 157,
        'endLine' => 160,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\ReportCardSnapshot',
        'implementingClassName' => 'App\\Models\\ReportCardSnapshot',
        'currentClassName' => 'App\\Models\\ReportCardSnapshot',
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