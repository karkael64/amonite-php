<?php

namespace Amonite;

if( !class_exists( "Amonite\\Unit" ) ) {

	class Unit {

		private $name;

		/**
		 * Unit constructor.
		 * @param string $name
         * @param callable $fn
		 */
		function __construct( $name, $fn = null ) {

			$this->name = $name;
			$this->previousTimestamp = $this->getTimestamp();

			if( is_callable( $fn ) ) {
				$this->expectNoThrow( $fn, array( $this ), "This unit section has not thrown any error." );
			}
		}


		// SECTIONS

		protected $sections = array();

		/**
		 * @param $name
         * @param $fn
		 * @return Unit
		 * @throws CustomException
		 */
		function section( $name, $fn = null ) {

			if( is_string( $name ) && strlen( $name ) ) {

				$section = new self( $name, $fn );
				array_push( $this->sections, $section );
				return $section;

			} elseif( $name instanceof Unit ) {

				array_push( $this->sections, $name );
				return $name;

			} else {

				throw new CustomException( "Section parameter is not a String nor a Section instance." );
			}
		}

		/**
		 * @return string
		 */
		function getName() {
			return $this->name;
		}

		/**
         * @param bool $errors_only
		 * @return array
		 */
		function toArray( $errors_only = false ) {

			$sections = array();
			foreach( $this->sections as $section ) {
				if( $section instanceof self && ( $section->countErrors() or !$errors_only ) )
					array_push( $sections, $section->toArray( $errors_only ) );
			}
			return array(
				"name" => $this->getName(),
				"lines" => $errors_only ? $this->getErrors() : $this->getLines(),
				"sections" => $sections
			);
		}

		private $lines = array();

		/**
		 * @return array
		 */
		function getLines() {
			return $this->lines;
		}

		/**
		 * @return int
		 */
		function count() {
			$count = count( $this->getLines() );
			foreach( $this->sections as $section ) {
				if( $section instanceof self )
					$count += $section->count();
			}
			return $count;
		}

		/**
		 * @return int
		 */
		function countErrors() {
			$errors = count( $this->getErrors() );
			foreach( $this->sections as $section ) {
				if( $section instanceof self )
					$errors += $section->countErrors();
			}
			return $errors;
		}

		/**
		 * @return int
		 */
		function getTime() {
			$time = 0;
			foreach( $this->lines as $line ) {
				$time += $line[ "time" ];
			}
			foreach( $this->sections as $section ) {
				if( $section instanceof self )
					$time += $section->getTime();
			}
			return $time;
		}

		/**
		 * @return array
		 */
		function getErrors() {
			$errors = array();
			foreach( $this->lines as $line ) {
				if( $line[ "value" ] === false )
					array_push( $errors, $line );
			}
			return $errors;
		}


		// MAIN

		/**
		 * @param mixed $item
		 * @param string $description
		 * @return Unit
		 */
		function log( $item, $description ) {

			return $this->makeLine( "Log", null, to_string( $item ), to_string( $description ), "log" );
		}

		/**
		 * @param mixed $test
		 * @param string $description
		 * @return Unit
		 */
		function assert( $test, $description ) {

			return $this->makeLine( "Assert", !!$test, to_string( $test ), to_string( $description ), array( "assert", to_string( !!$test ) ) );
		}


		// EXPECTS

		/**
		 * @param mixed $value
		 * @param mixed $toBe
		 * @param string $description
		 * @return Unit
		 */
		function expect( $value, $toBe, $description ) {

			$test = ( $value == $toBe );
			return $this->customExpect( "Expect", $test, $value, $toBe, to_string( $description ), array( "expect", to_string( $test ) ) );
		}

		/**
		 * @param mixed $value
		 * @param mixed $toBe
		 * @param string $description
		 * @return Unit
		 */
		function expectStrict( $value, $toBe, $description ) {

			$test = ( $value === $toBe );
			return $this->customExpect( "Expect Strict", $test, $value, $toBe, to_string( $description ), array( "expect-strict", to_string( $test ) ) );
		}

		/**
		 * @param mixed $value
		 * @param mixed $instance
		 * @param string $description
		 * @return Unit
		 */
		function expectInstanceOf( $value, $instance, $description ) {

			$test = ( $value instanceof $instance );
			return $this->customExpect( "Expect Strict", $test, $value, $instance, to_string( $description ), array( "expect-instance", to_string( $test ) ) );
		}


		// CALL FUNCTION, MATCH RETURN

		/**
		 * @param callable $function
		 * @param array|null $args
		 * @param mixed $toBe
		 * @param string $description
		 * @return Unit
		 */
		function expectReturn( $function, $args, $toBe, $description ) {

			$title = "Expect Return";
			$class = "expect-return";

			if( !is_callable( $function ) ) {
				return $this->makeLine( $title, false, "First parameter is not a function !", to_string( $description ), array( $class, "error" ) );
			}
			if( !is_array( $args ) and !is_null( $args ) ) {
				return $this->makeLine( $title, false, "Second parameter is not an array !", to_string( $description ), array( $class, "error" ) );
			} else {
				try {
					$res = call_user_func_array( $function, $args );
					$test = ( $res == $toBe );
					return $this->customExpect( $title, $test, $res, $toBe, to_string( $description ), array( $class, to_string( $test ) ) );
				} catch( Throwable $ex ) {
					$c = get_class( $ex );
					return $this->makeLine( $title, false, "Throws an CustomException $c !", to_string( $description ), array( $class, "error" ) );
				}
			}
		}

		/**
		 * @param callable $function
		 * @param array|null $args
		 * @param mixed $toBe
		 * @param string $description
		 * @return Unit
		 */
		function expectReturnStrict( $function, $args, $toBe, $description ) {

			$title = "Expect Return Strict";
			$class = "expect-return-strict";

			if( !is_callable( $function ) ) {
				return $this->makeLine( $title, false, "First parameter is not a function !", to_string( $description ), array( $class, "error" ) );
			}
			if( !is_array( $args ) and !is_null( $args ) ) {
				return $this->makeLine( $title, false, "Second parameter is not an array !", to_string( $description ), array( $class, "error" ) );
			} else {
				try {
					$res = call_user_func_array( $function, $args );
					$test = ( $res === $toBe );
					return $this->customExpect( $title, $test, $res, $toBe, to_string( $description ), array( $class, to_string( $test ) ) );
				} catch( Throwable $ex ) {
					$c = get_class( $ex );
					return $this->makeLine( $title, false, "Throws an CustomException $c !", to_string( $description ), array( $class, "error" ) );
				}
			}
		}

		/**
		 * @param callable $function
		 * @param array|null $args
		 * @param string|object $toBe
		 * @param string $description
		 * @return Unit
		 */
		function expectReturnInstanceOf( $function, $args, $toBe, $description ) {

			$title = "Expect Return Instance";
			$class = "expect-return-instance";

			if( !is_callable( $function ) ) {
				return $this->makeLine( $title, false, "First parameter is not a function !", to_string( $description ), array( $class, "error" ) );
			}
			if( !is_array( $args ) and !is_null( $args ) ) {
				return $this->makeLine( $title, false, "Second parameter is not an array !", to_string( $description ), array( $title, "error" ) );
			} else {
				try {
					$res = call_user_func_array( $function, $args );
					$test = ( $res === $toBe );
					return $this->customExpect( $title, $test, $res, $toBe, to_string( $description ), array( $title, to_string( $test ) ) );
				} catch( Throwable $ex ) {
					$c = get_class( $ex );
					return $this->makeLine( $title, false, "Throws an CustomException $c !", to_string( $description ), array( $title, "error" ) );
				}
			}
		}


		// CALL FUNCTION, MATCH THROWS

		/**
		 * @param callable $function
		 * @param array|null $args
		 * @param string|object $instance
		 * @param $description
		 * @return Unit
		 */
		function expectThrow( $function, $args, $instance, $description ) {

			$title = "Expect Throw";
			$class = "expect-throw";

			if( !is_callable( $function ) ) {
				return $this->makeLine( $title, false, "First parameter is not a function !", to_string( $description ), array( $class, "error" ) );
			}
			if( !is_array( $args ) and !is_null( $args ) ) {
				return $this->makeLine( $title, false, "Second parameter is not an array !", to_string( $description ), array( $class, "error" ) );
			} else {
				try {
					call_user_func_array( $function, $args );
					return $this->makeLine( $title, false, "Hasn't thrown an CustomException !", to_string( $description ), array( $class, to_string( false ) ) );
				} catch( Throwable $ex ) {
					$test = ( $ex instanceof $instance );
					return $this->customExpect( $title, $test, $ex, $instance, to_string( $description ), array( $class, to_string( $test ) ) );
				}
			}
		}

		/**
		 * @param callable $function
		 * @param array|null $args
		 * @param $description
		 * @return Unit
		 */
		function expectNoThrow( $function, $args, $description ) {

			$title = "Expect No Throw";
			$class = "expect-no-throw";

			if( !is_callable( $function ) ) {
				return $this->makeLine( $title, false, "First parameter is not a function !", to_string( $description ), array( $class, "error" ) );
			}
			if( !is_array( $args ) and !is_null( $args ) ) {
				return $this->makeLine( $title, false, "Second parameter is not an array !", to_string( $description ), array( $class, "error" ) );
			} else {
				try {
					call_user_func_array( $function, $args );
					$test = true;
					return $this->customExpect( $title, $test, "No throw", true, to_string( $description ), array( $class, to_string( $test ) ) );
				} catch( Throwable $ex ) {
					return $this->makeLine( $title, false, "Has thrown an CustomException !", $ex->getMessage(), array( $class, to_string( false ) ) );
				}
			}
		}


		function expectClassExists( $className, $description ) {

			$title = "Expect Class Exists";
			$class = "expect-class-exists";
			$test = class_exists( $className );

			return $this->customExpect( $title, $test, $className, true, to_string( $description ), array( $class, to_string( $test ) ) );
		}


		function expectClassHasMethod( $className, $methodName, $description ) {

			$title = "Expect Class HasMethod";
			$class = "expect-class-hasmethod";
			$test = array_search( $methodName, get_class_methods( $className ) ) !== false;

			return $this->customExpect( $title, $test, $className . " has " . $methodName, true, to_string( $description ), array( $class, to_string( $test ) ) );
		}


		// MAIN PROTECTED FUNCTIONS

        /**
         * @param string $caller
         * @param mixed $value
         * @param string $print
         * @param string $description
         * @param string $classes
         * @param double $time
         * @return Unit $this
         */
		protected function makeLine( $caller, $value = false, $print = "", $description = "", $classes = "", $time = null ) {

			$time = $this->getTimestampDiff( $time );
			$trace = $this->getTrace();
			$classes = $this->getClasses( $classes );

			array_push( $this->lines, array(
				"caller" => $caller,
				"value" => $value,
				"print" => $print,
				"description" => $description,
				"classes" => $classes,
				"time" => $time,
				"trace" => $trace
			) );

			return $this;
		}

		protected function customExpect( $caller, $test, $value, $toBe, $description, $classes ) {

			$value = $this->string_hellip( to_string( $value ), 54, 6 );

			if( $test ) {
				$text = "$value as expected"; // 54 + 12 = 66 max length
			} else {
				$toBe = $this->string_hellip( to_string( $toBe ), 25, 6 );
				$text = "$value expected to be $toBe"; // 25 + 25 + 16 = 66 max length
			}

			return $this->makeLine( $caller, $test, $text, $description, $classes );
		}


		// LINES PREPARER

		private function getTimestamp() {

			return microtime( true ) * 1000;
		}

		private $previousTimestamp = 0;

		private function getTimestampDiff( $previousTimestamp = null ) {

			$currentTimestamp = $this->getTimestamp();

			if( is_null( $previousTimestamp ) ) {
				$previousTimestamp = $this->previousTimestamp;
			} elseif( !is_numeric( $previousTimestamp ) ) {
				throw new CustomException( "Parameter is not a number." );
			}

			$diff = $currentTimestamp - $previousTimestamp;
			$this->previousTimestamp = $currentTimestamp;

			return $diff;
		}

		private function getTrace() {

			$trace = debug_backtrace();
			while( count( $trace ) and ( $trace[ 0 ][ "file" ] ) === __FILE__ ) {
				array_shift( $trace );
			}
			if( isset( $trace[ 0 ] ) ) {
				return $trace[ 0 ];
			}
			return null;
		}

		private function getClasses( $classes ) {

			if( is_string( $classes ) )
				return $classes;
            elseif( is_null( $classes ) )
				return "";
            elseif( is_array( $classes ) )
				return implode( " ", $classes );
			else
				throw new CustomException( "Classes are not a String, Null nor an Array" );
		}

		private function string_force_length( $string, $length, $char = "0" ) {
			return str_pad( substr( to_string( $string ), 0, $length ), $length, $char, STR_PAD_RIGHT );
		}

		private function string_hellip( $string, $length, $margin = 1, $char = "&hellip;" ) {
			$string = to_string( $string );
			if( strlen( $string ) > $length )
				return substr( $string, 0, $length - $margin ) . "…";
			else
				return $string;
		}

		private function insert_tabs( $count = 0 ) {
			$str = "";
			while( $count > 0 ) {
				$str .= "\t";
				$count--;
			}
			return $str;
		}


		function toString( $errors_only = false, $tab_count = 0 ) {

			if( ( $countErrors = $this->countErrors() ) or !$errors_only ) {
				$name = $this->getName();
				$lines = $errors_only ? $this->getErrors() : $this->getLines();
				$sections = $this->sections;
				$count = $this->count();

				$tab = $this->insert_tabs( $tab_count );
				$str = "\n";
				$str .= $tab . "$name, $countErrors/$count errors\n\n";
				foreach( $lines as $line ) {
					$str .= $tab . "("
						. $this->string_force_length( $line[ "time" ], 14, "0" ) . "ms) "
						. $this->string_force_length( $line[ "caller" ], 30, " " ) . ": "
						. $this->string_hellip( $line[ "print" ], 66, 6 ) . "; "
						. $this->string_hellip( $line[ "description" ], 66, 6 ) . "\n";
				}
				foreach( $sections as $section ) {
					if( $section instanceof self ) {
						$str .= $section->toString( $errors_only, $tab_count + 1 );
					}
				}
				$str .= "\n";
				return $str;
			}
			return "";
		}

		function toHTML( $errors_only = false ) {

			Observer::start_chunk();
			if( ( $errors = $this->countErrors() ) or !$errors_only ):
				$count = $this->count();
				$time = $this->string_force_length( $this->getTime(), 14, "0" );
				?>
                <div class="unit section<?php echo $errors ? " errors" : ""; ?>" count-errors="<?php echo $errors; ?>"
                     count="<?php echo $count; ?>">
                    <h2><?php echo $this->name . ", $errors/$count errors, $time ms"; ?></h2>
                    <div class="lines">
						<?php $lines = $errors_only ? $this->getErrors() : $this->getLines();
						foreach( $lines as $line ): ?>
                            <div class="line <?php echo $line[ "classes" ]; ?>">
                                <span class="time"><?php echo $this->string_force_length( $line[ "time" ], 14, "0" ); ?>
                                    ms</span>
                                <span class="caller"><?php echo $line[ "caller" ]; ?></span>
                                <span class="value"><?php echo $this->string_hellip( $line[ "print" ], 66, 6 ); ?></span>
                                <span class="description"><?php echo $this->string_hellip( $line[ "description" ], 80, 5 ); ?></span>
                            </div>
						<?php endforeach; ?>
                    </div>
                    <div class="sections">
						<?php foreach( $this->sections as $section ):
							echo $section->toHTML( $errors_only );
						endforeach; ?>
                    </div>
                </div>
				<?php
			endif;
			return Observer::end_chunk();
		}

		function toJSON( $errors_only = false ) {

			return json_encode( $this->toArray( $errors_only ) );
		}

		function toMime( $mime = "text/plain", $errors_only = false ) {

			if( $mime === "application/json" )
				return $this->toJSON( $errors_only );
            elseif( $mime === "text/html" )
				return $this->toHTML( $errors_only );
			else
				return $this->toString( $errors_only );

		}
	}
}
