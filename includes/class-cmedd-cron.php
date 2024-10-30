<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class CMEDD_Cron
 */
class CMEDD_Cron {

	/**
	 * Option key
	 * @var string
	 */
	public static $last_data_args   = 'cmedd_cron_last_data_args';

	/**
	 * Options form data
	 * @var array
	 */
	public static $post_data        = 'cmedd_cron_post_data';

	/**
	 * Hold self::$post_data array
	 * @var array
	 */
	private static $_post_data      = array();

	public static function init() {
		self::activator();
	}

	public static function activator() {
		if ( ! wp_next_scheduled ( 'convert_media_edd' ) ) {
			wp_schedule_single_event( time(), 'convert_media_edd' );
		}
	}

	public static function deactivator() {
		wp_clear_scheduled_hook( 'convert_media_edd' );
	}

	/**
	 * @internal
	 */
	public static function start() {
		self::$_post_data = get_option( self::$post_data );

		self::_process();
	}

	private static function _process() {
		$last_data = self::last_data();

		$image_ids      = self::$_post_data['cmedd_media_ids'];
		$processed      = ( empty( $last_data ) ) ? 0 : $last_data['processed'];
		$remaining      = ( empty( $last_data ) ) ? 0 : $last_data['remaining'];
		$total          = count( $image_ids );
		$last_data_args = apply_filters( 'cmedd_cron_last_data_args', array(
			'status'    => 'progress',
			'processed' => $processed,
			'remaining' => $remaining,
			'total'     => $total
		) );
		$images         = cmedd_get_images( array(
			'offset'    => $processed,
			'post__in'  => $image_ids
		) );

		// No images found by given args above
		if ( empty( $images ) ) {
			// Are we done then?
			if ( ! empty( $last_data ) && $last_data['status'] === 'progress' ) {
				$last_data['status']    = 'completed';
				$last_data['remaining'] = 0;

				CMEDD_Log::info( "No further images were found to proceed at offset {$processed}" );
				CMEDD_Log::info( "Processed {$last_data['processed']}, total images were {$last_data['total']}" );

				if ( $last_data['total'] !== $last_data['processed'] ) {
					CMEDD_Log::warn( "Processed images not equal to the the total images." );
				}

				update_option( self::$last_data_args, $last_data, true );

				CMEDD_Log::info( "Cron Job is completed, last insertion was #{$last_data['last_id']}" );

				do_action( 'cmedd_cron_completed', $last_data );
			}

			// Let's just bail if no images found
			self::stop();
			return;
		}

		// Initial Call
		if ( empty( $last_data ) ) {
			do_action( 'cmedd_cron_started' );
		}

		// Temporary counter to remember offset
		$i = 0;
		foreach ( $images as $image ) {
			CMEDD_Log::info( "Task processing, for image #{$image->ID}" );

			// Create a download
			$download = new EDD_Download();
			$post = $download->create( array(
				'post_status'   => 'publish',
				'post_title'    => $image->post_title,
				'post_content'  => self::$_post_data['cmedd_description']
			) );

			// If edd download somehow fails, skip
			if ( $post === false ) {
				CMEDD_Log::error( "Task failed, for image #{$image->ID}, unable to create download, skipping..." );
				continue;
			}

			do_action( 'cmedd_cron_created_download', $download->id );

			// Set post thumbnail
			set_post_thumbnail( $download->id, $image->ID );

			wp_set_object_terms( $download->id, self::$_post_data['cmedd_download_cat'], 'download_category' );

			// Update price
			update_post_meta( $download->id, 'edd_price', self::$_post_data['cmedd_price'] );

			// Update download limit
			update_post_meta( $download->id, '_edd_download_limit', self::$_post_data['cmedd_download_limit'] );

			// Update product notes
			update_post_meta( $download->id, 'edd_product_notes', sanitize_text_field( self::$_post_data['cmedd_download_notes'] ) );

			$i++;

			$last_data_args['processed']    = $processed + $i;
			$last_data_args['remaining']    = $last_data_args['total'] - $last_data_args['processed'];
			$last_data_args['last_id']      = $download->id;

			update_option( self::$last_data_args, $last_data_args, true );

			CMEDD_Log::info( "Task succeeded, #{$last_data_args['processed']}, for image #{$image->ID} and new edd download #{$download->ID}" );

			do_action( 'cmedd_cron_updated_download', $download->id, $last_data_args );
		}

		// Free up memory usage
		sleep( 1 );

		// Recursive behavior
		self::_process();
	}

	/**
	 * Clear cron schedular
	 */
	public static function stop() {
		do_action( 'cmedd_cron_before_stop' );
		self::deactivator();
	}

	/**
	 * @return mixed
	 */
	public static function last_data() {
		return get_option( self::$last_data_args );
	}

	/**
	 * Force reset data, to start fresh
	 */
	public static function reset_data() {
		// In case of cron is in the queue
		self::stop();

		// Remove data leftovers
		delete_option( self::$last_data_args );
		delete_option( self::$post_data );
	}

	/**
	 * Check if cron is running
	 * @return bool
	 */
	public static function is_running() {
		$last_data_args = self::last_data();

		if ( ! empty( $last_data_args ) && $last_data_args['status'] === 'progress' ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if cron is completed
	 * @return bool
	 */
	public static function is_completed() {
		$last_data_args = self::last_data();

		if ( ! empty( $last_data_args ) && $last_data_args['status'] === 'completed' ) {
			return true;
		}

		return false;
	}

	public static function js_data() {
		return apply_filters( 'cmedd_cron_js_data', array(
			'data'      => self::last_data(),
			'running'   => self::is_running(),
			'log_url'   => CMEDD_Log::url()
		) );
	}

	/**
	 * @param $post $_POST
	 */
	public static function sanitize_data( $post ) {
		$final_data = array(
			'cmedd_media_ids'   => array(),
			'cmedd_price'       => null,
			'cmedd_description' => ''
		);

		if ( isset( $post['cmedd_media_ids'] ) && ! empty( $post['cmedd_media_ids'] ) ) {
			$final_data['cmedd_media_ids'] = cmedd_get_attach_ids_array( $post['cmedd_media_ids'] );
		}

		if ( isset( $post['cmedd_description'] ) && ! empty( $post['cmedd_description'] ) ) {
			$final_data['cmedd_description'] = $post['cmedd_description'];
		}

		if ( isset( $post['cmedd_download_cat'] ) && ! empty( $post['cmedd_download_cat'] ) ) {
			$cat_ids = $post['cmedd_download_cat'];

			if ( ! empty( $cat_ids ) ) {
				$cat_ids = array_map( 'intval', array_map( 'trim', $cat_ids ) );
				$cat_ids = array_unique( array_filter( $cat_ids ) );
				$final_data['cmedd_download_cat'] = $cat_ids;
			}
		}

		if ( isset( $post['cmedd_price'] ) && ! empty( $post['cmedd_price'] ) ) {
			$final_data['cmedd_price'] = sanitize_text_field( $post['cmedd_price'] );
		}

		if ( isset( $post['cmedd_download_limit'] ) && ! empty( $post['cmedd_download_limit'] ) ) {
			$final_data['cmedd_download_limit'] = sanitize_text_field( $post['cmedd_download_limit'] );
		}

		if ( isset( $post['cmedd_download_notes'] ) && ! empty( $post['cmedd_download_notes'] ) ) {
			$final_data['cmedd_download_notes'] = $post['cmedd_download_notes'];
		}

		update_option( self::$post_data, $final_data, true );
	}
}
