<?php

// STRINGIFY

if( !function_exists( "to_string" ) ) {

	/**
	 * @function to_string is used to translate every item to string.
	 * @param string|bool|int|double|float|array|callable|object|null $el is the parameter to stringify.
	 * @return string is the translate of bool, null, array to JSON, recursive call function until it does not return a
	 *              function, call object __toString function or set it between chevrons.
	 * @throws Exception if parameter is not a string, bool, int, double, float, array, callable, object or null.
	 */

	function to_string( $el ) {

		if( is_string( $el ) )
			return $el;
		elseif( is_bool( $el ) )
			return $el ? "true" : "false";
		elseif( is_numeric( $el ) )
			return "" . $el;
		elseif( is_null( $el ) )
			return "null";
		elseif( is_array( $el ) )
			return json_encode( $el );
		elseif( is_callable( $el ) )
			return to_string( call_user_func( $el, Request::getInstance(), Response::getInstance() ) );
		elseif( is_object( $el ) ) {
			if( key_exists( "__toString", get_class_methods( $el ) ) ) {
				return "" . $el;
			} else {
				return "<Object:" . get_class( $el ) . ">";
			}
		} else
			throw new Exception( "Parameter is not a String, a Boolean, a Number, an Array, a Function, an Object nor null." );
	}
}

//  DATE JSON MILLISECONDS

if( !function_exists( "now_ms" ) ) {

	/**
	 * @function now_ms returns current timestamp to milliseconds integer
	 * @return int milliseconds
	 */

	function now_ms() {
		return round( microtime( true ) * 1000 );
	}
}

if( !function_exists( "ms_to_json" ) ) {

	/**
	 * @function ms_to_json returns string for JSON objects or JavaScript scripts.
	 * @param int $int in milliseconds
	 * @return string formatted like "2012-06-13T16:43:01.000Z"
	 */

	function ms_to_json( $int ) {
		$dt = new DateTime();
		$dt->setTimestamp( $int / 1000 );
		$m = "" . round( $dt->format( 'u' ) );
		while( strlen( $m ) < 3 ) $m = '0' . $m;
		return $dt->format( 'Y-m-d' ) . 'T' . $dt->format( 'H:i:s' ) . '.' . $m . 'Z';
	}
}

if( !function_exists( "ms_to_date" ) ) {

	/**
	 * @function ms_to_date returns string for a pretty print of date
	 * @param int $int in milliseconds
	 * @return string formatted like "13 juin 2012"
	 */

	function ms_to_date( $int ) {
		$dt = new DateTime();
		$dt->setTimestamp( $int / 1000 );
		return $dt->format( 'd' ) . ' ' . month_to_fr( $dt->format( 'm' ) ) . ' ' . $dt->format( 'Y' );
	}
}

if( !function_exists( "ms_to_time" ) ) {

	/**
	 * @alias ms_to_second
	 */

	function ms_to_time( $int ) {
		return ms_to_second( $int );
	}
}

if( !function_exists( "ms_to_minute" ) ) {

	/**
	 * @function ms_to_minute returns string for a pretty print of hour and minutes
	 * @param int $int in milliseconds
	 * @return string formatted like "16:43"
	 */

	function ms_to_minute( $int ) {
		$dt = new DateTime();
		$dt->setTimestamp( $int / 1000 );
		return $dt->format( 'H:i' );
	}
}

if( !function_exists( "ms_to_second" ) ) {

	/**
	 * @function ms_to_second returns string for a pretty print of hour, minutes and seconds
	 * @param int $int in milliseconds
	 * @return string formatted like "16:43'01"
	 */

	function ms_to_second( $int ) {
		$dt = new DateTime();
		$dt->setTimestamp( $int / 1000 );
		return $dt->format( 'H:i\'s' );
	}
}

if( !function_exists( "ms_to_millisecond" ) ) {

	/**
	 * @function ms_to_milliseconds returns string for a pretty print of hour, minutes, seconds and milliseconds
	 * @param int $int in milliseconds
	 * @return string formatted like "16:43'01"000"
	 */

	function ms_to_millisecond( $int ) {
		$dt = new DateTime();
		$dt->setTimestamp( $int / 1000 );
		$m = "" . round( $dt->format( 'u' ) );
		while( strlen( $m ) < 3 ) $m = '0' . $m;
		return $dt->format( 'H:i\'s"' ) . $m;
	}
}

if( !function_exists( "month_to_fr" ) ) {

	/**
	 * @function month_to_fr returns month name in french by id
	 * @param int $int { 1 ; 12 }
	 * @return string formatted like "juin"
	 */

	function month_to_fr( $int ) {
		$int = ( ( $int - 1 ) % 12 ) + 1;

		if( $int == 1 ) return "janvier";
		elseif( $int == 2 ) return "février";
		elseif( $int == 3 ) return "mars";
		elseif( $int == 4 ) return "avril";
		elseif( $int == 5 ) return "mai";
		elseif( $int == 6 ) return "juin";
		elseif( $int == 7 ) return "juillet";
		elseif( $int == 8 ) return "août";
		elseif( $int == 9 ) return "septembre";
		elseif( $int == 10 ) return "octobre";
		elseif( $int == 11 ) return "novembre";
		else return "décembre";
	}
}


//  LETTER COMPARATOR

if( !function_exists( "isUppercase" ) ) {

	/**
	 * @function isUppercase returns true if $chr is a uppercase
	 * @param string $chr
	 * @return bool $chr between 'A' and 'Z' in ASCII table
	 */

	function isUppercase( $chr ) {
		$chr = ord( $chr );
		return ( $chr >= ord( "A" ) and $chr <= ord( "Z" ) );
	}
}

if( !function_exists( "isLetter" ) ) {

	/**
	 * @function isLetter returns true if $chr is a letter
	 * @param string $chr
	 * @return bool $chr between 'A' and 'Z' or 'a' and 'z' in ASCII table
	 */

	function isLetter( $chr ) {
		$chr = ord( $chr );
		return ( $chr >= ord( "a" ) and $chr <= ord( "z" ) ) ||
			( $chr >= ord( "A" ) and $chr <= ord( "Z" ) );
	}
}

if( !function_exists( "isFigure" ) ) {

	/**
	 * @function isFigure returns true if $chr is a figure
	 * @param string $chr
	 * @return bool $chr between '0' and '9' in ASCII table
	 */

	function isFigure( $chr ) {
		$chr = ord( $chr );
		return ( $chr >= ord( "0" ) and $chr <= ord( "9" ) );
	}
}

if( !function_exists( "isChar" ) ) {

	/**
	 * @function isChar returns true if $chr is a letter or a figure
	 * @param string $chr
	 * @return bool $char between 'A' and 'Z' or 'a' and 'z' or '0' and '9' in ASCII table
	 */

	function isChar( $chr ) {
		return ( isLetter( $chr ) || isFigure( $chr ) );
	}
}


//  TEXT TRANSFORMER

if( !function_exists( "file_basename" ) ) {

	/**
	 * @function file_basename returns a string about to be used for a generic filename
	 * @param string|object $el is any string, but advisable not a path with '/'
	 * @param null|string $ignore is any string, to remove in the end of the string
	 * @return string with only figure and lowercase and '-' characters
	 * @throws Exception if $el is not a string nor an object
	 */

	function file_basename( $el, $ignore = null ) {

		if( is_object( $el ) )
			$name = get_class( $el );
		elseif( is_string( $el ) )
			$name = $el;
		else
			throw new Exception( "Parameter is not an Object nor a String." );


		$i = 0;
		$str = "";
		$p = null;

		while( $i < strlen( $name ) ) {

			$c = $name[ $i ];

			if( $p === null )
				$str .= strtolower( $name[ $i ] );
			elseif( !isChar( $p ) and isChar( $c ) )
				$str .= "-" . strtolower( $c );
			elseif( isUppercase( $c ) )
				$str .= "-" . strtolower( $c );
			elseif( isLetter( $c ) )
				$str .= $c;

			$p = $c;
			$i++;
		}

		if( is_string( $ignore ) and strlen( $ignore ) ) {
			$r = '/-' . file_basename( $ignore ) . '$/';
			if( preg_match( $r, $str ) ) {
				$str = preg_replace( $r, '', $str );
			}
		}

		return $str;
	}
}

if( !function_exists( "to_string_field" ) ) {

	/**
	 * @function to_string_field is used to translate $el to a field name of an header
	 * @param string $el
	 * @return string
	 */

	function to_string_field( $el ) {
		return ucwords( strtolower( to_string( $el ) ) );
	}
}


//  INTEGER TO STRING

if( !function_exists( "integer_to_metric" ) ) {

	/**
	 * @function integer_to_metric is used to translate a number his multiplicative with prefix metric letter
	 *              in the end of the string, and the number rounded with one decimal.
	 * @param double|float $int
	 * @return string like "-1253.4 P"
	 */

	function integer_to_metric( $int ) {

		$p = array( -24 => "y", -21 => "z", -18 => "a", -15 => "f", -12 => "p", -9 => "n", -6 => "µ", -3 => "m", -2 => "c", -1 => "d",
			0 => "", 1 => "da", 2 => "h", 3 => "k", 6 => "M", 9 => "G", 12 => "T", 15 => "P", 18 => "E", 21 => "Z", 24 => "Y" );
		$i = 0;
		while( $int > 1000 ) {
			$i += 3;
			$int /= 1000;
		}
		while( $int < 1 ) {
			$i -= 3;
			$int *= 1000;
		}
		return round( $int, 1 ) . " " . $p[ $i ];
	}
}

if( !function_exists( "integer_to_computing" ) ) {

	/**
	 * @function integer_to_metric is used to translate a number his 1024 multiplicative with prefix metric letter
	 *              in the end of the string, and the number rounded with one decimal.
	 * @param double|float $int
	 * @return string like "-1253.4 Pi"
	 */

	function integer_to_computing( $int ) {

		$p = array( "", "ki", "Mi", "Gi", "Ti", "Pi", "Ei", "Zi", "Yi" );
		$i = 0;
		while( $int > 1024 ) {
			$i++;
			$int /= 1024;
		}
		return round( $int, 1 ) . " " . $p[ $i ];
	}
}


//  EXCEPTIONS TREATMENT

if( !function_exists( "exception_arg_to_string" ) ) {

	/**
	 * @function exception_arg_to_string is used to pretty print arguments sent to functions listed in the debug
	 *              callback.
	 * @param array $args
	 * @return string like "[[null, true], <Object stdClass>]"
	 */

	function exception_arg_to_string( $args = array() ) {
		if( !is_array( $args ) )
			return to_string( $args );
		foreach( $args as &$arg ) {
			if( is_array( $arg ) )
				$arg = "[" . exception_arg_to_string( $arg ) . "]";
			else {
				$arg = to_string( $arg );
			}
		}
		return implode( ", ", $args );
	}
}

if( !function_exists( "exception_to_string" ) ) {

	/**
	 * @function exception_to_string pretty print an exception for a "text/plain" document
	 * @param Throwable $e
	 * @return string
	 */

	function exception_to_string( Throwable $e ) {

		if( class_exists( "FatalException" ) and $e instanceof FatalException ) {

			$title = get_class( $e ) . " " . $e->getCode() . " catched in " . $e->getFile() . "(" . $e->getLine() . ") : ";
			return $title . "\n\n" . $e->getMessage() . "\n\n";
		} else {

			$title = get_class( $e ) . " " . $e->getCode() . " catched in " . $e->getFile() . "(" . $e->getLine() . ") : " . json_encode( $e->getMessage() );

			$trace = $e->getTrace();
			$lines = "";
			foreach( $trace as $key => $line ) {
				if( !isset( $line[ "file" ] ) ) $line[ "file" ] = "";
				if( !isset( $line[ "line" ] ) ) $line[ "line" ] = "";
				if( !isset( $line[ "class" ] ) ) $line[ "class" ] = "";
				if( !isset( $line[ "type" ] ) ) $line[ "type" ] = "";
				if( !isset( $line[ "function" ] ) ) $line[ "function" ] = "";
				if( !isset( $line[ "args" ] ) ) $line[ "args" ] = "";
				$lines .= "#$key " . $line[ "file" ] . "(" . $line[ "line" ] . "): " . $line[ "class" ] . $line[ "type" ] . $line[ "function" ] . "(" . exception_arg_to_string( $line[ "args" ] ) . ")\n";
			}

			return $title . "\n\n" . $lines . "\n\n";
		}
	}
}

if( !function_exists( "exception_to_html" ) ) {

	/**
	 * @function exception_to_html pretty print an exception for a "text/html" document
	 * @param Throwable $e
	 * @return string
	 */

	function exception_to_html( Throwable $e ) {

		if( class_exists( "FatalException" ) and $e instanceof FatalException ) {

			$title = get_class( $e ) . " " . $e->getCode() . " catched in " . $e->getFile() . "(" . $e->getLine() . ") : ";
			return "<div><pre>" . htmlentities( $title ) . "</pre><pre>" . htmlentities( $e->getMessage() ) . "</pre></div>";
		} else {

			$title = get_class( $e ) . " " . $e->getCode() . " catched in " . $e->getFile() . "(" . $e->getLine() . ") : " . json_encode( $e->getMessage() );

			$trace = $e->getTrace();
			$lines = "";
			foreach( $trace as $key => $line ) {
				if( !isset( $line[ "file" ] ) ) $line[ "file" ] = "";
				if( !isset( $line[ "line" ] ) ) $line[ "line" ] = "";
				if( !isset( $line[ "class" ] ) ) $line[ "class" ] = "";
				if( !isset( $line[ "type" ] ) ) $line[ "type" ] = "";
				if( !isset( $line[ "function" ] ) ) $line[ "function" ] = "";
				if( !isset( $line[ "args" ] ) ) $line[ "args" ] = "";
				$lines .= "#$key " . $line[ "file" ] . "(" . $line[ "line" ] . "): " . $line[ "class" ] . $line[ "type" ] . $line[ "function" ] . "(" . exception_arg_to_string( $line[ "args" ] ) . ")\n";
			}

			return "<div><pre>" . htmlentities( $title ) . "</pre><pre>" . htmlentities( $lines ) . "</pre></div>";
		}
	}
}

if( !function_exists( "throwable_to_array" ) ) {

	/**
	 * @function throwable_to_array returns an exception to an array well formatted.
	 * @param Throwable $e
	 * @return array
	 */
	function throwable_to_array( Throwable $e ) {
		$trace = $e->getTrace();
		$trace[ "code" ] = $e->getCode();
		$trace[ "message" ] = $e->getMessage();
		$trace[ "file" ] = $e->getFile();
		$trace[ "line" ] = $e->getLine();
		return $trace;
	}
}

if( !function_exists( "throwable_to_json" ) ) {

	/**
	 * @function throwable_to_json pretty print an exception for a "application/json" document
	 * @param Throwable $e
	 * @param int|null $options
	 * @return string
	 */

	function throwable_to_json( Throwable $e, $options = null ) {
		return json_encode( throwable_to_array( $e ), $options );
	}

}

if( !function_exists( "throwable_to_mime" ) ) {

	/**
	 * @function throwable_to_mime pretty print an exception for a document with mime $mime
	 * @param Throwable $e
	 * @param string $mime
	 * @return string
	 */

	function throwable_to_mime( Throwable $e, $mime ) {

		if( $mime === "application/json" ) {
			return throwable_to_json( $e );
		} elseif( $mime === "text/html" ) {
			return exception_to_html( $e );
		} else {
			return exception_to_string( $e );
		}
	}
}

