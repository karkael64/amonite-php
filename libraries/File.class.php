<?php

if( !class_exists( "File" ) ) {

	require_once "Content.class.php";

	class File extends Content {

		private $filename;

		function __construct( $filename ) {

			parent::__construct();

			$this->filename = $filename;
			$this->setMime( Content::getFilenameMime( $filename ) );
			$this->setHeader( "Content-Disposition", "inline; filename=" . basename( $filename ) );
		}


		function get() {
			return file_get_contents( $this->filename );
		}

		function set( $text ) {
			file_put_contents( $this->filename, $text );
			return $this;
		}


		function append( $text ) {
			$this->set( $this->get() . $text );
			return $this;
		}

		function prepend( $text ) {
			$this->set( $text . $this->get() );
			return $this;
		}

		function wrap( $before, $after ) {
			$this->set( $before . $this->get() . $after );
			return $this;
		}


		function exists() {
			return file_exists( $this->filename );
		}

		function getFilename() {
			return $this->filename;
		}

		function length() {
			return filesize( $this->filename );
		}

		function getContent() {
			return $this->get();
		}

		static function execFile( $filename, $args = NULL ) {

			try {
				ob_start();
				if( is_array( $args ) ) {
					if( isset( $args[ "filename" ] ) )
						unset( $args[ "filename" ] );
					extract( $args );
				}
				$r = require $filename;
				$o = ob_get_clean();
				return ( $r === 1 ) ? $o : $r;
			} catch( Exception $e ) {
				ob_clean();
				throw $e;
			}
		}
	}
}

