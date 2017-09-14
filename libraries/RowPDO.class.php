<?php

if( !class_exists( "RowPDO" ) ) {

	require_once "ModelPDO.class.php";
	require_once "Row.interface.php";

	class RowPDO extends ModelPDO implements Row {

		protected $data;
		const ENABLE = false;
		const DISABLE = true;
		const KEY_ENABLED = "removed";

		function __construct( $id = array() ) {

			if( is_numeric( $id ) ) {
				$this->data = $this->selectFirst( null, array( self::ID => +$id, self::KEY_ENABLED => self::ENABLE ) );
			}
			elseif( is_array( $id ) ) {
				$this->data = $id;
			}
			else {
				$this->data = array();
			}
		}

		public function save( $arr = null ) {

			if( is_array( $arr ) )
				$this->data += $arr;
			$this->data[ "date" ] = now_ms();

			if( isset( $this->data[ self::ID ] )
				and ( $d = self::update( $this->data, array( self::ID => $this->data[ self::ID ] ) ) )
				and isset( $d[ 0 ] ) )
				$d = $d[ 0 ];
			else
				$d = self::insert( $this->data );

			$this->data = $d;

			return $this;
		}

		public function disable() {

			return $this->save( array( self::KEY_ENABLED => self::DISABLE ) );
		}

		public function __get( $name ) {
			return isset( $this->data[ $name ] ) ? $this->data[ $name ] : null;
		}

		public function __set( $name, $value ) {
			$this->data[ $name ] = $value;
		}

		public function __isset( $name ) {
			return isset( $this->data[ $name ] );
		}

		public function __unset( $name ) {
			unset( $this->data[ $name ] );
		}

		public function toArray() {
			return $this->data;
		}

		static public function arrayToModel( $arr, $model_name = null ) {

			if( is_null( $model_name ) )
				$model_name = get_called_class();

			if( $arr instanceof $model_name )
				return $arr;

			if( class_exists( $model_name ) ) {
				$res = array();
				foreach( $arr as $row ) {
					array_push( $res, new $model_name( $row ) );
				}
				return $res;
			}
			return null;
		}


		protected static $foreign_multiple = array();
		protected static $foreign_unique = array();

		static function registerForeignMultiple( $class_name, $foreign_id_field = null, $self_id_field = self::ID ) {
			if( is_null( $foreign_id_field ) )
				$foreign_id_field = self::ID . "_" . file_basename( get_called_class(), "Model" );
			if( !isset( self::$foreign_multiple[ get_called_class() ] ) ) {
				self::$foreign_multiple[ get_called_class() ] = array();
			}
			if( !isset( self::$foreign_multiple[ get_called_class() ][ $class_name ] ) ) {
				self::$foreign_multiple[ get_called_class() ][ $class_name ] = array();
			}
			self::$foreign_multiple[ get_called_class() ][ $class_name ][ $self_id_field ] = $foreign_id_field;
		}

		static function registerForeignUnique( $class_name, $self_id_field = null, $foreign_id_field = self::ID ) {
			if( is_null( $self_id_field ) )
				$self_id_field = self::ID . "_" . file_basename( $class_name, "Model" );
			if( !isset( self::$foreign_unique[ get_called_class() ] ) ) {
				self::$foreign_unique[ get_called_class() ] = array();
			}
			if( !isset( self::$foreign_unique[ get_called_class() ][ $class_name ] ) ) {
				self::$foreign_unique[ get_called_class() ][ $class_name ] = array();
			}
			self::$foreign_unique[ get_called_class() ][ $class_name ][ $self_id_field ] = $foreign_id_field;
		}

		static function issetForeign( $class_name ) {
			return self::issetForeignUnique( $class_name ) or self::issetForeignMultiple( $class_name );
		}

		static function issetForeignMultiple( $class_name ) {
			return isset( self::$foreign_multiple[ get_called_class() ][ $class_name ] );
		}

		static function issetForeignUnique( $class_name ) {
			return isset( self::$foreign_unique[ get_called_class() ][ $class_name ] );
		}


		function getForeignAsArray( $class_name, $fields = null, $where = array(), $limit = 0, $start_at = 0 ) {
			if( self::issetForeignMultiple( $class_name ) and class_exists( $class_name ) ) {
				foreach( self::$foreign_multiple[ get_called_class() ][ $class_name ] as $self_id_field => $foreign_id_field ) {
					$where[ $foreign_id_field ] = $this->__get( $self_id_field );
				}
				return $class_name::select( $fields, $where, $limit, $start_at );
			}
			if( self::issetForeignUnique( $class_name ) and class_exists( $class_name ) ) {
				foreach( self::$foreign_unique[ get_called_class() ][ $class_name ] as $self_id_field => $foreign_id_field ) {
					$where[ $foreign_id_field ] = $this->__get( $self_id_field );
				}
				return $class_name::selectFirst( $fields, $where, $start_at );
			}
			return null;
		}

		function getForeign( $class_name, $fields = null, $where = array(), $limit = 0, $start_at = 0 ) {
			if( self::issetForeignMultiple( $class_name ) and class_exists( $class_name ) ) {
				foreach( self::$foreign_multiple[ get_called_class() ][ $class_name ] as $self_id_field => $foreign_id_field ) {
					$where[ $foreign_id_field ] = $this->__get( $self_id_field );
				}
				return $class_name::arrayToModel( $class_name::select( $fields, $where, $limit, $start_at ) );
			}
			if( self::issetForeignUnique( $class_name ) and class_exists( $class_name ) ) {
				foreach( self::$foreign_unique[ get_called_class() ][ $class_name ] as $self_id_field => $foreign_id_field ) {
					$where[ $foreign_id_field ] = $this->__get( $self_id_field );
				}
				return new $class_name( $class_name::selectFirst( $fields, $where, $start_at ) );
			}
			return null;
		}

		function addManyForeign( $class_name, $value ) {
			$res = array();
			foreach( $value as $v ) {
				$res[] = $this->addForeign( $class_name, $v );
			}
			return $res;
		}

		function addForeign( $class_name, $value ) {
			if( self::issetForeignMultiple( $class_name ) and class_exists( $class_name ) ) {
				if( is_array( $value ) ) {
					foreach( self::$foreign_multiple[ get_called_class() ][ $class_name ] as $self_id_field => $foreign_id_field ) {
						$value[ $foreign_id_field ] = $this->__get( $self_id_field );
					}
					return new $class_name( $class_name::insert( $value ) );
				}
				elseif( ( $value instanceof self ) and ( get_class( $value ) === $class_name ) ) {
					foreach( self::$foreign_multiple[ get_called_class() ][ $class_name ] as $self_id_field => $foreign_id_field ) {
						$value->__set( $foreign_id_field, $this->__get( $self_id_field ) );
					}
					return $value->save();
				}
			}
			return null;
		}

		function setForeign( $class_name, $value ) {
			if( self::issetForeignUnique( $class_name ) and class_exists( $class_name ) and ( $el = $this->getForeign( $class_name ) ) and ( $el instanceof self ) ) {
				if( is_array( $value ) ) {
					foreach( $value as $field => $v ) {
						$el->__set( $field, $v );
					}
					return $el->save();
				}
				elseif( ( $value instanceof self ) and ( get_class( $value ) === $class_name ) ) {
					if( ( $value === $el ) or ( $value->__get( self::ID ) === $el->__get( self::ID ) ) ) {
						$value->save();
					}
					else {
						$value->__set( self::KEY_ENABLED, self::ENABLE );
						$el->__set( self::KEY_ENABLED, self::DISABLE );
						$el->save();
						return $value->save();
					}
				}
			}
			return null;
		}
	}
}
