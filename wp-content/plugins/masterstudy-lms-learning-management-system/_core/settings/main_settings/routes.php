<?php

function stm_lms_settings_route_section()
{

    $pages = WPCFTO_Settings::stm_get_post_type_array('page');

    $data = array(
        'icon' => 'fas fa-link',
        'name' => esc_html__('LMS Pages', 'masterstudy-lms-learning-management-system'),
        'fields' => array(

            'user_url' => array(
                'type' => 'select',
                'label' => esc_html__('User Account', 'masterstudy-lms-learning-management-system'),
                'options' => $pages,
            ),

            'user_url_profile' => array(
                'type' => 'select',
                'label' => esc_html__('User Public Account', 'masterstudy-lms-learning-management-system'),
                'options' => $pages,
            ),

            'wishlist_url' => array(
                'type' => 'select',
                'label' => esc_html__('Wishlist', 'masterstudy-lms-learning-management-system'),
                'options' => $pages,
            ),

            'checkout_url' => array(
                'type' => 'select',
                'options' => $pages,
                'label' => esc_html__('Checkout', 'masterstudy-lms-learning-management-system'),
                'dependency' => array(
                    'key' => 'wocommerce_checkout',
                    'value' => 'not_empty',
                    'section' => 'section_1'
                ),
                'dependency_mode' => 'disabled',
            ),

        )
    );

    if(!stm_lms_has_generated_pages(stm_lms_generate_pages_list())) {
        $data['fields']['lms_pages'] = array(
            'type' => 'generate_page',
            'options' => stm_lms_generate_pages_list(),
            'label' => esc_html__('Generate LMS Pages', 'masterstudy-lms-learning-management-system'),
        );
    }

    return $data;
}