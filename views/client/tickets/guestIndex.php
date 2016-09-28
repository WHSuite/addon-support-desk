<?php echo $view->fetch('elements/header.php'); ?>

<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-primary">
            <div class="panel-heading"><?php echo $lang->get('client_login'); ?></div>
            <div class="panel-body">
                <?php echo $forms->open(array('action' => $router->generate('client-login'), 'class' => 'form-vertical')); ?>

                    <?php echo $forms->input('email', $lang->get('email')); ?>
                    <?php echo $forms->password('password', $lang->get('password')); ?>
                    <?php echo $forms->checkbox('remember', $lang->get('remember_me')); ?>

                    <div class="form-actions">
                        <?php echo $forms->submit('submit', $lang->get('login'), array('class' => 'btn btn-primary btn-block')); ?>
                    </div>
                    <p class="text-center">
                        <a href="<?php echo $router->generate('client-forgot-password'); ?>" class="btn btn-default btn-sm"><?php echo $lang->get('reset_password'); ?></a>
                        <a href="<?php echo $router->generate('client-create-account'); ?>" class="btn btn-default btn-sm"><?php echo $lang->get('create_account'); ?></a>
                    </p>
                <?php echo $forms->close(); ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">

        <div class="panel panel-default">
            <div class="panel-heading"><?php echo $lang->get('supportticket_add'); ?></div>
            <div class="panel-body">
                <a href="<?php echo $router->generate('client-supportticket-add'); ?>" class="btn btn-success btn-block"><?php echo $lang->get('create_ticket_guest'); ?></a>
            </div>
        </div>

    </div>
</div>


<?php echo $view->fetch('elements/footer.php'); ?>
