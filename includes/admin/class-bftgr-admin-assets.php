<?php
/**
 * Enqueue CSS/JS Files
 */

defined ( 'ABSPATH' ) || exit;

class BFTGR_Admin_Assets {

	/**
	 * Constructor
	 */
	public function __construct () {
		add_action ( 'admin_enqueue_scripts', array ( __CLASS__, 'load_assets' ) );
	}

	/**
	 * Register/Enqueue Admin Assets
	 */
	public static function load_assets () {
		$screen			= get_current_screen ();
		$screen_id		= $screen ? $screen->id : '';

		// Register Scripts
		$wp_scripts = wp_scripts ();
		
		wp_register_style ( 'bftgr-jquery-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-core']->ver . '/themes/smoothness/jquery-ui.css' );
		wp_register_style ( 'bftgr-main-style', BFTGR_PLUGIN_URL . '/assets/css/admin/style.css' );
		wp_register_script ( 'bftgr-main-script', BFTGR_PLUGIN_URL . '/assets/js/admin/scripts.js', array ( 'jquery' ) );

		// Enqueue Scripts
		if ( in_array ( $screen_id, array ( 'settings_page_bftgr-settings' ) ) ) {
			wp_enqueue_style ( 'bftgr-jquery-ui-css' );
			wp_enqueue_script ( 'jquery-ui-core' );
			wp_enqueue_script ( 'jquery-ui-tabs' );
			
			wp_enqueue_style ( 'bftgr-main-style' );
			wp_enqueue_script ( 'bftgr-main-script' );
		}
	}
}

new BFTGR_Admin_Assets ();
