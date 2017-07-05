<?php

$unit = Request::getLibrary( "units/CustomException.unit.php" );
if( $unit instanceof Unit )
	$unit->toString( true );

$unit = Request::getLibrary( "units/Unit.unit.php" );
if( $unit instanceof Unit )
	echo $unit->toString();

