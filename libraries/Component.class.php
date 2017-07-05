<?php

if( !class_exists( "Component" ) ) {

	require_once "Content.class.php";

	abstract class Component extends Content {

		abstract public function onCall( Request $req, Response $res );

		abstract public function getComponent( Request $req, Response $res );

		public function getInner() {

			Observer::start_chunk();

			$this->setMime( "text/html" );
			$this->setCharset( "utf-8" );

			if( Request::getArg( "component" ) === file_basename( get_class( $this ), get_class() ) ) {
				$this->onCall( Request::i(), Response::getInstance() );
			}

			$content = $this->getComponent( Request::i(), Response::getInstance() );
			return strlen( $content ) ? $content : Observer::end_chunk();
		}

		public function getContent() {

			$c = $this->getInner();
			$v = sha1( $c );
			$n = file_basename( get_class( $this ), get_class() );

			return "<div component=\"$n\" version=\"$v\">$c</div>";
		}
	}

	require_once "Request.class.php";
	require_once "Response.class.php";
	require_once "Observer.class.php";
}

