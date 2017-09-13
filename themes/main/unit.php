<?php

$unit = require_once ROOT . "/libraries/units/CustomException.unit.php" ;
if( $unit instanceof Unit )
	$unit->toString( true );

$unit = require_once ROOT . "/libraries/units/Unit.unit.php" ;
if( $unit instanceof Unit )
	echo $unit->toString();

