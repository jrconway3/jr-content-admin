<?php
/**
 * Plugin Name: jr-content-admin
 * Plugin URI: https://www.jrconway.net
 * Description: Admin editing layer for jr-content-core content types and shared structured content fields.
 * Version: 0.1.0
 * Author: JaidynReiman
 * Author URI: https://www.jrconway.net
 * Text Domain: jr-content-admin
 * Requires Plugins: jr-content-core
 */

if (!defined('ABSPATH')) {
    exit;
}

define('JR_CONTENT_ADMIN_VERSION', '0.1.0');
define('JR_CONTENT_ADMIN_FILE', __FILE__);
define('JR_CONTENT_ADMIN_PATH', plugin_dir_path(__FILE__));
define('JR_CONTENT_ADMIN_URL', plugin_dir_url(__FILE__));

require_once JR_CONTENT_ADMIN_PATH . 'includes/dependencies.php';

if (!jr_content_admin_is_core_plugin_active()) {
    add_action('admin_notices', 'jr_content_admin_render_dependency_notice');
    return;
}

function jr_content_admin_load()
{
    if (!jr_content_admin_has_dependencies()) {
        add_action('admin_notices', 'jr_content_admin_render_dependency_notice');
        return;
    }

    require_once JR_CONTENT_ADMIN_PATH . 'includes/screens.php';
    require_once JR_CONTENT_ADMIN_PATH . 'includes/assets.php';
    require_once JR_CONTENT_ADMIN_PATH . 'includes/metaboxes.php';
    require_once JR_CONTENT_ADMIN_PATH . 'includes/save-post.php';
    require_once JR_CONTENT_ADMIN_PATH . 'includes/bootstrap.php';

    jr_content_admin_bootstrap();
}

add_action('plugins_loaded', 'jr_content_admin_load', 20);
