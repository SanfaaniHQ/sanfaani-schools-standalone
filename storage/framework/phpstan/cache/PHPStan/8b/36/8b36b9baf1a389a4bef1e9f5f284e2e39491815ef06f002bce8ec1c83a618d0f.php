<?php declare(strict_types = 1);

// osfsl-C:/laragon/www/sanfaani-schools/vendor/composer/../laravel/framework/src/Illuminate/Database/Eloquent/ModelNotFoundException.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Illuminate\Database\Eloquent\ModelNotFoundException
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-d1cb35c1d08529a09385d169d5adc2b52b03481e70533a17fffc55268f5ba5f2-8.3.30-6.70.0.0',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
        'filename' => 'C:/laragon/www/sanfaani-schools/vendor/composer/../laravel/framework/src/Illuminate/Database/Eloquent/ModelNotFoundException.php',
      ),
    ),
    'namespace' => 'Illuminate\\Database\\Eloquent',
    'name' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
    'shortName' => 'ModelNotFoundException',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @template TModel of \\Illuminate\\Database\\Eloquent\\Model
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 12,
    'endLine' => 74,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Database\\RecordsNotFoundException',
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
      'model' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
        'name' => 'model',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * Name of the affected Eloquent model.
 *
 * @var class-string<TModel>
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 19,
        'endLine' => 19,
        'startColumn' => 5,
        'endColumn' => 21,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'ids' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
        'name' => 'ids',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The affected model IDs.
 *
 * @var array<int, int|string>
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 26,
        'endLine' => 26,
        'startColumn' => 5,
        'endColumn' => 19,
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
      'setModel' => 
      array (
        'name' => 'setModel',
        'parameters' => 
        array (
          'model' => 
          array (
            'name' => 'model',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 35,
            'endLine' => 35,
            'startColumn' => 30,
            'endColumn' => 35,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'ids' => 
          array (
            'name' => 'ids',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 35,
                'endLine' => 35,
                'startTokenPos' => 63,
                'startFilePos' => 751,
                'endTokenPos' => 64,
                'endFilePos' => 752,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 35,
            'endLine' => 35,
            'startColumn' => 38,
            'endColumn' => 46,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set the affected Eloquent model and instance ids.
 *
 * @param  class-string<TModel>  $model
 * @param  array<int, int|string>|int|string  $ids
 * @return $this
 */',
        'startLine' => 35,
        'endLine' => 53,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
        'aliasName' => NULL,
      ),
      'getModel' => 
      array (
        'name' => 'getModel',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the affected Eloquent model.
 *
 * @return class-string<TModel>
 */',
        'startLine' => 60,
        'endLine' => 63,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
        'aliasName' => NULL,
      ),
      'getIds' => 
      array (
        'name' => 'getIds',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the affected Eloquent model IDs.
 *
 * @return array<int, int|string>
 */',
        'startLine' => 70,
        'endLine' => 73,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
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