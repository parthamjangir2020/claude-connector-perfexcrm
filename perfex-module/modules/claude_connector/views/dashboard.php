<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="no-margin font-bold">
                                    <i class="fa fa-robot"></i> <?php echo _l('claude_connector'); ?>
                                </h4>
                                <p class="text-muted"><?php echo _l('claude_connector_description'); ?></p>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="<?php echo admin_url('claude_connector/settings'); ?>" class="btn btn-default">
                                    <i class="fa fa-cog"></i> <?php echo _l('settings'); ?>
                                </a>
                                <a href="<?php echo admin_url('claude_connector/setup'); ?>" class="btn btn-primary">
                                    <i class="fa fa-book"></i> <?php echo _l('setup_guide'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="panel_s">
                            <div class="panel-body">
                                <h3 class="text-success bold">
                                    <?php echo isset($stats['total_logs']) ? $stats['total_logs'] : 0; ?>
                                </h3>
                                <span class="text-muted"><?php echo _l('total_actions_logged'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="panel_s">
                            <div class="panel-body">
                                <h3 class="text-info bold">
                                    <?php echo isset($stats['today_logs']) ? $stats['today_logs'] : 0; ?>
                                </h3>
                                <span class="text-muted"><?php echo _l('actions_today'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="panel_s">
                            <div class="panel-body">
                                <h3 class="text-primary bold">
                                    <?php echo isset($settings['enabled']) && $settings['enabled'] == '1' ? _l('active') : _l('inactive'); ?>
                                </h3>
                                <span class="text-muted"><?php echo _l('connection_status'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Actions -->
                <?php if (!empty($stats['top_actions'])): ?>
                <div class="panel_s">
                    <div class="panel-heading">
                        <h4 class="panel-title"><?php echo _l('top_actions'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('action'); ?></th>
                                        <th><?php echo _l('count'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['top_actions'] as $action): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($action['action']); ?></code></td>
                                        <td><?php echo $action['count']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Recent Logs -->
                <div class="panel_s">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <?php echo _l('recent_activity'); ?>
                            <a href="<?php echo admin_url('claude_connector/logs'); ?>" class="pull-right text-muted">
                                <?php echo _l('view_all'); ?> <i class="fa fa-arrow-right"></i>
                            </a>
                        </h4>
                    </div>
                    <div class="panel-body">
                        <?php if (!empty($recent_logs)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('action'); ?></th>
                                        <th><?php echo _l('entity'); ?></th>
                                        <th><?php echo _l('date'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_logs as $log): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($log->action); ?></code></td>
                                        <td>
                                            <?php if ($log->entity_type): ?>
                                                <?php echo htmlspecialchars($log->entity_type); ?>
                                                <?php if ($log->entity_id): ?>
                                                    #<?php echo $log->entity_id; ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo _dt($log->created_at); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p class="text-muted text-center"><?php echo _l('no_activity_yet'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
