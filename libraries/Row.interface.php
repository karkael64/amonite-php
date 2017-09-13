<?php

if( !interface_exists( "Row" ) ) {

	interface Row {


		// DATA MANAGEMENT

		/**
		 * @method __construct
		 * @param int|array|null $id_or_data,
		 * 			if int, try to load data if KEY_ENABLED is ENABLE
		 * 			if array, cache data, prepared to be inserted
		 * 			if null, new object, prepared to be inserted
		 */
		function __construct( $id_or_data = null );

		/**
		 * @method Row::save the data in files, insert or update if id already exists
		 * @param array|null $data to be merged with current data
		 * @return Row $this
		 */
		function save( $data = null );

		/**
		 * @method Row::disable set KEY_ENABLED to DISABLE
		 * @return Row $this
		 */
		function disable();

		function __get( $name );
		function __set( $name, $value );
		function __isset( $name );
		function __unset( $name );


		//	DATA OUTPUT

		function toArray();

		/**
		 * @method Row::arrayToModel change each $array item to a $model_name object
		 * @param array $array
		 * @param string|null $model_name
		 * @return array|null
		 */
		static function arrayToModel( $array, $model_name = null );


		//	FOREIGN ROWS REGISTER

		/**
		 * @method Row::registerForeignMultiple register a foreign model to be read as multiple child
		 * @param $class_name
		 * @param $foreign_id_field
		 * @param $self_id_field
		 * @return void
		 */
		static function registerForeignMultiple( $class_name, $foreign_id_field, $self_id_field );

		/**
		 * @method Row::registerForeignMultiple register a foreign model to be read as unique parent
		 * @param $class_name
		 * @param $foreign_id_field
		 * @param $self_id_field
		 * @return void
		 */
		static function registerForeignUnique( $class_name, $self_id_field, $foreign_id_field );

		/**
		 * @method Row::issetForeign verify foreign link existence.
		 * @param $class_name
		 * @return bool
		 */
		static function issetForeign( $class_name );


		//	FOREIGN ROWS USAGE

		/**
		 * @method Row::getForeignAsArray select all $class_name rows whose match $where
		 * @param string $class_name
		 * @param array|null $fields
		 * @param array $where
		 * @param int $limit
		 * @param int $start_at
		 * @return array return an array "rowset" with arrays "rows" into.
		 */
		function getForeignAsArray( $class_name, $fields, $where, $limit, $start_at );

		/**
		 * @method Row::getForeignAsArray select all $class_name rows whose match $where
		 * @param string $class_name
		 * @param array|null $fields
		 * @param array $where
		 * @param int $limit
		 * @param int $start_at
		 * @return array return an array "rowset" with objects Row into.
		 */
		function getForeign( $class_name, $fields, $where, $limit, $start_at );

		/**
		 * @method Row::addManyForeign insert array rowset $value in foreign $class_name
		 * @param string $class_name
		 * @param array|Row $value
		 * @return null|Row inserted or not.
		 */
		function addManyForeign( $class_name, $value );

		/**
		 * @method Row::addForeign insert $value in foreign $class_name
		 * @param string $class_name
		 * @param array|Row $value
		 * @return null|Row inserted or not.
		 */
		function addForeign( $class_name, $value );

		/**
		 * @method Row::setForeign update or replace current unique $class_name linked with $value
		 * @param string $class_name
		 * @param array|Row $value
		 * @return null|Row inserted or not.
		 */
		function setForeign( $class_name, $value );
	}
}

