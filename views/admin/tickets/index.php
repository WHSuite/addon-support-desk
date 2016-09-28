<?php echo $view->fetch('elements/header.php'); ?>

    <div class="content-inner">
        <div class="container">
            <div class="row">
                <div class="col-md-12">

                    <div class="panel panel-secondary">
                        <div class="panel-heading"><?php echo $title; ?></div>
                        <div class="panel-content panel-table">
                            <table class="table table-striped">

                                <?php echo $view->fetch($tbl_header_tpl); ?>

                                <?php echo $view->fetch($tbl_body_tpl, array('section' => 'admin')); ?>

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
    </div>

<?php echo $view->fetch('elements/footer.php');
