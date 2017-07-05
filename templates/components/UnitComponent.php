<?php

if( !class_exists( "UnitComponent" ) ) {

	Request::getLibrary( "Unit" );

	class UnitComponent extends Component {

		function onCall( Request $req, Response $res ) {

			throw new HttpCode( 418 );
		}

		function getComponent( Request $req, Response $res ) {

			$all = new Unit( "Unit tests" );

			$all->section( Request::getLibrary( "units/CustomException.unit.php" ) );
			$all->section( Request::getLibrary( "units/Unit.unit.php" ) );

			echo $all->toMime( $this->mime );
			//echo "<pre>" . $all->toString() . "</pre>";
		}
	}
}

