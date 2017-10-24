<?php

if( !class_exists( "Component" ) ) {

	require_once "Content.class.php";

	abstract class Component extends Content {

		abstract public function onCall( Request $req, Response $res );

		abstract public function getComponent( Request $req, Response $res );

		private function getCall() {

			if( Request::getArg( "component" ) === $this->getName() ) {
				Observer::start_chunk();
				$content = $this->onCall( Request::i(), Response::getInstance() );
				if( is_null( $content ) ) {
					$content = Observer::end_chunk();
					if( !strlen( $content ) )
						$content = null;
				}
				return $content;
			}
			return null;
		}

		public function getInner() {

			Observer::start_chunk();
			$content = $this->getComponent( Request::i(), Response::getInstance() );
			if( is_null( $content ) ) {
				$content = Observer::end_chunk();
				if( !strlen( $content ) )
					$content = null;
			}
			return $content;
		}

		public function getContent() {

			$this->setMime( "text/html" );
			$this->setCharset( "utf-8" );

			if( is_null( $c = $this->getCall() ) and is_null( $c = $this->getInner() ) )
				$c = "";

			if( !is_string( $c ) )
				$c = to_string( $c );

			$v = sha1( $c );
			$n = $this->getName();

			return "<div component=\"$n\" version=\"$v\">$c</div>";
		}

		public function getName() {

			return file_basename( get_class( $this ), get_class() );
		}
	}

	require_once "Request.class.php";
	require_once "Response.class.php";
	require_once "Observer.class.php";
}

