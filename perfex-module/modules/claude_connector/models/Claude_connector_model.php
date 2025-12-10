<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Claude_connector_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all settings
     */
    public function get_all_settings()
    {
        $settings = [];
        $result = $this->db->get(db_prefix() . 'claude_connector_settings')->result();
        
        foreach ($result as $row) {
            $settings[$row->setting_name] = $row->setting_value;
        }
        
        return $settings;
    }

    /**
     * Get single setting
     */
    public function get_setting($name, $default = null)
    {
        $this->db->where('setting_name', $name);
        $row = $this->db->get(db_prefix() . 'claude_connector_settings')->row();
        
        return $row ? $row->setting_value : $default;
    }

    /**
     * Update setting
     */
    public function update_setting($name, $value)
    {
        $exists = $this->db->where('setting_name', $name)->get(db_prefix() . 'claude_connector_settings')->row();
        
        if ($exists) {
            $this->db->where('setting_name', $name);
            return $this->db->update(db_prefix() . 'claude_connector_settings', [
                'setting_value' => $value,
            ]);
        }
        
        return $this->db->insert(db_prefix() . 'claude_connector_settings', [
            'setting_name'  => $name,
            'setting_value' => $value,
        ]);
    }

    /**
     * Log an action
     */
    public function log_action($action, $entity_type = null, $entity_id = null, $details = null)
    {
        // Check if logging is enabled
        if ($this->get_setting('log_actions', '1') !== '1') {
            return false;
        }

        $CI = &get_instance();
        
        return $this->db->insert(db_prefix() . 'claude_connector_logs', [
            'action'      => $action,
            'entity_type' => $entity_type,
            'entity_id'   => $entity_id,
            'details'     => is_array($details) ? json_encode($details) : $details,
            'ip_address'  => $CI->input->ip_address(),
            'user_agent'  => $CI->input->user_agent(),
        ]);
    }

    /**
     * Get logs
     */
    public function get_logs($limit = 50, $offset = 0)
    {
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        
        return $this->db->get(db_prefix() . 'claude_connector_logs')->result();
    }

    /**
     * Get logs count
     */
    public function get_logs_count()
    {
        return $this->db->count_all(db_prefix() . 'claude_connector_logs');
    }

    /**
     * Clear all logs
     */
    public function clear_logs()
    {
        return $this->db->truncate(db_prefix() . 'claude_connector_logs');
    }

    /**
     * Delete old logs (older than X days)
     */
    public function delete_old_logs($days = 30)
    {
        $this->db->where('created_at <', date('Y-m-d H:i:s', strtotime("-{$days} days")));
        return $this->db->delete(db_prefix() . 'claude_connector_logs');
    }
}
