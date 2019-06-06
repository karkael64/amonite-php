<?php

$unit = require_once ROOT . "/libraries/units/CustomException.unit.php" ;
if( $unit instanceof Amonite\Unit )
	echo $unit->toString();

$unit = require_once ROOT . "/libraries/units/Unit.unit.php" ;
if( $unit instanceof Amonite\Unit )
	echo $unit->toString();
