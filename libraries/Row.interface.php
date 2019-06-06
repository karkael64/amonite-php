<?php

namespace Amonite;

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

		function __call( $name, $arguments );
	}
}
