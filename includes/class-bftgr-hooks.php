<?php
/**
 * WordPress Action Hooks/Filters
 */

defined ( 'ABSPATH' ) || exit;

class BFTGR_Hooks {

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
	public function __construct () {
		add_action ( 'init', array ( $this, 'init' ) );
	}

	public function init () {
		$bftgr_captcha_settings = bftgr_captcha_settings ();

		if ( empty ( $bftgr_captcha_settings ) ) {
			return;
		}

		$this->site_key		= $bftgr_captcha_settings['site_key'];
		$this->secret_key	= $bftgr_captcha_settings['secret_key'];

		if ( empty ( $this->site_key ) || empty ( $this->secret_key ) ) {
			return;
		}

		if( bftgr_disabled_for_current_user () ){
			return;
		}

		/*
		 * Check if bftgr is enable for wordpress login form
		 */
		if ( bftgr_enable_for_wp_login () == 'yes' ) {
			add_action ( 'login_form', array ( $this, 'wp_recaptcha_login' ) );
			add_filter ( 'authenticate', array ( $this, 'validate_recaptcha_for_login_register' ), 60, 3 );
		}

		/*
		 * Check if bftgr is enable for wordpress registration form
		 */
		if ( bftgr_enable_for_wp_registration () == 'yes' ) {
			add_action ( 'register_form', array ( $this, 'wp_recaptcha_register' ) );
			add_filter ( 'registration_errors', array ( $this, 'validate_recaptcha_for_login_register' ), 60, 3 );
		}

		/*
		 * Check if bftgr is enable for wordpress post comment form
		 */
		if ( bftgr_enable_for_wp_comment () == 'yes' ) {
			add_action ( 'comment_form_logged_in_after', array ( $this, 'wp_recaptcha_comment' ), 90 );
			add_action ( 'comment_form_after_fields', array ( $this, 'wp_recaptcha_comment' ), 90 );
			add_filter ( 'preprocess_comment', array ( $this, 'validate_recaptcha_for_comment' ), 60 );
		}

		/*
		 * Check if bftgr is enable for wordpress forgot password form
		 */
		if ( bftgr_enable_for_wp_forgot_pwd () == 'yes' ) {
			add_action ( 'lostpassword_form', array ( $this, 'wp_recaptcha_forgot_password' ) );
			add_action ( 'lostpassword_post', array ( $this, 'validate_recaptcha_for_lostpassword' ) );
		}

		/*
		 * Check if bftgr is enable for woocommerce login form
		 */
		if ( bftgr_enable_for_wc_login () == 'yes' ) {
			add_action ( 'woocommerce_login_form', array ( $this, 'wc_recaptcha_login' ), 99 );
			add_action ( 'authenticate', array ( $this, 'validate_recaptcha_for_login_register' ), 60, 3 );
		}

		/*
		 * Check if bftgr is enable for woocommerce registration form
		 */
		if ( bftgr_enable_for_wc_registration () == 'yes' ) {
			add_action ( 'woocommerce_register_form', array ( $this, 'wc_recaptcha_register' ), 99 );
			add_action ( 'woocommerce_process_registration_errors', array ( $this, 'validate_recaptcha_for_login_register' ), 60, 3 );
		}

		/*
		 * Check if bftgr is enable for woocommerce forgot password form
		 */
		if ( bftgr_enable_for_woocommerce_forgot_pwd () == 'yes' ) {
			add_action ( 'woocommerce_lostpassword_form', array ( $this, 'wc_recaptcha_forgot_password' ), 99 );
			add_action ( 'woocommerce_resetpassword_form', array ( $this, 'wc_recaptcha_forgot_password' ), 99 );	
			add_action ( 'validate_password_reset', array ( $this, 'validate_recaptcha_for_lostpassword' ) );
		}
        
	}
	
	public function wp_recaptcha_login () {
		$this->recaptcha_field ( 'bftgr_wp_recaptcha_login' );
	}
	
	public function wp_recaptcha_register () {
		$this->recaptcha_field ( 'bftgr_wp_recaptcha_register' );
	}
	
	public function wp_recaptcha_comment () {
		$this->recaptcha_field ( 'bftgr_wp_recaptcha_comment' );
	}
	
	public function wp_recaptcha_forgot_password () {
		$this->recaptcha_field ( 'bftgr_wp_recaptcha_forgot_password' );
	}
	
	public function wc_recaptcha_login () {
		$this->recaptcha_field ( 'bftgr_wc_recaptcha_login' );
	}
	
	public function wc_recaptcha_register () {
		$this->recaptcha_field ( 'bftgr_wc_recaptcha_register' );
	}
	
	public function wc_recaptcha_forgot_password () {
		$this->recaptcha_field ( 'bftgr_wc_recaptcha_forgot_password' );
	}

	/*
	 * reCaptcha validation and verification
	 */
	public function validate_recaptcha ( $captcha_data = null, $action = '' ) {
	
		if ( empty( $captcha_data['g-recaptcha-response'] ) ) {

			$captcha_data = $this->reCaptcha_error ( $captcha_data, '', $action );
 
    	} else {
    	
			$recaptcha_data = http_build_query(
				array (
					'secret'	=> $this->secret_key,
					'response'	=> $captcha_data['g-recaptcha-response'],
					'remoteip'	=> $_SERVER['REMOTE_ADDR']
				)
			);

			$recaptcha_response	= wp_remote_retrieve_body( wp_remote_get( "https://www.google.com/recaptcha/api/siteverify?". $recaptcha_data ) );

			if ( ! $recaptcha_response ) {
				return __( 'Failed to validate Recaptcha. For more information please contact us.', BFTGR_TEXT_DOMAIN );
			}

			$result = json_decode( $recaptcha_response, true );
	
			$recaptcha_error_msg = '';

			if ( isset( $result['error-codes'] ) && ! empty( $result['error-codes'] ) ) {
				$error_codes = $result['error-codes'];

				if ( ! empty ( $error_codes ) ) {

					$recaptcha_error = $error_codes[0];

					switch ( $recaptcha_error ) {

						case 'missing-input-secret':
							$recaptcha_error_msg = __( 'The secret parameter is missing.', BFTGR_TEXT_DOMAIN );
							break;

						case 'invalid-input-secret':
							$recaptcha_error_msg = __( 'The secret parameter is invalid.', BFTGR_TEXT_DOMAIN );
							break;

						case 'missing-input-response':
							$recaptcha_error_msg = __( 'The response parameter is missing.', BFTGR_TEXT_DOMAIN );
							break;

						case 'invalid-input-response':	
							$recaptcha_error_msg = __( 'The response parameter is invalid.', BFTGR_TEXT_DOMAIN );
							break;

						case 'bad-request':	
							$recaptcha_error_msg = __( 'The request is invalid.', BFTGR_TEXT_DOMAIN );
							break;
	
						case 'invalid-keys':
							$recaptcha_error_msg = __( 'Invalid recaptcha keys.', BFTGR_TEXT_DOMAIN );
							break;
	
						case 'timeout-or-duplicate':
							$recaptcha_error_msg = __( 'The reCaptcha is no longer valid: either is too old or has been used previously.', BFTGR_TEXT_DOMAIN );
							break;

						default :
							$recaptcha_error_msg = bftgr_recaptcha_error_msg ();
					}
				} else {
					$recaptcha_error_msg = bftgr_recaptcha_error_msg ();
				}

				if( !empty( $action ) && $action == 'login' ){
					$error = __( '<strong>ERROR</strong>: ', BFTGR_TEXT_DOMAIN ) . $recaptcha_error_msg;
				}
		
				$captcha_data = $this->reCaptcha_error ( $captcha_data, $recaptcha_error_msg, $action );

			}

		}

		return $captcha_data;
	}
	
	/*
	 * reCaptcha validation for login and register forms
	 */
	public function validate_recaptcha_for_login_register ( $user, $username, $password ){

		global $action;

		$check_captcha = $this->validate_recaptcha ( $_POST, $action );

		if( is_wp_error( $check_captcha ) ){
	
			$user = $check_captcha;
		}

		return $user;

	}
	
	/*
	 * reCaptcha validation for lost password form
	 */
	public function validate_recaptcha_for_lostpassword ( $error ) {
	
		$check_captcha = $this->validate_recaptcha ( $_POST );

		if( is_wp_error( $check_captcha ) ){
	
			$error = $check_captcha;
		}
        
        return $error;
	}
	
	/*
	 * Validate reCaptcha for comment form
	 */
	public function validate_recaptcha_for_comment ( $commentdata ) {
	
		$check_captcha = $this->validate_recaptcha ( $_POST );

		if( is_wp_error( $check_captcha ) ){
	
			wp_die ( '<strong> '. __( 'ERROR', BFTGR_TEXT_DOMAIN ) .'</strong>: '. __( $check_captcha->get_error_message() ) );
		}
        
        return $commentdata;
	}
	
	/*
	 * Add reCaptcha field
	 */
	public function recaptcha_field ( $recaptcha_id = '' ) {
		if ( ! empty ( $recaptcha_id ) ) {
			if ( bftgr_v_type () == 'v3' ) {
				echo '<input type="hidden" name="g-recaptcha-response" class="g-recaptcha bftgr_recaptcha" id="' . $recaptcha_id . '" />';
			}else{
				echo '<div class="g-recaptcha bftgr_recaptcha" id="' . $recaptcha_id . '"></div>';
			}
		}
	}	
    
    /*
     * Return reCaptcha error
     */
    public function reCaptcha_error ( $errData = null, $errorMsg = '', $action = '' ) {
    
    	if ( ! isset( $errData->errors ) ) {
			$errData = new WP_Error();
		}
		
		$errorMsg = ! empty ( $errorMsg ) ? $errorMsg : bftgr_recaptcha_error_msg ();
		
		$wp_actions = array( 'login', 'register' );
		
		if( ! empty ( $action ) && in_array ( $action, $wp_actions ) ) {
		
			$errorMsg = __( '<strong>ERROR</strong>: ', BFTGR_TEXT_DOMAIN ) . $errorMsg;
			$errData->add ( 'reCaptcha_error' , $errorMsg );
			
		} else {
			
			$errData->add ( 'reCaptcha_error' , $errorMsg );
		}
		
		return $errData;
	}	

}

new BFTGR_Hooks ();
