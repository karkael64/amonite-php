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

		static function getAnswerable( $el ) {

			if( $el instanceof Answerable )
				return $el;

			elseif( $el instanceof Throwable )
				return new HttpCode( 500, "Throwable catched.", $el );

			elseif( $el instanceof Exception )
				return new HttpCode( 500, "Throwable catched.", $el );

			elseif( is_callable( $el ) )
				return self::getAnswerable( call_user_func_array( $el, array( Request::getInstance(), Response::getInstance() ) ) );

			elseif( is_string( $el = to_string( $el ) ) ) {
				if( !strlen( $el ) )
					return new HttpCode( 204, "" );
				if( sha1( $el ) === Request::getEtag() )
					return new HttpCode( 304, "" );
				else
					return new HttpCode( 200, $el );
			}

			else
				return ( new HttpCode( 500, new TypeException( "Parameter is not Answerable object nor can be casted to a String." ) ) );
		}


		private function sendHeader( $code, $title, $etag, $len ) {

			header( Request::i()->PROTOCOL . " $code $title", true, $code );

			if( !Request::denyCache() ) {
				$this->setHeader( "Etag", $etag );
			}

			foreach( $this->header as $field => $header ) {
				if( $field !== "Set-cookie" )
					header( $field . ": " . $header, true );
			}

			header( "Content-Type: " . $this->mime . "; charset=" . $this->charset, true );
			header( "Content-length: " . $len );

			if( !Request::denyCookie() ) {
				foreach( $this->cookie as $cookie )
					setcookie( $cookie[ 0 ], $cookie[ 1 ], $cookie[ 2 ], $cookie[ 3 ], $cookie[ 4 ], $cookie[ 5 ], $cookie[ 6 ] );
			}

			return $this;
		}

		private function sendBody( $content ) {

			die( $content );
			return $this;
		}

		public function sendAnswerable( Answerable $el ) {

			if( $el instanceof HttpCode ) {

				if( ( $code = $el->getCode() ) < 300 )
					$content = $el->getMessage();
				else
					$content = to_string( $el->getContent() );
			}
			else {

				$content = to_string( $el->getContent() );

				if( $content === "" )
					$el = new HttpCode( $code = 204, "" );
				elseif( sha1( $content ) === Request::getEtag() )
					$el = new HttpCode( $code = 304, "" );
				else
					$el = new HttpCode( $code = 200, $content );
			}

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

			try {
				return self::getInstance()->sendAnswerable( self::getAnswerable( $el ) );
			}
			catch( Throwable $answer ) {
				ob_clean();
				return self::getInstance()->sendAnswerable( self::getAnswerable( $answer ) );
			}
			catch( Exception $answer ) {
				ob_clean();
				return self::getInstance()->sendAnswerable( self::getAnswerable( $answer ) );
			}
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
			unset( $this->cookie[ $field ] );
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

