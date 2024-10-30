/*
 * BFTGR frontend script 
 */

if ( bftgr.type == 'v2-invisible' ) {
	var bftgr_recaptcha_settings = {
		'sitekey'	:	bftgr.site_key,
		'theme'		:	bftgr.theme,
		'size'		:	'invisible',
		'badge'		:	'inline'
	};
} else {
	var bftgr_recaptcha_settings = {
		'sitekey'	:	bftgr.site_key,
		'theme'		: 	bftgr.theme,
		'size'		: 	bftgr.size,
	};
}

function bftgr_show_recaptcha ( recaptcha_id = '' ) {
	if ( bftgr.type == 'v3' ) {
		grecaptcha.ready(function() {
			grecaptcha.execute( bftgr.site_key, { action: 'bftgr_recapcha' } ).then( function (token) {
				var bftgr_wp_recaptcha_response 		= document.getElementById( recaptcha_id );
				bftgr_wp_recaptcha_response.value 	= token;
            });
        });
	} else {
		grecaptcha.render( recaptcha_id, bftgr_recaptcha_settings );
		
		if ( bftgr.type == 'v2-invisible' ) {
			jQuery( '#' + recaptcha_id ).parents( 'form' ).submit( function( e ) {
				if ( jQuery( '#' + recaptcha_id +' #g-recaptcha-response' ).val() == '' ) {
					e.preventDefault();
					grecaptcha.execute();
				}
			});			
		}
	}
}		
 
var bftgr_onloadCallback = function () {
	if ( bftgr.wp_login == 'yes' && document.getElementById( 'bftgr_wp_recaptcha_login') ) {
		bftgr_show_recaptcha( 'bftgr_wp_recaptcha_login' );
	}
	
	if ( bftgr.wp_registration == 'yes' && document.getElementById( 'bftgr_wp_recaptcha_register') ) {
		bftgr_show_recaptcha( 'bftgr_wp_recaptcha_register' );
	}
	
	if ( bftgr.wp_forgot_pwd == 'yes' && document.getElementById( 'bftgr_wp_recaptcha_forgot_password') ) {
		bftgr_show_recaptcha( 'bftgr_wp_recaptcha_forgot_password' );
	}
	
	if ( bftgr.wp_comment == 'yes' && document.getElementById( 'bftgr_wp_recaptcha_comment') ) {
		bftgr_show_recaptcha( 'bftgr_wp_recaptcha_comment' );
	}
	
	if ( bftgr.wc_login == 'yes' && document.getElementById( 'bftgr_wc_recaptcha_login') ) {
		bftgr_show_recaptcha( 'bftgr_wc_recaptcha_login' );
	}
	
	if ( bftgr.wc_registration == 'yes' && document.getElementById( 'bftgr_wc_recaptcha_register') ) {
		bftgr_show_recaptcha( 'bftgr_wc_recaptcha_register' );
	}
	
	if ( bftgr.wc_forgot_pwd == 'yes' && document.getElementById( 'bftgr_wc_recaptcha_forgot_password') ) {
		bftgr_show_recaptcha( 'bftgr_wc_recaptcha_forgot_password' );
	}
};
