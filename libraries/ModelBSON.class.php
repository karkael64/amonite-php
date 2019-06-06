<?php

namespace Amonite;

if( !class_exists( "Amonite\\ModelBSON" ) ) {

	require_once "Model.interface.php";

	class ModelBSON implements Model {


		//  QUERIES

		const ID = "id";

		static function select( $fields = array(), $where = array(), $limit = 0, $start_at = 0 ) {
			$res = array();
			self::startReading();
			self::$last_method = "select";
			self::$last_counter = 0;
			if( $fields instanceof Closure ) {
				while( ( $row = self::getRow() ) and !call_user_func( $fields, $res, $row ) ) {}
			}
			else {
				while( ( $row = self::getRow() ) and ( !$limit or ( $limit < count( $res ) ) ) ) {
					self::$last_counter++;
					if( self::filterWhere( $row, $where ) ) {
						if( $start_at > 0 )
							$start_at--;
						else
							array_push( $res, self::filterFields( $row, $fields ) );
					}
				}
			}
			self::endReading();
			return $res;
		}

		static function selectFirst( $fields = array(), $where = array(), $start_at = 0 ) {
			self::startReading();
			self::$last_method = "selectFirst";
			self::$last_counter = 0;
			if( $fields instanceof Closure ) {
				$res = null;
				while( ( $row = self::getRow() ) and !call_user_func( $fields, $res, $row ) ) {}
				return $res;
			}
			else {
				while( $row = self::getRow() ) {
					self::$last_counter++;
					if( self::filterWhere( $row, $where ) ) {
						if( $start_at > 0 )
							$start_at--;
						else {
							$row = self::filterFields( $row, $fields );
							self::endReading();
							return $row;
						}
					}
				}
			}
			self::endReading();
			return null;
		}

		static function update( $value = array(), $where = array(), $limit = 0, $start_at = 0 ) {
			$res = array();
			self::startWritingTemp();
			self::startReading();
			self::$last_method = "update";
			self::$last_counter = 0;
			if( $value instanceof Closure ) {
				while( ( $row = self::getRow() ) and !call_user_func( $value, $res, $row ) ) {}
			}
			else {
				while( ( $row = self::getRow() ) and ( !$limit or ( $limit < count( $res ) ) ) ) {
					self::$last_counter++;
					if( self::filterWhere( $row, $where ) ) {
						if( $start_at > 0 ) {
							$start_at--;
							self::setRow( $row );
						} else {
							array_push( $res, $row );
							self::setRow( array_merge( $row, $value ) );
						}
					} else {
						self::setRow( $row );
					}
				}
			}
			self::endReading();
			self::endWritingTemp();
			return $res;
		}

		static function insert( $value = array() ) {

			if( $value instanceof Closure ) {
				$result = array();
				while( !( $res = call_user_func( $value ) ) ) {
					$res[ self::ID ] = self::nextId();
					$handler = fopen( self::getFilePath(), 'a' );
					fputs( $handler, json_encode( $value ) . "\n" );
					fclose( $handler );
					$result[] = $res;
				}
				return $result;
			}
			else {
				if( !self::$handler ) {
					if( is_array( $value ) ) {

						$value[ self::ID ] = self::nextId();
						$handler = fopen( self::getFilePath(), 'a' );
						fputs( $handler, json_encode( $value ) . "\n" );
						fclose( $handler );

						return $value;
					}
				} else {
					throw new CustomException( "Already reading in file " . self::getName() . "." );
				}
			}

			return null;
		}

		static function count( $where = array(), $limit = 0, $start_at = 0 ) {
			$res = 0;
			self::startReading();
			self::$last_method = "count";
			self::$last_counter = 0;
			if( $where instanceof Closure ) {
				while( $row = self::getRow() ) {
					if( call_user_func( $where, $row ) )
						$res++;
				}
			}
			else {
				while( ( $row = self::getRow() ) and ( !$limit or ( $limit < $res ) ) ) {
					self::$last_counter++;
					if( self::filterWhere( $row, $where ) ) {
						if( $start_at > 0 )
							$start_at--;
						else
							$res++;
					}
				}
			}
			self::endReading();
			return $res;
		}

		static function remove( array $where, $limit = 0, $start_at = 0 ) {
			$res = array();
			self::startWritingTemp();
			self::startReading();
			self::$last_method = "remove";
			self::$last_counter = 0;
			while( ( $row = self::getRow() ) and ( !$limit or ( $limit < count( $res ) ) ) ) {
				self::$last_counter++;
				if( self::filterWhere( $row, $where ) ) {
					if( $start_at > 0 ) {
						$start_at--;
						self::setRow( $row );
					} else {
						array_push( $res, $row );
					}
				} else {
					self::setRow( $row );
				}
			}
			self::endReading();
			self::endWritingTemp();
			return $res;
		}

		static private function nextId() {
			$id = 0;
			self::startReading();
			while( ( $row = self::getRow() ) ) {
				if( $id < $row[ self::ID ] )
					$id = $row[ self::ID ];
			}
			self::endReading();
			return $id + 1;
		}


		//  FILE MANAGER

		private static $last_file;
		private static $last_method;
		private static $last_counter;
		private static $handler;
		private static $handler_temp;

		static private function startWritingTemp() {

			if( !self::$handler_temp ) {
				$temp = self::getTempPath();
				self::$handler_temp = fopen( $temp, 'w' );
			} else {
				throw new CustomException( -1, "Already writing in file " . self::getName() . "." );
			}
		}

		static private function setRow( $row ) {

			if( is_array( $row ) ) {

				if( !isset( $row[ self::ID ] ) ) {
					$row[ self::ID ] = self::nextId();
				}

				foreach( $row as $field => $value )
					if( is_null( $value ) )
						unset( $row[ $field ] );

				$row = json_encode( $row ) . "\n";
				fputs( self::$handler_temp, $row );
			}
		}

		static private function endWritingTemp() {

			if( self::$handler_temp ) {

				fclose( self::$handler_temp );
				self::$handler_temp = null;

				self::endReading();
				$temp = self::getTempPath();
				$file = self::getFilePath();
				rename( $temp, $file );
			}
		}

		static private function startReading() {

			$dt = now_ms();
			while( !is_null( self::$handler ) && ( now_ms() - $dt ) <  2000 ) { time_nanosleep( 0, 1 ); }

			if( !self::$handler ) {
				$file = self::getFilePath();
				if( !file_exists( $file ) )
					touch( $file );

				self::$handler = fopen( $file, 'r' );
				self::$last_file = $file;
			} else {
				throw new CustomException( "Can't read " . self::getName()
					. ", already reading in file " . self::$last_file
					. ", counter " . self::$last_counter
					. ", method " . self::$last_method
					. ".", -1 );
			}
		}

		static private function getRow() {

			return json_decode( fgets( self::$handler ), true );
		}

		static private function endReading() {

			if( self::$handler ) {

				fclose( self::$handler );
				self::$handler = null;
			}
		}

		static private function getName() {
			return file_basename( get_called_class(), "Model" );
		}


		//  CONSTANTS

		static $datafiles_path;

		static private function getFilePath() {
			return self::$datafiles_path . "/" . self::getName() . ".bson";
		}

		static private function getTempPath() {
			return self::$datafiles_path . "/" . self::getName() . ".bson_temp";
		}


		//  FILTERS

		static private function filterFields( $row = array(), $fields = array() ) {
			if( !is_array( $fields ) or !count( $fields ) )
				return $row;
			else {
				$res = array();
				foreach( $fields as $key => $field ) {
					if( is_numeric( $key ) )
						$res[ $field ] = isset( $row[ $field ] ) ? $row[ $field ] : null;
					else
						$res[ $key ] = isset( $row[ $key ] ) ? $row[ $key ] : $row[ $field ];
				}
				return $res;
			}
		}

		static private function filterWhere( $row = array(), $where = array() ) {
			return self::check_conditions_and( $row, $where );
		}


		// CONDITIONS and, or, between, in

		const KEY_AND = "&and";
		const KEY_OR = "&or";
		const KEY_BETWEEN = "&between";
		const KEY_IN = "&in";

		static private function check_conditions_and( $data, $where ) {
			if( is_array( $where ) ) {
				if( isset( $where[ 0 ] ) && is_callable( $where[ 0 ] ) ) {
					return $where[ 0 ]( $data, $where[ 1 ] );
				} else {
					foreach( $where as $k => $w ) {

						if( $k == self::KEY_AND && !self::check_conditions_and( $data, $w ) )
							return false;
						elseif( $k == self::KEY_OR && !self::check_conditions_or( $data, $w ) )
							return false;
						elseif( $k == self::KEY_BETWEEN && !self::check_conditions_between( $data, $w ) )
							return false;
						elseif( $k == self::KEY_IN && !self::check_conditions_in( $data, $w ) )
							return false;

						elseif( !isset( $data[ $k ] ) ) {
							if( $w === null )
								continue;
							else
								return false;
						}
						elseif( $data[ $k ] !== $w )
							return false;
					}
					return true;
				}
			}
			return false;
		}

		static private function check_conditions_or( $data, $where ) {
			if( is_array( $where ) ) {
				if( isset( $where[ 0 ] ) && is_callable( $where[ 0 ] ) ) {
					return $where[ 0 ]( $data, $where[ 1 ] );
				} else {
					foreach( $where as $k => $w ) {

						if( $k == self::KEY_AND && self::check_conditions_and( $data, $w ) )
							return true;
						elseif( $k == self::KEY_OR && self::check_conditions_or( $data, $w ) )
							return true;
						elseif( $k == self::KEY_BETWEEN && self::check_conditions_between_or( $data, $w ) )
							return true;
						elseif( $k == self::KEY_IN && self::check_conditions_in_or( $data, $w ) )
							return true;

						elseif( !isset( $data[ $k ] ) ) {
							if( $w === null )
								return true;
							else
								continue;
						}
						if( $data[ $k ] === $w )
							return true;
					}
					return false;
				}
			}
			return false;
		}

		static private function check_conditions_between( $data, $between ) {
			if( is_array( $between ) && isset( $between[ 0 ] ) ) {

				foreach( $between as $k => $w ) {
					if( is_array( $w ) ) {
						$min = min( $w );
						$max = max( $w );
						$value = $data[ $k ];
						if( $value < $min || $value > $max )
							return false;
					}
				}
				return true;
			}
			return false;
		}

		static private function check_conditions_in( $data, $in ) {
			if( is_array( $in ) && isset( $in[ 0 ] ) && isset( $in[ 1 ] ) ) {

				foreach( $in as $k => $w ) {
					$value = $data[ $k ];
					$b = false;
					if( is_array( $w ) ) {
						foreach( $w as $t ) {
							if( $t === $value )
								$b = true;
						}
					}
					if( !$b )
						return false;
				}
				return true;
			}
			return false;
		}

		static private function check_conditions_between_or( $data, $between ) {
			if( is_array( $between ) && isset( $between[ 0 ] ) && isset( $between[ 1 ] ) ) {

				foreach( $between as $k => $w ) {
					if( is_array( $w ) ) {
						$min = min( $w );
						$max = max( $w );
						$value = @$data[ $k ];
						if( $value >= $min && $value <= $max )
							return true;
					}
				}
			}
			return false;
		}

		static private function check_conditions_in_or( $data, $in ) {
			if( is_array( $in ) && isset( $in[ 0 ] ) && isset( $in[ 1 ] ) ) {

				foreach( $in as $k => $w ) {
					$value = $data[ $k ];
					$b = false;
					if( is_array( $w ) ) {
						foreach( $w as $t ) {
							if( $t == $value )
								$b = true;
						}
					}
					if( $b )
						return true;
				}
			}
			return false;
		}
	}
}
