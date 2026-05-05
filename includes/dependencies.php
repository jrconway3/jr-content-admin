<?php

if (!defined('ABSPATH')) {
    exit;
}

function jr_content_admin_has_dependencies()
{
    return defined('JR_CONTENT_CORE_VERSION');
}

function jr_content_admin_is_core_plugin_active()
{
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    if (function_exists('is_plugin_active') && is_plugin_active('jr-content-core/jr-content-core.php')) {
        return true;
    }

    if (function_exists('is_plugin_active_for_network') && is_plugin_active_for_network('jr-content-core/jr-content-core.php')) {
        return true;
    }

    return false;
}

function jr_content_admin_render_dependency_notice()
{
    if (!current_user_can('activate_plugins')) {
        return;
    }

    echo '<div class="notice notice-error"><p>';
    echo esc_html__('jr-content-admin requires jr-content-core to be active before it can load.', 'jr-content-admin');
    echo '</p></div>';
}
