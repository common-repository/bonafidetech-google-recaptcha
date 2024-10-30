<?php

/*
 * BFTGR Core Functions
 */

defined ( 'ABSPATH' ) || exit;

/**
 * Form Fields Template
 */

function BFTGR_form_field ( $option, $value, $custom_attributes ) {
    if ( empty ( $option ) ) {
        return;
    }

    switch ( $option['type'] ) {
        case 'select':
        case 'multiselect':
        case 'select_taxonomy':
            echo '<select name="' . esc_attr ( $option['name'] ) . ( $option['type'] == 'multiselect' ? '[]' : '' ) . '" id="' . esc_attr ( $option['name'] ) . '" class="regular-text" ' . $custom_attributes . ' ' . ( $option['type'] == 'multiselect' ? 'multiple="multiple"' : '' ) . '>';

				if ( isset ( $option['opts'] ) && ! empty ( $option['opts'] ) ) {
					foreach ( $option['opts'] as $key => $opt ) {
						if ( is_array ( $value ) ) {
							$selected = selected ( in_array ( $key, $value ), true, false );
						} else {
							$selected = selected ( $value, $key, false );
						}

						$opt_attributes = '';
						if ( $option['type'] == 'select_taxonomy' ) {
							$opt_arr	= explode ( '{SEPARATOR}', $opt );
							$opt		= trim ( $opt_arr[0] );

							if ( isset ( $opt_arr[1] ) ) {
								$opt_attributes .= ' data-object-types="' . $opt_arr[1] . '"';
							}
						}

						echo '<option value="' . esc_attr ( $key ) . '" ' . $selected . $opt_attributes . '>' . esc_attr ( $opt ) . '</option>';
					}
				}

            echo '</select>';
            break;

        case 'textarea':
            echo '<textarea name="' . esc_attr ( $option['name'] ) . '" id="' . esc_attr ( $option['name'] ) . '" class="regular-text" ' . $custom_attributes . ' placeholder="' . esc_attr ( $option['placeholder'] ) . '" rows="5" cols="50">' . esc_textarea( $value ) . '</textarea>';
            break;

        case 'checkbox':
            echo ''
            . '<fieldset>'
				. '<legend class="screen-reader-text">'
					. '<span>' . $option['label'] . '</span>'
				. '</legend>'
				. '<label for="' . $option['name'] . '">'
					. '<input type="checkbox" name="' . esc_attr ( $option['name'] ) . '" id="' . esc_attr ( $option['name'] ) . '" ' . $custom_attributes . ' value="1" ' . checked ( $value, 'yes', false ) . ' />'
				. '</label>'
            . '</fieldset>';
            break;

        case 'checkbox_group':
            echo ''
            . '<fieldset>'
				. '<legend class="screen-reader-text"><span>' . $option['label'] . '</span></legend>';

				if ( isset ( $option['opts'] ) && ! empty ( $option['opts'] ) ) {

					foreach ( $option['opts'] as $key => $opt ) {

						if ( is_array ( $value ) ) {
							$checked = checked ( in_array ( $key, $value ), true, false );
						} else {
							$checked = checked ( $value, $key, false );
						}

						echo ''
						. '<label for="' . esc_attr ( $option['name'] . '_' . $key ) . '">'
							. '<input type="checkbox" name="' . esc_attr ( $option['name'] ) . '[]" id="' . esc_attr ( $option['name'] . '_' . $key ) . '" ' . $custom_attributes . ' value="' . esc_attr ( $key ) . '" ' . $checked . ' /> ' . esc_attr ( $opt ) . ''
						. '</label><br />';
					}
				}

            echo '</fieldset>';
            break;

        case 'radio':
            echo ''
            . '<fieldset>'
				. '<legend class="screen-reader-text"><span>' . $option['label'] . '</span></legend>';

				if ( isset ( $option['opts'] ) && ! empty ( $option['opts'] ) ) {

					foreach ( $option['opts'] as $key => $opt ) {

						echo '<label for="' . esc_attr ( $option['name'] . '_' . $key ) . '">'
							. '<input type="radio" name="' . esc_attr ( $option['name'] ) . '" id="' . esc_attr ( $option['name'] . '_' . $key ) . '" ' . $custom_attributes . ' value="' . esc_attr ( $key ) . '" ' . checked ( $value, $key, false ) . ' /> ' . esc_attr ( $opt ) . ''
						. '</label><br />';
					}
				}

            echo '</fieldset>';
            break;

        case 'colour':
            echo '<input type="text" name="' . esc_attr ( $option['name'] ) . '" id="' . esc_attr ( $option['name'] ) . '" class="wpse-cpick" ' . $custom_attributes . ' placeholder="' . esc_attr ( $option['placeholder'] ) . '" value="' . esc_attr ( $value ) . '" />';
            break;

        default:
            echo '<input type="' . esc_attr ( $option['type'] ) . '" name="' . esc_attr ( $option['name'] ) . '" id="' . esc_attr ( $option['name'] ) . '" class="regular-text" ' . $custom_attributes . ' placeholder="' . esc_attr ( $option['placeholder'] ) . '" value="' . esc_attr ( $value ) . '" />';
    }

    if ( ! empty ( $option['desc'] ) ) {
        echo '<p class="description">' . $option['desc'] . '</p>';
    }
}

/**
 * Form: Form Field Format Before Save
 */
function BFTGR_format_form_field ( $option ) {
    if ( empty ( $option ) ) {
        return;
    }

    // Get posted value
    if ( strstr( $option['name'], '[' ) ) {
		parse_str ( $option['name'], $option_name_array );
        $option_name	= current ( array_keys ( $option_name_array ) );
        $setting_name	= key ( $option_name_array[ $option_name ] );
        $raw_value		= isset ( $_POST[ $option_name ][ $setting_name ] ) ? wp_unslash ( $_POST[ $option_name ][ $setting_name ] ) : null;
    } else {
        $option_name	= $option['name'];
        $setting_name	= '';
        $raw_value		= isset ( $_POST[ $option['name'] ] ) ? wp_unslash ( $_POST[ $option['name'] ] ) : null;
    }

    // Format the Value based on Option Type
    switch ( $option['type'] ) {
        case 'checkbox' :
            $value = is_null ( $raw_value ) ? 'no' : 'yes';
            break;

        case 'textarea' :
            $value = wp_kses_post ( trim ( $raw_value ) );
            break;

        default :
            $value = is_array ( $raw_value ) ? array_map ( 'sanitize_text_field', $raw_value ) : sanitize_text_field ( $raw_value );
            break;
    }

    return array ( $option_name, $setting_name, $value );
}

/**
 * Disable reCaptcha for User Roles
 */
function BFTGR_disabled_for_user_roles () {
	global $BFTGR_disabled_for_user_roles;
	
	if ( empty ( $BFTGR_disabled_for_user_roles ) ) {
		$BFTGR_disabled_for_user_roles = get_option ( 'BFTGR_user_roles' );
	}
	
	return $BFTGR_disabled_for_user_roles;
}

/**
 * Get Recaptcha error message
 */
function BFTGR_recaptcha_error_msg () {
 	global $BFTGR_recaptcha_error_msg;
	
	if ( empty ( $BFTGR_recaptcha_error_msg ) ) {
		$BFTGR_recaptcha_error_msg = ! empty ( get_option ( 'BFTGR_error_message' ) ) ? get_option ( 'BFTGR_error_message' ) : __( "Captcha Error. Please verify.", BFTGR_TEXT_DOMAIN );
	}
	
	return $BFTGR_recaptcha_error_msg;
 }

/**
 * Get BFTGR reCaptcha Global Settings
 */
function BFTGR_captcha_settings () {
	global $BFTGR_captcha_settings;
	
	if ( empty ( $BFTGR_captcha_settings ) ) {
	
		$BFTGR_captcha_settings = array (
			'type'			=> get_option ( 'BFTGR_type' ),
			'site_key'		=> get_option ( 'BFTGR_site_key' ),
			'secret_key'	=> get_option ( 'BFTGR_secret_key' ),
			'theme'			=> get_option ( 'BFTGR_theme' ),
			'size'			=> get_option ( 'BFTGR_size' )
		);
	}
	
	return $BFTGR_captcha_settings;
}

/**
 * Check if recaptcha is enabled for WordPress login form or not
 */
function BFTGR_enable_for_wp_login () {
	global $BFTGR_enable_for_wp_login;
	
	if ( empty ( $BFTGR_enable_for_wp_login ) ){
		$BFTGR_enable_for_wp_login = get_option ( 'BFTGR_wp_login' );
	}
	
	return $BFTGR_enable_for_wp_login;
}

/**
 * Check if recaptcha is enabled for WordPress registration form or not
 */
function BFTGR_enable_for_wp_registration () {
	global $BFTGR_enable_for_wp_registration;
	
	if ( empty ( $BFTGR_enable_for_wp_registration ) ){
		$BFTGR_enable_for_wp_registration = get_option ( 'BFTGR_wp_registration' );
	}
	
	return $BFTGR_enable_for_wp_registration;
}

/**
 * Check if recaptcha is enabled for WordPress forgot password form
 */
function BFTGR_enable_for_wp_forgot_pwd () {
	global $BFTGR_enable_for_wp_forgot_pwd;
	
	if ( empty ( $BFTGR_enable_for_wp_forgot_pwd ) ){
		$BFTGR_enable_for_wp_forgot_pwd = get_option ( 'BFTGR_wp_forgot_pwd' );
	}
	
	return $BFTGR_enable_for_wp_forgot_pwd;
}

/**
 * Check if recaptcha is enabled for WordPress comment form
 */
function BFTGR_enable_for_wp_comment () {
	global $BFTGR_enable_for_wp_comment;
	
	if ( empty ( $BFTGR_enable_for_wp_comment ) ){
		$BFTGR_enable_for_wp_comment = get_option ( 'BFTGR_wp_comment' );
	}
	
	return $BFTGR_enable_for_wp_comment;
}

/**
 * Check if recaptcha is enabled for WooCommerce login form or not
 */
function BFTGR_enable_for_wc_login () {
	global $BFTGR_enable_for_wc_login;
	
	if ( empty ( $BFTGR_enable_for_wc_login ) ){
		$BFTGR_enable_for_wc_login = get_option ( 'BFTGR_wc_login' );
	}
	
	return $BFTGR_enable_for_wc_login;
}

/**
 * Check if recaptcha is enabled for WooCommerce registration form or not
 */
function BFTGR_enable_for_wc_registration () {
	global $BFTGR_enable_for_wc_registration;
	
	if ( empty ( $BFTGR_enable_for_wc_registration ) ){
		$BFTGR_enable_for_wc_registration = get_option ( 'BFTGR_wc_registration' );
	}
	
	return $BFTGR_enable_for_wc_registration;
}

/**
 * Check if recaptcha is enabled for WooCommerce forgot password form or not
 */
function BFTGR_enable_for_woocommerce_forgot_pwd () {
	global $BFTGR_enable_for_woocommerce_forgot_pwd;
	
	if ( empty ( $BFTGR_enable_for_woocommerce_forgot_pwd ) ){
		$BFTGR_enable_for_woocommerce_forgot_pwd = get_option ( 'BFTGR_wc_forgot_pwd' );
	}
	
	return $BFTGR_enable_for_woocommerce_forgot_pwd;
}

/**
 * Check recaptcha version
 */
function BFTGR_v_type () {
	global $BFTGR_v_type;
	
	if ( empty ( $BFTGR_v_type ) ){
		$BFTGR_v_type = get_option ( 'BFTGR_type' );
	}
	
	return $BFTGR_v_type;
}

function BFTGR_forms_options () {
	global $BFTGR_forms_options;
	
	if ( empty ( $BFTGR_forms_options ) ) {
	
		$BFTGR_forms_options = array (
			'wp_login'			=> BFTGR_enable_for_wp_login (),
			'wp_registration'	=> BFTGR_enable_for_wp_registration (),
			'wp_forgot_pwd'		=> BFTGR_enable_for_wp_forgot_pwd (),
			'wp_comment'		=> BFTGR_enable_for_wp_comment (),
			'wc_login'			=> BFTGR_enable_for_wc_login (),
			'wc_registration'	=> BFTGR_enable_for_wc_registration (),
			'wc_forgot_pwd'		=> BFTGR_enable_for_woocommerce_forgot_pwd ()
		);
	}
	
	return $BFTGR_forms_options;
}

/**
 * Check if disabled for the current user
 */
function BFTGR_disabled_for_current_user (){
	global $BFTGR_disabled_for_current_user;
	
	if( ! $BFTGR_disabled_for_current_user ){
	
		if ( is_user_logged_in () && is_array ( BFTGR_disabled_for_user_roles() ) ) {
			$current_user = wp_get_current_user();
			
			$BFTGR_disabled_for_current_user = false;
			foreach ( $current_user->roles as $user_role ) {
				if ( in_array ( $user_role, BFTGR_disabled_for_user_roles () ) ) {
					$BFTGR_disabled_for_current_user = true;
				}
			}
		}
	}
	
	return $BFTGR_disabled_for_current_user;
}

/**
 * Check if WooCommerce is activated
 */
function BFTGR_is_woocommerce_activated () {
	if ( class_exists ( 'woocommerce' ) ) {
		return true;
	}
	
	return false;
}
