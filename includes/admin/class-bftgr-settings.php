<?php
/**
 * Settings Panel
 */

defined ( 'ABSPATH' ) || exit;

class BFTGR_Admin_Settings {

	/**
	 * Constructor
	 */
	public function __construct () {
		add_action ( 'admin_menu', array ( $this, 'add_settings_menu' ) );
		add_action ( 'admin_init', array ( $this, 'save_settings' ) );
	}

	/**
	 * Add Sub Menu: Settings
	 */
	public function add_settings_menu () {
		add_options_page ( __( 'BFT Google reCAPTCHA for WordPress', BFTGR_TEXT_DOMAIN ), __( 'BFT Google reCAPTCHA for WordPress', BFTGR_TEXT_DOMAIN ), 'manage_options', 'bftgr-settings', array ( $this, 'add_settings' ) );
	}

	/**
	 * Add Settings Form
	 */
	public function add_settings () {
		$option_sections = self::get_settings ();

		if ( ! empty ( $option_sections ) ) {
			?><div class="wrap bftgr-wrap">
				<h1><?php _e( 'BFT Google reCAPTCHA for WordPress', BFTGR_TEXT_DOMAIN ); ?></h1>

				<form method="post" action="">
					<div class="bftgr-tabs-wrapper">						
						<ul>
							<?php
								foreach ( $option_sections as $option_section_key => $option_section ) {
									if ( isset ( $option_section['options'] ) && ! empty ( $option_section['options'] ) ) {
										?><li>
											<a href="#tab-<?php echo $option_section_key; ?>">
												<span><?php echo $option_section['heading']; ?></span>
											</a>
										</li><?php
									}
								}
							?>
						</ul>
						
						<div class="bftgr-tabs">
							<?php
								foreach ( $option_sections as $option_section_key => $option_section ) {
									if ( isset ( $option_section['options'] ) && ! empty ( $option_section['options'] ) ) { ?>
										<div id="tab-<?php echo $option_section_key; ?>">
											<table class="form-table">
												<tbody>
													<?php
														foreach ( $option_section['options'] as $option ) {

															// Default Args
															$option		=	wp_parse_args ( $option, array (
																'type'				=> 'text',
																'label'				=> '',
																'desc'				=> '',
																'placeholder'		=> '',
																'opts'				=> array (),
																'default'			=> '',
																'custom_attributes'	=> array (),
																'class'				=> ''
															) );

															// Option Value
															$value = get_option ( $option['name'] );
															if ( $value === false ) {
																$value = $option['default'];
															}

															// Custom Attribute Handling
															$custom_attributes = array ();

															if ( ! empty ( $option['custom_attributes'] ) && is_array ( $option['custom_attributes'] ) ) {
																foreach ( $option['custom_attributes'] as $attribute => $attribute_value ) {
																	$custom_attributes[] = esc_attr ( $attribute ) . '="'. esc_attr ( $attribute_value ) . '"';
																}
															}

															$custom_attributes = implode ( '', $custom_attributes );

															// Option Row
															?><tr class="field_<?php echo $option['type']; ?>">
																<th scope="row"><label for="<?php echo $option['name']; ?>"><?php echo $option['label']; ?></label></th>
																<td><?php bftgr_form_field ( $option, $value, $custom_attributes ); ?></td>
															</tr><?php
														}
													?>
												</tbody>
											</table>
										</div><?php
									}
								}
							?>
							
							<input type="hidden" name="action" value="bftgr_settings" />
							<?php submit_button (); ?>
						</div>						
					</div>
				</form>
			</div><?php
		}
	}

	/**
	 * Save Settings Form
	 */
	public function save_settings () {
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'bftgr_settings' ) {

			// Options to Update will be Stored Here and Saved Later
			$update_options = array ();

			$option_sections = self::get_settings ();
			if ( ! empty ( $option_sections ) ) {
				foreach ( $option_sections as $option_section ) {

					if ( isset ( $option_section['options'] ) && ! empty ( $option_section['options'] ) ) {
						foreach ( $option_section['options'] as $option ) {
							if ( ! isset ( $option['name'] ) || ! isset ( $option['type'] ) ) {
								continue;
							}

							list ( $option_name, $setting_name, $value ) = bftgr_format_form_field ( $option );

							// Check if Option is an Array and Handle That Differently to Single Values
							if ( $option_name && $setting_name ) {
								if ( ! isset ( $update_options[ $option_name ] ) ) {
									$update_options[ $option_name ] = get_option ( $option_name, array () );
								}
								
								if ( ! is_array ( $update_options[ $option_name ] ) ) {
									$update_options[ $option_name ] = array ();
								}
								
								$update_options[ $option_name ][ $setting_name ] = $value;
							} else {
								$update_options[ $option_name ] = $value;
							}
						}
					}
				}

				// Save All Options in Our Array
				foreach ( $update_options as $name => $value ) {
					update_option ( $name, $value );
				}

				return true;
			}
		}
	}

	/**
	 * Setting Options
	 */
	public static function get_settings () {
		
		$option_sections = array (
			'bftgr_recaptcha_section'			=> array (
				'heading'						=> __( 'Google reCAPTCHA API', BFTGR_TEXT_DOMAIN ),
				'options'						=> array (
					'bftgr_type'					=> array (
						'name'					=> 'bftgr_type',
						'label'					=> __( 'Type', BFTGR_TEXT_DOMAIN ),
						'type'					=> 'select',
						'opts'					=> array (
							'v3'				=> __( 'reCAPTCHA v3', BFTGR_TEXT_DOMAIN ),
							'v2-checkbox'		=> __( 'reCAPTCHA v2 ( "I\'m not a robot" Checkbox )', BFTGR_TEXT_DOMAIN ),
							'v2-invisible'		=> __( 'reCAPTCHA v2 ( Invisible reCAPTCHA badge )', BFTGR_TEXT_DOMAIN ),
						),
						'default'				=> __( 'v3', BFTGR_TEXT_DOMAIN )
					),
					'bftgr_site_key'				=> array (
						'name'					=> 'bftgr_site_key',
						'label'					=> __( 'Site Key', BFTGR_TEXT_DOMAIN ),
						'type'					=> 'text',
						'default'				=> '',
					),
					'bftgr_secret_key'			=> array (
						'name'					=> 'bftgr_secret_key',
						'label'					=> __( 'Secret Key', BFTGR_TEXT_DOMAIN ),
						'type'					=> 'text',
						'default'				=> '',
						'desc'					=> sprintf( __( 'You can create/find reCAPTCHA key pair here: %s', BFTGR_TEXT_DOMAIN ), '<a href="https://www.google.com/recaptcha/admin" target="_blank">' . __( 'Get the API Keys', BFTGR_TEXT_DOMAIN ) . '</a>' ),
					),
					'bftgr_theme'				=> array (
						'name'					=> 'bftgr_theme',
						'label'					=> __( 'Theme', BFTGR_TEXT_DOMAIN ),
						'type'					=> 'select',
						'opts'					=> array (
							'light'				=> __( 'Light', BFTGR_TEXT_DOMAIN ),
							'dark'				=> __( 'Dark', BFTGR_TEXT_DOMAIN )
						),
						'default'				=> 'light'
					),
					'bftgr_size'					=> array (
						'name'					=> 'bftgr_size',
						'label'					=> __( 'Size', BFTGR_TEXT_DOMAIN ),
						'type'					=> 'select',
						'opts'					=> array (
							'normal'			=> __( 'Normal', BFTGR_TEXT_DOMAIN ),
							'compact'			=> __( 'Compact', BFTGR_TEXT_DOMAIN )
						),
						'default'				=> 'normal'
					),
					'bftgr_error_message'		=> array (
						'name'					=> 'bftgr_error_message',
						'label'					=> __( 'Error Message', BFTGR_TEXT_DOMAIN ),
						'type'					=> 'text',
						'default'				=> __( 'Captcha Error. Please verify.', BFTGR_TEXT_DOMAIN )
					),
					'bftgr_user_roles'			=> array (
						'name'					=> 'bftgr_user_roles',
						'label'					=> __( 'Disable reCaptcha for', BFTGR_TEXT_DOMAIN ),
						'type'					=> 'checkbox_group',
						'opts'					=> self::get_user_roles()
					),
				),
			),
			
			'bftgr_wordpress_section'			=> array (
				'heading'						=> __( 'WordPress', BFTGR_TEXT_DOMAIN ),
				'options'						=> array (
					'bftgr_wp_login'				=> array (
						'name'					=> 'bftgr_wp_login',
						'label'					=> __( 'Login Form', BFTGR_TEXT_DOMAIN ),
						'type'					=> 'checkbox',
						'default'				=> 'no'
					),
					'bftgr_wp_registration'		=> array (
						'name'					=> 'bftgr_wp_registration',
						'label'					=> __( 'Registration Form', BFTGR_TEXT_DOMAIN ),
						'type'					=> 'checkbox',
						'default'				=> __( 'no', BFTGR_TEXT_DOMAIN )
					),					
					'bftgr_wp_forgot_pwd'		=> array (
						'name'					=> 'bftgr_wp_forgot_pwd',
						'label'					=> __( 'Forgot Password Form', BFTGR_TEXT_DOMAIN ),
						'type'					=> 'checkbox',
						'default'				=> __( 'no', BFTGR_TEXT_DOMAIN )
					),
					'bftgr_wp_comment'			=> array (
						'name'					=> 'bftgr_wp_comment',
						'label'					=> __( 'Comments Form', BFTGR_TEXT_DOMAIN ),
						'type'					=> 'checkbox',
						'default'				=> 'no'
					)
				)
			),
			
			'bftgr_woocommerce_section'			=> array (
				'heading'						=> __( 'WooCommerce', BFTGR_TEXT_DOMAIN ),
				'options'						=> array (
					'bftgr_wc_login'				=> array (
						'name'					=> 'bftgr_wc_login',
						'label'					=> __( 'Login Form', BFTGR_TEXT_DOMAIN ),
						'type'					=> 'checkbox',
						'default'				=> 'no'
					),
					'bftgr_wc_registration'		=> array (
						'name'					=> 'bftgr_wc_registration',
						'label'					=> __( 'Registration Form', BFTGR_TEXT_DOMAIN ),
						'type'					=> 'checkbox',
						'default'				=> __( 'no', BFTGR_TEXT_DOMAIN )
					),					
					'bftgr_wc_forgot_pwd'		=> array (
						'name'					=> 'bftgr_wc_forgot_pwd',
						'label'					=> __( 'Forgot Password Form', BFTGR_TEXT_DOMAIN ),
						'type'					=> 'checkbox',
						'default'				=> __( 'no', BFTGR_TEXT_DOMAIN )
					)
				)
			)
		);
		
		if ( ! bftgr_is_woocommerce_activated () ) {
			unset ( $option_sections['bftgr_woocommerce_section'] );
		}
		
		return apply_filters ( 'bftgr_settings', $option_sections );
	}
	
	/*
	 * Get User Roles List
	 */
	public static function get_user_roles () {
		$user_roles		= array ();

		$wp_roles		= wp_roles ();
		if ( ! empty ( $wp_roles ) && ! empty ( $wp_roles->roles ) ) {
			foreach ( $wp_roles->roles as $wp_role_key => $wp_role ) {
				$user_roles[ $wp_role_key ] = $wp_role['name'];
			}
		}

		return $user_roles;
	}
}

new BFTGR_Admin_Settings ();
