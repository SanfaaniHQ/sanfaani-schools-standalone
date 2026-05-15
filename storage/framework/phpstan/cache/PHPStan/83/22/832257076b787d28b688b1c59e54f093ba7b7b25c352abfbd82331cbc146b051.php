<?php declare(strict_types = 1);

// osfsl-C:/laragon/www/sanfaani-schools/vendor/composer/../laravel/framework/src/Illuminate/Mail/Mailable.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Illuminate\Mail\Mailable
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-4bf85bf286a24d9dedfe589c82aec490b4e77f0e21e098dfccebdbc11c3acf54-8.3.30-6.70.0.0',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Illuminate\\Mail\\Mailable',
        'filename' => 'C:/laragon/www/sanfaani-schools/vendor/composer/../laravel/framework/src/Illuminate/Mail/Mailable.php',
      ),
    ),
    'namespace' => 'Illuminate\\Mail',
    'name' => 'Illuminate\\Mail\\Mailable',
    'shortName' => 'Mailable',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 34,
    'endLine' => 1943,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
      0 => 'Illuminate\\Contracts\\Mail\\Mailable',
      1 => 'Illuminate\\Contracts\\Support\\Renderable',
    ),
    'traitClassNames' => 
    array (
      0 => 'Illuminate\\Support\\Traits\\Conditionable',
      1 => 'Illuminate\\Support\\Traits\\ForwardsCalls',
      2 => 'Illuminate\\Support\\Traits\\Localizable',
      3 => 'Illuminate\\Support\\Traits\\ReadsClassAttributes',
      4 => 'Illuminate\\Support\\Traits\\Tappable',
      5 => 'Illuminate\\Support\\Traits\\Macroable',
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
      'locale' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'locale',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The locale of the message.
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 45,
        'endLine' => 45,
        'startColumn' => 5,
        'endColumn' => 19,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'from' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'from',
        'modifiers' => 1,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 52,
            'endLine' => 52,
            'startTokenPos' => 229,
            'startFilePos' => 1640,
            'endTokenPos' => 230,
            'endFilePos' => 1641,
          ),
        ),
        'docComment' => '/**
 * The person the message is from.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 52,
        'endLine' => 52,
        'startColumn' => 5,
        'endColumn' => 22,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'to' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'to',
        'modifiers' => 1,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 59,
            'endLine' => 59,
            'startTokenPos' => 241,
            'startFilePos' => 1746,
            'endTokenPos' => 242,
            'endFilePos' => 1747,
          ),
        ),
        'docComment' => '/**
 * The "to" recipients of the message.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 59,
        'endLine' => 59,
        'startColumn' => 5,
        'endColumn' => 20,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'cc' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'cc',
        'modifiers' => 1,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 66,
            'endLine' => 66,
            'startTokenPos' => 253,
            'startFilePos' => 1852,
            'endTokenPos' => 254,
            'endFilePos' => 1853,
          ),
        ),
        'docComment' => '/**
 * The "cc" recipients of the message.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 66,
        'endLine' => 66,
        'startColumn' => 5,
        'endColumn' => 20,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'bcc' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'bcc',
        'modifiers' => 1,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 73,
            'endLine' => 73,
            'startTokenPos' => 265,
            'startFilePos' => 1960,
            'endTokenPos' => 266,
            'endFilePos' => 1961,
          ),
        ),
        'docComment' => '/**
 * The "bcc" recipients of the message.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 73,
        'endLine' => 73,
        'startColumn' => 5,
        'endColumn' => 21,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'replyTo' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'replyTo',
        'modifiers' => 1,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 80,
            'endLine' => 80,
            'startTokenPos' => 277,
            'startFilePos' => 2077,
            'endTokenPos' => 278,
            'endFilePos' => 2078,
          ),
        ),
        'docComment' => '/**
 * The "reply to" recipients of the message.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 80,
        'endLine' => 80,
        'startColumn' => 5,
        'endColumn' => 25,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'subject' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'subject',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The subject of the message.
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 87,
        'endLine' => 87,
        'startColumn' => 5,
        'endColumn' => 20,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'markdown' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'markdown',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The Markdown template for the message (if applicable).
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 94,
        'endLine' => 94,
        'startColumn' => 5,
        'endColumn' => 21,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'html' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'html',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The HTML to use for the message.
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 101,
        'endLine' => 101,
        'startColumn' => 5,
        'endColumn' => 20,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'view' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'view',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The view to use for the message.
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 108,
        'endLine' => 108,
        'startColumn' => 5,
        'endColumn' => 17,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'textView' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'textView',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The plain text view to use for the message.
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 115,
        'endLine' => 115,
        'startColumn' => 5,
        'endColumn' => 21,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'viewData' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'viewData',
        'modifiers' => 1,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 122,
            'endLine' => 122,
            'startTokenPos' => 324,
            'startFilePos' => 2731,
            'endTokenPos' => 325,
            'endFilePos' => 2732,
          ),
        ),
        'docComment' => '/**
 * The view data for the message.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 122,
        'endLine' => 122,
        'startColumn' => 5,
        'endColumn' => 26,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'attachments' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'attachments',
        'modifiers' => 1,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 129,
            'endLine' => 129,
            'startTokenPos' => 336,
            'startFilePos' => 2843,
            'endTokenPos' => 337,
            'endFilePos' => 2844,
          ),
        ),
        'docComment' => '/**
 * The attachments for the message.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 129,
        'endLine' => 129,
        'startColumn' => 5,
        'endColumn' => 29,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'rawAttachments' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'rawAttachments',
        'modifiers' => 1,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 136,
            'endLine' => 136,
            'startTokenPos' => 348,
            'startFilePos' => 2962,
            'endTokenPos' => 349,
            'endFilePos' => 2963,
          ),
        ),
        'docComment' => '/**
 * The raw attachments for the message.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 136,
        'endLine' => 136,
        'startColumn' => 5,
        'endColumn' => 32,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'diskAttachments' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'diskAttachments',
        'modifiers' => 1,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 143,
            'endLine' => 143,
            'startTokenPos' => 360,
            'startFilePos' => 3082,
            'endTokenPos' => 361,
            'endFilePos' => 3083,
          ),
        ),
        'docComment' => '/**
 * The attachments from a storage disk.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 143,
        'endLine' => 143,
        'startColumn' => 5,
        'endColumn' => 33,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'tags' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'tags',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 150,
            'endLine' => 150,
            'startTokenPos' => 372,
            'startFilePos' => 3183,
            'endTokenPos' => 373,
            'endFilePos' => 3184,
          ),
        ),
        'docComment' => '/**
 * The tags for the message.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 150,
        'endLine' => 150,
        'startColumn' => 5,
        'endColumn' => 25,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'metadata' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'metadata',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 157,
            'endLine' => 157,
            'startTokenPos' => 384,
            'startFilePos' => 3292,
            'endTokenPos' => 385,
            'endFilePos' => 3293,
          ),
        ),
        'docComment' => '/**
 * The metadata for the message.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 157,
        'endLine' => 157,
        'startColumn' => 5,
        'endColumn' => 29,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'callbacks' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'callbacks',
        'modifiers' => 1,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 164,
            'endLine' => 164,
            'startTokenPos' => 396,
            'startFilePos' => 3400,
            'endTokenPos' => 397,
            'endFilePos' => 3401,
          ),
        ),
        'docComment' => '/**
 * The callbacks for the message.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 164,
        'endLine' => 164,
        'startColumn' => 5,
        'endColumn' => 27,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'theme' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'theme',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The name of the theme that should be used when formatting the message.
 *
 * @var string|null
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 171,
        'endLine' => 171,
        'startColumn' => 5,
        'endColumn' => 18,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'mailer' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'mailer',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The name of the mailer that should send the message.
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 178,
        'endLine' => 178,
        'startColumn' => 5,
        'endColumn' => 19,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'assertionableRenderStrings' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'assertionableRenderStrings',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The rendered mailable views for testing / assertions.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 185,
        'endLine' => 185,
        'startColumn' => 5,
        'endColumn' => 42,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'viewDataCallback' => 
      array (
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'name' => 'viewDataCallback',
        'modifiers' => 17,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The callback that should be invoked while building the view data.
 *
 * @var callable
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 192,
        'endLine' => 192,
        'startColumn' => 5,
        'endColumn' => 36,
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
      'send' => 
      array (
        'name' => 'send',
        'parameters' => 
        array (
          'mailer' => 
          array (
            'name' => 'mailer',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 200,
            'endLine' => 200,
            'startColumn' => 26,
            'endColumn' => 32,
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
 * Send the message using the given mailer.
 *
 * @param  \\Illuminate\\Contracts\\Mail\\Factory|\\Illuminate\\Contracts\\Mail\\Mailer  $mailer
 * @return \\Illuminate\\Mail\\SentMessage|null
 */',
        'startLine' => 200,
        'endLine' => 219,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'queue' => 
      array (
        'name' => 'queue',
        'parameters' => 
        array (
          'queue' => 
          array (
            'name' => 'queue',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Contracts\\Queue\\Factory',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 227,
            'endLine' => 227,
            'startColumn' => 27,
            'endColumn' => 38,
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
 * Queue the message for sending.
 *
 * @param  \\Illuminate\\Contracts\\Queue\\Factory  $queue
 * @return mixed
 */',
        'startLine' => 227,
        'endLine' => 252,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'later' => 
      array (
        'name' => 'later',
        'parameters' => 
        array (
          'delay' => 
          array (
            'name' => 'delay',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 261,
            'endLine' => 261,
            'startColumn' => 27,
            'endColumn' => 32,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'queue' => 
          array (
            'name' => 'queue',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Contracts\\Queue\\Factory',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 261,
            'endLine' => 261,
            'startColumn' => 35,
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
 * Deliver the queued message after (n) seconds.
 *
 * @param  \\DateTimeInterface|\\DateInterval|int  $delay
 * @param  \\Illuminate\\Contracts\\Queue\\Factory  $queue
 * @return mixed
 */',
        'startLine' => 261,
        'endLine' => 284,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'newQueuedJob' => 
      array (
        'name' => 'newQueuedJob',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Make the queued mailable job instance.
 *
 * @return mixed
 */',
        'startLine' => 291,
        'endLine' => 304,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'render' => 
      array (
        'name' => 'render',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Render the mailable into a view.
 *
 * @return string
 *
 * @throws \\ReflectionException
 */',
        'startLine' => 313,
        'endLine' => 322,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'buildView' => 
      array (
        'name' => 'buildView',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Build the view for the message.
 *
 * @return array|string
 *
 * @throws \\ReflectionException
 */',
        'startLine' => 331,
        'endLine' => 351,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'buildMarkdownView' => 
      array (
        'name' => 'buildMarkdownView',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Build the Markdown view for the message.
 *
 * @return array
 *
 * @throws \\ReflectionException
 */',
        'startLine' => 360,
        'endLine' => 368,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'buildViewData' => 
      array (
        'name' => 'buildViewData',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Build the view data for the message.
 *
 * @return array
 *
 * @throws \\ReflectionException
 */',
        'startLine' => 377,
        'endLine' => 392,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'additionalMessageData' => 
      array (
        'name' => 'additionalMessageData',
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
        'docComment' => '/**
 * Get additional meta-data to pass along with the view data.
 *
 * @return array<string, mixed>
 */',
        'startLine' => 399,
        'endLine' => 404,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'buildMarkdownHtml' => 
      array (
        'name' => 'buildMarkdownHtml',
        'parameters' => 
        array (
          'viewData' => 
          array (
            'name' => 'viewData',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 412,
            'endLine' => 412,
            'startColumn' => 42,
            'endColumn' => 50,
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
 * Build the HTML view for a Markdown message.
 *
 * @param  array  $viewData
 * @return \\Closure
 */',
        'startLine' => 412,
        'endLine' => 418,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'buildMarkdownText' => 
      array (
        'name' => 'buildMarkdownText',
        'parameters' => 
        array (
          'viewData' => 
          array (
            'name' => 'viewData',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 426,
            'endLine' => 426,
            'startColumn' => 42,
            'endColumn' => 50,
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
 * Build the text view for a Markdown message.
 *
 * @param  array  $viewData
 * @return \\Closure
 */',
        'startLine' => 426,
        'endLine' => 440,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'markdownRenderer' => 
      array (
        'name' => 'markdownRenderer',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Resolves a Markdown instance with the mail\'s theme.
 *
 * @return \\Illuminate\\Mail\\Markdown
 */',
        'startLine' => 447,
        'endLine' => 454,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'buildFrom' => 
      array (
        'name' => 'buildFrom',
        'parameters' => 
        array (
          'message' => 
          array (
            'name' => 'message',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 462,
            'endLine' => 462,
            'startColumn' => 34,
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
 * Add the sender to the message.
 *
 * @param  \\Illuminate\\Mail\\Message  $message
 * @return $this
 */',
        'startLine' => 462,
        'endLine' => 469,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'buildRecipients' => 
      array (
        'name' => 'buildRecipients',
        'parameters' => 
        array (
          'message' => 
          array (
            'name' => 'message',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 477,
            'endLine' => 477,
            'startColumn' => 40,
            'endColumn' => 47,
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
 * Add all of the recipients to the message.
 *
 * @param  \\Illuminate\\Mail\\Message  $message
 * @return $this
 */',
        'startLine' => 477,
        'endLine' => 486,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'buildSubject' => 
      array (
        'name' => 'buildSubject',
        'parameters' => 
        array (
          'message' => 
          array (
            'name' => 'message',
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
            'startColumn' => 37,
            'endColumn' => 44,
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
 * Set the subject for the message.
 *
 * @param  \\Illuminate\\Mail\\Message  $message
 * @return $this
 */',
        'startLine' => 494,
        'endLine' => 503,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'buildAttachments' => 
      array (
        'name' => 'buildAttachments',
        'parameters' => 
        array (
          'message' => 
          array (
            'name' => 'message',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 511,
            'endLine' => 511,
            'startColumn' => 41,
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
 * Add all of the attachments to the message.
 *
 * @param  \\Illuminate\\Mail\\Message  $message
 * @return $this
 */',
        'startLine' => 511,
        'endLine' => 526,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'buildDiskAttachments' => 
      array (
        'name' => 'buildDiskAttachments',
        'parameters' => 
        array (
          'message' => 
          array (
            'name' => 'message',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 534,
            'endLine' => 534,
            'startColumn' => 45,
            'endColumn' => 52,
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
 * Add all of the disk attachments to the message.
 *
 * @param  \\Illuminate\\Mail\\Message  $message
 * @return void
 */',
        'startLine' => 534,
        'endLine' => 547,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'buildTags' => 
      array (
        'name' => 'buildTags',
        'parameters' => 
        array (
          'message' => 
          array (
            'name' => 'message',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 555,
            'endLine' => 555,
            'startColumn' => 34,
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
 * Add all defined tags to the message.
 *
 * @param  \\Illuminate\\Mail\\Message  $message
 * @return $this
 */',
        'startLine' => 555,
        'endLine' => 564,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'buildMetadata' => 
      array (
        'name' => 'buildMetadata',
        'parameters' => 
        array (
          'message' => 
          array (
            'name' => 'message',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 572,
            'endLine' => 572,
            'startColumn' => 38,
            'endColumn' => 45,
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
 * Add all defined metadata to the message.
 *
 * @param  \\Illuminate\\Mail\\Message  $message
 * @return $this
 */',
        'startLine' => 572,
        'endLine' => 581,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'runCallbacks' => 
      array (
        'name' => 'runCallbacks',
        'parameters' => 
        array (
          'message' => 
          array (
            'name' => 'message',
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
            'startColumn' => 37,
            'endColumn' => 44,
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
 * Run the callbacks for the message.
 *
 * @param  \\Illuminate\\Mail\\Message  $message
 * @return $this
 */',
        'startLine' => 589,
        'endLine' => 596,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'locale' => 
      array (
        'name' => 'locale',
        'parameters' => 
        array (
          'locale' => 
          array (
            'name' => 'locale',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 604,
            'endLine' => 604,
            'startColumn' => 28,
            'endColumn' => 34,
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
 * Set the locale of the message.
 *
 * @param  string  $locale
 * @return $this
 */',
        'startLine' => 604,
        'endLine' => 609,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'priority' => 
      array (
        'name' => 'priority',
        'parameters' => 
        array (
          'level' => 
          array (
            'name' => 'level',
            'default' => 
            array (
              'code' => '3',
              'attributes' => 
              array (
                'startLine' => 619,
                'endLine' => 619,
                'startTokenPos' => 2513,
                'startFilePos' => 15715,
                'endTokenPos' => 2513,
                'endFilePos' => 15715,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 619,
            'endLine' => 619,
            'startColumn' => 30,
            'endColumn' => 39,
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
 * Set the priority of this message.
 *
 * The value is an integer where 1 is the highest priority and 5 is the lowest.
 *
 * @param  int  $level
 * @return $this
 */',
        'startLine' => 619,
        'endLine' => 626,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'from' => 
      array (
        'name' => 'from',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 635,
            'endLine' => 635,
            'startColumn' => 26,
            'endColumn' => 33,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 635,
                'endLine' => 635,
                'startTokenPos' => 2573,
                'startFilePos' => 16073,
                'endTokenPos' => 2573,
                'endFilePos' => 16076,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 635,
            'endLine' => 635,
            'startColumn' => 36,
            'endColumn' => 47,
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
 * Set the sender of the message.
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @return $this
 */',
        'startLine' => 635,
        'endLine' => 638,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'hasFrom' => 
      array (
        'name' => 'hasFrom',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 647,
            'endLine' => 647,
            'startColumn' => 29,
            'endColumn' => 36,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 647,
                'endLine' => 647,
                'startTokenPos' => 2611,
                'startFilePos' => 16383,
                'endTokenPos' => 2611,
                'endFilePos' => 16386,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 647,
            'endLine' => 647,
            'startColumn' => 39,
            'endColumn' => 50,
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
 * Determine if the given recipient is set on the mailable.
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @return bool
 */',
        'startLine' => 647,
        'endLine' => 650,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'to' => 
      array (
        'name' => 'to',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 659,
            'endLine' => 659,
            'startColumn' => 24,
            'endColumn' => 31,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 659,
                'endLine' => 659,
                'startTokenPos' => 2649,
                'startFilePos' => 16669,
                'endTokenPos' => 2649,
                'endFilePos' => 16672,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 659,
            'endLine' => 659,
            'startColumn' => 34,
            'endColumn' => 45,
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
 * Set the recipients of the message.
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @return $this
 */',
        'startLine' => 659,
        'endLine' => 666,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'hasTo' => 
      array (
        'name' => 'hasTo',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 675,
            'endLine' => 675,
            'startColumn' => 27,
            'endColumn' => 34,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 675,
                'endLine' => 675,
                'startTokenPos' => 2721,
                'startFilePos' => 17116,
                'endTokenPos' => 2721,
                'endFilePos' => 17119,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 675,
            'endLine' => 675,
            'startColumn' => 37,
            'endColumn' => 48,
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
 * Determine if the given recipient is set on the mailable.
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @return bool
 */',
        'startLine' => 675,
        'endLine' => 678,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'cc' => 
      array (
        'name' => 'cc',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 687,
            'endLine' => 687,
            'startColumn' => 24,
            'endColumn' => 31,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 687,
                'endLine' => 687,
                'startTokenPos' => 2759,
                'startFilePos' => 17400,
                'endTokenPos' => 2759,
                'endFilePos' => 17403,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 687,
            'endLine' => 687,
            'startColumn' => 34,
            'endColumn' => 45,
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
 * Set the recipients of the message.
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @return $this
 */',
        'startLine' => 687,
        'endLine' => 690,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'hasCc' => 
      array (
        'name' => 'hasCc',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 699,
            'endLine' => 699,
            'startColumn' => 27,
            'endColumn' => 34,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 699,
                'endLine' => 699,
                'startTokenPos' => 2797,
                'startFilePos' => 17706,
                'endTokenPos' => 2797,
                'endFilePos' => 17709,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 699,
            'endLine' => 699,
            'startColumn' => 37,
            'endColumn' => 48,
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
 * Determine if the given recipient is set on the mailable.
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @return bool
 */',
        'startLine' => 699,
        'endLine' => 702,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'bcc' => 
      array (
        'name' => 'bcc',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 711,
            'endLine' => 711,
            'startColumn' => 25,
            'endColumn' => 32,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 711,
                'endLine' => 711,
                'startTokenPos' => 2835,
                'startFilePos' => 17991,
                'endTokenPos' => 2835,
                'endFilePos' => 17994,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 711,
            'endLine' => 711,
            'startColumn' => 35,
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
 * Set the recipients of the message.
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @return $this
 */',
        'startLine' => 711,
        'endLine' => 714,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'hasBcc' => 
      array (
        'name' => 'hasBcc',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 723,
            'endLine' => 723,
            'startColumn' => 28,
            'endColumn' => 35,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 723,
                'endLine' => 723,
                'startTokenPos' => 2873,
                'startFilePos' => 18299,
                'endTokenPos' => 2873,
                'endFilePos' => 18302,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 723,
            'endLine' => 723,
            'startColumn' => 38,
            'endColumn' => 49,
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
 * Determine if the given recipient is set on the mailable.
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @return bool
 */',
        'startLine' => 723,
        'endLine' => 726,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'replyTo' => 
      array (
        'name' => 'replyTo',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 735,
            'endLine' => 735,
            'startColumn' => 29,
            'endColumn' => 36,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 735,
                'endLine' => 735,
                'startTokenPos' => 2911,
                'startFilePos' => 18597,
                'endTokenPos' => 2911,
                'endFilePos' => 18600,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 735,
            'endLine' => 735,
            'startColumn' => 39,
            'endColumn' => 50,
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
 * Set the "reply to" address of the message.
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @return $this
 */',
        'startLine' => 735,
        'endLine' => 738,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'hasReplyTo' => 
      array (
        'name' => 'hasReplyTo',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 747,
            'endLine' => 747,
            'startColumn' => 32,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 747,
                'endLine' => 747,
                'startTokenPos' => 2949,
                'startFilePos' => 18911,
                'endTokenPos' => 2949,
                'endFilePos' => 18914,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 747,
            'endLine' => 747,
            'startColumn' => 42,
            'endColumn' => 53,
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
 * Determine if the given replyTo is set on the mailable.
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @return bool
 */',
        'startLine' => 747,
        'endLine' => 750,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'setAddress' => 
      array (
        'name' => 'setAddress',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 762,
            'endLine' => 762,
            'startColumn' => 35,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 762,
                'endLine' => 762,
                'startTokenPos' => 2987,
                'startFilePos' => 19330,
                'endTokenPos' => 2987,
                'endFilePos' => 19333,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 762,
            'endLine' => 762,
            'startColumn' => 45,
            'endColumn' => 56,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'property' => 
          array (
            'name' => 'property',
            'default' => 
            array (
              'code' => '\'to\'',
              'attributes' => 
              array (
                'startLine' => 762,
                'endLine' => 762,
                'startTokenPos' => 2994,
                'startFilePos' => 19348,
                'endTokenPos' => 2994,
                'endFilePos' => 19351,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 762,
            'endLine' => 762,
            'startColumn' => 59,
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
 * Set the recipients of the message.
 *
 * All recipients are stored internally as [[\'name\' => ?, \'address\' => ?]]
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @param  string  $property
 * @return $this
 */',
        'startLine' => 762,
        'endLine' => 785,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'addressesToArray' => 
      array (
        'name' => 'addressesToArray',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 794,
            'endLine' => 794,
            'startColumn' => 41,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 794,
            'endLine' => 794,
            'startColumn' => 51,
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
 * Convert the given recipient arguments to an array.
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @return array
 */',
        'startLine' => 794,
        'endLine' => 801,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'normalizeRecipient' => 
      array (
        'name' => 'normalizeRecipient',
        'parameters' => 
        array (
          'recipient' => 
          array (
            'name' => 'recipient',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 809,
            'endLine' => 809,
            'startColumn' => 43,
            'endColumn' => 52,
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
 * Convert the given recipient into an object.
 *
 * @param  mixed  $recipient
 * @return object
 */',
        'startLine' => 809,
        'endLine' => 828,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'hasRecipient' => 
      array (
        'name' => 'hasRecipient',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 838,
            'endLine' => 838,
            'startColumn' => 37,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 838,
                'endLine' => 838,
                'startTokenPos' => 3433,
                'startFilePos' => 21601,
                'endTokenPos' => 3433,
                'endFilePos' => 21604,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 838,
            'endLine' => 838,
            'startColumn' => 47,
            'endColumn' => 58,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'property' => 
          array (
            'name' => 'property',
            'default' => 
            array (
              'code' => '\'to\'',
              'attributes' => 
              array (
                'startLine' => 838,
                'endLine' => 838,
                'startTokenPos' => 3440,
                'startFilePos' => 21619,
                'endTokenPos' => 3440,
                'endFilePos' => 21622,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 838,
            'endLine' => 838,
            'startColumn' => 61,
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
 * Determine if the given recipient is set on the mailable.
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @param  string  $property
 * @return bool
 */',
        'startLine' => 838,
        'endLine' => 864,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'hasEnvelopeRecipient' => 
      array (
        'name' => 'hasEnvelopeRecipient',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 874,
            'endLine' => 874,
            'startColumn' => 43,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 874,
            'endLine' => 874,
            'startColumn' => 53,
            'endColumn' => 57,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'property' => 
          array (
            'name' => 'property',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 874,
            'endLine' => 874,
            'startColumn' => 60,
            'endColumn' => 68,
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
 * Determine if the mailable "envelope" method defines a recipient.
 *
 * @param  string  $address
 * @param  string|null  $name
 * @param  string  $property
 * @return bool
 */',
        'startLine' => 874,
        'endLine' => 883,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'subject' => 
      array (
        'name' => 'subject',
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
            'startLine' => 891,
            'endLine' => 891,
            'startColumn' => 29,
            'endColumn' => 36,
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
 * Set the subject of the message.
 *
 * @param  string  $subject
 * @return $this
 */',
        'startLine' => 891,
        'endLine' => 896,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'hasSubject' => 
      array (
        'name' => 'hasSubject',
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
            'startLine' => 904,
            'endLine' => 904,
            'startColumn' => 32,
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
 * Determine if the mailable has the given subject.
 *
 * @param  string  $subject
 * @return bool
 */',
        'startLine' => 904,
        'endLine' => 908,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'markdown' => 
      array (
        'name' => 'markdown',
        'parameters' => 
        array (
          'view' => 
          array (
            'name' => 'view',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 917,
            'endLine' => 917,
            'startColumn' => 30,
            'endColumn' => 34,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'data' => 
          array (
            'name' => 'data',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 917,
                'endLine' => 917,
                'startTokenPos' => 3866,
                'startFilePos' => 23815,
                'endTokenPos' => 3867,
                'endFilePos' => 23816,
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
            'startLine' => 917,
            'endLine' => 917,
            'startColumn' => 37,
            'endColumn' => 52,
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
 * Set the Markdown template for the message.
 *
 * @param  string  $view
 * @param  array  $data
 * @return $this
 */',
        'startLine' => 917,
        'endLine' => 923,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'view' => 
      array (
        'name' => 'view',
        'parameters' => 
        array (
          'view' => 
          array (
            'name' => 'view',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 932,
            'endLine' => 932,
            'startColumn' => 26,
            'endColumn' => 30,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'data' => 
          array (
            'name' => 'data',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 932,
                'endLine' => 932,
                'startTokenPos' => 3922,
                'startFilePos' => 24149,
                'endTokenPos' => 3923,
                'endFilePos' => 24150,
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
            'startLine' => 932,
            'endLine' => 932,
            'startColumn' => 33,
            'endColumn' => 48,
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
 * Set the view and view data for the message.
 *
 * @param  string  $view
 * @param  array  $data
 * @return $this
 */',
        'startLine' => 932,
        'endLine' => 938,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'html' => 
      array (
        'name' => 'html',
        'parameters' => 
        array (
          'html' => 
          array (
            'name' => 'html',
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
            'startColumn' => 26,
            'endColumn' => 30,
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
 * Set the rendered HTML content for the message.
 *
 * @param  string  $html
 * @return $this
 */',
        'startLine' => 946,
        'endLine' => 951,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'text' => 
      array (
        'name' => 'text',
        'parameters' => 
        array (
          'textView' => 
          array (
            'name' => 'textView',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 960,
            'endLine' => 960,
            'startColumn' => 26,
            'endColumn' => 34,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'data' => 
          array (
            'name' => 'data',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 960,
                'endLine' => 960,
                'startTokenPos' => 4007,
                'startFilePos' => 24708,
                'endTokenPos' => 4008,
                'endFilePos' => 24709,
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
            'startLine' => 960,
            'endLine' => 960,
            'startColumn' => 37,
            'endColumn' => 52,
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
 * Set the plain text view for the message.
 *
 * @param  string  $textView
 * @param  array  $data
 * @return $this
 */',
        'startLine' => 960,
        'endLine' => 966,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'with' => 
      array (
        'name' => 'with',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 975,
            'endLine' => 975,
            'startColumn' => 26,
            'endColumn' => 29,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'value' => 
          array (
            'name' => 'value',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 975,
                'endLine' => 975,
                'startTokenPos' => 4061,
                'startFilePos' => 25037,
                'endTokenPos' => 4061,
                'endFilePos' => 25040,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 975,
            'endLine' => 975,
            'startColumn' => 32,
            'endColumn' => 44,
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
 * Set the view data for the message.
 *
 * @param  string|array  $key
 * @param  mixed  $value
 * @return $this
 */',
        'startLine' => 975,
        'endLine' => 984,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'attach' => 
      array (
        'name' => 'attach',
        'parameters' => 
        array (
          'file' => 
          array (
            'name' => 'file',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 993,
            'endLine' => 993,
            'startColumn' => 28,
            'endColumn' => 32,
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
                'startLine' => 993,
                'endLine' => 993,
                'startTokenPos' => 4138,
                'startFilePos' => 25504,
                'endTokenPos' => 4139,
                'endFilePos' => 25505,
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
            'startLine' => 993,
            'endLine' => 993,
            'startColumn' => 35,
            'endColumn' => 53,
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
 * Attach a file to the message.
 *
 * @param  string|\\Illuminate\\Contracts\\Mail\\Attachable|\\Illuminate\\Mail\\Attachment  $file
 * @param  array  $options
 * @return $this
 */',
        'startLine' => 993,
        'endLine' => 1009,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'attachMany' => 
      array (
        'name' => 'attachMany',
        'parameters' => 
        array (
          'files' => 
          array (
            'name' => 'files',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1017,
            'endLine' => 1017,
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
 * Attach multiple files to the message.
 *
 * @param  array  $files
 * @return $this
 */',
        'startLine' => 1017,
        'endLine' => 1028,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'hasAttachment' => 
      array (
        'name' => 'hasAttachment',
        'parameters' => 
        array (
          'file' => 
          array (
            'name' => 'file',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1037,
            'endLine' => 1037,
            'startColumn' => 35,
            'endColumn' => 39,
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
                'startLine' => 1037,
                'endLine' => 1037,
                'startTokenPos' => 4337,
                'startFilePos' => 26609,
                'endTokenPos' => 4338,
                'endFilePos' => 26610,
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
            'startLine' => 1037,
            'endLine' => 1037,
            'startColumn' => 42,
            'endColumn' => 60,
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
 * Determine if the mailable has the given attachment.
 *
 * @param  string|\\Illuminate\\Contracts\\Mail\\Attachable|\\Illuminate\\Mail\\Attachment  $file
 * @param  array  $options
 * @return bool
 */',
        'startLine' => 1037,
        'endLine' => 1068,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'hasEnvelopeAttachment' => 
      array (
        'name' => 'hasEnvelopeAttachment',
        'parameters' => 
        array (
          'attachment' => 
          array (
            'name' => 'attachment',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1077,
            'endLine' => 1077,
            'startColumn' => 44,
            'endColumn' => 54,
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
                'startLine' => 1077,
                'endLine' => 1077,
                'startTokenPos' => 4641,
                'startFilePos' => 27932,
                'endTokenPos' => 4642,
                'endFilePos' => 27933,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1077,
            'endLine' => 1077,
            'startColumn' => 57,
            'endColumn' => 69,
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
 * Determine if the mailable has the given envelope attachment.
 *
 * @param  \\Illuminate\\Mail\\Attachment  $attachment
 * @param  array  $options
 * @return bool
 */',
        'startLine' => 1077,
        'endLine' => 1088,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'attachFromStorage' => 
      array (
        'name' => 'attachFromStorage',
        'parameters' => 
        array (
          'path' => 
          array (
            'name' => 'path',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1098,
            'endLine' => 1098,
            'startColumn' => 39,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1098,
                'endLine' => 1098,
                'startTokenPos' => 4775,
                'startFilePos' => 28615,
                'endTokenPos' => 4775,
                'endFilePos' => 28618,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1098,
            'endLine' => 1098,
            'startColumn' => 46,
            'endColumn' => 57,
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
                'startLine' => 1098,
                'endLine' => 1098,
                'startTokenPos' => 4784,
                'startFilePos' => 28638,
                'endTokenPos' => 4785,
                'endFilePos' => 28639,
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
            'startLine' => 1098,
            'endLine' => 1098,
            'startColumn' => 60,
            'endColumn' => 78,
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
 * Attach a file to the message from storage.
 *
 * @param  string  $path
 * @param  string|null  $name
 * @param  array  $options
 * @return $this
 */',
        'startLine' => 1098,
        'endLine' => 1101,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'attachFromStorageDisk' => 
      array (
        'name' => 'attachFromStorageDisk',
        'parameters' => 
        array (
          'disk' => 
          array (
            'name' => 'disk',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1112,
            'endLine' => 1112,
            'startColumn' => 43,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'path' => 
          array (
            'name' => 'path',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1112,
            'endLine' => 1112,
            'startColumn' => 50,
            'endColumn' => 54,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1112,
                'endLine' => 1112,
                'startTokenPos' => 4829,
                'startFilePos' => 29011,
                'endTokenPos' => 4829,
                'endFilePos' => 29014,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1112,
            'endLine' => 1112,
            'startColumn' => 57,
            'endColumn' => 68,
            'parameterIndex' => 2,
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
                'startLine' => 1112,
                'endLine' => 1112,
                'startTokenPos' => 4838,
                'startFilePos' => 29034,
                'endTokenPos' => 4839,
                'endFilePos' => 29035,
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
            'startLine' => 1112,
            'endLine' => 1112,
            'startColumn' => 71,
            'endColumn' => 89,
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
 * Attach a file to the message from storage.
 *
 * @param  string  $disk
 * @param  string  $path
 * @param  string|null  $name
 * @param  array  $options
 * @return $this
 */',
        'startLine' => 1112,
        'endLine' => 1124,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'hasAttachmentFromStorage' => 
      array (
        'name' => 'hasAttachmentFromStorage',
        'parameters' => 
        array (
          'path' => 
          array (
            'name' => 'path',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1134,
            'endLine' => 1134,
            'startColumn' => 46,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1134,
                'endLine' => 1134,
                'startTokenPos' => 4958,
                'startFilePos' => 29676,
                'endTokenPos' => 4958,
                'endFilePos' => 29679,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1134,
            'endLine' => 1134,
            'startColumn' => 53,
            'endColumn' => 64,
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
                'startLine' => 1134,
                'endLine' => 1134,
                'startTokenPos' => 4967,
                'startFilePos' => 29699,
                'endTokenPos' => 4968,
                'endFilePos' => 29700,
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
            'startLine' => 1134,
            'endLine' => 1134,
            'startColumn' => 67,
            'endColumn' => 85,
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
 * Determine if the mailable has the given attachment from storage.
 *
 * @param  string  $path
 * @param  string|null  $name
 * @param  array  $options
 * @return bool
 */',
        'startLine' => 1134,
        'endLine' => 1137,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'hasAttachmentFromStorageDisk' => 
      array (
        'name' => 'hasAttachmentFromStorageDisk',
        'parameters' => 
        array (
          'disk' => 
          array (
            'name' => 'disk',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1148,
            'endLine' => 1148,
            'startColumn' => 50,
            'endColumn' => 54,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'path' => 
          array (
            'name' => 'path',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1148,
            'endLine' => 1148,
            'startColumn' => 57,
            'endColumn' => 61,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1148,
                'endLine' => 1148,
                'startTokenPos' => 5012,
                'startFilePos' => 30123,
                'endTokenPos' => 5012,
                'endFilePos' => 30126,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1148,
            'endLine' => 1148,
            'startColumn' => 64,
            'endColumn' => 75,
            'parameterIndex' => 2,
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
                'startLine' => 1148,
                'endLine' => 1148,
                'startTokenPos' => 5021,
                'startFilePos' => 30146,
                'endTokenPos' => 5022,
                'endFilePos' => 30147,
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
            'startLine' => 1148,
            'endLine' => 1148,
            'startColumn' => 78,
            'endColumn' => 96,
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
 * Determine if the mailable has the given attachment from a specific storage disk.
 *
 * @param  string  $disk
 * @param  string  $path
 * @param  string|null  $name
 * @param  array  $options
 * @return bool
 */',
        'startLine' => 1148,
        'endLine' => 1156,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'attachData' => 
      array (
        'name' => 'attachData',
        'parameters' => 
        array (
          'data' => 
          array (
            'name' => 'data',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1166,
            'endLine' => 1166,
            'startColumn' => 32,
            'endColumn' => 36,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1166,
            'endLine' => 1166,
            'startColumn' => 39,
            'endColumn' => 43,
            'parameterIndex' => 1,
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
                'startLine' => 1166,
                'endLine' => 1166,
                'startTokenPos' => 5127,
                'startFilePos' => 30719,
                'endTokenPos' => 5128,
                'endFilePos' => 30720,
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
            'startLine' => 1166,
            'endLine' => 1166,
            'startColumn' => 46,
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
 * Attach in-memory data as an attachment.
 *
 * @param  string  $data
 * @param  string  $name
 * @param  array  $options
 * @return $this
 */',
        'startLine' => 1166,
        'endLine' => 1174,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'hasAttachedData' => 
      array (
        'name' => 'hasAttachedData',
        'parameters' => 
        array (
          'data' => 
          array (
            'name' => 'data',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1184,
            'endLine' => 1184,
            'startColumn' => 37,
            'endColumn' => 41,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1184,
            'endLine' => 1184,
            'startColumn' => 44,
            'endColumn' => 48,
            'parameterIndex' => 1,
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
                'startLine' => 1184,
                'endLine' => 1184,
                'startTokenPos' => 5220,
                'startFilePos' => 31240,
                'endTokenPos' => 5221,
                'endFilePos' => 31241,
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
            'startLine' => 1184,
            'endLine' => 1184,
            'startColumn' => 51,
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
 * Determine if the mailable has the given data as an attachment.
 *
 * @param  string  $data
 * @param  string  $name
 * @param  array  $options
 * @return bool
 */',
        'startLine' => 1184,
        'endLine' => 1191,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'tag' => 
      array (
        'name' => 'tag',
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
            'startLine' => 1199,
            'endLine' => 1199,
            'startColumn' => 25,
            'endColumn' => 30,
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
 * Add a tag header to the message when supported by the underlying transport.
 *
 * @param  string  $value
 * @return $this
 */',
        'startLine' => 1199,
        'endLine' => 1204,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'hasTag' => 
      array (
        'name' => 'hasTag',
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
            'startLine' => 1212,
            'endLine' => 1212,
            'startColumn' => 28,
            'endColumn' => 33,
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
 * Determine if the mailable has the given tag.
 *
 * @param  string  $value
 * @return bool
 */',
        'startLine' => 1212,
        'endLine' => 1216,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'metadata' => 
      array (
        'name' => 'metadata',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1225,
            'endLine' => 1225,
            'startColumn' => 30,
            'endColumn' => 33,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'value' => 
          array (
            'name' => 'value',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1225,
                'endLine' => 1225,
                'startTokenPos' => 5394,
                'startFilePos' => 32347,
                'endTokenPos' => 5394,
                'endFilePos' => 32350,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1225,
            'endLine' => 1225,
            'startColumn' => 36,
            'endColumn' => 48,
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
 * Add a metadata header to the message when supported by the underlying transport.
 *
 * @param  array|string  $key
 * @param  string|null  $value
 * @return $this
 */',
        'startLine' => 1225,
        'endLine' => 1234,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'hasMetadata' => 
      array (
        'name' => 'hasMetadata',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1243,
            'endLine' => 1243,
            'startColumn' => 33,
            'endColumn' => 36,
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
            'startLine' => 1243,
            'endLine' => 1243,
            'startColumn' => 39,
            'endColumn' => 44,
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
 * Determine if the mailable has the given metadata.
 *
 * @param  string  $key
 * @param  string  $value
 * @return bool
 */',
        'startLine' => 1243,
        'endLine' => 1247,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertFrom' => 
      array (
        'name' => 'assertFrom',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1256,
            'endLine' => 1256,
            'startColumn' => 32,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1256,
                'endLine' => 1256,
                'startTokenPos' => 5543,
                'startFilePos' => 33188,
                'endTokenPos' => 5543,
                'endFilePos' => 33191,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1256,
            'endLine' => 1256,
            'startColumn' => 42,
            'endColumn' => 53,
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
 * Assert that the mailable is from the given address.
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @return $this
 */',
        'startLine' => 1256,
        'endLine' => 1269,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertTo' => 
      array (
        'name' => 'assertTo',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1278,
            'endLine' => 1278,
            'startColumn' => 30,
            'endColumn' => 37,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1278,
                'endLine' => 1278,
                'startTokenPos' => 5637,
                'startFilePos' => 33811,
                'endTokenPos' => 5637,
                'endFilePos' => 33814,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1278,
            'endLine' => 1278,
            'startColumn' => 40,
            'endColumn' => 51,
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
 * Assert that the mailable has the given recipient.
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @return $this
 */',
        'startLine' => 1278,
        'endLine' => 1291,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertHasTo' => 
      array (
        'name' => 'assertHasTo',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1300,
            'endLine' => 1300,
            'startColumn' => 33,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1300,
                'endLine' => 1300,
                'startTokenPos' => 5731,
                'startFilePos' => 34453,
                'endTokenPos' => 5731,
                'endFilePos' => 34456,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1300,
            'endLine' => 1300,
            'startColumn' => 43,
            'endColumn' => 54,
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
 * Assert that the mailable has the given recipient.
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @return $this
 */',
        'startLine' => 1300,
        'endLine' => 1303,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertHasCc' => 
      array (
        'name' => 'assertHasCc',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1312,
            'endLine' => 1312,
            'startColumn' => 33,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1312,
                'endLine' => 1312,
                'startTokenPos' => 5766,
                'startFilePos' => 34751,
                'endTokenPos' => 5766,
                'endFilePos' => 34754,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1312,
            'endLine' => 1312,
            'startColumn' => 43,
            'endColumn' => 54,
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
 * Assert that the mailable has the given recipient.
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @return $this
 */',
        'startLine' => 1312,
        'endLine' => 1325,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertHasBcc' => 
      array (
        'name' => 'assertHasBcc',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1334,
            'endLine' => 1334,
            'startColumn' => 34,
            'endColumn' => 41,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1334,
                'endLine' => 1334,
                'startTokenPos' => 5860,
                'startFilePos' => 35394,
                'endTokenPos' => 5860,
                'endFilePos' => 35397,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1334,
            'endLine' => 1334,
            'startColumn' => 44,
            'endColumn' => 55,
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
 * Assert that the mailable has the given recipient.
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @return $this
 */',
        'startLine' => 1334,
        'endLine' => 1347,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertHasReplyTo' => 
      array (
        'name' => 'assertHasReplyTo',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1356,
            'endLine' => 1356,
            'startColumn' => 38,
            'endColumn' => 45,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1356,
                'endLine' => 1356,
                'startTokenPos' => 5954,
                'startFilePos' => 36053,
                'endTokenPos' => 5954,
                'endFilePos' => 36056,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1356,
            'endLine' => 1356,
            'startColumn' => 48,
            'endColumn' => 59,
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
 * Assert that the mailable has the given "reply to" address.
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @return $this
 */',
        'startLine' => 1356,
        'endLine' => 1369,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'formatAssertionRecipient' => 
      array (
        'name' => 'formatAssertionRecipient',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1378,
            'endLine' => 1378,
            'startColumn' => 47,
            'endColumn' => 54,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1378,
                'endLine' => 1378,
                'startTokenPos' => 6048,
                'startFilePos' => 36740,
                'endTokenPos' => 6048,
                'endFilePos' => 36743,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1378,
            'endLine' => 1378,
            'startColumn' => 57,
            'endColumn' => 68,
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
 * Format the mailable recipient for display in an assertion message.
 *
 * @param  object|array|string  $address
 * @param  string|null  $name
 * @return string
 */',
        'startLine' => 1378,
        'endLine' => 1389,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'formatActualRecipients' => 
      array (
        'name' => 'formatActualRecipients',
        'parameters' => 
        array (
          'recipients' => 
          array (
            'name' => 'recipients',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1397,
            'endLine' => 1397,
            'startColumn' => 45,
            'endColumn' => 55,
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
 * Format actual recipients for display in assertion messages.
 *
 * @param  array  $recipients
 * @return string
 */',
        'startLine' => 1397,
        'endLine' => 1411,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertHasSubject' => 
      array (
        'name' => 'assertHasSubject',
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
            'startLine' => 1419,
            'endLine' => 1419,
            'startColumn' => 38,
            'endColumn' => 45,
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
 * Assert that the mailable has the given subject.
 *
 * @param  string  $subject
 * @return $this
 */',
        'startLine' => 1419,
        'endLine' => 1431,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertSeeInHtml' => 
      array (
        'name' => 'assertSeeInHtml',
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
            'startLine' => 1440,
            'endLine' => 1440,
            'startColumn' => 37,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'escape' => 
          array (
            'name' => 'escape',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 1440,
                'endLine' => 1440,
                'startTokenPos' => 6346,
                'startFilePos' => 38390,
                'endTokenPos' => 6346,
                'endFilePos' => 38393,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1440,
            'endLine' => 1440,
            'startColumn' => 46,
            'endColumn' => 59,
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
 * Assert that the given text is present in the HTML email body.
 *
 * @param  string  $string
 * @param  bool  $escape
 * @return $this
 */',
        'startLine' => 1440,
        'endLine' => 1453,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertDontSeeInHtml' => 
      array (
        'name' => 'assertDontSeeInHtml',
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
            'startLine' => 1462,
            'endLine' => 1462,
            'startColumn' => 41,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'escape' => 
          array (
            'name' => 'escape',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 1462,
                'endLine' => 1462,
                'startTokenPos' => 6434,
                'startFilePos' => 38977,
                'endTokenPos' => 6434,
                'endFilePos' => 38980,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1462,
            'endLine' => 1462,
            'startColumn' => 50,
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
 * Assert that the given text is not present in the HTML email body.
 *
 * @param  string  $string
 * @param  bool  $escape
 * @return $this
 */',
        'startLine' => 1462,
        'endLine' => 1475,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertSeeInOrderInHtml' => 
      array (
        'name' => 'assertSeeInOrderInHtml',
        'parameters' => 
        array (
          'strings' => 
          array (
            'name' => 'strings',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1484,
            'endLine' => 1484,
            'startColumn' => 44,
            'endColumn' => 51,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'escape' => 
          array (
            'name' => 'escape',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 1484,
                'endLine' => 1484,
                'startTokenPos' => 6522,
                'startFilePos' => 39579,
                'endTokenPos' => 6522,
                'endFilePos' => 39582,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1484,
            'endLine' => 1484,
            'startColumn' => 54,
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
 * Assert that the given text strings are present in order in the HTML email body.
 *
 * @param  array  $strings
 * @param  bool  $escape
 * @return $this
 */',
        'startLine' => 1484,
        'endLine' => 1495,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertSeeInText' => 
      array (
        'name' => 'assertSeeInText',
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
            'startLine' => 1503,
            'endLine' => 1503,
            'startColumn' => 37,
            'endColumn' => 43,
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
 * Assert that the given text is present in the plain-text email body.
 *
 * @param  string  $string
 * @return $this
 */',
        'startLine' => 1503,
        'endLine' => 1514,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertDontSeeInText' => 
      array (
        'name' => 'assertDontSeeInText',
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
            'startLine' => 1522,
            'endLine' => 1522,
            'startColumn' => 41,
            'endColumn' => 47,
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
 * Assert that the given text is not present in the plain-text email body.
 *
 * @param  string  $string
 * @return $this
 */',
        'startLine' => 1522,
        'endLine' => 1533,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertSeeInOrderInText' => 
      array (
        'name' => 'assertSeeInOrderInText',
        'parameters' => 
        array (
          'strings' => 
          array (
            'name' => 'strings',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1541,
            'endLine' => 1541,
            'startColumn' => 44,
            'endColumn' => 51,
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
 * Assert that the given text strings are present in order in the plain-text email body.
 *
 * @param  array  $strings
 * @return $this
 */',
        'startLine' => 1541,
        'endLine' => 1548,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertHasNoAttachments' => 
      array (
        'name' => 'assertHasNoAttachments',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Assert the mailable has the given attachment.
 *
 * @param  string|\\Illuminate\\Contracts\\Mail\\Attachable|\\Illuminate\\Mail\\Attachment  $file
 * @param  array  $options
 * @return $this
 */',
        'startLine' => 1557,
        'endLine' => 1577,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertHasAttachment' => 
      array (
        'name' => 'assertHasAttachment',
        'parameters' => 
        array (
          'file' => 
          array (
            'name' => 'file',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1579,
            'endLine' => 1579,
            'startColumn' => 41,
            'endColumn' => 45,
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
                'startLine' => 1579,
                'endLine' => 1579,
                'startTokenPos' => 6886,
                'startFilePos' => 42134,
                'endTokenPos' => 6887,
                'endFilePos' => 42135,
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
            'startLine' => 1579,
            'endLine' => 1579,
            'startColumn' => 48,
            'endColumn' => 66,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 1579,
        'endLine' => 1589,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertHasAttachedData' => 
      array (
        'name' => 'assertHasAttachedData',
        'parameters' => 
        array (
          'data' => 
          array (
            'name' => 'data',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1599,
            'endLine' => 1599,
            'startColumn' => 43,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1599,
            'endLine' => 1599,
            'startColumn' => 50,
            'endColumn' => 54,
            'parameterIndex' => 1,
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
                'startLine' => 1599,
                'endLine' => 1599,
                'startTokenPos' => 6947,
                'startFilePos' => 42626,
                'endTokenPos' => 6948,
                'endFilePos' => 42627,
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
            'startLine' => 1599,
            'endLine' => 1599,
            'startColumn' => 57,
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
 * Assert the mailable has the given data as an attachment.
 *
 * @param  string  $data
 * @param  string  $name
 * @param  array  $options
 * @return $this
 */',
        'startLine' => 1599,
        'endLine' => 1609,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertHasAttachmentFromStorage' => 
      array (
        'name' => 'assertHasAttachmentFromStorage',
        'parameters' => 
        array (
          'path' => 
          array (
            'name' => 'path',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1619,
            'endLine' => 1619,
            'startColumn' => 52,
            'endColumn' => 56,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1619,
                'endLine' => 1619,
                'startTokenPos' => 7006,
                'startFilePos' => 43127,
                'endTokenPos' => 7006,
                'endFilePos' => 43130,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1619,
            'endLine' => 1619,
            'startColumn' => 59,
            'endColumn' => 70,
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
                'startLine' => 1619,
                'endLine' => 1619,
                'startTokenPos' => 7015,
                'startFilePos' => 43150,
                'endTokenPos' => 7016,
                'endFilePos' => 43151,
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
            'startLine' => 1619,
            'endLine' => 1619,
            'startColumn' => 73,
            'endColumn' => 91,
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
 * Assert the mailable has the given attachment from storage.
 *
 * @param  string  $path
 * @param  string|null  $name
 * @param  array  $options
 * @return $this
 */',
        'startLine' => 1619,
        'endLine' => 1629,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertHasAttachmentFromStorageDisk' => 
      array (
        'name' => 'assertHasAttachmentFromStorageDisk',
        'parameters' => 
        array (
          'disk' => 
          array (
            'name' => 'disk',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1640,
            'endLine' => 1640,
            'startColumn' => 56,
            'endColumn' => 60,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'path' => 
          array (
            'name' => 'path',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1640,
            'endLine' => 1640,
            'startColumn' => 63,
            'endColumn' => 67,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1640,
                'endLine' => 1640,
                'startTokenPos' => 7077,
                'startFilePos' => 43716,
                'endTokenPos' => 7077,
                'endFilePos' => 43719,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1640,
            'endLine' => 1640,
            'startColumn' => 70,
            'endColumn' => 81,
            'parameterIndex' => 2,
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
                'startLine' => 1640,
                'endLine' => 1640,
                'startTokenPos' => 7086,
                'startFilePos' => 43739,
                'endTokenPos' => 7087,
                'endFilePos' => 43740,
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
            'startLine' => 1640,
            'endLine' => 1640,
            'startColumn' => 84,
            'endColumn' => 102,
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
 * Assert the mailable has the given attachment from a specific storage disk.
 *
 * @param  string  $disk
 * @param  string  $path
 * @param  string|null  $name
 * @param  array  $options
 * @return $this
 */',
        'startLine' => 1640,
        'endLine' => 1650,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertHasTag' => 
      array (
        'name' => 'assertHasTag',
        'parameters' => 
        array (
          'tag' => 
          array (
            'name' => 'tag',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1658,
            'endLine' => 1658,
            'startColumn' => 34,
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
 * Assert that the mailable has the given tag.
 *
 * @param  string  $tag
 * @return $this
 */',
        'startLine' => 1658,
        'endLine' => 1671,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'assertHasMetadata' => 
      array (
        'name' => 'assertHasMetadata',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1680,
            'endLine' => 1680,
            'startColumn' => 39,
            'endColumn' => 42,
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
            'startLine' => 1680,
            'endLine' => 1680,
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
 * Assert that the mailable has the given metadata.
 *
 * @param  string  $key
 * @param  string  $value
 * @return $this
 */',
        'startLine' => 1680,
        'endLine' => 1694,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'renderForAssertions' => 
      array (
        'name' => 'renderForAssertions',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Render the HTML and plain-text version of the mailable into views for assertions.
 *
 * @return array
 *
 * @throws \\ReflectionException
 */',
        'startLine' => 1703,
        'endLine' => 1730,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'prepareMailableForDelivery' => 
      array (
        'name' => 'prepareMailableForDelivery',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Prepare the mailable instance for delivery.
 *
 * @return void
 */',
        'startLine' => 1737,
        'endLine' => 1747,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'ensureHeadersAreHydrated' => 
      array (
        'name' => 'ensureHeadersAreHydrated',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Ensure the mailable\'s headers are hydrated from the "headers" method.
 *
 * @return void
 */',
        'startLine' => 1754,
        'endLine' => 1775,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'ensureEnvelopeIsHydrated' => 
      array (
        'name' => 'ensureEnvelopeIsHydrated',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Ensure the mailable\'s "envelope" data is hydrated from the "envelope" method.
 *
 * @return void
 */',
        'startLine' => 1782,
        'endLine' => 1815,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'ensureContentIsHydrated' => 
      array (
        'name' => 'ensureContentIsHydrated',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Ensure the mailable\'s content is hydrated from the "content" method.
 *
 * @return void
 */',
        'startLine' => 1822,
        'endLine' => 1853,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'ensureAttachmentsAreHydrated' => 
      array (
        'name' => 'ensureAttachmentsAreHydrated',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Ensure the mailable\'s attachments are hydrated from the "attachments" method.
 *
 * @return void
 */',
        'startLine' => 1860,
        'endLine' => 1872,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'usesMailer' => 
      array (
        'name' => 'usesMailer',
        'parameters' => 
        array (
          'mailer' => 
          array (
            'name' => 'mailer',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1880,
            'endLine' => 1880,
            'startColumn' => 32,
            'endColumn' => 38,
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
 * Determine if the mailable will be sent by the given mailer.
 *
 * @param  string  $mailer
 * @return bool
 */',
        'startLine' => 1880,
        'endLine' => 1883,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'mailer' => 
      array (
        'name' => 'mailer',
        'parameters' => 
        array (
          'mailer' => 
          array (
            'name' => 'mailer',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1891,
            'endLine' => 1891,
            'startColumn' => 28,
            'endColumn' => 34,
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
 * Set the name of the mailer that should send the message.
 *
 * @param  string  $mailer
 * @return $this
 */',
        'startLine' => 1891,
        'endLine' => 1896,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'withSymfonyMessage' => 
      array (
        'name' => 'withSymfonyMessage',
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
            'startLine' => 1904,
            'endLine' => 1904,
            'startColumn' => 40,
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
 * Register a callback to be called with the Symfony message instance.
 *
 * @param  callable  $callback
 * @return $this
 */',
        'startLine' => 1904,
        'endLine' => 1909,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      'buildViewDataUsing' => 
      array (
        'name' => 'buildViewDataUsing',
        'parameters' => 
        array (
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
            'startLine' => 1917,
            'endLine' => 1917,
            'startColumn' => 47,
            'endColumn' => 64,
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
 * Register a callback to be called while building the view data.
 *
 * @param  callable  $callback
 * @return void
 */',
        'startLine' => 1917,
        'endLine' => 1920,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
      '__call' => 
      array (
        'name' => '__call',
        'parameters' => 
        array (
          'method' => 
          array (
            'name' => 'method',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1931,
            'endLine' => 1931,
            'startColumn' => 28,
            'endColumn' => 34,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'parameters' => 
          array (
            'name' => 'parameters',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1931,
            'endLine' => 1931,
            'startColumn' => 37,
            'endColumn' => 47,
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
 * Dynamically bind parameters to the message.
 *
 * @param  string  $method
 * @param  array  $parameters
 * @return $this
 *
 * @throws \\BadMethodCallException
 */',
        'startLine' => 1931,
        'endLine' => 1942,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Mail',
        'declaringClassName' => 'Illuminate\\Mail\\Mailable',
        'implementingClassName' => 'Illuminate\\Mail\\Mailable',
        'currentClassName' => 'Illuminate\\Mail\\Mailable',
        'aliasName' => NULL,
      ),
    ),
    'traitsData' => 
    array (
      'aliases' => 
      array (
        'Illuminate\\Support\\Traits\\Conditionable' => 
        array (
          0 => 
          array (
            'alias' => 'macroCall',
            'method' => '__call',
            'hash' => 'illuminate\\support\\traits\\conditionable::__call',
          ),
        ),
        'Illuminate\\Support\\Traits\\ForwardsCalls' => 
        array (
          0 => 
          array (
            'alias' => 'macroCall',
            'method' => '__call',
            'hash' => 'illuminate\\support\\traits\\forwardscalls::__call',
          ),
        ),
        'Illuminate\\Support\\Traits\\Localizable' => 
        array (
          0 => 
          array (
            'alias' => 'macroCall',
            'method' => '__call',
            'hash' => 'illuminate\\support\\traits\\localizable::__call',
          ),
        ),
        'Illuminate\\Support\\Traits\\ReadsClassAttributes' => 
        array (
          0 => 
          array (
            'alias' => 'macroCall',
            'method' => '__call',
            'hash' => 'illuminate\\support\\traits\\readsclassattributes::__call',
          ),
        ),
        'Illuminate\\Support\\Traits\\Tappable' => 
        array (
          0 => 
          array (
            'alias' => 'macroCall',
            'method' => '__call',
            'hash' => 'illuminate\\support\\traits\\tappable::__call',
          ),
        ),
        'Illuminate\\Support\\Traits\\Macroable' => 
        array (
          0 => 
          array (
            'alias' => 'macroCall',
            'method' => '__call',
            'hash' => 'illuminate\\support\\traits\\macroable::__call',
          ),
        ),
      ),
      'modifiers' => 
      array (
      ),
      'precedences' => 
      array (
      ),
      'hashes' => 
      array (
        'illuminate\\support\\traits\\conditionable::__call' => 'Illuminate\\Support\\Traits\\Conditionable::__call',
        'illuminate\\support\\traits\\forwardscalls::__call' => 'Illuminate\\Support\\Traits\\ForwardsCalls::__call',
        'illuminate\\support\\traits\\localizable::__call' => 'Illuminate\\Support\\Traits\\Localizable::__call',
        'illuminate\\support\\traits\\readsclassattributes::__call' => 'Illuminate\\Support\\Traits\\ReadsClassAttributes::__call',
        'illuminate\\support\\traits\\tappable::__call' => 'Illuminate\\Support\\Traits\\Tappable::__call',
        'illuminate\\support\\traits\\macroable::__call' => 'Illuminate\\Support\\Traits\\Macroable::__call',
      ),
    ),
  ),
));