<?php

Request::getLibrary( "Unit" );
Request::getLibrary( "CustomException" );

return new Unit( "CustomException", function( Unit $unit ){

	$unit->log( get_declared_classes(), "Declared classes" );

	$unit->section( "Classes are set", function( $unit ){

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
			->expectClassExists( "TypeException", "TypeException class exists." );
	});
});

