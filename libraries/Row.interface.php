<?php

if( !interface_exists( "Row" ) ) {

	interface Row {

		const ID = "id";

		function __construct( $id_or_data = null );
		function save( $data = null );
		function disable();

		function __get( $name );
		function __set( $name, $value );
		function __isset( $name );
		function __unset( $name );

		function toArray();
		static function arrayToModel( $array, $model_name = null );

		static function addForeignMultiple( $class_name, $foreign_id_field, $self_id_field = self::ID );
		static function addForeignUnique( $class_name, $self_id_field, $foreign_id_field = self::ID );
		static function issetForeign( $class_name );

		function getForeignAsArray( $class_name );
		function getForeign( $class_name );
	}
}
