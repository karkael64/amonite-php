<?php

if( !class_exists( "RowPDO" ) ) {

	require_once "ModelPDO.class.php";

	class RowPDO extends ModelPDO {

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
	}
}
