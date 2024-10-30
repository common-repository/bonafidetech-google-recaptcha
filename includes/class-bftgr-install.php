<?php
/**
 * Installation Based Hooks
 */

defined ( 'ABSPATH' ) || exit;

class BFTGR_Install {

	/**
	 * Constructor
	 */
	public function __construct() {
		$plugin = plugin_basename ( BFTGR_PLUGIN_FILE );
		add_filter ( "plugin_action_links_$plugin", array ( __CLASS__, 'quick_links' ) );
	}
	
	/**
	 * Quick Links to Plugin's List Page
	 */
	public static function quick_links ( $links ) {
		array_unshift ( $links, '<a href="options-general.php?page=bftgr-settings">' . __( 'Settings', BFTGR_TEXT_DOMAIN ) . '</a>' ); 
		return $links; 
	}
	
	/**
	 * On BFTGR Install
	 */
	public static function install() {
	
		// Setup Default Settings
		include_once( 'admin/class-bftgr-settings.php' );

		$option_sections = BFTGR_Admin_Settings::get_settings();
		if ( ! empty ( $option_sections ) ) {
			foreach ( $option_sections as $option_section ) {
				if ( isset ( $option_section['options'] ) && ! empty ( $option_section['options'] ) ) {
					foreach ( $option_section['options'] as $option ) {
						if ( isset( $option['default'] ) && isset ( $option['name'] ) ) {
							$autoload = isset ( $option['autoload'] ) ? ( bool ) $option['autoload'] : true;
							add_option ( $option['name'], $option['default'], '', ( $autoload ? 'yes' : 'no' ) );
						}
					}
				}
			}
		}
	}

}

new BFTGR_Install ();
