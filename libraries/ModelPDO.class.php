<?php

if( !class_exists( "ModelPDO" ) ) {

	class ModelPDO implements Model {

		protected $tableName = "";
		protected static $pdo = null;

		static function setPDO( PDO $pdo ) {
			self::$pdo = $pdo;
		}

		public function __construct( $name = null ) {

			$this->tableName = file_basename( ( is_string( $name ) and strlen( $name ) ) ? $name : get_class( $this ), "Model" );
		}


		public function select( $fields = array(), $where = array(), $limit = 0, $start_at = 0 ) {

			$pdo = self::$pdo;
			if( $pdo instanceof PDO ) {

				$req = "SELECT " . $this->getFields( $fields ) . " "
					. "FROM " . $pdo->quote( $this->tableName ) . " "
					. $this->getWhere( $where ) . " "
					. $this->getLimit( $limit, $start_at ) . ";";

				$query = $pdo->prepare( $req );
				$query->execute();
				return $query->fetchAll( PDO::FETCH_ASSOC );
			}

			return null;
		}

		public function count( $where = array(), $limit = 0, $start_at = 0 ) {

			$pdo = self::$pdo;
			if( $pdo instanceof PDO ) {

				$req = "SELECT count( * ) as c "
					. "FROM " . $pdo->quote( $this->tableName ) . " "
					. $this->getWhere( $where ) . " "
					. $this->getLimit( $limit, $start_at ) . ";";

				$query = $pdo->prepare( $req );
				$query->execute();
				$all = $query->fetchAll( PDO::FETCH_ASSOC );
				return $all[ 0 ][ "c" ];
			}

			return null;
		}


		public function remove( array $where, $limit = 0, $start_at = 0 ) {

			$pdo = self::$pdo;
			if( $pdo instanceof PDO && count( $where ) ) {

				$req = "DELETE FROM " . $pdo->quote( $this->tableName ) . " "
					. $this->getWhere( $where ) . " "
					. $this->getLimit( $limit, $start_at ) . ";";

				return $pdo->exec( $req );
			}

			return null;
		}

		public function update( $value = array(), $where = array(), $limit = 0, $start_at = 0 ) {

			$pdo = self::$pdo;
			if( $pdo instanceof PDO && count( $value ) ) {

				$req = "UPDATE " . $pdo->quote( $this->tableName ) . " "
					. "SET " . $this->getUpdateValues( $value ) . " "
					. $this->getWhere( $where ) . ";";

				return $pdo->exec( $req );
			}

			return null;
		}

		public function insert( $value = array() ) {

			$pdo = self::$pdo;
			if( $pdo instanceof PDO && count( $value ) ) {

				$req = "INSERT INTO " . $pdo->quote( $this->tableName ) . " "
					. "VALUES " . $this->getInsertValues( $value ) . ";";

				return $pdo->exec( $req );
			}

			return null;
		}


		protected function getInsertValues( $values ) {

			$pdo = self::$pdo;
			if( $pdo instanceof PDO && count( $values ) ) {
				foreach( $values as &$value ) {
					$value = $pdo->quote( $value );
				}
				return "( " . implode( ", ", $values ) . " )";
			}
			return "";
		}

		protected function getUpdateValues( $values ) {

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

		protected function getFields( $fields ) {
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

		protected function getLimit( $limit = 0, $start_at = 0 ) {
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

		const KEY_AND = "&and";
		const KEY_OR = "&or";
		const KEY_BETWEEN = "&between";
		const KEY_IN = "&in";

		protected function getWhere( $where ) {
			return is_array( $where ) ? "WHERE " . $this->getWhereAnd( $where ) : "";
		}

		protected function getWhereAnd( $where ) {
			if( is_array( $where ) && count( $where ) ) {
				$res = array();
				$pdo = self::$pdo;
				if( $pdo instanceof PDO ) {
					foreach( $where as $field => $value ) {
						if( is_array( $value ) ) {
							if( $field === self::KEY_AND )
								$v = $this->getWhereAnd( $field );
							elseif( $field === self::KEY_OR )
								$v = $this->getWhereOr( $field );
							elseif( $field === self::KEY_BETWEEN )
								$v = $this->getWhereBetween( $field );
							elseif( $field === self::KEY_IN )
								$v = $this->getWhereIn( $field );

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

		protected function getWhereOr( $where ) {
			if( is_array( $where ) && count( $where ) ) {
				$res = array();
				$pdo = self::$pdo;
				if( $pdo instanceof PDO ) {
					foreach( $where as $field => $value ) {
						if( is_array( $value ) ) {
							if( $field === self::KEY_AND )
								$v = $this->getWhereAnd( $field );
							elseif( $field === self::KEY_OR )
								$v = $this->getWhereOr( $field );
							elseif( $field === self::KEY_BETWEEN )
								$v = $this->getWhereBetween( $field );
							elseif( $field === self::KEY_IN )
								$v = $this->getWhereIn( $field );

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

		protected function getWhereBetween( $where ) {
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

		protected function getWhereIn( $where ) {
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