<?php

namespace Amonite;

if( !class_exists( "Amonite\\Constants" ) ) {

	class Constants {

		private $constants;

		function __construct( $array ) {

			$this->constants = array();

			if( is_array( $array ) ) {
				foreach( $array as $field => $value ) {
					$this->__set( $field, $value );
				}
			}
		}

		function __isset( $field ) {

			$field = strtoupper( $field );
			return isset( $this->constants[ $field ] );
		}

		function __get( $field ) {

			$field = strtoupper( $field );
			return isset( $this->constants[ $field ] ) ? $this->constants[ $field ] : NULL;
		}

		function __set( $field, $value ) {

			$field = strtoupper( $field );
			if( !isset( $this->constants[ $field ] ) ) {

				if( is_array( $value ) ) {
					$this->constants[ $field ] = new self( $value );
				} else {
					$this->constants[ $field ] = $value;
				}
			}
		}

		function getKeys() {
			return array_keys( $this->constants );
		}

		function toArray() {
			$res = array();
			foreach( $this->getKeys() as $key ) {
				if( $this->constants[ $key ] instanceof self ) {
					$res[ $key ] = $this->constants[ $key ]->toArray();
				} else {
					$res[ $key ] = $this->constants[ $key ];
				}
			}
			return $res;
		}
	}
}
