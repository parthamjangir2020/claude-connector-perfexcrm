<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Claude Connector
Description: AI-powered integration between Claude Desktop and Perfex CRM using Model Context Protocol (MCP)
Version: 1.0.0
Requires at least: 3.0
Author: Claude Connector Team
Author URI: https://github.com/yourorg/claude-connector
*/

define('CLAUDE_CONNECTOR_MODULE_NAME', 'claude_connector');
define('CLAUDE_CONNECTOR_VERSION', '1.0.0');

/**
 * Register language files - This is the correct way for Perfex CRM 3.4.0
 */
register_language_files(CLAUDE_CONNECTOR_MODULE_NAME, [CLAUDE_CONNECTOR_MODULE_NAME]);

/**
 * Register module in admin sidebar menu
 */
hooks()->add_action('admin_init', 'claude_connector_init_menu_items');

function claude_connector_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('claude_connector', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('claude-connector', [
            'slug'     => 'claude-connector',
            'name'     => _l('claude_connector'),
            'icon'     => 'fa fa-robot',
            'href'     => admin_url('claude_connector'),
            'position' => 40,
        ]);
    }
}

/**
 * Add CSS/JS assets to admin head
 */
hooks()->add_action('app_admin_head', 'claude_connector_add_head_components');

function claude_connector_add_head_components()
{
    $CI = &get_instance();
    
    // Add custom CSS if on module pages
    if ($CI->uri->segment(1) === 'admin' && $CI->uri->segment(2) === 'claude_connector') {
        echo '<link rel="stylesheet" type="text/css" href="' . module_dir_url(CLAUDE_CONNECTOR_MODULE_NAME, 'assets/css/claude_connector.css') . '?v=' . CLAUDE_CONNECTOR_VERSION . '">';
    }
}

/**
 * Register module permissions
 */
hooks()->add_filter('staff_permissions', 'claude_connector_permissions');

function claude_connector_permissions($permissions)
{
    $permissions['claude_connector'] = [
        'name'         => _l('claude_connector'),
        'capabilities' => [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];
    
    return $permissions;
}

/**
 * Register activation hook
 */
register_activation_hook(CLAUDE_CONNECTOR_MODULE_NAME, 'claude_connector_activation_hook');

function claude_connector_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register deactivation hook
 */
register_deactivation_hook(CLAUDE_CONNECTOR_MODULE_NAME, 'claude_connector_deactivation_hook');

function claude_connector_deactivation_hook()
{
    // Cleanup if needed
}

/**
 * Register uninstall hook
 */
register_uninstall_hook(CLAUDE_CONNECTOR_MODULE_NAME, 'claude_connector_uninstall_hook');

function claude_connector_uninstall_hook()
{
    $CI = &get_instance();
    
    // Drop tables
    $CI->db->query("DROP TABLE IF EXISTS " . db_prefix() . "claude_connector_settings");
    $CI->db->query("DROP TABLE IF EXISTS " . db_prefix() . "claude_connector_logs");
    
    // Delete options
    delete_option('claude_connector_api_key');
    delete_option('claude_connector_enabled');
    delete_option('claude_connector_log_actions');
}

/**
 * Helper functions
 */

/**
 * Get module setting
 */
function claude_connector_get_setting($name, $default = null)
{
    $CI = &get_instance();
    $CI->db->where('setting_name', $name);
    $row = $CI->db->get(db_prefix() . 'claude_connector_settings')->row();
    
    return $row ? $row->setting_value : $default;
}

/**
 * Check if Claude Connector is enabled
 */
function claude_connector_is_enabled()
{
    return claude_connector_get_setting('enabled', '1') === '1';
}

/**
 * Log an action
 */
function claude_connector_log($action, $entity_type = null, $entity_id = null, $details = null)
{
    if (claude_connector_get_setting('log_actions', '1') !== '1') {
        return false;
    }

    $CI = &get_instance();
    
    return $CI->db->insert(db_prefix() . 'claude_connector_logs', [
        'action'      => $action,
        'entity_type' => $entity_type,
        'entity_id'   => $entity_id,
        'details'     => is_array($details) ? json_encode($details) : $details,
        'ip_address'  => $CI->input->ip_address(),
        'user_agent'  => $CI->input->user_agent(),
    ]);
}
