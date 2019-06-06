<?php

require_once ROOT . "/libraries/Unit.class.php";

return new Amonite\Unit( "Exception", function( Amonite\Unit $unit ){

	require_once ROOT . "/libraries/CustomException.class.php";

	$unit->section( "Classes are set", function( Amonite\Unit $unit ){

		$unit
			->expectClassExists( "Amonite\\CustomException", "Amonite\\CustomException class exists." )
			->expectClassExists( "Amonite\\ErrorException", "Amonite\\ErrorException class exists." )
			->expectClassExists( "Amonite\\WarningException", "Amonite\\WarningException class exists." )
			->expectClassExists( "Amonite\\ParseException", "Amonite\\ParseException class exists." )
			->expectClassExists( "Amonite\\NoticeException", "Amonite\\NoticeException class exists." )
			->expectClassExists( "Amonite\\CoreErrorException", "Amonite\\CoreErrorException class exists." )
			->expectClassExists( "Amonite\\CoreWarningException", "Amonite\\CoreWarningException class exists." )
			->expectClassExists( "Amonite\\CompileErrorException", "Amonite\\CompileErrorException class exists." )
			->expectClassExists( "Amonite\\CompileWarningException", "Amonite\\CompileWarningException class exists." )
			->expectClassExists( "Amonite\\UserErrorException", "Amonite\\UserErrorException class exists." )
			->expectClassExists( "Amonite\\UserWarningException", "Amonite\\UserWarningException class exists." )
			->expectClassExists( "Amonite\\UserNoticeException", "Amonite\\UserNoticeException class exists." )
			->expectClassExists( "Amonite\\StrictException", "Amonite\\StrictException class exists." )
			->expectClassExists( "Amonite\\RecoverableErrorException", "Amonite\\RecoverableErrorException class exists." )
			->expectClassExists( "Amonite\\DeprecatedException", "Amonite\\DeprecatedException class exists." )
			->expectClassExists( "Amonite\\UserDeprecatedException", "Amonite\\UserDeprecatedException class exists." )
			->expectClassExists( "Amonite\\FatalException", "Amonite\\FatalException class exists." )
			->expectClassExists( "Amonite\\TypeException", "Amonite\\TypeException class exists." )
		;
	});

	$unit->section( "Test", function( Amonite\Unit $unit ){

		$unit
			->assert( Amonite\CustomException::isset_error_handler(), "Exception error handler should be already set." )
			->assert( Amonite\CustomException::isset_fatal_handler(), "Exception fatal handler should be already set." )
		;
	});
});
