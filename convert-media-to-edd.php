<?php
/**
 * Plugin Name: Convert Media to EDD
 * Plugin URI: https://wooninjas.com
 * Description: This EDD addon converts the selected media library images to EDD downloads
 * Version: 1.0.0
 * Author: WooNinjas
 * Author URI: https://wooninjas.com
 * Text Domain: cmedd
 * License: GPLv2 or later
 */

if ( ! defined( "ABSPATH" ) ) exit;

// Directory
define( 'CMEDD_DIR', plugin_dir_path( __FILE__ ) );
define( 'CMEDD_DIR_FILE', CMEDD_DIR . basename( __FILE__ ) );
define( 'CMEDD_DIR_INCLUDES', trailingslashit( CMEDD_DIR . 'includes' ) );

// URLS
define( 'CMEDD_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );
define( 'CMEDD_ASSETS_URL', trailingslashit( CMEDD_URL . 'assets' ) );

/**
 * Class Convert_Media_EDD for plugin initiation
 *
 * @since 1.0
 */
final class Convert_Media_EDD {
	const VERSION = '1.0.0';

	/**
	 * @var Convert_Media_EDD
	 */
	private static $instance    = null;

	/**
	 * @var CMEDD_Options
	 */
	public $options = null;

	protected function __construct() {
		if ( self::require_dependency() ) {
			add_action( 'admin_notices', array( __CLASS__, 'dependency_notice' ) );
			return;
		}

		// Adding settings tab
		add_filter( 'plugin_action_links_' . plugin_basename( CMEDD_DIR_FILE ), function ( $links ) {
			return array_merge( $links, array(
				sprintf(
					'<a href="%s">Options</a>',
					admin_url( 'admin.php?page=cmedd-options' )
				),
			) );
		} );

		$this->includes();

		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * @since 1.0
	 * @return $this
	 */
	public static function instance() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof Convert_Media_EDD ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Checks external plugin dependency
	 *
	 * @since 1.0
	 * @return bool
	 */
	public static function require_dependency() {
		/**
		 * First check if EDD plugin is installed?
		 */
		if ( ! function_exists( 'EDD' ) ) {
			// Ok it's not, bail here
			return true;
		}

		return false;
	}

	/**
	 * Print dependency required notice
	 *
	 * @since 1.0
	 */
	public static function dependency_notice() {
		$message = sprintf(
			'<div class="error"><p>%s</p></div>',
			__( 'The <a href="https://wordpress.org/plugins/easy-digital-downloads/">Easy Digital Downloads</a> plugin is required by <strong>Convert Media to EDD</strong> plugin', 'cmedd' )
		);
		echo $message;
	}

	/**
	 * Activation function hook
	 *
	 * @since 1.0
	 * @return void
	 */
	public function activation() {
		if ( ! current_user_can( 'activate_plugins' ) )
			return;
	}

	/**
	 * Deactivation function hook
	 * No used in this plugin
	 *
	 * @since 1.0
	 * @return void
	 */
	public function deactivation() {
		CMEDD_Cron::deactivator();
	}

	private function includes() {
		require_once CMEDD_DIR_INCLUDES . 'functions.php';
		require_once CMEDD_DIR_INCLUDES . 'class-cmedd-log.php';
		require_once CMEDD_DIR_INCLUDES . 'class-cmedd-options.php';
		require_once CMEDD_DIR_INCLUDES . 'class-cmedd-cron.php';
		require_once CMEDD_DIR_INCLUDES . 'class-cmedd-ajax.php';
	}

	/**
	 * Enqueue scripts on admin
	 *
	 * @since 1.0
	 */
	public function admin_enqueue_scripts() {
		$screen = get_current_screen();

		// Only our admin options page
		if ( $screen->id !== CMEDD_Options::$page ) {
			return;
		}

		wp_enqueue_media();

		// Styles
		wp_enqueue_style( 'cmedd-css', CMEDD_ASSETS_URL . 'css/cmedd.css', array(), self::VERSION );
		wp_enqueue_style( 'multiselect-css', CMEDD_ASSETS_URL . 'css/multi-select.css', array(), self::VERSION );

		// Scripts
		wp_enqueue_script( 'multiselect-js', CMEDD_ASSETS_URL . 'js/jquery.multi-select.js', array( 'jquery' ), self::VERSION, true );
		wp_enqueue_script( 'cmedd-js', CMEDD_ASSETS_URL . 'js/cmedd.js', array( 'jquery', 'multiselect-js' ), self::VERSION, true );
		wp_localize_script( 'cmedd-js', 'CMEDD_Cron', CMEDD_Cron::js_data() );
	}
}

/**
 * Convert_Media_EDD instance
 *
 * @return Convert_Media_EDD
 */
function CMEDD() {
	return Convert_Media_EDD::instance();
}

add_action( 'plugins_loaded', 'CMEDD', 101 );
