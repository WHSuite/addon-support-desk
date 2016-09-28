<div class="col-md-8">

    <div class="panel panel-secondary">
        <div class="panel-heading"><?php echo $lang->get('widget_support_desk_active_tickets'); ?></div>
        <div class="panel-content panel-table">
            <table class="table table-striped">

                <?php echo $view->fetch($tbl_header_tpl); ?>

                <?php echo $view->fetch($tbl_body_tpl, array('section' => 'admin')); ?>

                <tfoot>
                    <tr>
                        <td colspan="7" class="text-right">
                            <a href="<?php echo $router->generate('admin-supportticket'); ?>">
                                <?php echo $lang->get('view_all'); ?>
                            </a>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>