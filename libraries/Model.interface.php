<?php
namespace Amonite;

if( !interface_exists( "Model" ) ) {
	interface Model {
		static public function select( $fields = array(), $where = array(), $limit = 0, $start_at = 0 );
		static public function selectFirst( $fields = array(), $where = array(), $start_at = 0 );
		static public function update( $value = array(), $where = array(), $limit = 1, $start_at = 0 );
		static public function insert( $value = array() );
		static public function count( $where = array(), $limit = 0, $start_at = 0 );
		static public function remove( array $where, $limit = 1, $start_at = 0 );
	}
}
