<?php declare(strict_types = 1);

// osfsl-C:/laragon/www/sanfaani-schools/vendor/composer/../laravel/framework/src/Illuminate/Support/Str.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Illuminate\Support\Str
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-b82b5b28d7c8f4c2b49ab5bf2226982103d78776a07d7b3ee4d16eb32b386186-8.3.30-6.70.0.0',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Illuminate\\Support\\Str',
        'filename' => 'C:/laragon/www/sanfaani-schools/vendor/composer/../laravel/framework/src/Illuminate/Support/Str.php',
      ),
    ),
    'namespace' => 'Illuminate\\Support',
    'name' => 'Illuminate\\Support\\Str',
    'shortName' => 'Str',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 23,
    'endLine' => 2166,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
      0 => 'Illuminate\\Support\\Traits\\Macroable',
    ),
    'immediateConstants' => 
    array (
      'INVISIBLE_CHARACTERS' => 
      array (
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'name' => 'INVISIBLE_CHARACTERS',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'\\x{0009}\\x{0020}\\x{00A0}\\x{00AD}\\x{034F}\\x{061C}\\x{115F}\\x{1160}\\x{17B4}\\x{17B5}\\x{180E}\\x{2000}\\x{2001}\\x{2002}\\x{2003}\\x{2004}\\x{2005}\\x{2006}\\x{2007}\\x{2008}\\x{2009}\\x{200A}\\x{200B}\\x{200C}\\x{200D}\\x{200E}\\x{200F}\\x{202F}\\x{205F}\\x{2060}\\x{2061}\\x{2062}\\x{2063}\\x{2064}\\x{2065}\\x{206A}\\x{206B}\\x{206C}\\x{206D}\\x{206E}\\x{206F}\\x{3000}\\x{2800}\\x{3164}\\x{FEFF}\\x{FFA0}\\x{1D159}\\x{1D173}\\x{1D174}\\x{1D175}\\x{1D176}\\x{1D177}\\x{1D178}\\x{1D179}\\x{1D17A}\\x{E0020}\'',
          'attributes' => 
          array (
            'startLine' => 32,
            'endLine' => 32,
            'startTokenPos' => 111,
            'startFilePos' => 869,
            'endTokenPos' => 111,
            'endFilePos' => 1328,
          ),
        ),
        'docComment' => '/**
 * The list of characters that are considered "invisible" in strings.
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 32,
        'endLine' => 32,
        'startColumn' => 5,
        'endColumn' => 494,
      ),
    ),
    'immediateProperties' => 
    array (
      'snakeCache' => 
      array (
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'name' => 'snakeCache',
        'modifiers' => 18,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 39,
            'endLine' => 39,
            'startTokenPos' => 124,
            'startFilePos' => 1463,
            'endTokenPos' => 125,
            'endFilePos' => 1464,
          ),
        ),
        'docComment' => '/**
 * The cache of snake-cased words.
 *
 * @var array<string, string>
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 39,
        'endLine' => 39,
        'startColumn' => 5,
        'endColumn' => 38,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'camelCache' => 
      array (
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'name' => 'camelCache',
        'modifiers' => 18,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 46,
            'endLine' => 46,
            'startTokenPos' => 138,
            'startFilePos' => 1599,
            'endTokenPos' => 139,
            'endFilePos' => 1600,
          ),
        ),
        'docComment' => '/**
 * The cache of camel-cased words.
 *
 * @var array<string, string>
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 46,
        'endLine' => 46,
        'startColumn' => 5,
        'endColumn' => 38,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'studlyCache' => 
      array (
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'name' => 'studlyCache',
        'modifiers' => 18,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 53,
            'endLine' => 53,
            'startTokenPos' => 152,
            'startFilePos' => 1737,
            'endTokenPos' => 153,
            'endFilePos' => 1738,
          ),
        ),
        'docComment' => '/**
 * The cache of studly-cased words.
 *
 * @var array<string, string>
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 53,
        'endLine' => 53,
        'startColumn' => 5,
        'endColumn' => 39,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'uuidFactory' => 
      array (
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'name' => 'uuidFactory',
        'modifiers' => 18,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The callback that should be used to generate UUIDs.
 *
 * @var (callable(): \\Ramsey\\Uuid\\UuidInterface)|null
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 60,
        'endLine' => 60,
        'startColumn' => 5,
        'endColumn' => 34,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'ulidFactory' => 
      array (
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'name' => 'ulidFactory',
        'modifiers' => 18,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The callback that should be used to generate ULIDs.
 *
 * @var (callable(): \\Symfony\\Component\\Uid\\Ulid)|null
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 67,
        'endLine' => 67,
        'startColumn' => 5,
        'endColumn' => 34,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'randomStringFactory' => 
      array (
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'name' => 'randomStringFactory',
        'modifiers' => 18,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The callback that should be used to generate random strings.
 *
 * @var (callable(int): string)|null
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 74,
        'endLine' => 74,
        'startColumn' => 5,
        'endColumn' => 42,
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
      'of' => 
      array (
        'name' => 'of',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 82,
            'endLine' => 82,
            'startColumn' => 31,
            'endColumn' => 37,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get a new stringable object from the given string.
 *
 * @param  string  $string
 * @return \\Illuminate\\Support\\Stringable
 */',
        'startLine' => 82,
        'endLine' => 85,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'after' => 
      array (
        'name' => 'after',
        'parameters' => 
        array (
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 94,
            'endLine' => 94,
            'startColumn' => 34,
            'endColumn' => 41,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'search' => 
          array (
            'name' => 'search',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 94,
            'endLine' => 94,
            'startColumn' => 44,
            'endColumn' => 50,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Return the remainder of a string after the first occurrence of a given value.
 *
 * @param  string  $subject
 * @param  string  $search
 * @return string
 */',
        'startLine' => 94,
        'endLine' => 97,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'afterLast' => 
      array (
        'name' => 'afterLast',
        'parameters' => 
        array (
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 106,
            'endLine' => 106,
            'startColumn' => 38,
            'endColumn' => 45,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'search' => 
          array (
            'name' => 'search',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 106,
            'endLine' => 106,
            'startColumn' => 48,
            'endColumn' => 54,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Return the remainder of a string after the last occurrence of a given value.
 *
 * @param  string  $subject
 * @param  string  $search
 * @return string
 */',
        'startLine' => 106,
        'endLine' => 119,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'ascii' => 
      array (
        'name' => 'ascii',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 128,
            'endLine' => 128,
            'startColumn' => 34,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'language' => 
          array (
            'name' => 'language',
            'default' => 
            array (
              'code' => '\'en\'',
              'attributes' => 
              array (
                'startLine' => 128,
                'endLine' => 128,
                'startTokenPos' => 372,
                'startFilePos' => 3613,
                'endTokenPos' => 372,
                'endFilePos' => 3616,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 128,
            'endLine' => 128,
            'startColumn' => 42,
            'endColumn' => 57,
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
 * Transliterate a UTF-8 value to ASCII.
 *
 * @param  string  $value
 * @param  string  $language
 * @return string
 */',
        'startLine' => 128,
        'endLine' => 131,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'transliterate' => 
      array (
        'name' => 'transliterate',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 141,
            'endLine' => 141,
            'startColumn' => 42,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'unknown' => 
          array (
            'name' => 'unknown',
            'default' => 
            array (
              'code' => '\'?\'',
              'attributes' => 
              array (
                'startLine' => 141,
                'endLine' => 141,
                'startTokenPos' => 417,
                'startFilePos' => 4001,
                'endTokenPos' => 417,
                'endFilePos' => 4003,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 141,
            'endLine' => 141,
            'startColumn' => 51,
            'endColumn' => 64,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'strict' => 
          array (
            'name' => 'strict',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 141,
                'endLine' => 141,
                'startTokenPos' => 424,
                'startFilePos' => 4016,
                'endTokenPos' => 424,
                'endFilePos' => 4020,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 141,
            'endLine' => 141,
            'startColumn' => 67,
            'endColumn' => 81,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Transliterate a string to its closest ASCII representation.
 *
 * @param  string  $string
 * @param  string|null  $unknown
 * @param  bool|null  $strict
 * @return string
 */',
        'startLine' => 141,
        'endLine' => 144,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'before' => 
      array (
        'name' => 'before',
        'parameters' => 
        array (
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 153,
            'endLine' => 153,
            'startColumn' => 35,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'search' => 
          array (
            'name' => 'search',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 153,
            'endLine' => 153,
            'startColumn' => 45,
            'endColumn' => 51,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the portion of a string before the first occurrence of a given value.
 *
 * @param  string  $subject
 * @param  string  $search
 * @return string
 */',
        'startLine' => 153,
        'endLine' => 162,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'beforeLast' => 
      array (
        'name' => 'beforeLast',
        'parameters' => 
        array (
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 171,
            'endLine' => 171,
            'startColumn' => 39,
            'endColumn' => 46,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'search' => 
          array (
            'name' => 'search',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 171,
            'endLine' => 171,
            'startColumn' => 49,
            'endColumn' => 55,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the portion of a string before the last occurrence of a given value.
 *
 * @param  string  $subject
 * @param  string  $search
 * @return string
 */',
        'startLine' => 171,
        'endLine' => 184,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'between' => 
      array (
        'name' => 'between',
        'parameters' => 
        array (
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 194,
            'endLine' => 194,
            'startColumn' => 36,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'from' => 
          array (
            'name' => 'from',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 194,
            'endLine' => 194,
            'startColumn' => 46,
            'endColumn' => 50,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'to' => 
          array (
            'name' => 'to',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 194,
            'endLine' => 194,
            'startColumn' => 53,
            'endColumn' => 55,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the portion of a string between two given values.
 *
 * @param  string  $subject
 * @param  string  $from
 * @param  string  $to
 * @return string
 */',
        'startLine' => 194,
        'endLine' => 201,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'betweenFirst' => 
      array (
        'name' => 'betweenFirst',
        'parameters' => 
        array (
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 211,
            'endLine' => 211,
            'startColumn' => 41,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'from' => 
          array (
            'name' => 'from',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 211,
            'endLine' => 211,
            'startColumn' => 51,
            'endColumn' => 55,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'to' => 
          array (
            'name' => 'to',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 211,
            'endLine' => 211,
            'startColumn' => 58,
            'endColumn' => 60,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the smallest possible portion of a string between two given values.
 *
 * @param  string  $subject
 * @param  string  $from
 * @param  string  $to
 * @return string
 */',
        'startLine' => 211,
        'endLine' => 218,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'camel' => 
      array (
        'name' => 'camel',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 226,
            'endLine' => 226,
            'startColumn' => 34,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Convert a value to camel case.
 *
 * @param  string  $value
 * @return ($value is \'\' ? \'\' : string)
 */',
        'startLine' => 226,
        'endLine' => 229,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'charAt' => 
      array (
        'name' => 'charAt',
        'parameters' => 
        array (
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 238,
            'endLine' => 238,
            'startColumn' => 35,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'index' => 
          array (
            'name' => 'index',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 238,
            'endLine' => 238,
            'startColumn' => 45,
            'endColumn' => 50,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the character at the specified index.
 *
 * @param  string  $subject
 * @param  int  $index
 * @return string|false
 */',
        'startLine' => 238,
        'endLine' => 247,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'chopStart' => 
      array (
        'name' => 'chopStart',
        'parameters' => 
        array (
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 256,
            'endLine' => 256,
            'startColumn' => 38,
            'endColumn' => 45,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'needle' => 
          array (
            'name' => 'needle',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 256,
            'endLine' => 256,
            'startColumn' => 48,
            'endColumn' => 54,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Remove the given string(s) if it exists at the start of the haystack.
 *
 * @param  string  $subject
 * @param  string|string[]  $needle
 * @return string
 */',
        'startLine' => 256,
        'endLine' => 265,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'chopEnd' => 
      array (
        'name' => 'chopEnd',
        'parameters' => 
        array (
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 274,
            'endLine' => 274,
            'startColumn' => 36,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'needle' => 
          array (
            'name' => 'needle',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 274,
            'endLine' => 274,
            'startColumn' => 46,
            'endColumn' => 52,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Remove the given string(s) if it exists at the end of the haystack.
 *
 * @param  string  $subject
 * @param  string|string[]  $needle
 * @return string
 */',
        'startLine' => 274,
        'endLine' => 283,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'contains' => 
      array (
        'name' => 'contains',
        'parameters' => 
        array (
          'haystack' => 
          array (
            'name' => 'haystack',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 293,
            'endLine' => 293,
            'startColumn' => 37,
            'endColumn' => 45,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'needles' => 
          array (
            'name' => 'needles',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 293,
            'endLine' => 293,
            'startColumn' => 48,
            'endColumn' => 55,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'ignoreCase' => 
          array (
            'name' => 'ignoreCase',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 293,
                'endLine' => 293,
                'startTokenPos' => 1066,
                'startFilePos' => 7935,
                'endTokenPos' => 1066,
                'endFilePos' => 7939,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 293,
            'endLine' => 293,
            'startColumn' => 58,
            'endColumn' => 76,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if a given string contains a given substring.
 *
 * @param  string  $haystack
 * @param  string|iterable<string>  $needles
 * @param  bool  $ignoreCase
 * @return ($needles is array{} ? false : ($haystack is non-empty-string ? bool : false))
 */',
        'startLine' => 293,
        'endLine' => 318,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'containsAll' => 
      array (
        'name' => 'containsAll',
        'parameters' => 
        array (
          'haystack' => 
          array (
            'name' => 'haystack',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 328,
            'endLine' => 328,
            'startColumn' => 40,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'needles' => 
          array (
            'name' => 'needles',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 328,
            'endLine' => 328,
            'startColumn' => 51,
            'endColumn' => 58,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'ignoreCase' => 
          array (
            'name' => 'ignoreCase',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 328,
                'endLine' => 328,
                'startTokenPos' => 1223,
                'startFilePos' => 8848,
                'endTokenPos' => 1223,
                'endFilePos' => 8852,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 328,
            'endLine' => 328,
            'startColumn' => 61,
            'endColumn' => 79,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if a given string contains all array values.
 *
 * @param  string  $haystack
 * @param  iterable<string>  $needles
 * @param  bool  $ignoreCase
 * @return ($needles is array{} ? false : ($haystack is non-empty-string ? bool : false))
 */',
        'startLine' => 328,
        'endLine' => 337,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'doesntContain' => 
      array (
        'name' => 'doesntContain',
        'parameters' => 
        array (
          'haystack' => 
          array (
            'name' => 'haystack',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 347,
            'endLine' => 347,
            'startColumn' => 42,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'needles' => 
          array (
            'name' => 'needles',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 347,
            'endLine' => 347,
            'startColumn' => 53,
            'endColumn' => 60,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'ignoreCase' => 
          array (
            'name' => 'ignoreCase',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 347,
                'endLine' => 347,
                'startTokenPos' => 1297,
                'startFilePos' => 9431,
                'endTokenPos' => 1297,
                'endFilePos' => 9435,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 347,
            'endLine' => 347,
            'startColumn' => 63,
            'endColumn' => 81,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if a given string doesn\'t contain a given substring.
 *
 * @param  string  $haystack
 * @param  string|iterable<string>  $needles
 * @param  bool  $ignoreCase
 * @return ($needles is array{} ? true : ($haystack is non-empty-string ? bool : true))
 */',
        'startLine' => 347,
        'endLine' => 350,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'convertCase' => 
      array (
        'name' => 'convertCase',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
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
            'startLine' => 360,
            'endLine' => 360,
            'startColumn' => 40,
            'endColumn' => 53,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'mode' => 
          array (
            'name' => 'mode',
            'default' => 
            array (
              'code' => 'MB_CASE_FOLD',
              'attributes' => 
              array (
                'startLine' => 360,
                'endLine' => 360,
                'startTokenPos' => 1343,
                'startFilePos' => 9921,
                'endTokenPos' => 1343,
                'endFilePos' => 9932,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 360,
            'endLine' => 360,
            'startColumn' => 56,
            'endColumn' => 79,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'encoding' => 
          array (
            'name' => 'encoding',
            'default' => 
            array (
              'code' => '\'UTF-8\'',
              'attributes' => 
              array (
                'startLine' => 360,
                'endLine' => 360,
                'startTokenPos' => 1353,
                'startFilePos' => 9955,
                'endTokenPos' => 1353,
                'endFilePos' => 9961,
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
            'startLine' => 360,
            'endLine' => 360,
            'startColumn' => 82,
            'endColumn' => 108,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Convert the case of a string.
 *
 * @param  string  $string
 * @param  MB_CASE_UPPER|MB_CASE_LOWER|MB_CASE_TITLE|MB_CASE_FOLD|MB_CASE_UPPER_SIMPLE|MB_CASE_LOWER_SIMPLE|MB_CASE_TITLE_SIMPLE|MB_CASE_FOLD_SIMPLE  $mode
 * @param  string|null  $encoding
 * @return ($string is \'\' ? \'\' : string)
 */',
        'startLine' => 360,
        'endLine' => 363,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'deduplicate' => 
      array (
        'name' => 'deduplicate',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
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
            'startLine' => 372,
            'endLine' => 372,
            'startColumn' => 40,
            'endColumn' => 53,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'characters' => 
          array (
            'name' => 'characters',
            'default' => 
            array (
              'code' => '\' \'',
              'attributes' => 
              array (
                'startLine' => 372,
                'endLine' => 372,
                'startTokenPos' => 1397,
                'startFilePos' => 10369,
                'endTokenPos' => 1397,
                'endFilePos' => 10371,
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
                      'name' => 'array',
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
            'startLine' => 372,
            'endLine' => 372,
            'startColumn' => 56,
            'endColumn' => 85,
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
 * Replace consecutive instances of a given character with a single character in the given string.
 *
 * @param  string  $string
 * @param  array<string>|string  $characters
 * @return ($string is \'\' ? \'\' : string)
 */',
        'startLine' => 372,
        'endLine' => 383,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'endsWith' => 
      array (
        'name' => 'endsWith',
        'parameters' => 
        array (
          'haystack' => 
          array (
            'name' => 'haystack',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 392,
            'endLine' => 392,
            'startColumn' => 37,
            'endColumn' => 45,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'needles' => 
          array (
            'name' => 'needles',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 392,
            'endLine' => 392,
            'startColumn' => 48,
            'endColumn' => 55,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if a given string ends with a given substring.
 *
 * @param  string  $haystack
 * @param  string|iterable<string>  $needles
 * @return ($needles is array{} ? false : ($haystack is non-empty-string ? bool : false))
 */',
        'startLine' => 392,
        'endLine' => 409,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'doesntEndWith' => 
      array (
        'name' => 'doesntEndWith',
        'parameters' => 
        array (
          'haystack' => 
          array (
            'name' => 'haystack',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 418,
            'endLine' => 418,
            'startColumn' => 42,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'needles' => 
          array (
            'name' => 'needles',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 418,
            'endLine' => 418,
            'startColumn' => 53,
            'endColumn' => 60,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if a given string doesn\'t end with a given substring.
 *
 * @param  string  $haystack
 * @param  string|iterable<string>  $needles
 * @return ($needles is array{} ? true : ($haystack is non-empty-string ? bool : true))
 */',
        'startLine' => 418,
        'endLine' => 421,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'excerpt' => 
      array (
        'name' => 'excerpt',
        'parameters' => 
        array (
          'text' => 
          array (
            'name' => 'text',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 431,
            'endLine' => 431,
            'startColumn' => 36,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'phrase' => 
          array (
            'name' => 'phrase',
            'default' => 
            array (
              'code' => '\'\'',
              'attributes' => 
              array (
                'startLine' => 431,
                'endLine' => 431,
                'startTokenPos' => 1651,
                'startFilePos' => 12132,
                'endTokenPos' => 1651,
                'endFilePos' => 12133,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 431,
            'endLine' => 431,
            'startColumn' => 43,
            'endColumn' => 54,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'options' => 
          array (
            'name' => 'options',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 431,
                'endLine' => 431,
                'startTokenPos' => 1658,
                'startFilePos' => 12147,
                'endTokenPos' => 1659,
                'endFilePos' => 12148,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 431,
            'endLine' => 431,
            'startColumn' => 57,
            'endColumn' => 69,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Extracts an excerpt from text that matches the first instance of a phrase.
 *
 * @param  string  $text
 * @param  string  $phrase
 * @param  array{radius?: int|float, omission?: string}  $options
 * @return string|null
 */',
        'startLine' => 431,
        'endLine' => 457,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'finish' => 
      array (
        'name' => 'finish',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 466,
            'endLine' => 466,
            'startColumn' => 35,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'cap' => 
          array (
            'name' => 'cap',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 466,
            'endLine' => 466,
            'startColumn' => 43,
            'endColumn' => 46,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Cap a string with a single instance of a given value.
 *
 * @param  string  $value
 * @param  string  $cap
 * @return ($value is \'\' ? ($cap is \'\' ? \'\' : non-empty-string) : non-empty-string)
 */',
        'startLine' => 466,
        'endLine' => 471,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'wrap' => 
      array (
        'name' => 'wrap',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 481,
            'endLine' => 481,
            'startColumn' => 33,
            'endColumn' => 38,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'before' => 
          array (
            'name' => 'before',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 481,
            'endLine' => 481,
            'startColumn' => 41,
            'endColumn' => 47,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'after' => 
          array (
            'name' => 'after',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 481,
                'endLine' => 481,
                'startTokenPos' => 2003,
                'startFilePos' => 13859,
                'endTokenPos' => 2003,
                'endFilePos' => 13862,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 481,
            'endLine' => 481,
            'startColumn' => 50,
            'endColumn' => 62,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Wrap the string with the given strings.
 *
 * @param  string  $value
 * @param  string  $before
 * @param  string|null  $after
 * @return ($value is \'\' ? ($before is \'\' ? ($after is \'\' ? \'\' : ($after is null ? \'\' : non-empty-string)) : non-empty-string) : non-empty-string)
 */',
        'startLine' => 481,
        'endLine' => 484,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'unwrap' => 
      array (
        'name' => 'unwrap',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 494,
            'endLine' => 494,
            'startColumn' => 35,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'before' => 
          array (
            'name' => 'before',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 494,
            'endLine' => 494,
            'startColumn' => 43,
            'endColumn' => 49,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'after' => 
          array (
            'name' => 'after',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 494,
                'endLine' => 494,
                'startTokenPos' => 2045,
                'startFilePos' => 14179,
                'endTokenPos' => 2045,
                'endFilePos' => 14182,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 494,
            'endLine' => 494,
            'startColumn' => 52,
            'endColumn' => 64,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Unwrap the string with the given strings.
 *
 * @param  string  $value
 * @param  string  $before
 * @param  string|null  $after
 * @return string
 */',
        'startLine' => 494,
        'endLine' => 505,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'is' => 
      array (
        'name' => 'is',
        'parameters' => 
        array (
          'pattern' => 
          array (
            'name' => 'pattern',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 515,
            'endLine' => 515,
            'startColumn' => 31,
            'endColumn' => 38,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 515,
            'endLine' => 515,
            'startColumn' => 41,
            'endColumn' => 46,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'ignoreCase' => 
          array (
            'name' => 'ignoreCase',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 515,
                'endLine' => 515,
                'startTokenPos' => 2161,
                'startFilePos' => 14774,
                'endTokenPos' => 2161,
                'endFilePos' => 14778,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 515,
            'endLine' => 515,
            'startColumn' => 49,
            'endColumn' => 67,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if a given string matches a given pattern.
 *
 * @param  string|iterable<string>  $pattern
 * @param  string  $value
 * @param  bool  $ignoreCase
 * @return bool
 */',
        'startLine' => 515,
        'endLine' => 550,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'isAscii' => 
      array (
        'name' => 'isAscii',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 558,
            'endLine' => 558,
            'startColumn' => 36,
            'endColumn' => 41,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if a given string is 7 bit ASCII.
 *
 * @param  string  $value
 * @return bool
 */',
        'startLine' => 558,
        'endLine' => 561,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'isJson' => 
      array (
        'name' => 'isJson',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 571,
            'endLine' => 571,
            'startColumn' => 35,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if a given value is valid JSON.
 *
 * @param  mixed  $value
 * @return bool
 *
 * @phpstan-assert-if-true =non-empty-string $value
 */',
        'startLine' => 571,
        'endLine' => 578,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'isUrl' => 
      array (
        'name' => 'isUrl',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 589,
            'endLine' => 589,
            'startColumn' => 34,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'protocols' => 
          array (
            'name' => 'protocols',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 589,
                'endLine' => 589,
                'startTokenPos' => 2463,
                'startFilePos' => 16876,
                'endTokenPos' => 2464,
                'endFilePos' => 16877,
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
            'startLine' => 589,
            'endLine' => 589,
            'startColumn' => 42,
            'endColumn' => 62,
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
 * Determine if a given value is a valid URL.
 *
 * @param  mixed  $value
 * @param  string[]  $protocols
 * @return bool
 *
 * @phpstan-assert-if-true =non-empty-string $value
 */',
        'startLine' => 589,
        'endLine' => 634,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'isUuid' => 
      array (
        'name' => 'isUuid',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 645,
            'endLine' => 645,
            'startColumn' => 35,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'version' => 
          array (
            'name' => 'version',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 645,
                'endLine' => 645,
                'startTokenPos' => 2565,
                'startFilePos' => 23581,
                'endTokenPos' => 2565,
                'endFilePos' => 23584,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 645,
            'endLine' => 645,
            'startColumn' => 43,
            'endColumn' => 57,
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
 * Determine if a given value is a valid UUID.
 *
 * @param  mixed  $value
 * @param  int<0, 8>|\'nil\'|\'max\'|null  $version
 * @return bool
 *
 * @phpstan-assert-if-true =non-empty-string $value
 */',
        'startLine' => 645,
        'endLine' => 678,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'isUlid' => 
      array (
        'name' => 'isUlid',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 688,
            'endLine' => 688,
            'startColumn' => 35,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if a given value is a valid ULID.
 *
 * @param  mixed  $value
 * @return bool
 *
 * @phpstan-assert-if-true =non-empty-string $value
 */',
        'startLine' => 688,
        'endLine' => 695,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'kebab' => 
      array (
        'name' => 'kebab',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 703,
            'endLine' => 703,
            'startColumn' => 34,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Convert a string to kebab case.
 *
 * @param  string  $value
 * @return ($value is \'\' ? \'\' : string)
 */',
        'startLine' => 703,
        'endLine' => 706,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'length' => 
      array (
        'name' => 'length',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 715,
            'endLine' => 715,
            'startColumn' => 35,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'encoding' => 
          array (
            'name' => 'encoding',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 715,
                'endLine' => 715,
                'startTokenPos' => 2858,
                'startFilePos' => 25207,
                'endTokenPos' => 2858,
                'endFilePos' => 25210,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 715,
            'endLine' => 715,
            'startColumn' => 43,
            'endColumn' => 58,
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
 * Return the length of the given string.
 *
 * @param  string  $value
 * @param  string|null  $encoding
 * @return non-negative-int
 */',
        'startLine' => 715,
        'endLine' => 718,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'limit' => 
      array (
        'name' => 'limit',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 729,
            'endLine' => 729,
            'startColumn' => 34,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'limit' => 
          array (
            'name' => 'limit',
            'default' => 
            array (
              'code' => '100',
              'attributes' => 
              array (
                'startLine' => 729,
                'endLine' => 729,
                'startTokenPos' => 2893,
                'startFilePos' => 25538,
                'endTokenPos' => 2893,
                'endFilePos' => 25540,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 729,
            'endLine' => 729,
            'startColumn' => 42,
            'endColumn' => 53,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'end' => 
          array (
            'name' => 'end',
            'default' => 
            array (
              'code' => '\'...\'',
              'attributes' => 
              array (
                'startLine' => 729,
                'endLine' => 729,
                'startTokenPos' => 2900,
                'startFilePos' => 25550,
                'endTokenPos' => 2900,
                'endFilePos' => 25554,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 729,
            'endLine' => 729,
            'startColumn' => 56,
            'endColumn' => 67,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'preserveWords' => 
          array (
            'name' => 'preserveWords',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 729,
                'endLine' => 729,
                'startTokenPos' => 2907,
                'startFilePos' => 25574,
                'endTokenPos' => 2907,
                'endFilePos' => 25578,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 729,
            'endLine' => 729,
            'startColumn' => 70,
            'endColumn' => 91,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Limit the number of characters in a string.
 *
 * @param  string  $value
 * @param  int  $limit
 * @param  string  $end
 * @param  bool  $preserveWords
 * @return string
 */',
        'startLine' => 729,
        'endLine' => 748,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'lower' => 
      array (
        'name' => 'lower',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 756,
            'endLine' => 756,
            'startColumn' => 34,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Convert the given string to lower-case.
 *
 * @param  string  $value
 * @return ($value is \'\' ? \'\' : non-empty-string&lowercase-string)
 */',
        'startLine' => 756,
        'endLine' => 759,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'words' => 
      array (
        'name' => 'words',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 769,
            'endLine' => 769,
            'startColumn' => 34,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'words' => 
          array (
            'name' => 'words',
            'default' => 
            array (
              'code' => '100',
              'attributes' => 
              array (
                'startLine' => 769,
                'endLine' => 769,
                'startTokenPos' => 3117,
                'startFilePos' => 26625,
                'endTokenPos' => 3117,
                'endFilePos' => 26627,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 769,
            'endLine' => 769,
            'startColumn' => 42,
            'endColumn' => 53,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'end' => 
          array (
            'name' => 'end',
            'default' => 
            array (
              'code' => '\'...\'',
              'attributes' => 
              array (
                'startLine' => 769,
                'endLine' => 769,
                'startTokenPos' => 3124,
                'startFilePos' => 26637,
                'endTokenPos' => 3124,
                'endFilePos' => 26641,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 769,
            'endLine' => 769,
            'startColumn' => 56,
            'endColumn' => 67,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Limit the number of words in a string.
 *
 * @param  string  $value
 * @param  int  $words
 * @param  string  $end
 * @return string
 */',
        'startLine' => 769,
        'endLine' => 778,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'markdown' => 
      array (
        'name' => 'markdown',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 788,
            'endLine' => 788,
            'startColumn' => 37,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'options' => 
          array (
            'name' => 'options',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 788,
                'endLine' => 788,
                'startTokenPos' => 3223,
                'startFilePos' => 27228,
                'endTokenPos' => 3224,
                'endFilePos' => 27229,
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
            'startLine' => 788,
            'endLine' => 788,
            'startColumn' => 46,
            'endColumn' => 64,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'extensions' => 
          array (
            'name' => 'extensions',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 788,
                'endLine' => 788,
                'startTokenPos' => 3233,
                'startFilePos' => 27252,
                'endTokenPos' => 3234,
                'endFilePos' => 27253,
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
            'startLine' => 788,
            'endLine' => 788,
            'startColumn' => 67,
            'endColumn' => 88,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Converts GitHub flavored Markdown into HTML.
 *
 * @param  string  $string
 * @param  array  $options
 * @param  \\League\\CommonMark\\Extension\\ExtensionInterface[]  $extensions
 * @return ($string is \'\' ? \'\' : string)
 */',
        'startLine' => 788,
        'endLine' => 801,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'inlineMarkdown' => 
      array (
        'name' => 'inlineMarkdown',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 811,
            'endLine' => 811,
            'startColumn' => 43,
            'endColumn' => 49,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'options' => 
          array (
            'name' => 'options',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 811,
                'endLine' => 811,
                'startTokenPos' => 3326,
                'startFilePos' => 27911,
                'endTokenPos' => 3327,
                'endFilePos' => 27912,
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
            'startLine' => 811,
            'endLine' => 811,
            'startColumn' => 52,
            'endColumn' => 70,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'extensions' => 
          array (
            'name' => 'extensions',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 811,
                'endLine' => 811,
                'startTokenPos' => 3336,
                'startFilePos' => 27935,
                'endTokenPos' => 3337,
                'endFilePos' => 27936,
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
            'startLine' => 811,
            'endLine' => 811,
            'startColumn' => 73,
            'endColumn' => 94,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Converts inline Markdown into HTML.
 *
 * @param  string  $string
 * @param  array  $options
 * @param  \\League\\CommonMark\\Extension\\ExtensionInterface[]  $extensions
 * @return ($string is \'\' ? \'\' : string)
 */',
        'startLine' => 811,
        'endLine' => 827,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'mask' => 
      array (
        'name' => 'mask',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 839,
            'endLine' => 839,
            'startColumn' => 33,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'character' => 
          array (
            'name' => 'character',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 839,
            'endLine' => 839,
            'startColumn' => 42,
            'endColumn' => 51,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'index' => 
          array (
            'name' => 'index',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 839,
            'endLine' => 839,
            'startColumn' => 54,
            'endColumn' => 59,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
          'length' => 
          array (
            'name' => 'length',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 839,
                'endLine' => 839,
                'startTokenPos' => 3458,
                'startFilePos' => 28738,
                'endTokenPos' => 3458,
                'endFilePos' => 28741,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 839,
            'endLine' => 839,
            'startColumn' => 62,
            'endColumn' => 75,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
          'encoding' => 
          array (
            'name' => 'encoding',
            'default' => 
            array (
              'code' => '\'UTF-8\'',
              'attributes' => 
              array (
                'startLine' => 839,
                'endLine' => 839,
                'startTokenPos' => 3465,
                'startFilePos' => 28756,
                'endTokenPos' => 3465,
                'endFilePos' => 28762,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 839,
            'endLine' => 839,
            'startColumn' => 78,
            'endColumn' => 96,
            'parameterIndex' => 4,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Masks a portion of a string with a repeated character.
 *
 * @param  string  $string
 * @param  string  $character
 * @param  int  $index
 * @param  int|null  $length
 * @param  string  $encoding
 * @return string
 */',
        'startLine' => 839,
        'endLine' => 863,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'match' => 
      array (
        'name' => 'match',
        'parameters' => 
        array (
          'pattern' => 
          array (
            'name' => 'pattern',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 872,
            'endLine' => 872,
            'startColumn' => 34,
            'endColumn' => 41,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 872,
            'endLine' => 872,
            'startColumn' => 44,
            'endColumn' => 51,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the string matching the given pattern.
 *
 * @param  string  $pattern
 * @param  string  $subject
 * @return string
 */',
        'startLine' => 872,
        'endLine' => 881,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'isMatch' => 
      array (
        'name' => 'isMatch',
        'parameters' => 
        array (
          'pattern' => 
          array (
            'name' => 'pattern',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 890,
            'endLine' => 890,
            'startColumn' => 36,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 890,
            'endLine' => 890,
            'startColumn' => 46,
            'endColumn' => 51,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if a given string matches a given pattern.
 *
 * @param  string|iterable<string>  $pattern
 * @param  string  $value
 * @return ($pattern is array{} ? false : bool)
 */',
        'startLine' => 890,
        'endLine' => 907,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'matchAll' => 
      array (
        'name' => 'matchAll',
        'parameters' => 
        array (
          'pattern' => 
          array (
            'name' => 'pattern',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 916,
            'endLine' => 916,
            'startColumn' => 37,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 916,
            'endLine' => 916,
            'startColumn' => 47,
            'endColumn' => 54,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the string matching the given pattern.
 *
 * @param  string  $pattern
 * @param  string  $subject
 * @return \\Illuminate\\Support\\Collection
 */',
        'startLine' => 916,
        'endLine' => 925,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'numbers' => 
      array (
        'name' => 'numbers',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 933,
            'endLine' => 933,
            'startColumn' => 36,
            'endColumn' => 41,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Remove all non-numeric characters from a string.
 *
 * @param  string  $value
 * @return string
 */',
        'startLine' => 933,
        'endLine' => 936,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'padBoth' => 
      array (
        'name' => 'padBoth',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 946,
            'endLine' => 946,
            'startColumn' => 36,
            'endColumn' => 41,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'length' => 
          array (
            'name' => 'length',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 946,
            'endLine' => 946,
            'startColumn' => 44,
            'endColumn' => 50,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'pad' => 
          array (
            'name' => 'pad',
            'default' => 
            array (
              'code' => '\' \'',
              'attributes' => 
              array (
                'startLine' => 946,
                'endLine' => 946,
                'startTokenPos' => 3959,
                'startFilePos' => 31363,
                'endTokenPos' => 3959,
                'endFilePos' => 31365,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 946,
            'endLine' => 946,
            'startColumn' => 53,
            'endColumn' => 62,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Pad both sides of a string with another.
 *
 * @param  string  $value
 * @param  int  $length
 * @param  string  $pad
 * @return string
 */',
        'startLine' => 946,
        'endLine' => 949,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'padLeft' => 
      array (
        'name' => 'padLeft',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 959,
            'endLine' => 959,
            'startColumn' => 36,
            'endColumn' => 41,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'length' => 
          array (
            'name' => 'length',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 959,
            'endLine' => 959,
            'startColumn' => 44,
            'endColumn' => 50,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'pad' => 
          array (
            'name' => 'pad',
            'default' => 
            array (
              'code' => '\' \'',
              'attributes' => 
              array (
                'startLine' => 959,
                'endLine' => 959,
                'startTokenPos' => 4003,
                'startFilePos' => 31686,
                'endTokenPos' => 4003,
                'endFilePos' => 31688,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 959,
            'endLine' => 959,
            'startColumn' => 53,
            'endColumn' => 62,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Pad the left side of a string with another.
 *
 * @param  string  $value
 * @param  int  $length
 * @param  string  $pad
 * @return string
 */',
        'startLine' => 959,
        'endLine' => 962,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'padRight' => 
      array (
        'name' => 'padRight',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 972,
            'endLine' => 972,
            'startColumn' => 37,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'length' => 
          array (
            'name' => 'length',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 972,
            'endLine' => 972,
            'startColumn' => 45,
            'endColumn' => 51,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'pad' => 
          array (
            'name' => 'pad',
            'default' => 
            array (
              'code' => '\' \'',
              'attributes' => 
              array (
                'startLine' => 972,
                'endLine' => 972,
                'startTokenPos' => 4047,
                'startFilePos' => 32011,
                'endTokenPos' => 4047,
                'endFilePos' => 32013,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 972,
            'endLine' => 972,
            'startColumn' => 54,
            'endColumn' => 63,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Pad the right side of a string with another.
 *
 * @param  string  $value
 * @param  int  $length
 * @param  string  $pad
 * @return string
 */',
        'startLine' => 972,
        'endLine' => 975,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'parseCallback' => 
      array (
        'name' => 'parseCallback',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 984,
            'endLine' => 984,
            'startColumn' => 42,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'default' => 
          array (
            'name' => 'default',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 984,
                'endLine' => 984,
                'startTokenPos' => 4088,
                'startFilePos' => 32357,
                'endTokenPos' => 4088,
                'endFilePos' => 32360,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 984,
            'endLine' => 984,
            'startColumn' => 53,
            'endColumn' => 67,
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
 * Parse a Class[@]method style callback into class and method.
 *
 * @param  string  $callback
 * @param  string|null  $default
 * @return array<int, string|null>
 */',
        'startLine' => 984,
        'endLine' => 998,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'plural' => 
      array (
        'name' => 'plural',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1008,
            'endLine' => 1008,
            'startColumn' => 35,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'count' => 
          array (
            'name' => 'count',
            'default' => 
            array (
              'code' => '2',
              'attributes' => 
              array (
                'startLine' => 1008,
                'endLine' => 1008,
                'startTokenPos' => 4226,
                'startFilePos' => 33071,
                'endTokenPos' => 4226,
                'endFilePos' => 33071,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1008,
            'endLine' => 1008,
            'startColumn' => 43,
            'endColumn' => 52,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'prependCount' => 
          array (
            'name' => 'prependCount',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 1008,
                'endLine' => 1008,
                'startTokenPos' => 4233,
                'startFilePos' => 33090,
                'endTokenPos' => 4233,
                'endFilePos' => 33094,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1008,
            'endLine' => 1008,
            'startColumn' => 55,
            'endColumn' => 75,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the plural form of an English word.
 *
 * @param  string  $value
 * @param  int|array|\\Countable  $count
 * @param  bool  $prependCount
 * @return string
 */',
        'startLine' => 1008,
        'endLine' => 1015,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'pluralStudly' => 
      array (
        'name' => 'pluralStudly',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1024,
            'endLine' => 1024,
            'startColumn' => 41,
            'endColumn' => 46,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'count' => 
          array (
            'name' => 'count',
            'default' => 
            array (
              'code' => '2',
              'attributes' => 
              array (
                'startLine' => 1024,
                'endLine' => 1024,
                'startTokenPos' => 4312,
                'startFilePos' => 33541,
                'endTokenPos' => 4312,
                'endFilePos' => 33541,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1024,
            'endLine' => 1024,
            'startColumn' => 49,
            'endColumn' => 58,
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
 * Pluralize the last word of an English, studly caps case string.
 *
 * @param  string  $value
 * @param  int|array|\\Countable  $count
 * @return string
 */',
        'startLine' => 1024,
        'endLine' => 1031,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'pluralPascal' => 
      array (
        'name' => 'pluralPascal',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1040,
            'endLine' => 1040,
            'startColumn' => 41,
            'endColumn' => 46,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'count' => 
          array (
            'name' => 'count',
            'default' => 
            array (
              'code' => '2',
              'attributes' => 
              array (
                'startLine' => 1040,
                'endLine' => 1040,
                'startTokenPos' => 4387,
                'startFilePos' => 33999,
                'endTokenPos' => 4387,
                'endFilePos' => 33999,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1040,
            'endLine' => 1040,
            'startColumn' => 49,
            'endColumn' => 58,
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
 * Pluralize the last word of an English, Pascal caps case string.
 *
 * @param  string  $value
 * @param  int|array|\\Countable  $count
 * @return string
 */',
        'startLine' => 1040,
        'endLine' => 1043,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'password' => 
      array (
        'name' => 'password',
        'parameters' => 
        array (
          'length' => 
          array (
            'name' => 'length',
            'default' => 
            array (
              'code' => '32',
              'attributes' => 
              array (
                'startLine' => 1055,
                'endLine' => 1055,
                'startTokenPos' => 4421,
                'startFilePos' => 34479,
                'endTokenPos' => 4421,
                'endFilePos' => 34480,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1055,
            'endLine' => 1055,
            'startColumn' => 37,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'letters' => 
          array (
            'name' => 'letters',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 1055,
                'endLine' => 1055,
                'startTokenPos' => 4428,
                'startFilePos' => 34494,
                'endTokenPos' => 4428,
                'endFilePos' => 34497,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1055,
            'endLine' => 1055,
            'startColumn' => 51,
            'endColumn' => 65,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'numbers' => 
          array (
            'name' => 'numbers',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 1055,
                'endLine' => 1055,
                'startTokenPos' => 4435,
                'startFilePos' => 34511,
                'endTokenPos' => 4435,
                'endFilePos' => 34514,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1055,
            'endLine' => 1055,
            'startColumn' => 68,
            'endColumn' => 82,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'symbols' => 
          array (
            'name' => 'symbols',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 1055,
                'endLine' => 1055,
                'startTokenPos' => 4442,
                'startFilePos' => 34528,
                'endTokenPos' => 4442,
                'endFilePos' => 34531,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1055,
            'endLine' => 1055,
            'startColumn' => 85,
            'endColumn' => 99,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
          'spaces' => 
          array (
            'name' => 'spaces',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 1055,
                'endLine' => 1055,
                'startTokenPos' => 4449,
                'startFilePos' => 34544,
                'endTokenPos' => 4449,
                'endFilePos' => 34548,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1055,
            'endLine' => 1055,
            'startColumn' => 102,
            'endColumn' => 116,
            'parameterIndex' => 4,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Generate a random, secure password.
 *
 * @param  int  $length
 * @param  bool  $letters
 * @param  bool  $numbers
 * @param  bool  $symbols
 * @param  bool  $spaces
 * @return ($letters is false ? ($numbers is true ? ($symbols is false ? ($spaces is false ? numeric-string : string) : string) : string) : string)
 */',
        'startLine' => 1055,
        'endLine' => 1086,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'position' => 
      array (
        'name' => 'position',
        'parameters' => 
        array (
          'haystack' => 
          array (
            'name' => 'haystack',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1097,
            'endLine' => 1097,
            'startColumn' => 37,
            'endColumn' => 45,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'needle' => 
          array (
            'name' => 'needle',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1097,
            'endLine' => 1097,
            'startColumn' => 48,
            'endColumn' => 54,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'offset' => 
          array (
            'name' => 'offset',
            'default' => 
            array (
              'code' => '0',
              'attributes' => 
              array (
                'startLine' => 1097,
                'endLine' => 1097,
                'startTokenPos' => 4977,
                'startFilePos' => 36256,
                'endTokenPos' => 4977,
                'endFilePos' => 36256,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1097,
            'endLine' => 1097,
            'startColumn' => 57,
            'endColumn' => 67,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'encoding' => 
          array (
            'name' => 'encoding',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1097,
                'endLine' => 1097,
                'startTokenPos' => 4984,
                'startFilePos' => 36271,
                'endTokenPos' => 4984,
                'endFilePos' => 36274,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1097,
            'endLine' => 1097,
            'startColumn' => 70,
            'endColumn' => 85,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Find the multi-byte safe position of the first occurrence of a given substring in a string.
 *
 * @param  string  $haystack
 * @param  string  $needle
 * @param  int  $offset
 * @param  string|null  $encoding
 * @return ($haystack is \'\' ? false : ($needle is \'\' ? false : int|false))
 */',
        'startLine' => 1097,
        'endLine' => 1100,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'random' => 
      array (
        'name' => 'random',
        'parameters' => 
        array (
          'length' => 
          array (
            'name' => 'length',
            'default' => 
            array (
              'code' => '16',
              'attributes' => 
              array (
                'startLine' => 1108,
                'endLine' => 1108,
                'startTokenPos' => 5024,
                'startFilePos' => 36542,
                'endTokenPos' => 5024,
                'endFilePos' => 36543,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1108,
            'endLine' => 1108,
            'startColumn' => 35,
            'endColumn' => 46,
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
 * Generate a more truly "random" alpha-numeric string.
 *
 * @param  int  $length
 * @return string
 */',
        'startLine' => 1108,
        'endLine' => 1125,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'createRandomStringsUsing' => 
      array (
        'name' => 'createRandomStringsUsing',
        'parameters' => 
        array (
          'factory' => 
          array (
            'name' => 'factory',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1133,
                'endLine' => 1133,
                'startTokenPos' => 5184,
                'startFilePos' => 37268,
                'endTokenPos' => 5184,
                'endFilePos' => 37271,
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
                      'name' => 'callable',
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
            'startLine' => 1133,
            'endLine' => 1133,
            'startColumn' => 53,
            'endColumn' => 77,
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
 * Set the callable that will be used to generate random strings.
 *
 * @param  (callable(int): string)|null  $factory
 * @return void
 */',
        'startLine' => 1133,
        'endLine' => 1136,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'createRandomStringsUsingSequence' => 
      array (
        'name' => 'createRandomStringsUsingSequence',
        'parameters' => 
        array (
          'sequence' => 
          array (
            'name' => 'sequence',
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
            'startLine' => 1145,
            'endLine' => 1145,
            'startColumn' => 61,
            'endColumn' => 75,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'whenMissing' => 
          array (
            'name' => 'whenMissing',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1145,
                'endLine' => 1145,
                'startTokenPos' => 5219,
                'startFilePos' => 37634,
                'endTokenPos' => 5219,
                'endFilePos' => 37637,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1145,
            'endLine' => 1145,
            'startColumn' => 78,
            'endColumn' => 96,
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
 * Set the sequence that will be used to generate random strings.
 *
 * @param  string[]  $sequence
 * @param  (callable(int): string)|null  $whenMissing
 * @return void
 */',
        'startLine' => 1145,
        'endLine' => 1170,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'createRandomStringsNormally' => 
      array (
        'name' => 'createRandomStringsNormally',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Indicate that random strings should be created normally and not using a custom factory.
 *
 * @return void
 */',
        'startLine' => 1177,
        'endLine' => 1180,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'repeat' => 
      array (
        'name' => 'repeat',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
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
            'startLine' => 1189,
            'endLine' => 1189,
            'startColumn' => 35,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'times' => 
          array (
            'name' => 'times',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1189,
            'endLine' => 1189,
            'startColumn' => 51,
            'endColumn' => 60,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Repeat the given string.
 *
 * @param  string  $string
 * @param  int  $times
 * @return string
 */',
        'startLine' => 1189,
        'endLine' => 1192,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'replaceArray' => 
      array (
        'name' => 'replaceArray',
        'parameters' => 
        array (
          'search' => 
          array (
            'name' => 'search',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1202,
            'endLine' => 1202,
            'startColumn' => 41,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'replace' => 
          array (
            'name' => 'replace',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1202,
            'endLine' => 1202,
            'startColumn' => 50,
            'endColumn' => 57,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1202,
            'endLine' => 1202,
            'startColumn' => 60,
            'endColumn' => 67,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Replace a given value in the string sequentially with an array.
 *
 * @param  string  $search
 * @param  iterable<string>  $replace
 * @param  string  $subject
 * @return string
 */',
        'startLine' => 1202,
        'endLine' => 1217,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'toStringOr' => 
      array (
        'name' => 'toStringOr',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1226,
            'endLine' => 1226,
            'startColumn' => 40,
            'endColumn' => 45,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'fallback' => 
          array (
            'name' => 'fallback',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1226,
            'endLine' => 1226,
            'startColumn' => 48,
            'endColumn' => 56,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Convert the given value to a string or return the given fallback on failure.
 *
 * @param  mixed  $value
 * @param  string  $fallback
 * @return string
 */',
        'startLine' => 1226,
        'endLine' => 1233,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 20,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'replace' => 
      array (
        'name' => 'replace',
        'parameters' => 
        array (
          'search' => 
          array (
            'name' => 'search',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1244,
            'endLine' => 1244,
            'startColumn' => 36,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'replace' => 
          array (
            'name' => 'replace',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1244,
            'endLine' => 1244,
            'startColumn' => 45,
            'endColumn' => 52,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1244,
            'endLine' => 1244,
            'startColumn' => 55,
            'endColumn' => 62,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
          'caseSensitive' => 
          array (
            'name' => 'caseSensitive',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 1244,
                'endLine' => 1244,
                'startTokenPos' => 5609,
                'startFilePos' => 40246,
                'endTokenPos' => 5609,
                'endFilePos' => 40249,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1244,
            'endLine' => 1244,
            'startColumn' => 65,
            'endColumn' => 85,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Replace the given value in the given string.
 *
 * @param  string|iterable<string>  $search
 * @param  string|iterable<string>  $replace
 * @param  string|iterable<string>  $subject
 * @param  bool  $caseSensitive
 * @return ($subject is string ? string : string[])
 */',
        'startLine' => 1244,
        'endLine' => 1261,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'replaceFirst' => 
      array (
        'name' => 'replaceFirst',
        'parameters' => 
        array (
          'search' => 
          array (
            'name' => 'search',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1271,
            'endLine' => 1271,
            'startColumn' => 41,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'replace' => 
          array (
            'name' => 'replace',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1271,
            'endLine' => 1271,
            'startColumn' => 50,
            'endColumn' => 57,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1271,
            'endLine' => 1271,
            'startColumn' => 60,
            'endColumn' => 67,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Replace the first occurrence of a given value in the string.
 *
 * @param  string  $search
 * @param  string  $replace
 * @param  string  $subject
 * @return string
 */',
        'startLine' => 1271,
        'endLine' => 1286,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'replaceStart' => 
      array (
        'name' => 'replaceStart',
        'parameters' => 
        array (
          'search' => 
          array (
            'name' => 'search',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1296,
            'endLine' => 1296,
            'startColumn' => 41,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'replace' => 
          array (
            'name' => 'replace',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1296,
            'endLine' => 1296,
            'startColumn' => 50,
            'endColumn' => 57,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1296,
            'endLine' => 1296,
            'startColumn' => 60,
            'endColumn' => 67,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Replace the first occurrence of the given value if it appears at the start of the string.
 *
 * @param  string  $search
 * @param  string  $replace
 * @param  string  $subject
 * @return string
 */',
        'startLine' => 1296,
        'endLine' => 1309,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'replaceLast' => 
      array (
        'name' => 'replaceLast',
        'parameters' => 
        array (
          'search' => 
          array (
            'name' => 'search',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1319,
            'endLine' => 1319,
            'startColumn' => 40,
            'endColumn' => 46,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'replace' => 
          array (
            'name' => 'replace',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1319,
            'endLine' => 1319,
            'startColumn' => 49,
            'endColumn' => 56,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1319,
            'endLine' => 1319,
            'startColumn' => 59,
            'endColumn' => 66,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Replace the last occurrence of a given value in the string.
 *
 * @param  string  $search
 * @param  string  $replace
 * @param  string  $subject
 * @return string
 */',
        'startLine' => 1319,
        'endLine' => 1334,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'replaceEnd' => 
      array (
        'name' => 'replaceEnd',
        'parameters' => 
        array (
          'search' => 
          array (
            'name' => 'search',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1344,
            'endLine' => 1344,
            'startColumn' => 39,
            'endColumn' => 45,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'replace' => 
          array (
            'name' => 'replace',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1344,
            'endLine' => 1344,
            'startColumn' => 48,
            'endColumn' => 55,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1344,
            'endLine' => 1344,
            'startColumn' => 58,
            'endColumn' => 65,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Replace the last occurrence of a given value if it appears at the end of the string.
 *
 * @param  string  $search
 * @param  string  $replace
 * @param  string  $subject
 * @return string
 */',
        'startLine' => 1344,
        'endLine' => 1357,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'replaceMatches' => 
      array (
        'name' => 'replaceMatches',
        'parameters' => 
        array (
          'pattern' => 
          array (
            'name' => 'pattern',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1368,
            'endLine' => 1368,
            'startColumn' => 43,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'replace' => 
          array (
            'name' => 'replace',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1368,
            'endLine' => 1368,
            'startColumn' => 53,
            'endColumn' => 60,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1368,
            'endLine' => 1368,
            'startColumn' => 63,
            'endColumn' => 70,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
          'limit' => 
          array (
            'name' => 'limit',
            'default' => 
            array (
              'code' => '-1',
              'attributes' => 
              array (
                'startLine' => 1368,
                'endLine' => 1368,
                'startTokenPos' => 6128,
                'startFilePos' => 43503,
                'endTokenPos' => 6129,
                'endFilePos' => 43504,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1368,
            'endLine' => 1368,
            'startColumn' => 73,
            'endColumn' => 83,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Replace the patterns matching the given regular expression.
 *
 * @param  string|string[]  $pattern
 * @param  (\\Closure(array): string)|string[]|string  $replace
 * @param  string[]|string  $subject
 * @param  int  $limit
 * @return ($subject is array ? string[]|null : string|null)
 */',
        'startLine' => 1368,
        'endLine' => 1375,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'remove' => 
      array (
        'name' => 'remove',
        'parameters' => 
        array (
          'search' => 
          array (
            'name' => 'search',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1385,
            'endLine' => 1385,
            'startColumn' => 35,
            'endColumn' => 41,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1385,
            'endLine' => 1385,
            'startColumn' => 44,
            'endColumn' => 51,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'caseSensitive' => 
          array (
            'name' => 'caseSensitive',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 1385,
                'endLine' => 1385,
                'startTokenPos' => 6204,
                'startFilePos' => 44034,
                'endTokenPos' => 6204,
                'endFilePos' => 44037,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1385,
            'endLine' => 1385,
            'startColumn' => 54,
            'endColumn' => 74,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Remove any occurrence of the given string in the subject.
 *
 * @param  string|iterable<string>  $search
 * @param  string|iterable<string>  $subject
 * @param  bool  $caseSensitive
 * @return string
 */',
        'startLine' => 1385,
        'endLine' => 1394,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'reverse' => 
      array (
        'name' => 'reverse',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
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
            'startLine' => 1402,
            'endLine' => 1402,
            'startColumn' => 36,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Reverse the given string.
 *
 * @param  string  $value
 * @return string
 */',
        'startLine' => 1402,
        'endLine' => 1405,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'start' => 
      array (
        'name' => 'start',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1414,
            'endLine' => 1414,
            'startColumn' => 34,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'prefix' => 
          array (
            'name' => 'prefix',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1414,
            'endLine' => 1414,
            'startColumn' => 42,
            'endColumn' => 48,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Begin a string with a single instance of a given value.
 *
 * @param  string  $value
 * @param  string  $prefix
 * @return ($value is \'\' ? ($prefix is \'\' ? \'\' : non-empty-string): non-empty-string)
 */',
        'startLine' => 1414,
        'endLine' => 1419,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'upper' => 
      array (
        'name' => 'upper',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1427,
            'endLine' => 1427,
            'startColumn' => 34,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Convert the given string to upper-case.
 *
 * @param  string  $value
 * @return ($value is \'\' ? \'\' : non-empty-string&uppercase-string)
 */',
        'startLine' => 1427,
        'endLine' => 1430,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'title' => 
      array (
        'name' => 'title',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1438,
            'endLine' => 1438,
            'startColumn' => 34,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Convert the given string to proper case.
 *
 * @param  string  $value
 * @return string
 */',
        'startLine' => 1438,
        'endLine' => 1441,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'headline' => 
      array (
        'name' => 'headline',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1449,
            'endLine' => 1449,
            'startColumn' => 37,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Convert the given string to proper case for each word.
 *
 * @param  string  $value
 * @return string
 */',
        'startLine' => 1449,
        'endLine' => 1460,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'initials' => 
      array (
        'name' => 'initials',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1469,
            'endLine' => 1469,
            'startColumn' => 37,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'capitalize' => 
          array (
            'name' => 'capitalize',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 1469,
                'endLine' => 1469,
                'startTokenPos' => 6565,
                'startFilePos' => 46278,
                'endTokenPos' => 6565,
                'endFilePos' => 46282,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1469,
            'endLine' => 1469,
            'startColumn' => 45,
            'endColumn' => 63,
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
 * Get the "initials" representing each word in the provided string, optionally capitalizing.
 *
 * @param  string  $value
 * @param  bool  $capitalize
 * @return string
 */',
        'startLine' => 1469,
        'endLine' => 1478,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'apa' => 
      array (
        'name' => 'apa',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1488,
            'endLine' => 1488,
            'startColumn' => 32,
            'endColumn' => 37,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Convert the given string to APA-style title case.
 *
 * See: https://apastyle.apa.org/style-grammar-guidelines/capitalization/title-case
 *
 * @param  string  $value
 * @return string
 */',
        'startLine' => 1488,
        'endLine' => 1530,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'singular' => 
      array (
        'name' => 'singular',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1538,
            'endLine' => 1538,
            'startColumn' => 37,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the singular form of an English word.
 *
 * @param  string  $value
 * @return string
 */',
        'startLine' => 1538,
        'endLine' => 1541,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'slug' => 
      array (
        'name' => 'slug',
        'parameters' => 
        array (
          'title' => 
          array (
            'name' => 'title',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1552,
            'endLine' => 1552,
            'startColumn' => 33,
            'endColumn' => 38,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'separator' => 
          array (
            'name' => 'separator',
            'default' => 
            array (
              'code' => '\'-\'',
              'attributes' => 
              array (
                'startLine' => 1552,
                'endLine' => 1552,
                'startTokenPos' => 7175,
                'startFilePos' => 48940,
                'endTokenPos' => 7175,
                'endFilePos' => 48942,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1552,
            'endLine' => 1552,
            'startColumn' => 41,
            'endColumn' => 56,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'language' => 
          array (
            'name' => 'language',
            'default' => 
            array (
              'code' => '\'en\'',
              'attributes' => 
              array (
                'startLine' => 1552,
                'endLine' => 1552,
                'startTokenPos' => 7182,
                'startFilePos' => 48957,
                'endTokenPos' => 7182,
                'endFilePos' => 48960,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1552,
            'endLine' => 1552,
            'startColumn' => 59,
            'endColumn' => 74,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'dictionary' => 
          array (
            'name' => 'dictionary',
            'default' => 
            array (
              'code' => '[\'@\' => \'at\']',
              'attributes' => 
              array (
                'startLine' => 1552,
                'endLine' => 1552,
                'startTokenPos' => 7189,
                'startFilePos' => 48977,
                'endTokenPos' => 7195,
                'endFilePos' => 48989,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1552,
            'endLine' => 1552,
            'startColumn' => 77,
            'endColumn' => 103,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Generate a URL friendly "slug" from a given string.
 *
 * @param  string  $title
 * @param  string  $separator
 * @param  string|null  $language
 * @param  array<string, string>  $dictionary
 * @return string
 */',
        'startLine' => 1552,
        'endLine' => 1575,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'snake' => 
      array (
        'name' => 'snake',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1584,
            'endLine' => 1584,
            'startColumn' => 34,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'delimiter' => 
          array (
            'name' => 'delimiter',
            'default' => 
            array (
              'code' => '\'_\'',
              'attributes' => 
              array (
                'startLine' => 1584,
                'endLine' => 1584,
                'startTokenPos' => 7408,
                'startFilePos' => 50128,
                'endTokenPos' => 7408,
                'endFilePos' => 50130,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1584,
            'endLine' => 1584,
            'startColumn' => 42,
            'endColumn' => 57,
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
 * Convert a string to snake case.
 *
 * @param  string  $value
 * @param  string  $delimiter
 * @return string
 */',
        'startLine' => 1584,
        'endLine' => 1599,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'trim' => 
      array (
        'name' => 'trim',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1608,
            'endLine' => 1608,
            'startColumn' => 33,
            'endColumn' => 38,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'charlist' => 
          array (
            'name' => 'charlist',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1608,
                'endLine' => 1608,
                'startTokenPos' => 7547,
                'startFilePos' => 50793,
                'endTokenPos' => 7547,
                'endFilePos' => 50796,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1608,
            'endLine' => 1608,
            'startColumn' => 41,
            'endColumn' => 56,
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
 * Remove all whitespace from both ends of a string.
 *
 * @param  string  $value
 * @param  string|null  $charlist
 * @return string
 */',
        'startLine' => 1608,
        'endLine' => 1617,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'ltrim' => 
      array (
        'name' => 'ltrim',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1626,
            'endLine' => 1626,
            'startColumn' => 34,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'charlist' => 
          array (
            'name' => 'charlist',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1626,
                'endLine' => 1626,
                'startTokenPos' => 7640,
                'startFilePos' => 51364,
                'endTokenPos' => 7640,
                'endFilePos' => 51367,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1626,
            'endLine' => 1626,
            'startColumn' => 42,
            'endColumn' => 57,
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
 * Remove all whitespace from the beginning of a string.
 *
 * @param  string  $value
 * @param  string|null  $charlist
 * @return string
 */',
        'startLine' => 1626,
        'endLine' => 1635,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'rtrim' => 
      array (
        'name' => 'rtrim',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1644,
            'endLine' => 1644,
            'startColumn' => 34,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'charlist' => 
          array (
            'name' => 'charlist',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1644,
                'endLine' => 1644,
                'startTokenPos' => 7725,
                'startFilePos' => 51873,
                'endTokenPos' => 7725,
                'endFilePos' => 51876,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1644,
            'endLine' => 1644,
            'startColumn' => 42,
            'endColumn' => 57,
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
 * Remove all whitespace from the end of a string.
 *
 * @param  string  $value
 * @param  string|null  $charlist
 * @return string
 */',
        'startLine' => 1644,
        'endLine' => 1653,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'squish' => 
      array (
        'name' => 'squish',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1661,
            'endLine' => 1661,
            'startColumn' => 35,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Remove all "extra" blank space from the given string.
 *
 * @param  string  $value
 * @return string
 */',
        'startLine' => 1661,
        'endLine' => 1664,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'startsWith' => 
      array (
        'name' => 'startsWith',
        'parameters' => 
        array (
          'haystack' => 
          array (
            'name' => 'haystack',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1675,
            'endLine' => 1675,
            'startColumn' => 39,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'needles' => 
          array (
            'name' => 'needles',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1675,
            'endLine' => 1675,
            'startColumn' => 50,
            'endColumn' => 57,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if a given string starts with a given substring.
 *
 * @param  string  $haystack
 * @param  string|iterable<string>  $needles
 * @return ($needles is array{} ? false : ($haystack is non-empty-string ? bool : false))
 *
 * @phpstan-assert-if-true =non-empty-string $haystack
 */',
        'startLine' => 1675,
        'endLine' => 1692,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'doesntStartWith' => 
      array (
        'name' => 'doesntStartWith',
        'parameters' => 
        array (
          'haystack' => 
          array (
            'name' => 'haystack',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1703,
            'endLine' => 1703,
            'startColumn' => 44,
            'endColumn' => 52,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'needles' => 
          array (
            'name' => 'needles',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1703,
            'endLine' => 1703,
            'startColumn' => 55,
            'endColumn' => 62,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if a given string doesn\'t start with a given substring.
 *
 * @param  string  $haystack
 * @param  string|iterable<string>  $needles
 * @return ($needles is array{} ? true : ($haystack is non-empty-string ? bool : true))
 *
 * @phpstan-assert-if-false =non-empty-string $haystack
 */',
        'startLine' => 1703,
        'endLine' => 1706,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'studly' => 
      array (
        'name' => 'studly',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1714,
            'endLine' => 1714,
            'startColumn' => 35,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Convert a value to studly caps case.
 *
 * @param  string  $value
 * @return ($value is \'\' ? \'\' : string)
 */',
        'startLine' => 1714,
        'endLine' => 1727,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'pascal' => 
      array (
        'name' => 'pascal',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1735,
            'endLine' => 1735,
            'startColumn' => 35,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Convert a value to Pascal case.
 *
 * @param  string  $value
 * @return ($value is \'\' ? \'\' : string)
 */',
        'startLine' => 1735,
        'endLine' => 1738,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'substr' => 
      array (
        'name' => 'substr',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1749,
            'endLine' => 1749,
            'startColumn' => 35,
            'endColumn' => 41,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'start' => 
          array (
            'name' => 'start',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1749,
            'endLine' => 1749,
            'startColumn' => 44,
            'endColumn' => 49,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'length' => 
          array (
            'name' => 'length',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1749,
                'endLine' => 1749,
                'startTokenPos' => 8147,
                'startFilePos' => 54766,
                'endTokenPos' => 8147,
                'endFilePos' => 54769,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1749,
            'endLine' => 1749,
            'startColumn' => 52,
            'endColumn' => 65,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'encoding' => 
          array (
            'name' => 'encoding',
            'default' => 
            array (
              'code' => '\'UTF-8\'',
              'attributes' => 
              array (
                'startLine' => 1749,
                'endLine' => 1749,
                'startTokenPos' => 8154,
                'startFilePos' => 54784,
                'endTokenPos' => 8154,
                'endFilePos' => 54790,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1749,
            'endLine' => 1749,
            'startColumn' => 68,
            'endColumn' => 86,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Returns the portion of the string specified by the start and length parameters.
 *
 * @param  string  $string
 * @param  int  $start
 * @param  int|null  $length
 * @param  string  $encoding
 * @return string
 */',
        'startLine' => 1749,
        'endLine' => 1752,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'substrCount' => 
      array (
        'name' => 'substrCount',
        'parameters' => 
        array (
          'haystack' => 
          array (
            'name' => 'haystack',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1763,
            'endLine' => 1763,
            'startColumn' => 40,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'needle' => 
          array (
            'name' => 'needle',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1763,
            'endLine' => 1763,
            'startColumn' => 51,
            'endColumn' => 57,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'offset' => 
          array (
            'name' => 'offset',
            'default' => 
            array (
              'code' => '0',
              'attributes' => 
              array (
                'startLine' => 1763,
                'endLine' => 1763,
                'startTokenPos' => 8198,
                'startFilePos' => 55157,
                'endTokenPos' => 8198,
                'endFilePos' => 55157,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1763,
            'endLine' => 1763,
            'startColumn' => 60,
            'endColumn' => 70,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'length' => 
          array (
            'name' => 'length',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1763,
                'endLine' => 1763,
                'startTokenPos' => 8205,
                'startFilePos' => 55170,
                'endTokenPos' => 8205,
                'endFilePos' => 55173,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1763,
            'endLine' => 1763,
            'startColumn' => 73,
            'endColumn' => 86,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Returns the number of substring occurrences.
 *
 * @param  string  $haystack
 * @param  string  $needle
 * @param  int  $offset
 * @param  int|null  $length
 * @return int
 */',
        'startLine' => 1763,
        'endLine' => 1770,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'substrReplace' => 
      array (
        'name' => 'substrReplace',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1781,
            'endLine' => 1781,
            'startColumn' => 42,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'replace' => 
          array (
            'name' => 'replace',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1781,
            'endLine' => 1781,
            'startColumn' => 51,
            'endColumn' => 58,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'offset' => 
          array (
            'name' => 'offset',
            'default' => 
            array (
              'code' => '0',
              'attributes' => 
              array (
                'startLine' => 1781,
                'endLine' => 1781,
                'startTokenPos' => 8278,
                'startFilePos' => 55691,
                'endTokenPos' => 8278,
                'endFilePos' => 55691,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1781,
            'endLine' => 1781,
            'startColumn' => 61,
            'endColumn' => 71,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'length' => 
          array (
            'name' => 'length',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1781,
                'endLine' => 1781,
                'startTokenPos' => 8285,
                'startFilePos' => 55704,
                'endTokenPos' => 8285,
                'endFilePos' => 55707,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1781,
            'endLine' => 1781,
            'startColumn' => 74,
            'endColumn' => 87,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Replace text within a portion of a string.
 *
 * @param  string|string[]  $string
 * @param  string|string[]  $replace
 * @param  int|int[]  $offset
 * @param  int|int[]|null  $length
 * @return string|string[]
 */',
        'startLine' => 1781,
        'endLine' => 1790,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'swap' => 
      array (
        'name' => 'swap',
        'parameters' => 
        array (
          'map' => 
          array (
            'name' => 'map',
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
            'startLine' => 1799,
            'endLine' => 1799,
            'startColumn' => 33,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'subject' => 
          array (
            'name' => 'subject',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1799,
            'endLine' => 1799,
            'startColumn' => 45,
            'endColumn' => 52,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Swap multiple keywords in a string with other keywords.
 *
 * @param  array<string, string>  $map
 * @param  string  $subject
 * @return string
 */',
        'startLine' => 1799,
        'endLine' => 1802,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'take' => 
      array (
        'name' => 'take',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1811,
            'endLine' => 1811,
            'startColumn' => 33,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'limit' => 
          array (
            'name' => 'limit',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1811,
            'endLine' => 1811,
            'startColumn' => 42,
            'endColumn' => 51,
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
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Take the first or last {$limit} characters of a string.
 *
 * @param  string  $string
 * @param  int  $limit
 * @return string
 */',
        'startLine' => 1811,
        'endLine' => 1818,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'toBase64' => 
      array (
        'name' => 'toBase64',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1826,
            'endLine' => 1826,
            'startColumn' => 37,
            'endColumn' => 43,
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
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Convert the given string to Base64 encoding.
 *
 * @param  string  $string
 * @return ($string is \'\' ? \'\' : string)
 */',
        'startLine' => 1826,
        'endLine' => 1829,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'fromBase64' => 
      array (
        'name' => 'fromBase64',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1838,
            'endLine' => 1838,
            'startColumn' => 39,
            'endColumn' => 45,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'strict' => 
          array (
            'name' => 'strict',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 1838,
                'endLine' => 1838,
                'startTokenPos' => 8496,
                'startFilePos' => 57157,
                'endTokenPos' => 8496,
                'endFilePos' => 57161,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1838,
            'endLine' => 1838,
            'startColumn' => 48,
            'endColumn' => 62,
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
 * Decode the given Base64 encoded string.
 *
 * @param  string  $string
 * @param  bool  $strict
 * @return ($strict is true ? ($string is \'\' ? \'\' : string|false) : ($string is \'\' ? \'\' : string))
 */',
        'startLine' => 1838,
        'endLine' => 1841,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'lcfirst' => 
      array (
        'name' => 'lcfirst',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1849,
            'endLine' => 1849,
            'startColumn' => 36,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Make a string\'s first character lowercase.
 *
 * @param  string  $string
 * @return ($string is \'\' ? \'\' : non-empty-string)
 */',
        'startLine' => 1849,
        'endLine' => 1852,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'ucfirst' => 
      array (
        'name' => 'ucfirst',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1860,
            'endLine' => 1860,
            'startColumn' => 36,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Make a string\'s first character uppercase.
 *
 * @param  string  $string
 * @return ($string is \'\' ? \'\' : non-empty-string)
 */',
        'startLine' => 1860,
        'endLine' => 1863,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'ucwords' => 
      array (
        'name' => 'ucwords',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1872,
            'endLine' => 1872,
            'startColumn' => 36,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'separators' => 
          array (
            'name' => 'separators',
            'default' => 
            array (
              'code' => '" \\t\\r\\n\\f\\v"',
              'attributes' => 
              array (
                'startLine' => 1872,
                'endLine' => 1872,
                'startTokenPos' => 8627,
                'startFilePos' => 58099,
                'endTokenPos' => 8627,
                'endFilePos' => 58111,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1872,
            'endLine' => 1872,
            'startColumn' => 45,
            'endColumn' => 71,
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
 * Capitalize the first character of each word in a string.
 *
 * @param  string  $string
 * @param  string  $separators
 * @return ($string is \'\' ? \'\' : non-empty-string)
 */',
        'startLine' => 1872,
        'endLine' => 1879,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'ucsplit' => 
      array (
        'name' => 'ucsplit',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1887,
            'endLine' => 1887,
            'startColumn' => 36,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Split a string into pieces by uppercase characters.
 *
 * @param  string  $string
 * @return ($string is \'\' ? array{} : string[])
 */',
        'startLine' => 1887,
        'endLine' => 1890,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'wordCount' => 
      array (
        'name' => 'wordCount',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1899,
            'endLine' => 1899,
            'startColumn' => 38,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'characters' => 
          array (
            'name' => 'characters',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1899,
                'endLine' => 1899,
                'startTokenPos' => 8741,
                'startFilePos' => 58885,
                'endTokenPos' => 8741,
                'endFilePos' => 58888,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1899,
            'endLine' => 1899,
            'startColumn' => 47,
            'endColumn' => 64,
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
 * Get the number of words a string contains.
 *
 * @param  string  $string
 * @param  string|null  $characters
 * @return non-negative-int
 */',
        'startLine' => 1899,
        'endLine' => 1902,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'wordWrap' => 
      array (
        'name' => 'wordWrap',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1913,
            'endLine' => 1913,
            'startColumn' => 37,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'characters' => 
          array (
            'name' => 'characters',
            'default' => 
            array (
              'code' => '75',
              'attributes' => 
              array (
                'startLine' => 1913,
                'endLine' => 1913,
                'startTokenPos' => 8779,
                'startFilePos' => 59246,
                'endTokenPos' => 8779,
                'endFilePos' => 59247,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1913,
            'endLine' => 1913,
            'startColumn' => 46,
            'endColumn' => 61,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'break' => 
          array (
            'name' => 'break',
            'default' => 
            array (
              'code' => '"\\n"',
              'attributes' => 
              array (
                'startLine' => 1913,
                'endLine' => 1913,
                'startTokenPos' => 8786,
                'startFilePos' => 59259,
                'endTokenPos' => 8786,
                'endFilePos' => 59262,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1913,
            'endLine' => 1913,
            'startColumn' => 64,
            'endColumn' => 76,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'cutLongWords' => 
          array (
            'name' => 'cutLongWords',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 1913,
                'endLine' => 1913,
                'startTokenPos' => 8793,
                'startFilePos' => 59281,
                'endTokenPos' => 8793,
                'endFilePos' => 59285,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1913,
            'endLine' => 1913,
            'startColumn' => 79,
            'endColumn' => 99,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Wrap a string to a given number of characters.
 *
 * @param  string  $string
 * @param  int  $characters
 * @param  string  $break
 * @param  bool  $cutLongWords
 * @return string
 */',
        'startLine' => 1913,
        'endLine' => 1916,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'uuid' => 
      array (
        'name' => 'uuid',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Generate a UUID (version 4).
 *
 * @return \\Ramsey\\Uuid\\UuidInterface
 */',
        'startLine' => 1923,
        'endLine' => 1928,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'uuid7' => 
      array (
        'name' => 'uuid7',
        'parameters' => 
        array (
          'time' => 
          array (
            'name' => 'time',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1936,
                'endLine' => 1936,
                'startTokenPos' => 8871,
                'startFilePos' => 59823,
                'endTokenPos' => 8871,
                'endFilePos' => 59826,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1936,
            'endLine' => 1936,
            'startColumn' => 34,
            'endColumn' => 45,
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
 * Generate a UUID (version 7).
 *
 * @param  \\DateTimeInterface|null  $time
 * @return \\Ramsey\\Uuid\\UuidInterface
 */',
        'startLine' => 1936,
        'endLine' => 1941,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'orderedUuid' => 
      array (
        'name' => 'orderedUuid',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Generate a time-ordered UUID.
 *
 * @return \\Ramsey\\Uuid\\UuidInterface
 */',
        'startLine' => 1948,
        'endLine' => 1966,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'createUuidsUsing' => 
      array (
        'name' => 'createUuidsUsing',
        'parameters' => 
        array (
          'factory' => 
          array (
            'name' => 'factory',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1974,
                'endLine' => 1974,
                'startTokenPos' => 9021,
                'startFilePos' => 60798,
                'endTokenPos' => 9021,
                'endFilePos' => 60801,
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
                      'name' => 'callable',
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
            'startLine' => 1974,
            'endLine' => 1974,
            'startColumn' => 45,
            'endColumn' => 69,
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
 * Set the callable that will be used to generate UUIDs.
 *
 * @param  (callable(): \\Ramsey\\Uuid\\UuidInterface)|null  $factory
 * @return void
 */',
        'startLine' => 1974,
        'endLine' => 1977,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'createUuidsUsingSequence' => 
      array (
        'name' => 'createUuidsUsingSequence',
        'parameters' => 
        array (
          'sequence' => 
          array (
            'name' => 'sequence',
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
            'startLine' => 1986,
            'endLine' => 1986,
            'startColumn' => 53,
            'endColumn' => 67,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'whenMissing' => 
          array (
            'name' => 'whenMissing',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1986,
                'endLine' => 1986,
                'startTokenPos' => 9056,
                'startFilePos' => 61176,
                'endTokenPos' => 9056,
                'endFilePos' => 61179,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1986,
            'endLine' => 1986,
            'startColumn' => 70,
            'endColumn' => 88,
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
 * Set the sequence that will be used to generate UUIDs.
 *
 * @param  \\Ramsey\\Uuid\\UuidInterface[]  $sequence
 * @param  (callable(): \\Ramsey\\Uuid\\UuidInterface)|null  $whenMissing
 * @return void
 */',
        'startLine' => 1986,
        'endLine' => 2011,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'freezeUuids' => 
      array (
        'name' => 'freezeUuids',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 2019,
                'endLine' => 2019,
                'startTokenPos' => 9215,
                'startFilePos' => 62016,
                'endTokenPos' => 9215,
                'endFilePos' => 62019,
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
                      'name' => 'Closure',
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
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 2019,
            'endLine' => 2019,
            'startColumn' => 40,
            'endColumn' => 64,
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
 * Always return the same UUID when generating new UUIDs.
 *
 * @param  (\\Closure(\\Ramsey\\Uuid\\UuidInterface): mixed)|null  $callback
 * @return \\Ramsey\\Uuid\\UuidInterface
 */',
        'startLine' => 2019,
        'endLine' => 2034,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'createUuidsNormally' => 
      array (
        'name' => 'createUuidsNormally',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Indicate that UUIDs should be created normally and not using a custom factory.
 *
 * @return void
 */',
        'startLine' => 2041,
        'endLine' => 2044,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'ulid' => 
      array (
        'name' => 'ulid',
        'parameters' => 
        array (
          'time' => 
          array (
            'name' => 'time',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 2052,
                'endLine' => 2052,
                'startTokenPos' => 9331,
                'startFilePos' => 62718,
                'endTokenPos' => 9331,
                'endFilePos' => 62721,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 2052,
            'endLine' => 2052,
            'startColumn' => 33,
            'endColumn' => 44,
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
 * Generate a ULID.
 *
 * @param  \\DateTimeInterface|null  $time
 * @return \\Symfony\\Component\\Uid\\Ulid
 */',
        'startLine' => 2052,
        'endLine' => 2063,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'createUlidsNormally' => 
      array (
        'name' => 'createUlidsNormally',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Indicate that ULIDs should be created normally and not using a custom factory.
 *
 * @return void
 */',
        'startLine' => 2070,
        'endLine' => 2073,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'createUlidsUsing' => 
      array (
        'name' => 'createUlidsUsing',
        'parameters' => 
        array (
          'factory' => 
          array (
            'name' => 'factory',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 2081,
                'endLine' => 2081,
                'startTokenPos' => 9440,
                'startFilePos' => 63430,
                'endTokenPos' => 9440,
                'endFilePos' => 63433,
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
                      'name' => 'callable',
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
            'startLine' => 2081,
            'endLine' => 2081,
            'startColumn' => 45,
            'endColumn' => 69,
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
 * Set the callable that will be used to generate ULIDs.
 *
 * @param  (callable(): \\Symfony\\Component\\Uid\\Ulid)|null  $factory
 * @return void
 */',
        'startLine' => 2081,
        'endLine' => 2084,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'createUlidsUsingSequence' => 
      array (
        'name' => 'createUlidsUsingSequence',
        'parameters' => 
        array (
          'sequence' => 
          array (
            'name' => 'sequence',
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
            'startLine' => 2093,
            'endLine' => 2093,
            'startColumn' => 53,
            'endColumn' => 67,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'whenMissing' => 
          array (
            'name' => 'whenMissing',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 2093,
                'endLine' => 2093,
                'startTokenPos' => 9475,
                'startFilePos' => 63810,
                'endTokenPos' => 9475,
                'endFilePos' => 63813,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 2093,
            'endLine' => 2093,
            'startColumn' => 70,
            'endColumn' => 88,
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
 * Set the sequence that will be used to generate ULIDs.
 *
 * @param  \\Symfony\\Component\\Uid\\Ulid[]  $sequence
 * @param  (callable(): \\Symfony\\Component\\Uid\\Ulid)|null  $whenMissing
 * @return void
 */',
        'startLine' => 2093,
        'endLine' => 2118,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'freezeUlids' => 
      array (
        'name' => 'freezeUlids',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 2126,
                'endLine' => 2126,
                'startTokenPos' => 9634,
                'startFilePos' => 64605,
                'endTokenPos' => 9634,
                'endFilePos' => 64608,
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
                      'name' => 'Closure',
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
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 2126,
            'endLine' => 2126,
            'startColumn' => 40,
            'endColumn' => 64,
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
 * Always return the same ULID when generating new ULIDs.
 *
 * @param  (Closure(Ulid): mixed)|null  $callback
 * @return Ulid
 */',
        'startLine' => 2126,
        'endLine' => 2141,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'flushCache' => 
      array (
        'name' => 'flushCache',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Remove all strings from the casing caches.
 *
 * @return void
 */',
        'startLine' => 2148,
        'endLine' => 2153,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
        'aliasName' => NULL,
      ),
      'resetFactoryState' => 
      array (
        'name' => 'resetFactoryState',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Return all factory functions to their default state.
 *
 * @return void
 */',
        'startLine' => 2160,
        'endLine' => 2165,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Support',
        'declaringClassName' => 'Illuminate\\Support\\Str',
        'implementingClassName' => 'Illuminate\\Support\\Str',
        'currentClassName' => 'Illuminate\\Support\\Str',
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