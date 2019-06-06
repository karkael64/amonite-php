<?php

require_once "index.php";
Amonite\CustomException::set_error_handler();
Amonite\CustomException::set_fatal_handler();

Amonite\Response::send( function( Amonite\Request $req, Amonite\Response $res ) {

	Amonite\HttpCode::$DEBUG_MODE = $req::isLocal();
	$sub = $req::getSubWebsiteName( "main" );

	Amonite\Controller::$theme_path = realpath( ROOT . "/themes/" . $sub );
	Amonite\ModelBSON::$datafiles_path = realpath( ROOT . "/datas/files" );

	$req->env = array(
		"theme" => realpath( ROOT . "/themes/" . $sub ),
		"documents" => realpath( ROOT . "/templates/documents" ),
		"components" => realpath( ROOT . "/templates/components" ),
		"models" => realpath( ROOT . "/datas/models" ),
		"datas" => realpath( ROOT . "/datas/files" ),
		"libraries" => realpath( ROOT . "/libraries" )
	);

	return Amonite\Controller::auto( Amonite\Controller::ALL_CTRL );
} );
