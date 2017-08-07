<?php

Request::getLibrary( "Unit" );

return new Unit( "Component", function( Unit $unit ){

	Request::getLibrary( "Component" );

	$unit->
		expectClassExists( "Component", "Can run tests!" );

});