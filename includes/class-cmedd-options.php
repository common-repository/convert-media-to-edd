<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class CMEDD_Options
 */
class CMEDD_Options {
	const NONCE = 'cmedd_options';

	public static $page;

	public static function init() {
		self::$page = add_menu_page(
			__( 'Convert Media to EDD', 'cmedd' ),
			'Convert Media to EDD',
			'manage_options',
			'cmedd-options',
			array( __CLASS__, 'render_page' )
		);
	}

	public static function render_page() {
		$download_categories = self::get_download_categories();
		include_once CMEDD_DIR_INCLUDES . 'admin/options.php';
	}

	public static function get_download_categories() {
		$download_categories = get_terms( array(
            'taxonomy' => 'download_category',
            'hide_empty' => false,
        ) ); 

        return $download_categories;
	}
}

add_action( 'admin_menu', array( 'CMEDD_Options', 'init' ) );
