# Amonite : how does it works ?

## Create entry HTTP points

After [installed](installation.md) the module and called it, Amonite helps you to create HTTP entry points. It means for example that a request like "GET http://your.site/script.js" to return the content of your file at "/your/site/path/themes/script.js" with default configuration. The file read and returned in HTTP protocol, with the good mime type, file  length, forced encoded in UTF-8 format, and an ETag for improved experience with the cache managed.


## Execute entry point

Amonite is not a simple file reader, so if you want to execute an entry point, you should name the file with ".php" at the end of filename in Amonite PHP. For example a request like "POST http://your.site/data/user.json" will try to open "/your/site/path/themes/data.json" and fail if file doesn't exists, then try to open "/your/site/path/themes/data.json.php" and succeed to execute its content, with default configuration. The value returned is the body following HTTP protocol, with the good mime type, file length, forced encoded in UTF-8 format, and an ETag for improved experience with the cache managed.


## Ordering actions

1. Security: set `ROOT` and delay execution
2. Errors: handle errors thrown
3. With `Amonite\Controller::auto( Amonite\Controller::ALL_CTRL )` you do:
  1. Return file content if file exists (if options contains `Amonite\Controller::CTRL_FILE`)
  2. Return file execution if file ends with ".php" (if options contains `Amonite\Controller::CTRL_PHP` or `Amonite\Controller::CTRL_HIDDEN`)
  3. Return a HttpCode with code 404 (if options contains `Amonite\Controller::CTRL_NOT_FOUND`)
4. With `Response::send( function( Amonite\Request $res, Amonite\Response $res ) { return "<html>"; } );` you do:
  1. Set the Request as a constant
  2. Get a Response instance
  3. Translate the returned or thrown String, HttpCode or Error to body string
  4. Send HTTP header and body

## Default configuration

The `ROOT` constant has for value the directory of this project, for example "/var/www/your.site/".

### Controller

Controller is a register composed with Controller instances. Every controllers registered executes their "priority" function, and the highest number returned determines the controller to execute. A controller instance is composed with two functions: a function to execute if the second function "priority" returns higher number :

``` PHP
<?php

$execute = function ( Amonite\Request $req, Amonite\Response $res ) {
  return "<pre>Hello, World!</pre>";
}

$priority = function ( Amonite\Request $req, Amonite\Response $res ) {
  return $req->file === "/hello.html" ? 10 : 0;
}

Amonite\Controller::register($helloWorld = new Amonite\Controller($execute, $priority));
```

The HTTP entry points of the default controllers are in folder sets here :


``` PHP
<?php

Amonite\Controller::$theme_path = realpath( ROOT . "/themes/" );
```


### Backup

A backup is a file which helps to retrieve data or show its evolution in time. With default configuration, a backup is produce when :
- a user send a request to server
- and there is no file match for this new day, or new week, or new month, or new year.

Easily Backup files with one of this lines:

``` PHP
<?php

Amonite\Backup::revertDay();
Amonite\Backup::revertWeek();
Amonite\Backup::revertMonth();
Amonite\Backup::revertYear();
Amonite\Backup::revertBackup( $filepath );
Amonite\Backup::revertFileWithBackup( $dest_filepath, $backup_filepath );
```

The default configuration:

``` PHP
<?php

Amonite\Backup::$backup_folder = ROOT . "/databases/backup";   // backup files are saved in this directory, named with creation date.
Amonite\Backup::$files_folder = ROOT . "/databases/datafiles"; // directory's files saved in backup.
```


### CustomException

When an error is triggered, the PHP throw errors in HTML format and is never catched by the user. By initializing this error catcher, errors are treated, returned in the format expected : HTML, JSON or a plain string.

``` PHP
<?php

Amonite\CustomException::set_error_handler();
Amonite\CustomException::set_fatal_handler();
```


### HttpCode

You can get errors more verbose by setting:

``` PHP
<?php

Amonite\HttpCode::$DEBUG_MODE = true;
```


### Request

A Request instance sets attributes as constants.

``` JSON
{
  "METHOD": "GET",
  "HOST": "http://your.site",
  "FILE": "/index.html",
  "ETAG": "",
  "TIME": "",
  "PROTOCOL": "http",
  "ARGUMENTS": {
    "ARGC": 0,
    "ARGV": {},
    "GET": {},
    "POST": {},
    "FILES": {},
    "COOKIE": {},
    "ALL": {}
  },
  "BODY": "",
  "USER": {
    "AGENT": "",
    "LANGUAGE": "",
    "CHARSET": "",
    "ENCODING": "",
    "ADDRESS": "",
    "PORT": "",
    "MIME": ""
  }
}
```


### Email

Email configuration helps you to identify a user to send an email in case of any error.

``` PHP
<?php

Amonite\Email::$ADMIN_EMAIL =       "webmaster@your.site";
Amonite\Email::$ADMIN_EMAIL_NAME =  "Webmaster";
Amonite\Email::$ADMIN_ERROR_TITLE = "Admin Error Report";
Amonite\Email::$SITE_NAME =         "Your Site";
```

Then you just have to send an email to admin with :

``` PHP
<?php

Amonite\Email::AdminError("Your message here!");
```


### ModelBSON

Every ModelBSON child classes read files in this directory :

``` PHP
<?php

Amonite\ModelBSON::$datafiles_path = realpath( ROOT . "/datas/files" );
```


### ETag

The ETag value is generated with the file data only.
