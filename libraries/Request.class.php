<?php

if( !class_exists( "Request" ) ) {

	require_once "Constants.class.php";

	class Request extends Constants {

		private static $INSTANCE = NULL;

		function __construct() {

			global $argc, $argv;

			// ETAG
			if( isset( $_SERVER[ "HTTP_IF_NONE_MATCH" ] ) ) $etag = $_SERVER[ "HTTP_IF_NONE_MATCH" ];
			elseif( isset( $_SERVER[ "IF_MODIFIED_SINCE" ] ) ) $etag = $_SERVER[ "IF_MODIFIED_SINCE" ];
			else $etag = NULL;

			// FILE
			if( isset( $argv ) && is_array( $argv ) && ( $k = array_search( "-f", $argv ) ) ) $file = @$argv[ $k + 1 ];
			elseif( isset( $_SERVER[ "REQUEST_URI" ] ) ) $file = $_SERVER[ "REQUEST_URI" ];
			elseif( isset( $_SERVER[ "REDIRECT_URL" ] ) ) $file = $_SERVER[ "REDIRECT_URL" ];
			else $file = $_SERVER[ "SCRIPT_NAME" ];
			$file = preg_replace( '/\/\.\.\//', '/', preg_replace( '/\/$/', '/index.html', preg_replace( '/(\?|#).*$/', '', $file ) ) );

			$argc = is_integer( $argc ) ? $argc : 0;
			$argv = is_array( $argv ) ? $argv : array();
			$all_args = array_merge( $argv, $_GET, $_POST, $_FILES );

			$array = array(
				"METHOD" => isset( $_SERVER[ "REQUEST_METHOD" ] ) ? $_SERVER[ "REQUEST_METHOD" ] : "GET",
				"HOST" => isset( $_SERVER[ "HTTP_HOST" ] ) ? $_SERVER[ "HTTP_HOST" ] : "127.0.0.1",
				"FILE" => $file,
				"ETAG" => $etag,
				"TIME" => isset( $_SERVER[ "REQUEST_TIME_FLOAT" ] ) ? $_SERVER[ "REQUEST_TIME_FLOAT" ] : isset( $_SERVER[ "REQUEST_TIME" ] ) ? $_SERVER[ "REQUEST_TIME" ] : microtime( true ),
				"PROTOCOL" => isset( $_SERVER[ "SERVER_PROTOCOL" ] ) ? $_SERVER[ "SERVER_PROTOCOL" ] : "HTTP1/0",
				"ARGUMENTS" => array(
					"ARGC" => $argc,
					"ARGV" => $argv,
					"GET" => $_GET,
					"POST" => $_POST,
					"FILES" => $_FILES,
					"COOKIE" => $_COOKIE,
					"ALL" => $all_args
				),
				"BODY" => file_get_contents( "php://input" ),
				"USER" => array(
					"AGENT" => isset( $_SERVER[ "HTTP_USER_AGENT" ] ) ? $_SERVER[ "HTTP_USER_AGENT" ] : NULL,
					"LANGUAGE" => isset( $_SERVER[ "HTTP_ACCEPT_LANGUAGE" ] ) ? $_SERVER[ "HTTP_ACCEPT_LANGUAGE" ] : NULL,
					"CHARSET" => isset( $_SERVER[ "HTTP_ACCEPT_CHARSET" ] ) ? $_SERVER[ "HTTP_ACCEPT_CHARSET" ] : NULL,
					"ENCODING" => isset( $_SERVER[ "HTTP_ACCEPT_ENCODING" ] ) ? $_SERVER[ "HTTP_ACCEPT_ENCODING" ] : NULL,
					"ADDRESS" => isset( $_SERVER[ "REMOTE_ADDR" ] ) ? $_SERVER[ "REMOTE_ADDR" ] : NULL,
					"PORT" => isset( $_SERVER[ "REMOTE_PORT" ] ) ? +$_SERVER[ "REMOTE_PORT" ] : NULL,
					"MIME" => isset( $_SERVER[ "HTTP_ACCEPT" ] ) ? $_SERVER[ "HTTP_ACCEPT" ] : NULL
				),
				"DENY" => array(
					"JS" => false,
					"CACHE" => false,
					"COOKIE" => false,
					"GEO" => false
				)
			);

			parent::__construct( $array );
		}

		static function getInstance() {
			if( self::$INSTANCE )
				return self::$INSTANCE;
			else
				return self::$INSTANCE = new self();
		}

		static function i() {
			return self::getInstance();
		}

		static function getMethod() {
			return self::i()->METHOD;
		}

		static function getFilename() {
			return self::i()->FILE;
		}

		static function getArray() {
			return self::i()->toArray();
		}

		static function denyJS() {
			return self::i()->DENY->JS;
		}

		static function denyCookie() {
			return self::i()->DENY->COOKIE;
		}

		static function denyCache() {
			return self::i()->DENY->CACHE;
		}

		static function denyGeo() {
			return self::i()->DENY->GEO;
		}

		static function getMimeTypes() {
			preg_match_all( '/[^,;= ]+\/[^,;= ]+/', self::i()->USER->MIME, $all );
			return $all[ 0 ];
		}

		static function getCharset() {
			return self::i()->USER->CHARSET;
		}

		static function getEtag() {
			return self::i()->ETAG;
		}

		static function getCookie() {
			return self::i()->ARGUMENTS->COOKIE;
		}


		static function getGET( $field = null ) {
			if( is_null( $field ) )
				return self::i()->ARGUMENTS->GET;
			else
				return self::i()->ARGUMENTS->GET->__get( $field );
		}

		static function getPOST( $field = null ) {
			if( is_null( $field ) )
				return self::i()->ARGUMENTS->POST;
			else
				return self::i()->ARGUMENTS->POST->__get( $field );
		}

		static function getFILES( $field = null ) {
			if( is_null( $field ) )
				return self::i()->ARGUMENTS->FILES;
			else
				return self::i()->ARGUMENTS->FILES->__get( $field );
		}

		static function getArg( $field = null ) {
			if( is_null( $field ) )
				return self::i()->ARGUMENTS->ALL;
			else
				return self::i()->ARGUMENTS->ALL->__get( $field );
		}


		/* URL */

		static function getSubWebsiteName( $default = "www" ) {
			$host = self::i()->host;

			if( preg_match( '/^(.+)\.[^\.]+\.[a-zA-Z]+$/', $host ) )
				return preg_replace( '/^(.+)\.[^\.]+\.[a-zA-Z]+$/', '$1', $host );
			else
				return $default;
		}

		static function isLocal() {
			$a = self::i()->USER->ADDRESS;
			return $a === "127.0.0.1" or $a === "::1";
		}


		/* IMPORTS */


		static function isFileRunnable( $filename ) {
			return !!preg_match( '/(\.php|\.phtml)$/', $filename );
		}

		private static function _isFileExists( $folder, $name, $type ) {

			if( !$folder or !file_exists( $folder ) or !is_dir( $folder ) )
				return false;

			$file = $folder . "/$name$type.php";
			if( !file_exists( $file ) )
				$file = $folder . "/$name.$type.php";
			if( !file_exists( $file ) )
				$file = $folder . "/$name";
			if( !file_exists( $file ) )
				return false;

			return true;
		}

		static function isFileThemeExists( $name ) {
			return self::_isFileExists( self::i()->env->theme, $name, "Theme" );
		}

		static function isFileDocumentExists( $name ) {
			return self::_isFileExists( self::i()->env->documents, $name, "Document" );
		}

		static function isFileComponentExists( $name ) {
			return self::_isFileExists( self::i()->env->components, $name, "Component" );
		}

		static function isFileLibraryExists( $name ) {
			return self::_isFileExists( self::i()->env->libreries, $name, "Library" );
		}

		static function isFileModelExists( $name ) {
			return self::_isFileExists( self::i()->env->models, $name, "Model" );
		}


		private static function _require( $folder, $name, $type ) {

			if( !$folder or !file_exists( $folder ) or !is_dir( $folder ) )
				throw new Exception( "Folder $type not defined in environment request constants." );

			$file = $folder . "/$name$type.php";
			if( !file_exists( $file ) )
				$file = $folder . "/$name.$type.php";
			if( !file_exists( $file ) )
				$file = $folder . "/$name";
			if( !file_exists( $file ) )
				throw new Exception( "File \"$name\" not defined in folder $type." );

			return require_once $file;
		}

		static function getTheme( $name ) {
			return self::_require( self::i()->env->theme, $name, "Theme" );
		}

		static function getDocument( $name ) {
			return self::_require( self::i()->env->documents, $name, "Document" );
		}

		static function getComponent( $name ) {
			return self::_require( self::i()->env->components, $name, "Component" );
		}

		static function getLibrary( $name ) {
			return self::_require( self::i()->env->libraries, $name, "Class" );
		}

		static function getModel( $name ) {
			return self::_require( self::i()->env->models, $name, "Model" );
		}
	}
}

