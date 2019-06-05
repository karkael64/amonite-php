# Amonite: Installation

## Copy files

Copy `amonite.phar` file in your project directory.

## Composer

Soon...! :)

## Single entry point

For getting the files in only one entry point, insert also `.htaccess` file with this code anywhere :

```
RewriteEngine On
RewriteRule !^index\.php$ index.php [L]
```

If you use Apache2, verify "apache2/apache2.conf" to contains "AllowOverride All" and the mod "rewrite" in "mods-enabled" directy (`/etc/apache2/mods-enabled/rewrite.load`).

And call it in your `/index.php` file with:

``` PHP
<?php

require_once "amonite.phar";
Response::send( function ( Request $req, Response $res ) {
  Controller::auto( Controller::ALL_CTRL );
});
```

## Full configured entry point

``` PHP
<?php


defined( "ROOT" ) or define( "ROOT", realpath( __DIR__ ) );
require_once "amonite.phar";


// error
CustomException::set_error_handler();
CustomException::set_fatal_handler();
HttpCode::$DEBUG_MODE = true;

// set directories
Controller::$theme_path = realpath( ROOT . "/themes/" );
Backup::$backup_folder = ROOT . "/databases/backup";
Backup::$files_folder = ROOT . "/databases/datafiles";
ModelBSON::$datafiles_path = realpath( ROOT . "/datas/files" );

// set admin email
Email::$ADMIN_EMAIL =       "webmaster@your.site";
Email::$ADMIN_EMAIL_NAME =  "Webmaster";
Email::$ADMIN_ERROR_TITLE = "Admin Error Report";
Email::$SITE_NAME =         "Your Site";

// add a controller
$execute = function ( Request $req, Response $res ) {
  return "<pre>Hello, World!</pre>";
}
$priority = function ( Request $req, Response $res ) {
  return $req->file === "/hello.html" ? 10 : 0;
}
Controller::register($helloWorld = new Controller($execute, $priority));


// run
Response::send( function ( Request $req, Response $res ) {
  // configure this request
  Request->custom = array( "foo" => "bar" );
  Controller::auto( Controller::ALL_CTRL );
});
```
