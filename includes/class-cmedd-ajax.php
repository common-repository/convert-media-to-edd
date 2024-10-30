<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class CMEDD_Ajax
 */
class CMEDD_Ajax {
	public static function init() {
		$nonce = isset( $_POST['_wp_nonce'] ) ? $_POST['_wp_nonce'] : null;

		if ( ! wp_verify_nonce( $nonce, CMEDD_Options::NONCE ) ) {
			self::send( array( 'msg' => 'Invalid nonce' ), true );
		}

		// Do not schedule job if it's running
		if ( CMEDD_Cron::is_running() ) {
			self::send( CMEDD_Cron::js_data() );
		}

		// Do not schedule job if it's completed and previous completed
		// job is not cleared out
		if ( CMEDD_Cron::is_completed() ) {
			$completed_log = 'Cron Job is already completed, you should reset it and try again.';
			CMEDD_Log::info( $completed_log );
			self::send( array_merge( CMEDD_Cron::js_data(), array( 'msg'=> $completed_log ) ) );
		}

		CMEDD_Cron::sanitize_data( $_POST );

		CMEDD_Cron::init();

		self::send( CMEDD_Cron::js_data() );
	}

	public function cron_polling() {
		self::send( CMEDD_Cron::js_data() );
	}

	public function cron_reset() {

		if ( CMEDD_Cron::is_running() ) {
			self::send();
		}

		if ( ! CMEDD_Cron::last_data() ) {
			self::send( array( 'msg' => 'No previous job found' ) );
		}

		CMEDD_Cron::reset_data();

		self::send( array( 'msg' => 'Cleared previous job.' ) );
	}

	protected static function send( $data = array(), $error = false ) {
		wp_send_json( array_merge( $data, array( 'error' => $error ) ) );
	}
}

add_action( 'wp_ajax_cmedd_cron', array( 'CMEDD_Ajax', 'init' ) );
add_action( 'wp_ajax_cmedd_cron_polling', array( 'CMEDD_Ajax', 'cron_polling' ) );
add_action( 'wp_ajax_cmedd_cron_reset', array( 'CMEDD_Ajax', 'cron_reset' ) );
