<?php

if( !class_exists( "ModelPDO" ) ) {

	class ModelPDO implements Model {

		const ID = "id";
		protected static $pdo = null;

		static function setPDO( PDO $pdo ) {
			self::$pdo = $pdo;
		}


		static public function select( $fields = array(), $where = array(), $limit = 0, $start_at = 0 ) {

			$pdo = self::$pdo;
			if( $pdo instanceof PDO ) {

				$req = "SELECT " . self::getFields( $fields ) . " "
					. "FROM " . $pdo->quote( self::getName() ) . " "
					. self::getWhere( $where ) . " "
					. self::getLimit( $limit, $start_at ) . ";";

				$query = $pdo->prepare( $req );
				$query->execute();
				return $query->fetchAll( PDO::FETCH_ASSOC );
			}

			return null;
		}

		static public function selectFirst( $fields = array(), $where = array(), $start_at = 0 ) {

			$pdo = self::$pdo;
			if( $pdo instanceof PDO ) {

				$req = "SELECT " . self::getFields( $fields ) . " "
					. "FROM " . $pdo->quote( self::getName() ) . " "
					. self::getWhere( $where ) . " "
					. self::getLimit( 1, $start_at ) . ";";

				$query = $pdo->prepare( $req );
				$query->execute();
				$rows = $query->fetchAll( PDO::FETCH_ASSOC );
				return isset( $rows[ 0 ] ) ? $rows[ 0 ] : null;
			}

			return null;
		}

		static public function count( $where = array(), $limit = 0, $start_at = 0 ) {

			$pdo = self::$pdo;
			if( $pdo instanceof PDO ) {

				$req = "SELECT count( * ) as c "
					. "FROM " . $pdo->quote( self::getName() ) . " "
					. self::getWhere( $where ) . " "
					. self::getLimit( $limit, $start_at ) . ";";

				$query = $pdo->prepare( $req );
				$query->execute();
				$all = $query->fetchAll( PDO::FETCH_ASSOC );
				return $all[ 0 ][ "c" ];
			}

			return null;
		}


		static public function remove( array $where, $limit = 0, $start_at = 0 ) {

			$pdo = self::$pdo;
			if( $pdo instanceof PDO && count( $where ) ) {

				$req = "DELETE FROM " . $pdo->quote( self::getName() ) . " "
					. self::getWhere( $where ) . " "
					. self::getLimit( $limit, $start_at ) . ";";

				return $pdo->exec( $req );
			}

			return null;
		}

		static public function update( $value = array(), $where = array(), $limit = 0, $start_at = 0 ) {

			$pdo = self::$pdo;
			if( $pdo instanceof PDO && count( $value ) ) {

				$req = "UPDATE " . $pdo->quote( self::tableName ) . " "
					. "SET " . self::getUpdateValues( $value ) . " "
					. self::getWhere( $where ) . ";";

				return $pdo->exec( $req );
			}

			return null;
		}

		static public function insert( $value = array() ) {

			$pdo = self::$pdo;
			if( $pdo instanceof PDO && count( $value ) ) {

				$req = "INSERT INTO " . $pdo->quote( self::getName() ) . " "
					. "VALUES " . self::getInsertValues( $value ) . ";";

				return $pdo->exec( $req );
			}

			return null;
		}


		static protected function getInsertValues( $values ) {

			$pdo = self::$pdo;
			if( $pdo instanceof PDO && count( $values ) ) {
				foreach( $values as &$value ) {
					$value = $pdo->quote( $value );
				}
				return "( " . implode( ", ", $values ) . " )";
			}
			return "";
		}

		static protected function getUpdateValues( $values ) {

			$pdo = self::$pdo;
			if( $pdo instanceof PDO && count( $values ) ) {
				$res = array();
				foreach( $values as $field => $value ) {
					array_push( $res, $pdo->quote( $field ) . " = " . $pdo->quote( $value ) );
				}
				return implode( ", ", $values );
			}
			return "";
		}

		static protected function getFields( $fields ) {
			if( is_array( $fields ) && count( $fields ) ) {
				$res = array();
				$pdo = self::$pdo;
				if( $pdo instanceof PDO ) {
					foreach( $fields as $key => $value ) {
						if( is_numeric( $key ) )
							array_push( $res, $pdo->quote( $value ) );
						else
							array_push( $res, $pdo->quote( $key ) );
					}
				}
				return implode( ", ", $res );
			} else {
				return "*";
			}
		}

		static protected function getLimit( $limit = 0, $start_at = 0 ) {
			if( $limit > 0 ) {
				if( $start_at > 0 ) {
					return "LIMIT $start_at, $limit";
				} else {
					return "LIMIT $limit";
				}
			} else {
				return "";
			}
		}

		static private function getName() {
			return file_basename( get_called_class() );
		}

		const KEY_AND = "&and";
		const KEY_OR = "&or";
		const KEY_BETWEEN = "&between";
		const KEY_IN = "&in";

		static protected function getWhere( $where ) {
			return is_array( $where ) ? "WHERE " . self::getWhereAnd( $where ) : "";
		}

		static protected function getWhereAnd( $where ) {
			if( is_array( $where ) && count( $where ) ) {
				$res = array();
				$pdo = self::$pdo;
				if( $pdo instanceof PDO ) {
					foreach( $where as $field => $value ) {
						if( is_array( $value ) ) {
							if( $field === self::KEY_AND )
								$v = self::getWhereAnd( $field );
							elseif( $field === self::KEY_OR )
								$v = self::getWhereOr( $field );
							elseif( $field === self::KEY_BETWEEN )
								$v = self::getWhereBetween( $field );
							elseif( $field === self::KEY_IN )
								$v = self::getWhereIn( $field );

							if( isset( $v ) and !is_null( $v ) and strlen( $v ) )
								array_push( $res, $v );
						} else {
							if( $value === null )
								array_push( $res, $pdo->quote( $field ) . " IS NULL" );
							else
								array_push( $res, $pdo->quote( $field ) . " = " . $pdo->quote( $value ) );
						}
					}
				}
				return "( " . implode( " AND ", $res ) . " )";
			}
			return NULL;
		}

		static protected function getWhereOr( $where ) {
			if( is_array( $where ) && count( $where ) ) {
				$res = array();
				$pdo = self::$pdo;
				if( $pdo instanceof PDO ) {
					foreach( $where as $field => $value ) {
						if( is_array( $value ) ) {
							if( $field === self::KEY_AND )
								$v = self::getWhereAnd( $field );
							elseif( $field === self::KEY_OR )
								$v = self::getWhereOr( $field );
							elseif( $field === self::KEY_BETWEEN )
								$v = self::getWhereBetween( $field );
							elseif( $field === self::KEY_IN )
								$v = self::getWhereIn( $field );

							if( isset( $v ) and !is_null( $v ) and strlen( $v ) )
								array_push( $res, $v );
						} else {
							array_push( $res, $pdo->quote( $field ) . " = " . $pdo->quote( $value ) );
						}
					}
				}
				return "( " . implode( " OR ", $res ) . " )";
			}
			return NULL;
		}

		static protected function getWhereBetween( $where ) {
			if( is_array( $where ) && count( $where ) ) {
				$res = array();
				$pdo = self::$pdo;
				if( $pdo instanceof PDO ) {
					foreach( $where as $field => $values ) {
						$min = $pdo->quote( min( $values ) );
						$max = $pdo->quote( max( $values ) );
						$field = $pdo->quote( $field );
						array_push( $res, "( $field BETWEEN $min AND $max )" );
					}
				}
				return "( " . implode( " AND ", $res ) . " )";
			}
			return NULL;
		}

		static protected function getWhereIn( $where ) {
			if( is_array( $where ) && count( $where ) ) {
				$res = array();
				$pdo = self::$pdo;
				if( $pdo instanceof PDO ) {
					foreach( $where as $field => $values ) {
						foreach( $values as &$value ) {
							$value = $pdo->quote( $value );
						}
						$field = $pdo->quote( $field );
						array_push( $res, "( $field IN ( " . implode( ", ", $values ) . " ) )" );
					}
				}
				return "( " . implode( " AND ", $res ) . " )";
			}
			return NULL;
		}
	}
}

