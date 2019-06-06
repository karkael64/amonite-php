<?php

if( !class_exists( "UnitComponent" ) ) {

	require_once ( ROOT . "/libraries/Unit.class.php" );

	class UnitComponent extends Amonite\Component {

		function onCall( Amonite\Request $req, Amonite\Response $res ) {

			throw new Amonite\HttpCode( 418 );
		}

		function getComponent( Amonite\Request $req, Amonite\Response $res ) {

			$all = new Amonite\Unit( "Unit tests" );

			$all->section( require ( ROOT . "/libraries/units/Unit.unit.php" ) );
			$all->section( require ( ROOT . "/libraries/units/CustomException.unit.php" ) );
			$all->section( require ( ROOT . "/libraries/units/Component.unit.php" ) );

			echo $all->toMime( $this->mime );
			//echo "<pre>" . $all->toString() . "</pre>";
		}
	}
}
