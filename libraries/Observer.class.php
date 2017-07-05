<?php

if( !class_exists( "Observer" ) ) {

	abstract class Observer {

		protected static $obs = array();

		static function start_chunk() {
			$el = end( self::$obs );
			$el .= self::get_chunk();
			self::$obs[ key( self::$obs ) ] = $el;
			self::$obs[] = "";
		}

		static function end_chunk() {
			$el = end( self::$obs );
			$el .= self::get_chunk();
			unset( self::$obs[ key( self::$obs ) ] );
			return $el;
		}

		private static function get_chunk() {
			$c = ob_get_contents();
			ob_clean();
			return $c;
		}
	}

	ob_start( null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_REMOVABLE );
	Observer::start_chunk();
}

