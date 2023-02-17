<?php
/*
Plugin Name: STM Configurations
Plugin URI: https://stylemixthemes.com/
Description: Configurations plugin for the Masterstudy theme
Author: StylemixThemes
Author URI: https://stylemixthemes.com/
Text Domain: stm-post-type
Version: 4.5.0
*/

define( 'STM_POST_TYPE', 'stm_post_type' );
define( 'STM_POST_TYPE_PATH', dirname( __FILE__ ) );
define( 'STM_POST_TYPE_URL', plugin_dir_url( __FILE__ ) );
$plugin_path = dirname( __FILE__ );

require_once $plugin_path . '/post_type/post_type.php';
require_once $plugin_path . '/importer/importer.php';
require_once $plugin_path . '/theme/helpers.php';
require_once $plugin_path . '/theme/mailchimp.php';
require_once $plugin_path . '/theme/share.php';
require_once $plugin_path . '/theme/payment.php';
require_once $plugin_path . '/theme/image_sizes.php';
require_once $plugin_path . '/theme/crop-images.php';
require_once $plugin_path . '/redux-framework/admin-init.php';
require_once $plugin_path . '/visual_composer/vc.php';
require_once $plugin_path . '/ajax/ajax.php';

/*Widgets*/
$widgets_path = "{$plugin_path}/widgets";
require_once $widgets_path . '/mailchimp.php';
require_once $widgets_path . '/contacts.php';
require_once $widgets_path . '/pages.php';
require_once $widgets_path . '/socials.php';
require_once $widgets_path . '/recent_posts.php';
require_once $widgets_path . '/working_hours.php';
require_once $widgets_path . '/text.php';
require_once $widgets_path . '/menus.php';
// Custom Woo widget
add_action( 'init', 'stm_check_woo_plugin' );
function stm_check_woo_plugin() {
	if ( class_exists( 'WooCommerce' ) ) {
		$plugin_path  = dirname( __FILE__ );
		$widgets_path = "{$plugin_path}/widgets";
		require_once $widgets_path . '/woo_popular_courses.php';
		require_once $plugin_path . '/theme/woocommerce_setups.php';
	}
}

if ( is_admin() ) {
	require_once $plugin_path . '/announcement/main.php';
}

if ( ! is_textdomain_loaded( 'stm-post-type' ) ) {
	load_plugin_textdomain(
		'stm-post-type',
		false,
		'stm-post-type/languages'
	);
}
