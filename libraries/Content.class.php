<?php

if( !class_exists( "Content" ) ) {

	require_once "Answerable.interface.php";

	abstract class Content implements Answerable {

		protected $mime = "text/plain";
		protected $charset = "utf-8";

		function __construct() {

			// MIME
			if( $mime = self::getFilenameMime( Request::getFilename() ) )
				$this->setMime( $mime );
			elseif( count( $mimes = Request::getMimeTypes() ) )
				$this->setMime( $mimes[ 0 ] );

			// CHARSET
			if( $charset = Request::getCharset() )
				$this->setCharset( $charset );
		}

		private static $MIME_TYPES = [
			"js" => "application/javascript", "mp3" => "audio/mpeg", "gif" => "image/gif", "mpg" => "video/mpeg", "css" => "text/css",
			"ogg" => "application/ogg", "wma" => "audio/x-ms-wma", "jpg" => "image/jpeg", "mpeg" => "video/mpeg", "csv" => "text/csv",
			"pdf" => "application/pdf", "ra" => "audio/vnd.rn-realaudio", "jpeg" => "image/jpeg", "mp4" => "video/mp4", "htm" => "text/html",
			"xhtml" => "application/xhtml+xml", "rm" => "audio/vnd.rn-realaudio", "png" => "image/png", "mov" => "video/quicktime", "html" => "text/html",
			"swf" => "application/x-shockwave-flash", "smil" => "audio/vnd.rn-realaudio", "tiff" => "image/tiff", "wmv" => "video/x-ms-wmv", "phtml" => "text/html",
			"json" => "application/json", "ram" => "audio/vnd.rn-realaudio", "ico" => "image/vnd.microsoft.icon", "avi" => "video/x-msvideo", "txt" => "text/plain",
			"xml" => "application/xml", "rmvb" => "audio/vnd.rn-realaudio", "djv" => "image/vnd.djvu", "flv" => "video/x-flv",
			"zip" => "application/zip", "rv" => "audio/vnd.rn-realaudio", "djvu" => "image/vnd.djvu", "webm" => "video/webm",
			"wav" => "audio/x-wav", "svg" => "image/svg+xml",
			"odt" => "application/vnd.oasis.opendocument.text",
			"ods" => "application/vnd.oasis.opendocument.spreadsheet",
			"odp" => "application/vnd.oasis.opendocument.presentation",
			"odg" => "application/vnd.oasis.opendocument.graphics",
			"odc" => "application/vnd.oasis.opendocument.chart",
			"odf" => "application/vnd.oasis.opendocument.formula",
			"odb" => "application/vnd.oasis.opendocument.database",
			"odi" => "application/vnd.oasis.opendocument.image",
			"odm" => "application/vnd.oasis.opendocument.text-master",
			"doc" => "application/msword",
			"dot" => "application/msword",
			"docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
			"dotx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.template",
			"docm" => "application/vnd.ms-word.document.macroEnabled.12",
			"dotm" => "application/vnd.ms-word.template.macroEnabled.12",
			"xls" => "application/vnd.ms-excel",
			"xlt" => "application/vnd.ms-excel",
			"xla" => "application/vnd.ms-excel",
			"xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
			"xltx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.template",
			"xlsm" => "application/vnd.ms-excel.sheet.macroEnabled.12",
			"xltm" => "application/vnd.ms-excel.template.macroEnabled.12",
			"xlam" => "application/vnd.ms-excel.addin.macroEnabled.12",
			"xlsb" => "application/vnd.ms-excel.sheet.binary.macroEnabled.12",
			"ppt" => "application/vnd.ms-powerpoint",
			"pot" => "application/vnd.ms-powerpoint",
			"pps" => "application/vnd.ms-powerpoint",
			"ppa" => "application/vnd.ms-powerpoint",
			"pptx" => "application/vnd.openxmlformats-officedocument.presentationml.presentation",
			"potx" => "application/vnd.openxmlformats-officedocument.presentationml.template",
			"ppsx" => "application/vnd.openxmlformats-officedocument.presentationml.slideshow",
			"ppam" => "application/vnd.ms-powerpoint.addin.macroEnabled.12",
			"pptm" => "application/vnd.ms-powerpoint.presentation.macroEnabled.12",
			"potm" => "application/vnd.ms-powerpoint.template.macroEnabled.12",
			"ppsm" => "application/vnd.ms-powerpoint.slideshow.macroEnabled.12",
		];

		private static $MIME_PICTURES = [
			"gif" => "image/gif",
			"jpg" => "image/jpeg",
			"jpeg" => "image/jpeg",
			"png" => "image/png",
			"tiff" => "image/tiff",
			"ico" => "image/vnd.microsoft.icon",
			"djv" => "image/vnd.djvu",
			"djvu" => "image/vnd.djvu"
		];

		static function getFilenameMime( $filename ) {

			$e = pathinfo( strtolower( $filename ), PATHINFO_EXTENSION );
			return ( isset( self::$MIME_TYPES[ $e ] ) ? self::$MIME_TYPES[ $e ] : "text/plain" );
		}

		static function isFilenamePicture( $filename ) {

			return (
				isset( self::$MIME_PICTURES[ $filename ] ) ||
				isset( self::$MIME_PICTURES[ pathinfo( strtolower( $filename ), PATHINFO_EXTENSION ) ] )
			);
		}


		/* HEADER */

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
			Response::getInstance()->addCookie( $field, $value, $expires, $domain, $path, $secure, $httpOnly );
			return $this;
		}

		public function removeCookie( $field ) {
			Response::getInstance()->removeCookie( $field );
			return $this;
		}

		public function setMime( $mime ) {
			Response::getInstance()->setMime( $this->mime = $mime );
			return $this;
		}

		public function setCharset( $charset ) {
			Response::getInstance()->setCharset( $this->charset = $charset );
			return $this;
		}

		abstract public function getContent();


		/* GET CONTENT */

		public function __toString() {

			try {
				return $this->getContent();
			} catch( Throwable $e ) {
				return throwable_to_mime( $e, $this->mime );
			} catch( Exception $e ) {
				return throwable_to_mime( $e, $this->mime );
			}
		}
	}

	require_once "Request.class.php";
	require_once "Response.class.php";
}

