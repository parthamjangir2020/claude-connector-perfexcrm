<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i class="fa fa-list"></i> <?php echo _l('claude_connector_logs'); ?>
                            <div class="pull-right">
                                <?php if (has_permission('claude_connector', '', 'delete')): ?>
                                <a href="<?php echo admin_url('claude_connector/clear_logs'); ?>" 
                                   class="btn btn-danger btn-xs _delete"
                                   onclick="return confirm('<?php echo _l('confirm_clear_logs'); ?>')">
                                    <i class="fa fa-trash"></i> <?php echo _l('clear_logs'); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </h4>
                    </div>
                    <div class="panel-body">
                        <?php if (!empty($logs)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped dt-table">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('id'); ?></th>
                                        <th><?php echo _l('action'); ?></th>
                                        <th><?php echo _l('entity_type'); ?></th>
                                        <th><?php echo _l('entity_id'); ?></th>
                                        <th><?php echo _l('details'); ?></th>
                                        <th><?php echo _l('ip_address'); ?></th>
                                        <th><?php echo _l('date'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?php echo $log->id; ?></td>
                                        <td><code><?php echo htmlspecialchars($log->action); ?></code></td>
                                        <td><?php echo $log->entity_type ?: '-'; ?></td>
                                        <td><?php echo $log->entity_id ?: '-'; ?></td>
                                        <td>
                                            <?php if ($log->details): ?>
                                                <button type="button" class="btn btn-xs btn-default" 
                                                    data-toggle="tooltip" 
                                                    title="<?php echo htmlspecialchars($log->details); ?>">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $log->ip_address ?: '-'; ?></td>
                                        <td><?php echo _dt($log->created_at); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center text-muted">
                            <i class="fa fa-inbox fa-3x"></i>
                            <p class="mtop15"><?php echo _l('no_logs_found'); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
