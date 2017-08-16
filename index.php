<?php

//require_once "amonite.phar";

/** @SECURITY Anti-DDOS system */
time_nanosleep( 0 , 1 );
defined( "ROOT" ) or define( "ROOT", realpath( __DIR__ ) );

/** @SECURITY Catch errors */
require_once ROOT . "/libraries/CustomException.class.php";
CustomException::set_error_handler();
CustomException::set_fatal_handler();

/** @APPLICATION Function, Content, Database */
require_once ROOT . "/libraries/Main.function.php";
require_once ROOT . "/libraries/Request.class.php";
require_once ROOT . "/libraries/Response.class.php";
require_once ROOT . "/libraries/Controller.class.php";
require_once ROOT . "/libraries/ModelBSON.class.php";
require_once ROOT . "/libraries/ModelPDO.class.php";
require_once ROOT . "/libraries/Component.class.php";
require_once ROOT . "/libraries/Document.class.php";
require_once ROOT . "/libraries/DownloadFile.class.php";

Response::send( function( Request $req, Response $res ) {

	HttpCode::$DEBUG_MODE = $req::isLocal();
	$sub = $req::getSubWebsiteName( "main" );

	$req->env = array(
		"theme" => realpath( ROOT . "/themes/" . $sub ),
		"documents" => realpath( ROOT . "/templates/documents" ),
		"components" => realpath( ROOT . "/templates/components" ),
		"models" => realpath( ROOT . "/datas/models" ),
		"datas" => realpath( ROOT . "/datas/files" ),
		"libraries" => realpath( ROOT . "/libraries" )
	);

	try {
		return Controller::auto();
	}
	catch( Throwable $t ) {
		return $t;
	}
} );

