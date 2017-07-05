<?php

if( !class_exists( "DownloadFile" ) ) {

	require_once "File.class.php";

	class DownloadFile extends File {

		function __construct( $filename ) {

			parent::__construct( $filename );
			$this->setHeader( "Content-Disposition", "inline; filename=" . basename( $filename ) );
		}
	}
}

