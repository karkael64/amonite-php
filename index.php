<?php

require_once "amonite.phar";

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

	return Controller::auto();
} );

