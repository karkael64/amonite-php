<?php

if( !class_exists( "RowBSON" ) ) {

	require_once "ModelBSON.class.php";
	require_once "Row.interface.php";

	class RowBSON extends ModelBSON implements Row {

		protected $data;

		function __construct( $id = array() ) {

			if( is_numeric( $id ) ) {
				$this->data = $this->selectFirst( null, array( self::ID => +$id, self::KEY_ENABLED => self::ENABLE ) );
			} elseif( is_array( $id ) ) {
				$this->data = $id;
			} else {
				$this->data = array();
			}
		}

		public function save( $arr = null ) {

			if( is_array( $arr ) )
				$this->data += $arr;

			if( isset( $this->data[ self::ID ] )
				and ( $d = self::update( $this->data, array( self::ID => $this->data[ self::ID ] ) ) )
				and isset( $d[ 0 ] )
			)
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

			$this->__set( self::KEY_ENABLED, self::DISABLE );
			return $this->save();
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



		function __call( $name, $args ) {

			$p = strpos( $name, "Model" ) ? 5 : 0;

			if( strpos( $name, "getMany" ) === 0 )
				return $this->_getMany( substr( $name, 7 ), $args, !!$p );
			elseif( strpos( $name, "get" ) === 0 )
				return $this->_get( substr( $name, 3 ), $args, !!$p );
			elseif( strpos( $name, "add" ) === 0 )
				return $this->_add( substr( $name, 3 ), $args, !!$p );
			else {
				throw new Exception( "Function not defined" );
			}
		}

		protected function _getMany( $name, $args, $toModel ) {

			$field = file_basename( get_called_class(), "Model" );
			$id_field = "id_$field";

			if( class_exists( $name . "Model" ) )
				$name .= "Model";

			if( class_exists( $name ) and is_subclass_of( $name, get_class() ) ) {
				if( isset( $args[ 1 ] ) && is_array( $args[ 1 ] ) ) {
					if( !isset( $args[ 1 ][ $id_field ] ) )
						$args[ 1 ][ $id_field ] = $this->__get( self::ID );

					if( !isset( $args[ 1 ][ self::KEY_ENABLED ] ) )
						$args[ 1 ][ self::KEY_ENABLED ] = self::ENABLE;
				}
				else {
					if( !isset( $args[ 0 ] ) )
						$args[ 0 ] = null;

					$args[ 1 ] = array(
						$id_field => $this->__get( self::ID ),
						self::KEY_ENABLED => self::ENABLE
					);
				}

				$data = call_user_func_array( array( $name, "select" ), $args );
				if( $toModel )
					return self::arrayToModel( $data, $name );
				else
					return $data;
			}
			else {
				throw new Exception( "Class $name not found." );
			}
		}

		protected function _get( $name, $args, $toModel ) {

			$field = file_basename( $name, "Model" );
			$id_field = "id_$field";

			if( class_exists( $name . "Model" ) )
				$name .= "Model";

			if( class_exists( $name ) and is_subclass_of( $name, get_class() ) ) {
				if( isset( $args[ 1 ] ) && is_array( $args[ 1 ] ) ) {
					if( !isset( $args[ 1 ][ self::ID ] ) )
						$args[ 1 ][ self::ID ] = $this->__get( $id_field );

					if( !isset( $args[ 1 ][ self::KEY_ENABLED ] ) )
						$args[ 1 ][ self::KEY_ENABLED ] = self::ENABLE;
				}
				else {
					if( !isset( $args[ 0 ] ) )
						$args[ 0 ] = null;

					$args[ 1 ] = array(
						self::ID => $this->__get( $id_field ),
						self::KEY_ENABLED => self::ENABLE
					);
				}

				$data = call_user_func_array( array( $name, "selectFirst" ), $args );
				if( $toModel )
					return new $name( $data );
				else
					return $data;
			}
			else {
				throw new Exception( "Class $name not found." );
			}
		}

		protected function _add( $name, $args, $toModel ) {

			$field = file_basename( get_called_class(), "Model" );
			$id_field = "id_$field";

			if( class_exists( $name . "Model" ) )
				$name .= "Model";

			if( class_exists( $name ) and is_subclass_of( $name, get_class() ) ) {

				$obj = null;
				if( is_array( $args ) ) {
					$obj = new $name( $args );
				}
				elseif( $args instanceof $name ) {
					$obj = $args;
				}

				if( $obj instanceof self ) {
					$obj->__set( $id_field, $this->__get( self::ID ) );
					$obj->save();

					if( $toModel )
						return $obj;
					else
						return $obj->toArray();
				}
			}
			else {
				throw new Exception( "Class $name not found." );
			}
		}
	}
}
