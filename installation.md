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
Amonite\Response::send( Amonite\Controller::auto( Amonite\Controller::ALL_CTRL ) );
```

## Full configured entry point

``` PHP
<?php

defined( "ROOT" ) or define( "ROOT", realpath( __DIR__ ) );
require_once "amonite.phar";


// error
Amonite\CustomException::set_error_handler();
Amonite\CustomException::set_fatal_handler();
Amonite\HttpCode::$DEBUG_MODE = true;

// set directories
Amonite\Controller::$theme_path = realpath( ROOT . "/themes/" );
Amonite\Backup::$backup_folder = ROOT . "/databases/backup";
Amonite\Backup::$files_folder = ROOT . "/databases/datafiles";
Amonite\ModelBSON::$datafiles_path = realpath( ROOT . "/datas/files" );

// set admin email
Amonite\Email::$ADMIN_EMAIL =       "webmaster@your.site";
Amonite\Email::$ADMIN_EMAIL_NAME =  "Webmaster";
Amonite\Email::$ADMIN_ERROR_TITLE = "Admin Error Report";
Amonite\Email::$SITE_NAME =         "Your Site";

// add a controller
$execute = function ( Amonite\Request $req, Amonite\Response $res ) {
  return "<pre>Hello, World!</pre>";
}
$priority = function ( Amonite\Request $req, Amonite\Response $res ) {
  return $req->file === "/hello.html" ? 10 : 0;
}
Amonite\Controller::register($helloWorld = new Controller($execute, $priority));


// run
Amonite\Response::send( function ( Amonite\Request $req, Amonite\Response $res ) {
  // configure this request
  Amonite\Request->custom = array( "foo" => "bar" );
  Amonite\Controller::auto( Amonite\Controller::ALL_CTRL );
});
```
