<?php 
	add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
	function theme_enqueue_styles() {

		wp_enqueue_style( 'theme-style', get_stylesheet_uri(), null, STM_THEME_VERSION, 'all' );

		
	}

	// checks if the user is logged in, if not, redirects to the user account page uses pmpro_checkout_before_submit_button hook
add_action('pmpro_checkout_before_submit_button', 'pmpro_checkout_redirect');

function pmpro_checkout_redirect(){
    if( !is_user_logged_in() ){
        wp_redirect( home_url( '/user-account/' ) );
        exit;
    }
}