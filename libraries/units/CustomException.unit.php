<?php

require_once ROOT . "/libraries/Unit.class.php";

return new Unit( "CustomException", function( Unit $unit ){

	require_once ROOT . "/libraries/CustomException.class.php";

	$unit->section( "Classes are set", function( Unit $unit ){

		$unit
			->expectClassExists( "CustomException", "CustomException class exists." )
			->expectClassExists( "ErrorException", "ErrorException class exists." )
			->expectClassExists( "WarningException", "WarningException class exists." )
			->expectClassExists( "ParseException", "ParseException class exists." )
			->expectClassExists( "NoticeException", "NoticeException class exists." )
			->expectClassExists( "CoreErrorException", "CoreErrorException class exists." )
			->expectClassExists( "CoreWarningException", "CoreWarningException class exists." )
			->expectClassExists( "CompileErrorException", "CompileErrorException class exists." )
			->expectClassExists( "CompileWarningException", "CompileWarningException class exists." )
			->expectClassExists( "UserErrorException", "UserErrorException class exists." )
			->expectClassExists( "UserWarningException", "UserWarningException class exists." )
			->expectClassExists( "UserNoticeException", "UserNoticeException class exists." )
			->expectClassExists( "StrictException", "StrictException class exists." )
			->expectClassExists( "RecoverableErrorException", "RecoverableErrorException class exists." )
			->expectClassExists( "DeprecatedException", "DeprecatedException class exists." )
			->expectClassExists( "UserDeprecatedException", "UserDeprecatedException class exists." )
			->expectClassExists( "FatalException", "FatalException class exists." )
			->expectClassExists( "TypeException", "TypeException class exists." )
		;
	});

	$unit->section( "Test", function( Unit $unit ){

		$unit
			->assert( CustomException::isset_error_handler(), "CustomException error handler should be already set." )
			->assert( CustomException::isset_fatal_handler(), "CustomException fatal handler should be already set." )
		;
	});
});

