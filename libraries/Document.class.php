<?php

if( !class_exists( "Document" ) ) {

	require_once "Content.class.php";

	abstract class Document extends Content {

		abstract function getDocument( Request $req, Response $res );

		public function getContent() {

			Observer::start_chunk();

			$this->setMime( "text/html" );
			$this->setCharset( "utf-8" );

			$content = $this->getDocument( Request::getInstance(), Response::getInstance() );
			return strlen( $content ) ? $content : Observer::end_chunk();
		}
	}

	require_once "Request.class.php";
	require_once "Response.class.php";
	require_once "Observer.class.php";
}

