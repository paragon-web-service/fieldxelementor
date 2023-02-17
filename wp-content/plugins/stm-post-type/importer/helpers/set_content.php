<?php
function stm_set_content_options( $layout, $builder ) {
	$locations = get_theme_mod('nav_menu_locations');
	$menus  = wp_get_nav_menus();

	if(!empty($menus))
	{
		foreach($menus as $menu)
		{
			$menu_names = array(
				'Primary menu',
				'Main Menu'
			);

			if(is_object($menu) && in_array($menu->name, $menu_names))
			{
				$locations['primary'] = $menu->term_id;
			}
			if(is_object($menu) && $menu->name == 'Footer menu')
			{
				$locations['secondary'] = $menu->term_id;
			}
		}
	}

	set_theme_mod('nav_menu_locations', $locations);

	update_option( 'show_on_front', 'page' );

	$front_pages = array(
		'Front Page',
		'Home'
	);

	foreach($front_pages as $front_page_name) {
		$front_page = get_page_by_title($front_page_name);
		if ( isset( $front_page->ID ) ) {
			update_option( 'page_on_front', $front_page->ID );
		}
	}

	$blog_page = get_page_by_title( 'Blog' );
	if ( isset( $blog_page->ID ) ) {
		update_option( 'page_for_posts', $blog_page->ID );
	}

	$exclude_layouts = stm_import_products_exclude_layouts();

	if(!in_array($layout,$exclude_layouts)) {

		$shop_page = get_page_by_title( 'Shop' );
		if ( isset( $shop_page->ID ) ) {
			update_option( 'woocommerce_shop_page_id', $shop_page->ID );
		} else {
			$page_id = stm_create_page('Shop','');
			if ( !empty( $page_id )  ) {
				update_option( 'woocommerce_shop_page_id', $page_id );
			}
		}

		$cart_page = get_page_by_title( 'Cart' );
		if ( isset( $cart_page->ID ) ) {
			update_option( 'woocommerce_cart_page_id', $cart_page->ID );
		} else {
			$page_id = stm_create_page('Cart','[woocommerce_cart]');
			if ( !empty( $page_id )  ) {
				update_option( 'woocommerce_cart_page_id', $page_id );
			}
		}

		$checkout_page = get_page_by_title( 'Checkout' );
		if ( isset( $checkout_page->ID ) ) {
			update_option( 'woocommerce_checkout_page_id', $checkout_page->ID );
		} else {
			$page_id = stm_create_page('Checkout','[woocommerce_checkout]');
			if ( !empty( $page_id )  ) {
				update_option( 'woocommerce_checkout_page_id', $page_id );
			}
		}

		$account_page = get_page_by_title( 'My Account' );
		if ( isset( $account_page->ID ) ) {
			update_option( 'woocommerce_myaccount_page_id', $account_page->ID );
		} else {
			$page_id = stm_create_page('My Account','[woocommerce_my_account]');
			if ( !empty( $page_id )  ) {
				update_option( 'woocommerce_myaccount_page_id', $page_id );
			}
		}


		if(isset($locations['primary']) && !empty($locations['primary'])) {
			$menu_id = intval($locations['primary']);
			$menu = wp_get_nav_menu_items( $menu_id, array('post_type'=>'nav_menu_item') );
			$has_menu_item = false;
			if($menu) {
				foreach ($menu as $key => $item) {
					if($item->title == 'Shop') $has_menu_item = true;
				}
				if(!$has_menu_item) {
					$updated_item = wp_update_nav_menu_item($menu_id, 0, array(
						'menu-item-title' =>  __('Shop'),
						'menu-item-url' => home_url( '/shop/' ),
						'menu-item-status' => 'publish',
						)
					);
				}
			}
		}

	}


	$single = array( 'width' => '840', 'height' => '400', 'crop' => 1 );
	$thumbnail = array( 'width' => '175', 'height' => '100', 'crop' => 1 );
	update_option( 'shop_single_image_size', $single );
	update_option( 'shop_thumbnail_image_size', $thumbnail );


	$fxml = get_temp_dir() . $layout . '.xml';
	$fzip = get_temp_dir() . $layout . '.zip';
	if( file_exists($fxml) ) @unlink($fxml);
	if( file_exists($fzip) ) @unlink($fzip);

    if($builder === 'elementor') {

        $from = "http://lmsdemomentor.loc";
        $to = get_site_url();

        global $wpdb;
        // @codingStandardsIgnoreStart cannot use `$wpdb->prepare` because it remove's the backslashes
        $wpdb->query(
            "UPDATE {$wpdb->postmeta} " .
            "SET `meta_value` = REPLACE(`meta_value`, '" . str_replace( '/', '\\\/', $from ) . "', '" . str_replace( '/', '\\\/', $to ) . "') " .
            "WHERE `meta_key` = '_elementor_data' AND `meta_value` LIKE '[%' ;" ); // meta_value LIKE '[%' are json formatted
        // @codingStandardsIgnoreEnd
        Elementor\Plugin::$instance->files_manager->clear_cache();

    }
}


function stm_create_page($title, $content = '') {
	$post_details = array(
		'post_title'    => $title,
		'post_content'  => $content,
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type' => 'page'
	);
	return wp_insert_post( $post_details );
}
