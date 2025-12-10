<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Claude_connector extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('claude_connector_model');
        
        if (!has_permission('claude_connector', '', 'view')) {
            access_denied('Claude Connector');
        }
    }

    /**
     * Main dashboard page
     */
    public function index()
    {
        $data['title'] = _l('claude_connector');
        $data['settings'] = $this->claude_connector_model->get_all_settings();
        $data['recent_logs'] = $this->claude_connector_model->get_logs(20);
        $data['stats'] = $this->get_dashboard_stats();
        
        $this->load->view('dashboard', $data);
    }

    /**
     * Settings page
     */
    public function settings()
    {
        if (!has_permission('claude_connector', '', 'edit')) {
            access_denied('Claude Connector Settings');
        }

        if ($this->input->post()) {
            $data = [
                'enabled'     => $this->input->post('enabled') ? '1' : '0',
                'log_actions' => $this->input->post('log_actions') ? '1' : '0',
            ];
            
            foreach ($data as $name => $value) {
                $this->claude_connector_model->update_setting($name, $value);
            }
            
            set_alert('success', _l('settings_updated'));
            redirect(admin_url('claude_connector/settings'));
        }

        $data['title'] = _l('claude_connector_settings');
        $data['settings'] = $this->claude_connector_model->get_all_settings();
        
        $this->load->view('settings', $data);
    }

    /**
     * View logs page
     */
    public function logs()
    {
        $data['title'] = _l('claude_connector_logs');
        $data['logs'] = $this->claude_connector_model->get_logs(100);
        
        $this->load->view('logs', $data);
    }

    /**
     * Clear logs
     */
    public function clear_logs()
    {
        if (!has_permission('claude_connector', '', 'delete')) {
            access_denied('Claude Connector Clear Logs');
        }

        $this->claude_connector_model->clear_logs();
        set_alert('success', _l('logs_cleared'));
        redirect(admin_url('claude_connector/logs'));
    }

    /**
     * Setup guide page
     */
    public function setup()
    {
        $data['title'] = _l('claude_connector_setup');
        $data['db_config'] = [
            'host'     => $this->db->hostname,
            'database' => $this->db->database,
            'prefix'   => db_prefix(),
        ];
        
        $this->load->view('setup', $data);
    }

    /**
     * Get dashboard statistics
     */
    private function get_dashboard_stats()
    {
        $stats = [];
        
        // Total logs
        $this->db->from(db_prefix() . 'claude_connector_logs');
        $stats['total_logs'] = $this->db->count_all_results();
        
        // Today's logs
        $this->db->from(db_prefix() . 'claude_connector_logs');
        $this->db->where('DATE(created_at)', date('Y-m-d'));
        $stats['today_logs'] = $this->db->count_all_results();
        
        // Most used actions
        $stats['top_actions'] = $this->db->query("
            SELECT action, COUNT(*) as count 
            FROM " . db_prefix() . "claude_connector_logs 
            GROUP BY action 
            ORDER BY count DESC 
            LIMIT 5
        ")->result_array();
        
        return $stats;
    }
}
