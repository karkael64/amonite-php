<?php

require_once ROOT . "/libraries/Unit.class.php";

return new Amonite\Unit( "Unit", function( Amonite\Unit $unit ){

	$unit
		->assert( class_exists( "Amonite\\Unit" ), "Class Amonite\\Unit exists." )
		->expectStrict( count( get_class_methods( $unit ) ), 25, "Class Amonite\\Unit has many methods." )
		->expectStrict( count( $unit->getLines() ), 2, "This instance has currently 2 assertions.")
		->expectClassHasMethod( "Amonite\\Unit", "log", "Amonite\\Unit class has log function." )
		->expectClassHasMethod( "Amonite\\Unit", "assert", "Amonite\\Unit class has assert function." )
		->expectClassHasMethod( "Amonite\\Unit", "expect", "Amonite\\Unit class has expect function." )
		->expectStrict( count( $unit->getLines() ), 6, "This instance has currently 6 assertions.")
	;
});
