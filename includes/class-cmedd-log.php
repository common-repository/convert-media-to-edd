<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class CMEDD_Log
 */
class CMEDD_Log {

	// Log file absolute path
	public static $log_file;

	// File pointer
	public static $file;

	// Indicates the file path is setup
	public static $setup = false;

	public static function _new() {
		self::setup_log();
		// Empty log file
		self::log( '', null, true );
	}

	/**
	 * @param string $content
	 * @param string $type
	 * @param bool $reset
	 * @return void
	 */
	public static function log( $content = '', $type = 'info', $reset = false ) {

		if ( ! self::$setup ) {
			self::setup_log();
		}

		$reset = $reset ? 'w' : 'a';

		// Todo: Open file once
		self::$file   = fopen( self::$log_file, $reset );

		if ( $reset === 'w' ) {
			fclose( self::$file );
			return;
		}

		$prefix = "[" . current_time( 'mysql' ) . "] [{$type}]: ";
		$data   = $prefix . $content . PHP_EOL;

		// Fail silently
		if ( fwrite( self::$file,  $data) === false ) {
			return;
		}
	}

	/**
	 * Alias of self::log()
	 *
	 * @see CMEDD_Log::log()
	 * @param $content
	 */
	public static function info( $content ) {
		self::log( $content );
	}

	/**
	 * @param $content
	 */
	public static function warn( $content ) {
		self::log( $content, 'warn' );
	}

	/**
	 * @param $content
	 */
	public static function error( $content ) {
		self::log( $content, 'error' );
	}

	/**
	 * @return string
	 */
	public static function read() {
		if ( ! self::$setup ) {
			self::setup_log();
		}

		$handle     = fopen( self::$log_file, 'r' );
		$contents   = fread( $handle, filesize( self::$log_file ) );
		fclose( $handle );
		return $contents;
	}

	/**
	 * Close opened
	 */
	public static function close() {
		if ( self::$file ) {
			fclose( self::$file );
		}
	}

	public static function url() {
		return content_url( '/cmedd_cron/cmedd.log' );
	}

	private static function setup_log() {
		self::$log_file = WP_CONTENT_DIR . '/cmedd_cron/cmedd.log';
		self::$setup    = true;
		self::create_dir();
	}

	private static function create_dir() {
		$dir = WP_CONTENT_DIR . '/cmedd_cron';

		if ( ! is_dir( $dir ) ) {
			mkdir( $dir, 0755, true );
		}
	}
}
