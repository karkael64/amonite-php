<?php

require_once ROOT . "/libraries/Unit.class.php";

return new Unit( "Component", function( Unit $unit ){

	require_once ROOT . "/libraries/Component.class.php";

	$unit->
		expectClassExists( "Component", "Can run tests!" );

});