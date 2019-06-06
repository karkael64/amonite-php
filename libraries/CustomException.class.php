<?php

namespace Amonite;

if( !class_exists( "Amonite\\CustomException" ) ) {

	abstract class CustomException extends \Exception {

		public function __construct( $message = "", $code = 0, $previous = null, $filename = null, $line = null ) {

			if( !is_null( $filename ) ) $this->file = $filename;
			if( !is_null( $line ) ) $this->line = $line;
			if( !( $previous instanceof \Throwable ) and !( $previous instanceof \Exception ) )
				$previous = null;

			parent::__construct( $message, $code, $previous );
		}

		protected static $is_set_error = false;
		protected static $is_set_fatal = false;

		static function error_handler( $error_number, $error_message, $filename, $line ) {
			switch( $error_number ) {
				case E_ERROR:
					throw new ErrorException( $error_message, $error_number, null, $filename, $line );
					break;
				case E_WARNING:
					throw new ParseException( $error_message, $error_number, null, $filename, $line );
					break;
				case E_NOTICE:
					throw new NoticeException( $error_message, $error_number, null, $filename, $line );
					break;
				case E_CORE_ERROR:
					throw new CoreErrorException( $error_message, $error_number, null, $filename, $line );
					break;
				case E_CORE_WARNING:
					throw new CoreWarningException( $error_message, $error_number, null, $filename, $line );
					break;
				case E_COMPILE_ERROR:
					throw new CompileErrorException( $error_message, $error_number, null, $filename, $line );
					break;
				case E_COMPILE_WARNING:
					throw new CompileWarningException( $error_message, $error_number, null, $filename, $line );
					break;
				case E_USER_ERROR:
					throw new UserErrorException( $error_message, $error_number, null, $filename, $line );
					break;
				case E_USER_WARNING:
					throw new UserWarningException( $error_message, $error_number, null, $filename, $line );
					break;
				case E_USER_NOTICE:
					throw new UserNoticeException( $error_message, $error_number, null, $filename, $line );
					break;
				case E_STRICT:
					throw new StrictException( $error_message, $error_number, null, $filename, $line );
					break;
				case E_RECOVERABLE_ERROR:
					throw new RecoverableErrorException( $error_message, $error_number, null, $filename, $line );
					break;
				case E_DEPRECATED:
					throw new DeprecatedException( $error_message, $error_number, null, $filename, $line );
					break;
				case E_USER_DEPRECATED:
					throw new UserDeprecatedException( $error_message, $error_number, null, $filename, $line );
					break;
				default:
					throw new ErrorException( $error_message, $error_number, null, $filename, $line );
			}
		}

		static function set_error_handler( $error_types = E_ALL ) {
			if( !self::isset_error_handler() ) {
				self::$is_set_error = true;
				return set_error_handler( array( "Exception", "error_handler" ), $error_types );
			}
			return false;
		}

		static function restore_error_handler() {
			self::$is_set_error = false;
			return restore_error_handler();
		}

		static function isset_error_handler() {
			return self::$is_set_error;
		}

		static function fatal_error_handler() {

			if( $error = error_get_last() ) {
				$type = $error[ "type" ];
				$msg = $error[ "message" ];
				$file = $error[ "file" ];
				$line = $error[ "line" ];

				$fatal = new FatalException( $msg, $type, null, $file, $line );
				Response::send( $fatal );
			}
		}

		static function set_fatal_handler() {
			if( !self::$is_set_fatal ) {
				self::$is_set_fatal = true;
				ini_set( "display_errors", 0 );
				register_shutdown_function( array( "Exception", "fatal_error_handler" ) );
			}
		}

		static function isset_fatal_handler() {
			return self::$is_set_fatal;
		}
	}

	if( !class_exists( "Amonite\\ErrorException" ) ) {
		class ErrorException extends CustomException {
		}
	}
	if( !class_exists( "Amonite\\WarningException" ) ) {
		class WarningException extends CustomException {
		}
	}
	if( !class_exists( "Amonite\\ParseException" ) ) {
		class ParseException extends CustomException {
		}
	}
	if( !class_exists( "Amonite\\NoticeException" ) ) {
		class NoticeException extends CustomException {
		}
	}
	if( !class_exists( "Amonite\\CoreErrorException" ) ) {
		class CoreErrorException extends CustomException {
		}
	}
	if( !class_exists( "Amonite\\CoreWarningException" ) ) {
		class CoreWarningException extends CustomException {
		}
	}
	if( !class_exists( "Amonite\\CompileErrorException" ) ) {
		class CompileErrorException extends CustomException {
		}
	}
	if( !class_exists( "Amonite\\CompileWarningException" ) ) {
		class CompileWarningException extends CustomException {
		}
	}
	if( !class_exists( "Amonite\\UserErrorException" ) ) {
		class UserErrorException extends CustomException {
		}
	}
	if( !class_exists( "Amonite\\UserWarningException" ) ) {
		class UserWarningException extends CustomException {
		}
	}
	if( !class_exists( "Amonite\\UserNoticeException" ) ) {
		class UserNoticeException extends CustomException {
		}
	}
	if( !class_exists( "Amonite\\StrictException" ) ) {
		class StrictException extends CustomException {
		}
	}
	if( !class_exists( "Amonite\\RecoverableErrorException" ) ) {
		class RecoverableErrorException extends CustomException {
		}
	}
	if( !class_exists( "Amonite\\DeprecatedException" ) ) {
		class DeprecatedException extends CustomException {
		}
	}
	if( !class_exists( "Amonite\\UserDeprecatedException" ) ) {
		class UserDeprecatedException extends CustomException {
		}
	}

	if( !class_exists( "Amonite\\FatalException" ) ) {
		class FatalException extends CustomException {
		}
	}
	if( !class_exists( "Amonite\\TypeException" ) ) {
		class TypeException extends CustomException {
		}
	}

	require_once "HttpCode.class.php";
	require_once "Response.class.php";
}
