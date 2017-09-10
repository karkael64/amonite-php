<?php

if( !class_exists( "RowBSON" ) ) {

	require_once "ModelBSON.class.php";
	require_once "Row.interface.php";

	class RowBSON extends ModelBSON implements Row {

		protected $data;

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


		const ENABLE = false;
		const DISABLE = true;
		const KEY_ENABLED = "removed";

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


		private static $foreign_multiple = array();
		private static $foreign_unique = array();

		static function addForeignMultiple( $class_name, $foreign_id_field, $self_id_field = self::ID ) {
			if( !isset( self::$foreign_multiple[ $class_name ] ) ) {
				self::$foreign_multiple[ $class_name ] = array();
			}
			self::$foreign_multiple[ $class_name ][ $self_id_field ] = $foreign_id_field;
		}

		static function addForeignUnique( $class_name, $self_id_field, $foreign_id_field = self::ID ) {
			if( !isset( self::$foreign_unique[ $class_name ] ) ) {
				self::$foreign_unique[ $class_name ] = array();
			}
			self::$foreign_unique[ $class_name ][ $self_id_field ] = $foreign_id_field;
		}

		static function issetForeign( $class_name ) {
			return isset( self::$foreign_multiple[ $class_name ] ) or isset( self::$foreign_unique[ $class_name ] );
		}


		function getForeignAsArray( $class_name ) {
			if( self::issetForeign( $class_name ) and class_exists( $class_name ) ) {
				$where = array();
				foreach( self::$foreign_unique[ $class_name ] as $self_id_field => $foreign_id_field ) {
					$where[ $foreign_id_field ] = $this->__get( $self_id_field );
				}
				return $class_name::select( null, $where );
			}
			return null;
		}

		function getForeign( $class_name ) {
			if( is_array( $data = self::getForeignAsArray( $class_name ) ) )
				return self::arrayToModel( $data, $class_name );
			else
				return null;
		}
	}
}
