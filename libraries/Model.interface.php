<?php

if( !interface_exists( "Model" ) ) {

	interface Model {

		public function __construct( $name = null );

		public function select( $fields = array(), $where = array(), $limit = 0, $start_at = 0 );
		public function update( $value = array(), $where = array(), $limit = 0, $start_at = 0 );
		public function insert( $value = array() );
		public function count( $where = array(), $limit = 0, $start_at = 0 );
		public function remove( array $where, $limit = 0, $start_at = 0 );
	}
}

