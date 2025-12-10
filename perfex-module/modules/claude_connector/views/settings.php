<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i class="fa fa-cog"></i> <?php echo _l('claude_connector_settings'); ?>
                        </h4>
                    </div>
                    <div class="panel-body">
                        <?php echo form_open(admin_url('claude_connector/settings')); ?>
                        
                        <div class="form-group">
                            <label class="control-label">
                                <input type="checkbox" name="enabled" value="1" 
                                    <?php echo (isset($settings['enabled']) && $settings['enabled'] == '1') ? 'checked' : ''; ?>>
                                <?php echo _l('claude_connector_enabled'); ?>
                            </label>
                            <p class="text-muted"><?php echo _l('claude_connector_enabled_help'); ?></p>
                        </div>

                        <hr>

                        <div class="form-group">
                            <label class="control-label">
                                <input type="checkbox" name="log_actions" value="1" 
                                    <?php echo (isset($settings['log_actions']) && $settings['log_actions'] == '1') ? 'checked' : ''; ?>>
                                <?php echo _l('log_actions'); ?>
                            </label>
                            <p class="text-muted"><?php echo _l('log_actions_help'); ?></p>
                        </div>

                        <hr>

                        <div class="text-right">
                            <a href="<?php echo admin_url('claude_connector'); ?>" class="btn btn-default">
                                <?php echo _l('cancel'); ?>
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <?php echo _l('save'); ?>
                            </button>
                        </div>

                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
