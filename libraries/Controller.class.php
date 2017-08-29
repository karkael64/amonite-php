<?php

if( !class_exists( "Controller" ) ) {

	class Controller {

		const NOT = 0;

		const CTRL_FILE = 0x001;
		const CTRL_PHP = 0x002;
		const CTRL_HIDDEN = 0x004;
		const CTRL_NOT_FOUND = 0x008;
		const CTRL_JOKE = 0x010;

		protected $fn_do;
		protected $fn_priority;

		function __construct( $fn_do, $fn_priority ) {

			if( is_callable( $fn_do ) ) {
				$this->fn_do = $fn_do;
			}
			if( is_callable( $fn_priority ) or is_numeric( $fn_priority ) ) {
				$this->fn_priority = $fn_priority;
			} else {
				$this->fn_priority = NULL;
			}
		}

		function getPriority( $args_where = NULL ) {

			if( is_callable( $this->fn_priority ) )
				return +call_user_func_array( $this->fn_priority, $args_where );
			elseif( is_numeric( $args_where ) )
				return +$this->fn_priority;
			else
				return self::NOT;
		}

		function launch( $args = NULL ) {

			if( is_callable( $this->fn_do ) )
				return call_user_func_array( $this->fn_do, $args );
			else
				throw new Error( "This is not a function." );
		}


		private static $list = array();

		static function register( Controller $c ) {
			array_push( self::$list, $c );
		}

		static function main( $args_where = null, $args = null ) {
			return self::launchHigherPriorityController( $args_where, $args );
		}

		static function launchHigherPriorityController( $args_where = null, $args = null ) {
			if( !is_array( $args ) and is_array( $args_where ) )
				$args = $args_where;

			if( ( $controller = self::getHigherPriorityController( $args_where ) ) instanceof self ) {
				return $controller->launch( $args );
			} else {
				throw new Error( "Not any controller as priority higher than 0." );
			}
		}

		private static function getHigherPriorityController( $args_where = null ) {
			$max = self::NOT;
			$selected = null;
			foreach( self::$list as $controller ) {
				if( $controller instanceof self ) {
					if( ( $p = $controller->getPriority( $args_where ) ) >= $max ) {
						$max = $p;
						$selected = $controller;
					}
				}
			}
			return $selected;
		}

		static function autoRegister( $mode = self::CTRL_FILE ) {


			/*
			 * $fileController is a controller
			 *  -   which read a file
			 *  -   if file does not end with .php nor .phtml
			 */
			if( $mode & self::CTRL_JOKE )
				self::register( $fileController = new self( function( Request $req, Response $res ) {
					return new HttpCode( 418 );
				}, function( Request $req, Response $res ) {
					return $req::getMethod() === "BREW";
				} ) );


			/*
			 * $fileController is a controller
			 *  -   which read a file
			 *  -   if file does not end with .php nor .phtml
			 */
			if( $mode & self::CTRL_FILE )
				self::register( $fileController = new self( function( Request $req, Response $res ) {
					$filename = $req->env->theme . $req->file;
					return new File( $filename );
				}, function( $req, $res ) {
					$filename = $req->env->theme . $req->file;
					return !preg_match( '/(\.php|\.phtml)$/', $filename ) and file_exists( $filename ) and !is_dir( $filename );
				} ) );


			/*
			 * $execController is a controller
			 *  -   which execute a file
			 *  -   if file ends with .php or .phtml
			 */
			if( $mode & self::CTRL_PHP )
				self::register( $execController = new self( function( Request $req, Response $res ) { // do execute a file
					$filename = $req->env->theme . $req->file;
					return File::execFile( $filename, array( "request" => $req, "response" => $res ) );
				}, function( $req, $res ) { // if
					$filename = $req->env->theme . $req->file;
					return preg_match( '/(\.php|\.phtml)$/', $filename ) and file_exists( $filename ) and !is_dir( $filename );
				} ) );


			/*
			 * $hiddenExecController is a controller
			 *  -   which execute a file name with .php at the end
			 *  -   if file does not exists but his name with .php at the end exists
			 */
			if( $mode & self::CTRL_HIDDEN )
				self::register( $hiddenExecController = new self( function( Request $req, Response $res ) {
					$filename = $req->env->theme . $req->file . ".php";
					return File::execFile( $filename, array( "request" => $req, "response" => $res ) );
				}, function( $req, $res ) {
					$filename = $req->env->theme . $req->file . ".php";
					return file_exists( $filename ) and !is_dir( $filename );
				} ) );


			/*
			 * $defaultController is a controller
			 *  -   which throw "404 Not Found"
			 *  -   if no other controller match
			 */
			if( $mode & self::CTRL_NOT_FOUND )
				self::register( $defaultController = new self( function( Request $req, Response $res ) {
					return new HttpCode( 404, $req->file );
				}, true ) );

		}

		static function autoCatch() {

			try {
				$answer = Controller::main( array( Request::getInstance(), Response::getInstance() ) );
			}
			catch( Throwable $answer ) {}

			return $answer;
		}

		static function auto( $mode = self::CTRL_FILE ) {

			self::autoRegister( $mode );
			return self::autoCatch();
		}
	}

	require_once "Request.class.php";
	require_once "Response.class.php";
	require_once "HttpCode.class.php";
	require_once "File.class.php";
}

