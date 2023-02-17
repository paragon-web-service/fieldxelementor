<?php
require_once STM_LMS_PRO_INCLUDES . '/helpers.php';

require_once STM_LMS_PRO_INCLUDES . '/hooks/templates.php';
require_once STM_LMS_PRO_INCLUDES . '/hooks/sale-price.php';
require_once STM_LMS_PRO_INCLUDES . '/hooks/instructors.php';
require_once STM_LMS_PRO_INCLUDES . '/hooks/routes.php';

if ( class_exists( 'SitePress' ) ) {
	require_once STM_LMS_PRO_INCLUDES . '/hooks/multilingual.php';
}

require_once STM_LMS_PRO_INCLUDES . '/classes/class-woocommerce-admin.php';
require_once STM_LMS_PRO_INCLUDES . '/hooks/woocommerce.php';

if ( STM_LMS_Cart::woocommerce_checkout_enabled() ) {
	require_once STM_LMS_PRO_INCLUDES . '/classes/class-woocommerce.php';
	require_once STM_LMS_PRO_INCLUDES . '/hooks/woocommerce-orders.php';
}

require_once STM_LMS_PRO_INCLUDES . '/classes/class-announcements.php';
require_once STM_LMS_PRO_INCLUDES . '/classes/class-manage-course.php';
require_once STM_LMS_PRO_INCLUDES . '/classes/class-courses.php';
require_once STM_LMS_PRO_INCLUDES . '/classes/class-addons.php';
require_once STM_LMS_PRO_INCLUDES . '/classes/class-certificates.php';

if ( is_admin() ) {
	require_once STM_LMS_PRO_INCLUDES . '/libraries/plugin-installer/plugin_installer.php';
	require_once STM_LMS_PRO_INCLUDES . '/libraries/announcement/item-announcements.php';
	require_once STM_LMS_PRO_INCLUDES . '/libraries/compatibility/main.php';
}
