<?php declare(strict_types = 1);

// odsl-C:\laragon\www\sanfaani-schools\app\Http\Controllers\School\SchoolAdminDashboardController.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Http\Controllers\School\SchoolAdminDashboardController
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.0-8.3.30-b35296bc2df50b144d4f3358c24ecda42ffd1a0ff027fc2f3898dd0883b47a03',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'filename' => 'C:/laragon/www/sanfaani-schools/app/Http/Controllers/School/SchoolAdminDashboardController.php',
      ),
    ),
    'namespace' => 'App\\Http\\Controllers\\School',
    'name' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
    'shortName' => 'SchoolAdminDashboardController',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 13,
    'endLine' => 325,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'App\\Http\\Controllers\\Controller',
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
    ),
    'immediateMethods' => 
    array (
      'index' => 
      array (
        'name' => 'index',
        'parameters' => 
        array (
          'currentSchool' => 
          array (
            'name' => 'currentSchool',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Services\\CurrentSchoolService',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 16,
            'endLine' => 16,
            'startColumn' => 9,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'onboarding' => 
          array (
            'name' => 'onboarding',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Services\\OnboardingProgressService',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 17,
            'endLine' => 17,
            'startColumn' => 9,
            'endColumn' => 45,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'authorization' => 
          array (
            'name' => 'authorization',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Services\\SchoolAuthorizationService',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 18,
            'endLine' => 18,
            'startColumn' => 9,
            'endColumn' => 49,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 15,
        'endLine' => 105,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Http\\Controllers\\School',
        'declaringClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'implementingClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'currentClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'aliasName' => NULL,
      ),
      'getTeacherDashboardData' => 
      array (
        'name' => 'getTeacherDashboardData',
        'parameters' => 
        array (
          'user' => 
          array (
            'name' => 'user',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 110,
            'endLine' => 110,
            'startColumn' => 46,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'school' => 
          array (
            'name' => 'school',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 110,
            'endLine' => 110,
            'startColumn' => 53,
            'endColumn' => 59,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get teacher-specific dashboard data.
 */',
        'startLine' => 110,
        'endLine' => 164,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Http\\Controllers\\School',
        'declaringClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'implementingClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'currentClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'aliasName' => NULL,
      ),
      'getResultOfficerDashboardData' => 
      array (
        'name' => 'getResultOfficerDashboardData',
        'parameters' => 
        array (
          'school' => 
          array (
            'name' => 'school',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 169,
            'endLine' => 169,
            'startColumn' => 52,
            'endColumn' => 58,
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
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get result officer-specific dashboard data.
 */',
        'startLine' => 169,
        'endLine' => 194,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Http\\Controllers\\School',
        'declaringClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'implementingClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'currentClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'aliasName' => NULL,
      ),
      'resultStatusMetrics' => 
      array (
        'name' => 'resultStatusMetrics',
        'parameters' => 
        array (
          'school' => 
          array (
            'name' => 'school',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 196,
            'endLine' => 196,
            'startColumn' => 42,
            'endColumn' => 48,
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
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 196,
        'endLine' => 215,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Http\\Controllers\\School',
        'declaringClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'implementingClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'currentClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'aliasName' => NULL,
      ),
      'scratchCardBatchMetrics' => 
      array (
        'name' => 'scratchCardBatchMetrics',
        'parameters' => 
        array (
          'school' => 
          array (
            'name' => 'school',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 217,
            'endLine' => 217,
            'startColumn' => 46,
            'endColumn' => 52,
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
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 217,
        'endLine' => 232,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Http\\Controllers\\School',
        'declaringClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'implementingClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'currentClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'aliasName' => NULL,
      ),
      'scratchCardMetrics' => 
      array (
        'name' => 'scratchCardMetrics',
        'parameters' => 
        array (
          'school' => 
          array (
            'name' => 'school',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 234,
            'endLine' => 234,
            'startColumn' => 41,
            'endColumn' => 47,
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
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 234,
        'endLine' => 247,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Http\\Controllers\\School',
        'declaringClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'implementingClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'currentClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'aliasName' => NULL,
      ),
      'guardedMetrics' => 
      array (
        'name' => 'guardedMetrics',
        'parameters' => 
        array (
          'context' => 
          array (
            'name' => 'context',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 249,
            'endLine' => 249,
            'startColumn' => 37,
            'endColumn' => 51,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'callable',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 249,
            'endLine' => 249,
            'startColumn' => 54,
            'endColumn' => 71,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'fallback' => 
          array (
            'name' => 'fallback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 249,
            'endLine' => 249,
            'startColumn' => 74,
            'endColumn' => 88,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 249,
        'endLine' => 261,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Http\\Controllers\\School',
        'declaringClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'implementingClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'currentClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'aliasName' => NULL,
      ),
      'defaultResultStatusMetrics' => 
      array (
        'name' => 'defaultResultStatusMetrics',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 263,
        'endLine' => 273,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Http\\Controllers\\School',
        'declaringClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'implementingClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'currentClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'aliasName' => NULL,
      ),
      'defaultScratchCardBatchMetrics' => 
      array (
        'name' => 'defaultScratchCardBatchMetrics',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 275,
        'endLine' => 283,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Http\\Controllers\\School',
        'declaringClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'implementingClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'currentClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'aliasName' => NULL,
      ),
      'defaultScratchCardMetrics' => 
      array (
        'name' => 'defaultScratchCardMetrics',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 285,
        'endLine' => 292,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Http\\Controllers\\School',
        'declaringClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'implementingClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'currentClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'aliasName' => NULL,
      ),
      'defaultTeacherDashboardData' => 
      array (
        'name' => 'defaultTeacherDashboardData',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 294,
        'endLine' => 309,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Http\\Controllers\\School',
        'declaringClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'implementingClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'currentClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'aliasName' => NULL,
      ),
      'defaultResultOfficerDashboardData' => 
      array (
        'name' => 'defaultResultOfficerDashboardData',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 311,
        'endLine' => 324,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Http\\Controllers\\School',
        'declaringClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'implementingClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
        'currentClassName' => 'App\\Http\\Controllers\\School\\SchoolAdminDashboardController',
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