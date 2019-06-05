<?php

if( !class_exists( "Response" ) ) {

	class Response {

		private static $instance;

		function __construct() {

			$cookie = Request::getCookie();
			foreach( $cookie as $field => $value ) {
				$this->addCookie( $field, $value );
			}
		}

		static function getInstance() {

			if( self::$instance instanceof self )
				return self::$instance;
			else
				return self::$instance = new self;
		}

		static function i() {
			return self::getInstance();
		}

		private function sendHeader( $code, $title, $etag, $len ) {

			header( Request::i()->PROTOCOL . " $code $title", true, $code );

			$this->setHeader( "Etag", $etag );

			foreach( $this->header as $field => $header ) {
				if( $field !== "Set-cookie" )
					header( $field . ": " . $header, true );
			}

			header( "Content-Type: " . $this->mime . "; charset=" . $this->charset, true );
			header( "Content-length: " . $len );

			foreach( $this->cookie as $cookie )
				setcookie( $cookie[ 0 ], $cookie[ 1 ], $cookie[ 2 ], $cookie[ 3 ], $cookie[ 4 ], $cookie[ 5 ], $cookie[ 6 ] );

			return $this;
		}

		private function sendBody( $content ) {

			die( $content );
			return $this;
		}

		public function sendAnswerable( Answerable $el ) {

			$this->sendHttpCode( HttpCode::getWrapped( $el ) );
		}

		public function sendHttpCode( HttpCode $el ) {

			if( ( $code = $el->getCode() ) < 300 )
				$content = $el->getMessage();
			else
				$content = to_string( $el->getContent() );

			if( $code === 307 || $code === 308 ) {
				$this->setHeader( "Location", $el->getMessage() );
			}

			$title = $el->getTitle();
			$etag = sha1( $content );
			$len = strlen( $content );

			if( Request::getMethod() === "HEAD" ) {
				return $this->sendHeader( $code, $title, $etag, $len )
					->sendBody( "" );
			}
			else {
				return $this->sendHeader( $code, $title, $etag, $len )
					->sendBody( $content );
			}
		}

		static function send( $el ) {

			return self::getInstance()->sendHttpCode( HttpCode::getWrapped( $el ) );
		}


		// Header config

		private $header = array();
		private $cookie = array();
		private $mime = "text/plain";
		private $charset = "utf-8";

		function setHeader( $field, $value ) {
			$field = to_string_field( $field );
			$this->header[ $field ] = $value;
			return $this;
		}

		function removeHeader( $field ) {
			$field = to_string_field( $field );
			unset( $this->header[ $field ] );
			return $this;
		}

		function addCookie( $field, $value, $expires = NULL, $domain = NULL, $path = NULL, $secure = false, $httpOnly = false ) {
			$this->cookie[ $field ] = array( $field, $value, $expires, $domain, $path, $secure, $httpOnly );
			return $this;
		}

		function removeCookie( $field ) {
			$this->cookie[ $field ][ 1 ] = null;
			$this->cookie[ $field ][ 1 ] = -1;
			return $this;
		}

		function setMime( $mime ) {
			$this->mime = $mime;
			return $this;
		}

		function setCharset( $charset ) {
			$this->charset = $charset;
			return $this;
		}
	}

	require_once "Request.class.php";
	require_once "HttpCode.class.php";
	require_once "Answerable.interface.php";
	require_once "Main.function.php";
}

