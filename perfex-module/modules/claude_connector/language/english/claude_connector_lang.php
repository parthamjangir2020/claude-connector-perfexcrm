<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Claude Connector Language File - English
 * 
 * All language strings for the Claude Connector module
 */

// Module name
$lang['claude_connector']             = 'Claude Connector';
$lang['claude_connector_description'] = 'AI-powered integration between Claude Desktop and Perfex CRM using Model Context Protocol (MCP).';

// Menu & Navigation
$lang['setup_guide']        = 'Setup Guide';
$lang['back_to_dashboard']  = 'Back to Dashboard';

// Dashboard
$lang['total_actions_logged'] = 'Total Actions Logged';
$lang['actions_today']        = 'Actions Today';
$lang['connection_status']    = 'Connection Status';
$lang['active']               = 'Active';
$lang['inactive']             = 'Inactive';
$lang['top_actions']          = 'Top Actions';
$lang['recent_activity']      = 'Recent Activity';
$lang['view_all']             = 'View All';
$lang['no_activity_yet']      = 'No activity recorded yet. Connect Claude Desktop to start.';

// Settings
$lang['claude_connector_settings']     = 'Claude Connector Settings';
$lang['claude_connector_enabled']      = 'Enable Claude Connector';
$lang['claude_connector_enabled_help'] = 'When enabled, Claude Desktop can interact with your Perfex CRM.';
$lang['log_actions']                   = 'Log All Actions';
$lang['log_actions_help']              = 'Record all actions performed by Claude for audit purposes.';
$lang['settings_updated']              = 'Settings updated successfully.';

// Logs
$lang['claude_connector_logs'] = 'Action Logs';
$lang['action']                = 'Action';
$lang['entity_type']           = 'Entity Type';
$lang['entity_id']             = 'Entity ID';
$lang['details']               = 'Details';
$lang['ip_address']            = 'IP Address';
$lang['clear_logs']            = 'Clear Logs';
$lang['confirm_clear_logs']    = 'Are you sure you want to clear all logs? This action cannot be undone.';
$lang['logs_cleared']          = 'Logs cleared successfully.';
$lang['no_logs_found']         = 'No logs found.';

// Setup Guide
$lang['claude_connector_setup']       = 'Setup Guide';
$lang['database_configuration']       = 'Database Configuration';
$lang['database_config_description']  = 'Your Perfex CRM database connection details are shown below. You\'ll need these for the MCP server configuration.';
$lang['database_host']                = 'Database Host';
$lang['database_name']                = 'Database Name';
$lang['table_prefix']                 = 'Table Prefix';
$lang['db_user_note']                 = 'Note: Create a dedicated database user with appropriate permissions for the MCP server connection.';

$lang['mcp_server_setup']       = 'MCP Server Setup';
$lang['mcp_server_description'] = 'Navigate to the MCP server directory and install dependencies:';

$lang['env_configuration']       = 'Environment Configuration';
$lang['env_config_description']  = 'Create a .env file in the mcp-server directory with your database credentials:';

$lang['claude_desktop_config']     = 'Claude Desktop Configuration';
$lang['claude_config_description'] = 'Add the Perfex CRM MCP server to your Claude Desktop configuration:';

$lang['test_connection']             = 'Test Connection';
$lang['test_connection_description'] = 'Verify that Claude Desktop can communicate with your Perfex CRM:';
$lang['restart_claude_desktop']      = 'Restart Claude Desktop';
$lang['open_new_conversation']       = 'Open a new conversation';
$lang['try_test_query']              = 'Try a test query like "List all Perfex CRM tables"';
$lang['example_prompt']              = 'Example Prompt';

// Permissions
$lang['permission_view']   = 'View';
$lang['permission_create'] = 'Create';
$lang['permission_edit']   = 'Edit';
$lang['permission_delete'] = 'Delete';

// Common
$lang['entity'] = 'Entity';
$lang['date']   = 'Date';
$lang['count']  = 'Count';
$lang['id']     = 'ID';
