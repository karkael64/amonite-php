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
require_once "amonite.phar";
Response::send( function ( Request $req, Response $res ) {
  Controller::auto( Controller::ALL_CTRL );
});
```
