<?php

if( !class_exists( "HttpCode" ) ) {

	require_once "Answerable.interface.php";

	class HttpCode extends Exception implements Answerable {

		function __construct( $code = 0, $message = "", $previous = null ) {

			if( !( $previous instanceof Throwable ) and !( $previous instanceof Exception ) )
				$previous = null;

			parent::__construct( to_string( $message ), $code, $previous );

			// MIME
			if( $mime = Content::getFilenameMime( Request::getFilename() ) ) {
				$this->setMime( $this->mime = $mime );
			} elseif( count( $mimes = Request::getMimeTypes() ) ) {
				$this->setMime( $this->mime = $mimes[ 0 ] );
			}

			// CHARSET
			if( $charset = Request::getCharset() )
				$this->setCharset( $this->charset = $charset );
		}

		public static $DEBUG_MODE = true;

		// HTTP CODE LIST

		const KEY_DSC = "description";
		const KEY_MSG = "message";

		private static $http = array(
			200 => array(
				self::KEY_DSC => "OK",
				self::KEY_MSG => "Hello World!"
			),
			204 => array(
				self::KEY_DSC => "No Content",
				self::KEY_MSG => "This file has no content and can't be displayed."
			),
			304 => array(
				self::KEY_DSC => "Not Modified",
				self::KEY_MSG => "This file is not modified since your last request."
			),
			307 => array(
				self::KEY_DSC => "Temporary Redirect",
				self::KEY_MSG => "The server redirected page to another link."
			),
			308 => array(
				self::KEY_DSC => "Permanent Redirect",
				self::KEY_MSG => "The server redirected page to another link."
			),
			400 => array(
				self::KEY_DSC => "Bad Request",
				self::KEY_MSG => "The server cannot or will not process request due to an apparent request error."
			),
			403 => array(
				self::KEY_DSC => "Forbidden",
				self::KEY_MSG => "The user does not have the necessary permissions for the resource."
			),
			404 => array(
				self::KEY_DSC => "Not Found",
				self::KEY_MSG => "The resource is not reachable or does not exists."
			),
			418 => array(
				self::KEY_DSC => "I'm a Teapot",
				self::KEY_MSG => "You can brew me, I'm hot !"
			),
			500 => array(
				self::KEY_DSC => "Internal Server Error",
				self::KEY_MSG => "An unexpected condition was encountered and no specific message is suitable. Please try again or contact administrator."
			),
			503 => array(
				self::KEY_DSC => "Service Unavailable",
				self::KEY_MSG => "The server HTTP is currently unavailable. Please try again or contact administrator."
			),
			"debug" => array(
				self::KEY_DSC => "500 Debug",
				self::KEY_MSG => "No trace!"
			),
			"default" => array(
				self::KEY_DSC => "Unknown Error",
				self::KEY_MSG => "An unknown error was encountered and no specific message is suitable."
			)
		);



		public function getDefaultMessage() {
			$code = $this->getCode();
			return isset( self::$http[ $code ] ) ? self::$http[ $code ][ self::KEY_MSG ] : self::$http[ "default" ][ self::KEY_MSG ];
		}



		//	ANSWERABLE
		public function setHeader( $field, $value ) {
			Response::getInstance()->setHeader( $field, $value );
			return $this;
		}

		public function removeHeader( $field ) {
			Response::getInstance()->removeHeader( $field );
			return $this;
		}

		public function addCookie( $field, $value, $expires = NULL, $domain = NULL, $path = NULL, $secure = false, $httpOnly = false ) {
			Response::getInstance()->addCookie( $field, $value, $expires = NULL, $domain = NULL, $path = NULL, $secure = false, $httpOnly = false );
			return $this;
		}

		public function removeCookie( $field ) {
			Response::getInstance()->removeCookie( $field );
			return $this;
		}



		protected $mime = "text/plain";

		public function setMime( $mime ) {
			Response::getInstance()->setMime( $this->mime = $mime );
			return $this;
		}

		protected $charset = "utf-8";

		public function setCharset( $charset ) {
			Response::getInstance()->setCharset( $this->mime = $charset );
			return $this;
		}

		public function getContent() {
			return $this->toMime();
		}



		//	HTTP CODE
		public function getTitle() {
			$code = $this->getCode();
			return isset( self::$http[ $code ] ) ? self::$http[ $code ][ self::KEY_DSC ] : self::$http[ "default" ][ self::KEY_DSC ];
		}

		public function getDescription( $code = null ) {
			if( is_null( $code ) ) $code = $this->getCode();
			return isset( self::$http[ $code ] ) ? self::$http[ $code ][ self::KEY_MSG ] : self::$http[ "default" ][ self::KEY_MSG ];
		}

		function toString() {

			$code = $this->getCode();
			if( self::$DEBUG_MODE && $code >= self::$DEBUG_MODE ) {
				$body = "";
				$e = $this;
				do {
					$body .= exception_to_string( $e );
				} while( $e = $e->getPrevious() );
				$body = "\n$body";
			} else {
				$body = $this->getDefaultMessage();
			}
			return ( $code ) . " - " . $this->getTitle() . "\n\n" . $body;
		}

		function toHTML() {

			$code = $this->getCode();
			if( self::$DEBUG_MODE && $code >= self::$DEBUG_MODE ) {
				$body = "";
				$e = $this;
				do {
					$body .= exception_to_html( $e );
				} while( $e = $e->getPrevious() );
			} else {
				$body = "<p>" . $this->getDefaultMessage() . "</p>";
			}
			$title = $code . " - " . $this->getTitle();
			return "<!doctype html><html><head><meta charset='UTF-8'><title>$title</title></head><body><h1>$title</h1>$body</body></html>";
		}

		function toArray() {

			$code = $this->getCode();
			if( self::$DEBUG_MODE && $code >= self::$DEBUG_MODE ) {

				$errors = array();
				$e = $this;
				do {
					array_push( $errors, throwable_to_array( $e ) );
				} while( $e = $e->getPrevious() );
			} else {

				$errors = null;
			}

			return array(
				"success" => false,
				"code" => $code,
				"title" => $this->getTitle(),
				"message" => $this->getMessage(),
				"errors" => $errors
			);
		}

		function toJSON( $options = null ) {

			return json_encode( $this->toArray(), $options );
		}

		function toMime( $mime = null ) {

			if( is_null( $mime ) )
				$mime = $this->mime;

			if( $mime === "application/json" )
				return $this->toJSON();
			elseif( $mime === "text/html" )
				return $this->toHTML();
			else
				return $this->toString();
		}

		static function getWrapped( $el ) {

			if( $el instanceof self )
				return $el;

			if( $el instanceof Throwable or $el instanceof Exception )
				return new HttpCode( 500, "Throwable catched.", $el );

			try {

				while( $el instanceof Closure or $el instanceof Answerable ) {

					if( $el instanceof Closure )
						$el = call_user_func_array( $el, array( Request::getInstance(), Response::getInstance() ) );

					if( $el instanceof Answerable )
						$el = $el->getContent();
				}

				if( !strlen( $el ) )
					return new HttpCode( 204, "" );
				if( sha1( $el ) === Request::getEtag() )
					return new HttpCode( 304, "" );
				else
					return new HttpCode( 200, $el );
			}
			catch( HttpCode $el ) {
				ob_get_clean();
				return $el;
			}
			catch( Throwable $el ) {
				ob_get_clean();
				return new HttpCode( 500, "Throwable catched.", $el );
			}
			catch( Exception $el ) {
				ob_get_clean();
				return new HttpCode( 500, "Throwable catched.", $el );
			}
		}

		static function send( $el ) {
			Response::getInstance()->sendAnswerable( self::getWrapped( $el ) );
		}
	}

	require_once "Content.class.php";
}

