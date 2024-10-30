<?php
/**
 * Plugin Name: Bonafide Tech Google Recaptcha for WordPress
 * Description: A perfect security solution to protect your website from spam and abuse.
 * Version: 1.0
 * Author: The Bonafide Team
 * Author URI: https://bonafidetech.com/
 */

defined ( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BFTGR' ) ) :

	/**
	 * Main Class
	 */
	final class BFTGR {

		/**
		 * The Single Instance of Class
		 */
		protected static $_instance = null;

		/**
		 * Main Instance
		 */
		public static function instance () {
			if ( is_null ( self::$_instance ) ) {
				self::$_instance = new self ();
			}

			return self::$_instance;
		}

		/**
		 * Constructor
		 */
		public function __construct () {

			// Define Constants
			$this->define_constants ();

			// Include Required Files
			$this->includes ();

			// Hooks
			$this->init_hooks ();
		}


		/**
		 * Define Constants
		 */
		private function define_constants () {
			define ( 'BFTGR_PLUGIN_FILE', __FILE__ );
			define ( 'BFTGR_PLUGIN_BASENAME', plugin_basename ( __FILE__ ) );
			define ( 'BFTGR_PLUGIN_URL', untrailingslashit ( plugins_url ( '/', __FILE__ ) ) );
			define ( 'BFTGR_PLUGIN_PATH', untrailingslashit ( plugin_dir_path ( __FILE__ ) ) );
			define ( 'BFTGR_TEXT_DOMAIN', 'bftgr' );
		}

		/**
		 * Include Core Files
		 */
		private function includes () {
			include_once ( 'includes/bftgr-functions.php' );
			include_once ( 'includes/class-bftgr-install.php' );

			if ( is_admin () ) {
				include_once ( 'includes/admin/class-bftgr-admin-assets.php' );
				include_once ( 'includes/admin/class-bftgr-settings.php' );
			} else {
				include_once ( 'includes/class-bftgr-frontend-assets.php' );
				include_once ( 'includes/class-bftgr-hooks.php' );
			}
		}

		/**
		 * Action Hooks and Filters
		 */
		private function init_hooks () {
			register_activation_hook ( __FILE__, array ( 'BFTGR_Install', 'install' ) );
			add_action ( 'init', array ( $this, 'init' ), 0 );
		}

		/**
		 * Init Localisation Files
		 */
		public function init () {
			$this->load_plugin_textdomain();
		}

		/**
		 * Load Localisation Files
		 */
		public function load_plugin_textdomain () {
			load_plugin_textdomain ( 'bftgr', false, plugin_basename ( dirname ( __FILE__ ) ) . '/i18n/languages' );
		}

	}

endif;

BFTGR::instance ();
