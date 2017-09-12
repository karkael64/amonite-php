<?php

echo "Building...\n\r";

$f = "amonite.phar";
if( file_exists( $f ) )
	unlink( $f );

$p = new Phar( $f );

define( "LIB", __DIR__ . "/libraries/" );

$files = array(

	// INTERFACES
	LIB . "Answerable.interface.php",
	LIB . "Model.interface.php",
	LIB . "Row.interface.php",

	// FUNCTIONS
	LIB . "Main.function.php",

	// CLASSES
	LIB . "Component.class.php",
	LIB . "Constants.class.php",
	LIB . "Content.class.php",
	LIB . "Controller.class.php",
	LIB . "CustomException.class.php",
	LIB . "Document.class.php",
	LIB . "DownloadFile.class.php",
	LIB . "Email.class.php",
	LIB . "File.class.php",
	LIB . "HttpCode.class.php",
	LIB . "ModelBSON.class.php",
	LIB . "ModelPDO.class.php",
	LIB . "Observer.class.php",
	LIB . "Request.class.php",
	LIB . "Response.class.php",
	LIB . "RowBSON.class.php",
	LIB . "RowPDO.class.php"
);

foreach( $files as $file ) {
	$p->addFile( $file, basename( $file ) );
}

$p->setStub( '<?php 

/** @SECURITY Anti-DDOS system */
time_nanosleep( 0 , 1 );
defined( "ROOT" ) or define( "ROOT", realpath( __DIR__ ) );
$pharname = basename( __FILE__ );

/** @SECURITY Catch errors */
require_once "phar://$pharname/CustomException.class.php";
CustomException::set_error_handler();
CustomException::set_fatal_handler();

/** @APPLICATION Function, Content, Database */
require_once "phar://$pharname/Main.function.php";
require_once "phar://$pharname/Request.class.php";
require_once "phar://$pharname/Response.class.php";
require_once "phar://$pharname/Controller.class.php";
require_once "phar://$pharname/RowBSON.class.php";
require_once "phar://$pharname/RowPDO.class.php";
require_once "phar://$pharname/Component.class.php";
require_once "phar://$pharname/Document.class.php";
require_once "phar://$pharname/DownloadFile.class.php";
require_once "phar://$pharname/Email.class.php";

__HALT_COMPILER(); 
?>' );

echo "Phar build !\n\r";
echo "Version: " . round( microtime( true ) * 1000 ) . "\n\r";

