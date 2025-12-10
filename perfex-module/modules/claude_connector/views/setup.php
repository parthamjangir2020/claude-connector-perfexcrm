<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i class="fa fa-book"></i> <?php echo _l('claude_connector_setup'); ?>
                        </h4>
                    </div>
                    <div class="panel-body">
                        
                        <!-- Step 1: Database Configuration -->
                        <div class="well">
                            <h5><span class="label label-primary">1</span> <?php echo _l('database_configuration'); ?></h5>
                            <p class="text-muted"><?php echo _l('database_config_description'); ?></p>
                            
                            <div class="row mtop15">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?php echo _l('database_host'); ?></label>
                                        <input type="text" class="form-control" 
                                            value="<?php echo htmlspecialchars($db_config['host']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?php echo _l('database_name'); ?></label>
                                        <input type="text" class="form-control" 
                                            value="<?php echo htmlspecialchars($db_config['database']); ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><?php echo _l('table_prefix'); ?></label>
                                <input type="text" class="form-control" 
                                    value="<?php echo htmlspecialchars($db_config['prefix']); ?>" readonly>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                <?php echo _l('db_user_note'); ?>
                            </div>
                        </div>

                        <!-- Step 2: MCP Server Setup -->
                        <div class="well">
                            <h5><span class="label label-primary">2</span> <?php echo _l('mcp_server_setup'); ?></h5>
                            <p class="text-muted"><?php echo _l('mcp_server_description'); ?></p>
                            
                            <pre class="mtop15"><code>cd mcp-server
npm install
npm run build</code></pre>
                        </div>

                        <!-- Step 3: Environment Configuration -->
                        <div class="well">
                            <h5><span class="label label-primary">3</span> <?php echo _l('env_configuration'); ?></h5>
                            <p class="text-muted"><?php echo _l('env_config_description'); ?></p>
                            
                            <pre class="mtop15"><code># Create .env file in mcp-server/
DB_HOST=<?php echo htmlspecialchars($db_config['host']); ?>

DB_PORT=3306
DB_NAME=<?php echo htmlspecialchars($db_config['database']); ?>

DB_USER=your_database_user
DB_PASSWORD=your_database_password
DB_PREFIX=<?php echo htmlspecialchars($db_config['prefix']); ?></code></pre>
                        </div>

                        <!-- Step 4: Claude Desktop Configuration -->
                        <div class="well">
                            <h5><span class="label label-primary">4</span> <?php echo _l('claude_desktop_config'); ?></h5>
                            <p class="text-muted"><?php echo _l('claude_config_description'); ?></p>
                            
                            <p><strong>Windows:</strong> <code>%APPDATA%\Claude\claude_desktop_config.json</code></p>
                            <p><strong>macOS:</strong> <code>~/Library/Application Support/Claude/claude_desktop_config.json</code></p>
                            
                            <pre class="mtop15"><code>{
  "mcpServers": {
    "perfex-crm": {
      "command": "node",
      "args": ["/path/to/mcp-server/dist/index.js"]
    }
  }
}</code></pre>
                        </div>

                        <!-- Step 5: Test Connection -->
                        <div class="well">
                            <h5><span class="label label-primary">5</span> <?php echo _l('test_connection'); ?></h5>
                            <p class="text-muted"><?php echo _l('test_connection_description'); ?></p>
                            
                            <ol class="mtop15">
                                <li><?php echo _l('restart_claude_desktop'); ?></li>
                                <li><?php echo _l('open_new_conversation'); ?></li>
                                <li><?php echo _l('try_test_query'); ?></li>
                            </ol>
                            
                            <div class="alert alert-success">
                                <strong><?php echo _l('example_prompt'); ?>:</strong><br>
                                "List all tables in my Perfex CRM database"
                            </div>
                        </div>

                        <div class="text-center mtop20">
                            <a href="<?php echo admin_url('claude_connector'); ?>" class="btn btn-default">
                                <i class="fa fa-arrow-left"></i> <?php echo _l('back_to_dashboard'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
