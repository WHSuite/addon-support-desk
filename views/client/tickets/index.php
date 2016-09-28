<?php echo $view->fetch('elements/header.php'); ?>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-primary panel-newsbox">
            <div class="panel-heading">
                <?php echo $title; ?>

                <a href="<?php echo $router->generate('client-supportticket-add'); ?>" class="pull-right btn btn-default btn-xs">
                    Submit Ticket
                </a>
            </div>
            <div class="panel-body panel-table">
                <div class="table-responsive">
                    <table class="table table-striped">

                        <?php echo $view->fetch($tbl_header_tpl); ?>

                        <?php echo $view->fetch($tbl_body_tpl, array('section' => 'client')); ?>

                        <tfoot>
                            <tr>
                                <td colspan="<?php echo (! empty($columns)) ? count($columns) : '0'; ?>" class="text-right">
                                    <?php echo $pagination; ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $view->fetch('elements/footer.php'); ?>