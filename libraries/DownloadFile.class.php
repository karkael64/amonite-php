<?php

if( !class_exists( "DownloadFile" ) ) {

	require_once "File.class.php";

	class DownloadFile extends File {

		function __construct( $filename, $data = null ) {

			parent::__construct( $filename, $data );
			$this->setHeader( "Content-Disposition", "inline; filename=" . basename( $filename ) );
		}
	}
}

