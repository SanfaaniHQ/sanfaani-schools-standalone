<?php declare(strict_types = 1);

// ftm-C:\laragon\www\sanfaani-schools\vendor\laravel\framework\src\Illuminate\Mail\MailManager.php
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v5-2.3.2',
   'data' => 
  array (
    0 => 
    array (
      '80c82ee8b9da775774502467fa0ef610' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => NULL,
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '407628b6c2c5d3eefdef0899633cd226' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => '__construct',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '40644cea1572b413fd27d71247bc605e' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'mailer',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '625965e6d99b7a5562a37b8c9b6eec77' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'driver',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '1bc6d70be4f2d46993a66cbf05573086' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'get',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '0d0dc1ba3b4195f34585e490728e9acb' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'resolve',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'c6240b9c9ff186e88208e4a8133cdf70' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'build',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'ba8057de0ed5edaeef24f4f8dec69675' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'createSymfonyTransport',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '353d0336afc29bdcbe7d76a6692de2a7' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'createSmtpTransport',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'fd449f24b3c41588e5242fb2d55529e6' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'configureSmtpTransport',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '416b749bf3636097acc86e124390e782' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'createSendmailTransport',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'fb41e3a56182a464f2d374c09a7e8070' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'createSesTransport',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'e302b5d328c7585dfe8cb9bb7ab787ae' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'createSesV2Transport',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '2fa18fd409d9bcbd804a4577f3aaa204' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'addSesCredentials',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '20fa335d0e0ba54be37621e03ea49435' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'createResendTransport',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '4650c3b8177886dc86967f737531a967' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'createCloudflareTransport',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '097f7b811fb87b6e2b7706f62a5e3c69' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'createMailTransport',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'fcd48c3b1554fff0e0bdbc7a69b0e92c' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'createMailgunTransport',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'bd7420330ee043d9804243f0ec8d1948' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'createPostmarkTransport',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '3d80c4b28e14fa1233f9ffedfb600b1c' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'createFailoverTransport',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '7610701a75b813a2fb8a616540f06992' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'createRoundrobinTransport',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '78b931cea4f3098a7704ff88a5518678' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'createRoundrobinTransportOfClass',
         'templatePhpDocNodes' => 
        array (
          'TClass' => 
          array (
            0 => '@template',
            1 => 
            \PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode::__set_state(array(
               'name' => 'TClass',
               'bound' => 
              \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode::__set_state(array(
                 'name' => '\\Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
                 'attributes' => 
                array (
                  'startLine' => 4,
                  'endLine' => 4,
                ),
              )),
               'default' => NULL,
               'lowerBound' => NULL,
               'description' => '',
               'attributes' => 
              array (
                'startLine' => 4,
                'endLine' => 4,
              ),
            )),
          ),
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'e602310fca07698b37f87ee0073a3523' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'createLogTransport',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'ef676b07288047bbe78e831a4f51cc4b' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'createArrayTransport',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '5e7128118166c385bdf263c317c2a6d1' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'getHttpClient',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'abd285d02f9f59175136aa771c983494' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'setGlobalAddress',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'e6fdfc14594e7e5595ba01616ee632ac' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'getConfig',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '6ecd969f347b4b7f9b61014346e5e156' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'getDefaultDriver',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '9c6b2f7b42e09df7727d20e47c6a3234' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'setDefaultDriver',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '67f3b74095bd5179a09c1f38f39037eb' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'purge',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '737b72ca43602da9cb3a48778297b86a' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'extend',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '9854df7a97365a4e3a36804fe9ab28b5' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'getApplication',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '8eab19b3ea80ff4676255b7fc08cea3b' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'setApplication',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '63e9b09ac218b818e4900905bcdb4781' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => 'forgetMailers',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '07586c4ea33b10270dd855f2d7fd0277' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Illuminate\\Mail',
         'uses' => 
        array (
          'sesclient' => 'Aws\\Ses\\SesClient',
          'sesv2client' => 'Aws\\SesV2\\SesV2Client',
          'closure' => 'Closure',
          'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
          'logmanager' => 'Illuminate\\Log\\LogManager',
          'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
          'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
          'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
          'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
          'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
          'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
          'arr' => 'Illuminate\\Support\\Arr',
          'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
          'str' => 'Illuminate\\Support\\Str',
          'invalidargumentexception' => 'InvalidArgumentException',
          'loggerinterface' => 'Psr\\Log\\LoggerInterface',
          'resend' => 'Resend',
          'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
          'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
          'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
          'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
          'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
          'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
          'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
          'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
          'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
          'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
        ),
         'className' => 'Illuminate\\Mail\\MailManager',
         'functionName' => '__call',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Illuminate\\Mail',
           'uses' => 
          array (
            'sesclient' => 'Aws\\Ses\\SesClient',
            'sesv2client' => 'Aws\\SesV2\\SesV2Client',
            'closure' => 'Closure',
            'factorycontract' => 'Illuminate\\Contracts\\Mail\\Factory',
            'logmanager' => 'Illuminate\\Log\\LogManager',
            'arraytransport' => 'Illuminate\\Mail\\Transport\\ArrayTransport',
            'cloudflaretransport' => 'Illuminate\\Mail\\Transport\\CloudflareTransport',
            'logtransport' => 'Illuminate\\Mail\\Transport\\LogTransport',
            'resendtransport' => 'Illuminate\\Mail\\Transport\\ResendTransport',
            'sestransport' => 'Illuminate\\Mail\\Transport\\SesTransport',
            'sesv2transport' => 'Illuminate\\Mail\\Transport\\SesV2Transport',
            'arr' => 'Illuminate\\Support\\Arr',
            'configurationurlparser' => 'Illuminate\\Support\\ConfigurationUrlParser',
            'str' => 'Illuminate\\Support\\Str',
            'invalidargumentexception' => 'InvalidArgumentException',
            'loggerinterface' => 'Psr\\Log\\LoggerInterface',
            'resend' => 'Resend',
            'httpclient' => 'Symfony\\Component\\HttpClient\\HttpClient',
            'mailguntransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory',
            'postmarktransportfactory' => 'Symfony\\Component\\Mailer\\Bridge\\Postmark\\Transport\\PostmarkTransportFactory',
            'dsn' => 'Symfony\\Component\\Mailer\\Transport\\Dsn',
            'failovertransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
            'roundrobintransport' => 'Symfony\\Component\\Mailer\\Transport\\RoundRobinTransport',
            'sendmailtransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
            'esmtptransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
            'esmtptransportfactory' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransportFactory',
            'socketstream' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream',
          ),
           'className' => 'Illuminate\\Mail\\MailManager',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
    ),
    1 => 
    array (
      'C:\\laragon\\www\\sanfaani-schools\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\MailManager.php' => '7cbad4b5781b4175b6f2e27c989da4fb5cee36952039760e58ad078769ea70cd',
    ),
  ),
));