<?php
/**
 * Enqueue CSS/JS Files
 */
 
defined ( 'ABSPATH' ) || exit;

class BFTGR_Frontend_Assets {

	/**
	 * @var string site_key
	 */
	public $site_key;
	
	/**
	 * @var string secret_key
	 */
	public $secret_key;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action ( 'wp_enqueue_scripts', array ( __CLASS__, 'register_assets' ) );
		add_action ( 'wp_enqueue_scripts', array ( __CLASS__, 'load_assets' ) );
			
		add_action ( 'login_enqueue_scripts', array ( __CLASS__, 'register_assets' ) );
		add_action ( 'login_enqueue_scripts', array ( __CLASS__, 'load_assets' ) );		
		
	}

	/**
	 * Register Scripts
	 */
	public static function register_assets () {
		$bftgr_captcha_settings = bftgr_captcha_settings ();
		
		if ( empty ( $bftgr_captcha_settings ) ) {
			return;
		}
		
		if( bftgr_disabled_for_current_user () ){
			return;
		}
	
		$site_key		= $bftgr_captcha_settings['site_key'];
		$secret_key		= $bftgr_captcha_settings['secret_key'];
		$language_code	= apply_filters ( 'bftgr_recaptcha_language', 'en' );
	
		if ( empty ( $site_key ) || empty ( $secret_key ) ) {
			return;
		}
		
		// Main Style
		wp_register_style ( 'bftgr-main-style', BFTGR_PLUGIN_URL . '/assets/css/style.css' );
		
		// Google reCaptcha script
		$render 	= bftgr_v_type () == 'v3' ? $site_key : 'explicit';
		$language 	= '&hl=' . $language_code;
		
		wp_register_script ( 'bftgr-google-recapcha-script', '//www.google.com/recaptcha/api.js?onload=bftgr_onloadCallback&render='. $render . $language );
		
		// Main Script
		wp_register_script ( 'bftgr-main-script', BFTGR_PLUGIN_URL . '/assets/js/scripts.js', array ( 'jquery' ) );
	}
	
	/**
	 * Enqueue Scripts
	 */
	public static function load_assets () {
		wp_enqueue_style ( 'bftgr-main-style' );
		wp_enqueue_script ( 'bftgr-google-recapcha-script' );
		wp_enqueue_script ( 'bftgr-main-script' );
		
		$bftgr_settings = array_merge ( bftgr_forms_options (), bftgr_captcha_settings () );
		
		// Localize Vars
		wp_localize_script ( 'bftgr-main-script', 'bftgr', $bftgr_settings );
	}
	
}

new BFTGR_Frontend_Assets ();
