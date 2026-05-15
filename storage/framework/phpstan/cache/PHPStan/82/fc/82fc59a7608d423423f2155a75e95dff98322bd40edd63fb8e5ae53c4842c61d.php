<?php declare(strict_types = 1);

// osfsl-C:/laragon/www/sanfaani-schools/vendor/composer/../spatie/laravel-permission/src/Models/Permission.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Spatie\Permission\Models\Permission
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-e8da5ff80f9c823bd95ff639908469eac9e0f496693f695a7720f57bcc600e71-8.3.30-6.70.0.0',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Spatie\\Permission\\Models\\Permission',
        'filename' => 'C:/laragon/www/sanfaani-schools/vendor/composer/../spatie/laravel-permission/src/Models/Permission.php',
      ),
    ),
    'namespace' => 'Spatie\\Permission\\Models',
    'name' => 'Spatie\\Permission\\Models\\Permission',
    'shortName' => 'Permission',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property int|string $id
 * @property string $name
 * @property string $guard_name
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property-read Collection<int, Role> $roles
 * @property-read Collection<int, Model> $users
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 27,
    'endLine' => 165,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Database\\Eloquent\\Model',
    'implementsClassNames' => 
    array (
      0 => 'Spatie\\Permission\\Contracts\\Permission',
    ),
    'traitClassNames' => 
    array (
      0 => 'Spatie\\Permission\\Traits\\HasRoles',
      1 => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
      'guarded' => 
      array (
        'declaringClassName' => 'Spatie\\Permission\\Models\\Permission',
        'implementingClassName' => 'Spatie\\Permission\\Models\\Permission',
        'name' => 'guarded',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 32,
            'endLine' => 32,
            'startTokenPos' => 103,
            'startFilePos' => 1000,
            'endTokenPos' => 104,
            'endFilePos' => 1001,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 32,
        'endLine' => 32,
        'startColumn' => 5,
        'endColumn' => 28,
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
      '__construct' => 
      array (
        'name' => '__construct',
        'parameters' => 
        array (
          'attributes' => 
          array (
            'name' => 'attributes',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 34,
                'endLine' => 34,
                'startTokenPos' => 119,
                'startFilePos' => 1057,
                'endTokenPos' => 120,
                'endFilePos' => 1058,
              ),
            ),
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
            'startLine' => 34,
            'endLine' => 34,
            'startColumn' => 33,
            'endColumn' => 54,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 34,
        'endLine' => 42,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Spatie\\Permission\\Models',
        'declaringClassName' => 'Spatie\\Permission\\Models\\Permission',
        'implementingClassName' => 'Spatie\\Permission\\Models\\Permission',
        'currentClassName' => 'Spatie\\Permission\\Models\\Permission',
        'aliasName' => NULL,
      ),
      'create' => 
      array (
        'name' => 'create',
        'parameters' => 
        array (
          'attributes' => 
          array (
            'name' => 'attributes',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 49,
                'endLine' => 49,
                'startTokenPos' => 203,
                'startFilePos' => 1474,
                'endTokenPos' => 204,
                'endFilePos' => 1475,
              ),
            ),
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
            'startLine' => 49,
            'endLine' => 49,
            'startColumn' => 35,
            'endColumn' => 56,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return PermissionContract|Permission
 *
 * @throws PermissionAlreadyExists
 */',
        'startLine' => 49,
        'endLine' => 60,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Spatie\\Permission\\Models',
        'declaringClassName' => 'Spatie\\Permission\\Models\\Permission',
        'implementingClassName' => 'Spatie\\Permission\\Models\\Permission',
        'currentClassName' => 'Spatie\\Permission\\Models\\Permission',
        'aliasName' => NULL,
      ),
      'roles' => 
      array (
        'name' => 'roles',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * A permission can be applied to roles.
 */',
        'startLine' => 65,
        'endLine' => 75,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Spatie\\Permission\\Models',
        'declaringClassName' => 'Spatie\\Permission\\Models\\Permission',
        'implementingClassName' => 'Spatie\\Permission\\Models\\Permission',
        'currentClassName' => 'Spatie\\Permission\\Models\\Permission',
        'aliasName' => NULL,
      ),
      'users' => 
      array (
        'name' => 'users',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * A permission belongs to some users of the model associated with its guard.
 */',
        'startLine' => 80,
        'endLine' => 89,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Spatie\\Permission\\Models',
        'declaringClassName' => 'Spatie\\Permission\\Models\\Permission',
        'implementingClassName' => 'Spatie\\Permission\\Models\\Permission',
        'currentClassName' => 'Spatie\\Permission\\Models\\Permission',
        'aliasName' => NULL,
      ),
      'findByName' => 
      array (
        'name' => 'findByName',
        'parameters' => 
        array (
          'name' => 
          array (
            'name' => 'name',
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
            'startLine' => 98,
            'endLine' => 98,
            'startColumn' => 39,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'guardName' => 
          array (
            'name' => 'guardName',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 98,
                'endLine' => 98,
                'startTokenPos' => 457,
                'startFilePos' => 2966,
                'endTokenPos' => 457,
                'endFilePos' => 2969,
              ),
            ),
            'type' => 
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
                      'name' => 'string',
                      'isIdentifier' => true,
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
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 98,
            'endLine' => 98,
            'startColumn' => 53,
            'endColumn' => 77,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Spatie\\Permission\\Contracts\\Permission',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Find a permission by its name (and optionally guardName).
 *
 * @return PermissionContract|Permission
 *
 * @throws PermissionDoesNotExist
 */',
        'startLine' => 98,
        'endLine' => 107,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Spatie\\Permission\\Models',
        'declaringClassName' => 'Spatie\\Permission\\Models\\Permission',
        'implementingClassName' => 'Spatie\\Permission\\Models\\Permission',
        'currentClassName' => 'Spatie\\Permission\\Models\\Permission',
        'aliasName' => NULL,
      ),
      'findById' => 
      array (
        'name' => 'findById',
        'parameters' => 
        array (
          'id' => 
          array (
            'name' => 'id',
            'default' => NULL,
            'type' => 
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
                      'name' => 'int',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 116,
            'endLine' => 116,
            'startColumn' => 37,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'guardName' => 
          array (
            'name' => 'guardName',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 116,
                'endLine' => 116,
                'startTokenPos' => 560,
                'startFilePos' => 3544,
                'endTokenPos' => 560,
                'endFilePos' => 3547,
              ),
            ),
            'type' => 
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
                      'name' => 'string',
                      'isIdentifier' => true,
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
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 116,
            'endLine' => 116,
            'startColumn' => 53,
            'endColumn' => 77,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Spatie\\Permission\\Contracts\\Permission',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Find a permission by its id (and optionally guardName).
 *
 * @return PermissionContract|Permission
 *
 * @throws PermissionDoesNotExist
 */',
        'startLine' => 116,
        'endLine' => 126,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Spatie\\Permission\\Models',
        'declaringClassName' => 'Spatie\\Permission\\Models\\Permission',
        'implementingClassName' => 'Spatie\\Permission\\Models\\Permission',
        'currentClassName' => 'Spatie\\Permission\\Models\\Permission',
        'aliasName' => NULL,
      ),
      'findOrCreate' => 
      array (
        'name' => 'findOrCreate',
        'parameters' => 
        array (
          'name' => 
          array (
            'name' => 'name',
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
            'startLine' => 133,
            'endLine' => 133,
            'startColumn' => 41,
            'endColumn' => 52,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'guardName' => 
          array (
            'name' => 'guardName',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 133,
                'endLine' => 133,
                'startTokenPos' => 669,
                'startFilePos' => 4106,
                'endTokenPos' => 669,
                'endFilePos' => 4109,
              ),
            ),
            'type' => 
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
                      'name' => 'string',
                      'isIdentifier' => true,
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
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 133,
            'endLine' => 133,
            'startColumn' => 55,
            'endColumn' => 79,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Spatie\\Permission\\Contracts\\Permission',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Find or create permission by its name (and optionally guardName).
 *
 * @return PermissionContract|Permission
 */',
        'startLine' => 133,
        'endLine' => 143,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Spatie\\Permission\\Models',
        'declaringClassName' => 'Spatie\\Permission\\Models\\Permission',
        'implementingClassName' => 'Spatie\\Permission\\Models\\Permission',
        'currentClassName' => 'Spatie\\Permission\\Models\\Permission',
        'aliasName' => NULL,
      ),
      'getPermissions' => 
      array (
        'name' => 'getPermissions',
        'parameters' => 
        array (
          'params' => 
          array (
            'name' => 'params',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 148,
                'endLine' => 148,
                'startTokenPos' => 778,
                'startFilePos' => 4578,
                'endTokenPos' => 779,
                'endFilePos' => 4579,
              ),
            ),
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
            'startLine' => 148,
            'endLine' => 148,
            'startColumn' => 46,
            'endColumn' => 63,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'onlyOne' => 
          array (
            'name' => 'onlyOne',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 148,
                'endLine' => 148,
                'startTokenPos' => 788,
                'startFilePos' => 4598,
                'endTokenPos' => 788,
                'endFilePos' => 4602,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'bool',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 148,
            'endLine' => 148,
            'startColumn' => 66,
            'endColumn' => 86,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Collection',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the current cached permissions.
 */',
        'startLine' => 148,
        'endLine' => 153,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 18,
        'namespace' => 'Spatie\\Permission\\Models',
        'declaringClassName' => 'Spatie\\Permission\\Models\\Permission',
        'implementingClassName' => 'Spatie\\Permission\\Models\\Permission',
        'currentClassName' => 'Spatie\\Permission\\Models\\Permission',
        'aliasName' => NULL,
      ),
      'getPermission' => 
      array (
        'name' => 'getPermission',
        'parameters' => 
        array (
          'params' => 
          array (
            'name' => 'params',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 160,
                'endLine' => 160,
                'startTokenPos' => 841,
                'startFilePos' => 4955,
                'endTokenPos' => 842,
                'endFilePos' => 4956,
              ),
            ),
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
            'startLine' => 160,
            'endLine' => 160,
            'startColumn' => 45,
            'endColumn' => 62,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
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
                  'name' => 'Spatie\\Permission\\Contracts\\Permission',
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
        'docComment' => '/**
 * Get the current cached first permission.
 *
 * @return PermissionContract|Permission|null
 */',
        'startLine' => 160,
        'endLine' => 164,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 18,
        'namespace' => 'Spatie\\Permission\\Models',
        'declaringClassName' => 'Spatie\\Permission\\Models\\Permission',
        'implementingClassName' => 'Spatie\\Permission\\Models\\Permission',
        'currentClassName' => 'Spatie\\Permission\\Models\\Permission',
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