<?php

Request::getLibrary( "Unit" );

return new Unit( "Unit", function( Unit $unit ){

	$unit
		->assert( class_exists( "Unit" ), "Class Unit exists." )
		->expectStrict( count( get_class_methods( $unit ) ), 25, "Class Unit has many methods." )
		->expectClassHasMethod( "Unit", "log", "Unit class has log function." )
		->expectClassHasMethod( "Unit", "assert", "Unit class has assert function." )
		->expectClassHasMethod( "Unit", "expect", "Unit class has expect function." )
		;
});

