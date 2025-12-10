<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Create settings table
if (!$CI->db->table_exists(db_prefix() . 'claude_connector_settings')) {
    $CI->db->query("
        CREATE TABLE `" . db_prefix() . "claude_connector_settings` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `setting_name` VARCHAR(255) NOT NULL,
            `setting_value` TEXT,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `setting_name` (`setting_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";
    ");
    
    // Insert default settings
    $CI->db->insert(db_prefix() . 'claude_connector_settings', [
        'setting_name'  => 'enabled',
        'setting_value' => '1',
    ]);
    
    $CI->db->insert(db_prefix() . 'claude_connector_settings', [
        'setting_name'  => 'log_actions',
        'setting_value' => '1',
    ]);
}

// Create logs table
if (!$CI->db->table_exists(db_prefix() . 'claude_connector_logs')) {
    $CI->db->query("
        CREATE TABLE `" . db_prefix() . "claude_connector_logs` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `action` VARCHAR(255) NOT NULL,
            `entity_type` VARCHAR(100),
            `entity_id` INT(11),
            `details` TEXT,
            `ip_address` VARCHAR(45),
            `user_agent` TEXT,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `action` (`action`),
            KEY `entity_type` (`entity_type`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";
    ");
}

// Add module option
add_option('claude_connector_enabled', '1');
add_option('claude_connector_log_actions', '1');
