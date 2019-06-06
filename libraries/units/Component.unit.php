<?php

require_once ROOT . "/libraries/Unit.class.php";

return new Amonite\Unit( "Component", function( Amonite\Unit $unit ){

	require_once ROOT . "/libraries/Component.class.php";

	$unit->
		expectClassExists( "Amonite\\Component", "Can run tests!" );

});
