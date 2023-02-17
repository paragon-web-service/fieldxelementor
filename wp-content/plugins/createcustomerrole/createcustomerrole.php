<?php
/*
Plugin Name: Create Customer Role
Description: Creates a customer role if it doesn't already exist
Version: 1.2
Author: Sean Baker
*/
// Create a customer role if it doesn't already exist
function check_and_create_customer_role() {
    if(!get_role('customer')) {
        $subscriber = get_role('subscriber');
        add_role( 'customer', __( 'Customer' ),
            $subscriber->capabilities
        );
    }
}
// Check and create the customer role on plugin activation
if ( ! wp_next_scheduled( 'check_and_create_customer_role' ) ) {
    wp_schedule_single_event( time(), 'check_and_create_customer_role' );
}
add_action( 'check_and_create_customer_role', 'check_and_create_customer_role' );