<?php

if( !class_exists( "Backup" ) ) {

	class Backup {


		static $backup_folder = ROOT . "/databases/backup";
		static $files_folder = ROOT . "/databases/datafiles";

		const MAX_DAYS = 6;
		const MAX_WEEK = 3;
		const MAX_MONTH = 11;
		const MAX_YEAR = 99;


		// body private functions

		private static function getFileList() {
			$res = array();
			$files = scandir( self::$files_folder );
			foreach( $files as $file ) {
				if( !( $file === "." or $file === ".." ) ) {
					$res[] = self::$files_folder . "/" . $file;
				}
			}
			return $res;
		}

		private static function getBackupList() {
			$res = array();
			$backups = scandir( self::$backup_folder );
			foreach( $backups as $backup ) {
				if( preg_match( '/.backup$/', $backup ) ) {
					$res[] = self::$backup_folder . "/" . $backup;
				}
			}
			return $res;
		}

		private static function getContent() {
			$content = "";
			$files = self::getFileList();
			foreach( $files as $file ) {
				$content .= "#$file" . "\n";
				$content .= file_get_contents( $file ) . "\n";
			}
			return $content;
		}

		private static function autoSave() {

			if( !file_exists( $day_file = self::getDayName() ) ) {
				$content = self::getContent();
				file_put_contents( $day_file, $content );

				if( !file_exists( $week_file = self::getWeekName() ) ) {
					file_put_contents( $week_file, $content );

					if( !file_exists( $month_file = self::getMonthName() ) ) {
						file_put_contents( $month_file, $content );

						if( !file_exists( $year_file = self::getYearName() ) ) {
							file_put_contents( $year_file, $content );
						}
					}
				}
				return true;
			}
			return false;
		}

		private static function autoRemove() {

			$i = 0;
			$files = self::getBackupList();
			$day_files = array();
			$week_files = array();
			$month_files = array();
			$year_files = array();

			foreach( $files as $file ) {
				if( preg_match( '/day_\d{4}-\d\d-\d\d\.backup$/', $file ) ) {
					$day_files[] = $file;
				}
				elseif( preg_match( '/week_\d{4}-\d\d\.backup$/', $file ) ) {
					$week_files[] = $file;
				}
				elseif( preg_match( '/month_\d{4}-\d\d\.backup$/', $file ) ) {
					$month_files[] = $file;
				}
				elseif( preg_match( '/year_\d{4}\.backup$/', $file ) ) {
					$year_files[] = $file;
				}
			}

			sort( $day_files );
			sort( $week_files );
			sort( $month_files );
			sort( $year_files );

			while( count( $day_files ) > self::MAX_DAYS ) {
				unlink( array_shift( $day_files ) );
				$i++;
			}
			while( count( $week_files ) > self::MAX_WEEK ) {
				unlink( array_shift( $week_files ) );
				$i++;
			}
			while( count( $month_files ) > self::MAX_MONTH ) {
				unlink( array_shift( $month_files ) );
				$i++;
			}
			while( count( $year_files ) > self::MAX_YEAR ) {
				unlink( array_shift( $year_files ) );
				$i++;
			}
			return $i;
		}

		private static function getDayName() {
			$dt = new DateTime();
			$dt->setTimestamp( self::now_ms() );
			return self::$backup_folder . "/day_" . $dt->format( 'Y-m-d' ) . ".backup";
		}

		private static function getWeekName() {
			$dt = new DateTime();
			$dt->setTimestamp( self::now_ms() );
			return self::$backup_folder . "/week_" . $dt->format( 'Y-w' ) . ".backup";
		}

		private static function getMonthName() {
			$dt = new DateTime();
			$dt->setTimestamp( self::now_ms() );
			return self::$backup_folder . "/month_" . $dt->format( 'Y-m' ) . ".backup";
		}

		private static function getYearName() {
			$dt = new DateTime();
			$dt->setTimestamp( self::now_ms() );
			return self::$backup_folder . "/year_" . $dt->format( 'Y' ) . ".backup";
		}

		static function now_ms() {
			return round( microtime( true ) );
		}


		private static function readLine( $handler ) {
			if( !is_null( $handler ) ) {
				return fgets( $handler );
			}
			return null;
		}

		private static function writeLine( $handler, $line ) {
			if( !is_null( $handler ) ) {
				return fputs( $handler, $line );
			}
			return null;
		}

		private static function isSection( $line ) {
			return substr( $line, 0, 1 ) === "#";
		}


		// public functions

		public static function auto() {

			if( self::autoSave() ) {
				self::autoRemove();
				return true;
			}
			else {
				return false;
			}
		}

		public static function revertFileWithBackup( $file_path, $backup ) {

			if( file_exists( $backup ) ) {
				$file_handler = null;
				$backup_handler = fopen( $backup, 'r' );
				$should_write = false;
				while( ( $line = self::readLine( $backup_handler ) ) !== false ) {
					if( self::isSection( $line ) ) {
						if( $should_write )
							break;
						$file = substr( $line, 1, strlen( $line )-2 );
						if( $should_write = ( $file === $file_path ) )
							$file_handler = fopen( $file, 'w' );
					}
					elseif( $should_write ) {
						self::writeLine( $file_handler, $line );
					}
				}
				fclose( $backup_handler );
				fclose( $file_handler );
			}

			return false;
		}

		public static function revertBackup( $backup ) {

			if( file_exists( $backup ) ) {
				$file_handler = null;
				$backup_handler = fopen( $backup, 'r' );
				while( ( $line = self::readLine( $backup_handler ) ) !== false ) {
					if( self::isSection( $line ) ) {
						if( !is_null( $file_handler ) )
							fclose( $file_handler );
						$file = substr( $line, 1, strlen( $line )-2 );
						$file_handler = fopen( $file, 'w' );
					}
					else {
						self::writeLine( $file_handler, $line );
					}
				}
				fclose( $backup_handler );
				fclose( $file_handler );
			}

			return $backup;
		}

		public static function revertDay() {
			$files = self::getBackupList();
			$day_files = array();

			foreach( $files as $file ) {
				if( preg_match( '/day_\d{4}-\d\d-\d\d\.backup$/', $file ) ) {
					$day_files[] = $file;
				}
			}

			sort( $day_files );

			return self::revertBackup( end( $day_files ) );
		}

		public static function revertWeek() {
			$files = self::getBackupList();
			$week_files = array();

			foreach( $files as $file ) {
				if( preg_match( '/week_\d{4}-\d\d\.backup$/', $file ) ) {
					$week_files[] = $file;
				}
			}

			sort( $week_files );

			return self::revertBackup( end( $week_files ) );
		}

		public static function revertMonth() {
			$files = self::getBackupList();
			$month_files = array();

			foreach( $files as $file ) {
				if( preg_match( '/month_\d{4}-\d\d\.backup$/', $file ) ) {
					$month_files[] = $file;
				}
			}

			sort( $month_files );

			return self::revertBackup( end( $month_files ) );
		}

		public static function revertYear() {
			$files = self::getBackupList();
			$year_files = array();

			foreach( $files as $file ) {
				if( preg_match( '/year_\d{4}\.backup$/', $file ) ) {
					$year_files[] = $file;
				}
			}

			sort( $year_files );

			return self::revertBackup( end( $year_files ) );
		}
	}
}