<?php declare(strict_types = 1);

// odsl-C:\laragon\www\sanfaani-schools\app\Models\SchoolReportCardSetting.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\SchoolReportCardSetting
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.0-8.3.30-9a1e48b61eae29a699720dc0635baaad15011a739f74985b7968b23e19b8615a',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\SchoolReportCardSetting',
        'filename' => 'C:/laragon/www/sanfaani-schools/app/Models/SchoolReportCardSetting.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\SchoolReportCardSetting',
    'shortName' => 'SchoolReportCardSetting',
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
    'endLine' => 61,
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
        'declaringClassName' => 'App\\Models\\SchoolReportCardSetting',
        'implementingClassName' => 'App\\Models\\SchoolReportCardSetting',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'school_id\', \'report_card_template_id\', \'primary_color\', \'accent_color\', \'school_name_font\', \'header_type\', \'student_info_layout\', \'result_table_style\', \'show_logo\', \'show_school_address\', \'show_school_phone\', \'show_school_email\', \'show_student_photo\', \'show_teacher_remark\', \'show_class_teacher\', \'show_head_teacher\', \'class_teacher_title\', \'head_teacher_title\', \'class_teacher_name\', \'head_teacher_name\', \'class_teacher_signature_path\', \'head_teacher_signature_path\', \'enable_auto_class_teacher_comment\', \'enable_auto_head_teacher_comment\', \'metadata\']',
          'attributes' => 
          array (
            'startLine' => 10,
            'endLine' => 36,
            'startTokenPos' => 33,
            'startFilePos' => 197,
            'endTokenPos' => 110,
            'endFilePos' => 958,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 10,
        'endLine' => 36,
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
        'declaringClassName' => 'App\\Models\\SchoolReportCardSetting',
        'implementingClassName' => 'App\\Models\\SchoolReportCardSetting',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'show_logo\' => \'boolean\', \'show_school_address\' => \'boolean\', \'show_school_phone\' => \'boolean\', \'show_school_email\' => \'boolean\', \'show_student_photo\' => \'boolean\', \'show_teacher_remark\' => \'boolean\', \'show_class_teacher\' => \'boolean\', \'show_head_teacher\' => \'boolean\', \'enable_auto_class_teacher_comment\' => \'boolean\', \'enable_auto_head_teacher_comment\' => \'boolean\', \'metadata\' => \'array\']',
          'attributes' => 
          array (
            'startLine' => 38,
            'endLine' => 50,
            'startTokenPos' => 119,
            'startFilePos' => 985,
            'endTokenPos' => 198,
            'endFilePos' => 1471,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 38,
        'endLine' => 50,
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
        'startLine' => 52,
        'endLine' => 55,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\SchoolReportCardSetting',
        'implementingClassName' => 'App\\Models\\SchoolReportCardSetting',
        'currentClassName' => 'App\\Models\\SchoolReportCardSetting',
        'aliasName' => NULL,
      ),
      'template' => 
      array (
        'name' => 'template',
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
        'startLine' => 57,
        'endLine' => 60,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\SchoolReportCardSetting',
        'implementingClassName' => 'App\\Models\\SchoolReportCardSetting',
        'currentClassName' => 'App\\Models\\SchoolReportCardSetting',
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