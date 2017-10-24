<?php

if( !class_exists( "UnitComponent" ) ) {

	require_once ( ROOT . "/libraries/Unit.class.php" );

	class UnitComponent extends Component {

		function onCall( Request $req, Response $res ) {

			throw new HttpCode( 418 );
		}

		function getComponent( Request $req, Response $res ) {

			$all = new Unit( "Unit tests" );

			$all->section( require ( ROOT . "/libraries/units/Unit.unit.php" ) );
			$all->section( require ( ROOT . "/libraries/units/CustomException.unit.php" ) );
			$all->section( require ( ROOT . "/libraries/units/Component.unit.php" ) );

			echo $all->toMime( $this->mime );
			//echo "<pre>" . $all->toString() . "</pre>";
		}
	}
}

