<?php echo $view->fetch('elements/header.php'); ?>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary" id="account-details">
                <div class="panel-heading"><?php echo $lang->get('supportticket_add'); ?></div>
                <div class="panel-body">
                    <?php
                        echo $forms->open(array(
                            'method' => 'files',
                            'class' => '',
                            'action' => $router->generate('client-supportticket-add')
                        ));
                    ?>
                        <?php
                            if (! isset($client) || ! is_object($client)):

                                echo $forms->input(
                                    'data.SupportTicket.Client.first_name',
                                    $lang->get('first_name')
                                );

                                echo $forms->input(
                                    'data.SupportTicket.Client.last_name',
                                    $lang->get('last_name')
                                );

                                echo $forms->input(
                                    'data.SupportTicket.Client.email',
                                    $lang->get('email')
                                );
                            endif;
                        ?>

                        <?php
                            echo $forms->input(
                                'data.SupportTicket.subject',
                                $lang->get('subject')
                            );
                        ?>

                        <?php
                            echo $forms->select(
                                'data.SupportTicket.support_ticket_priority_id',
                                $lang->get('priority'),
                                array(
                                    'options' => $priorities
                                )
                            );
                        ?>

                        <?php
                            echo $forms->select(
                                'data.SupportTicket.support_department_id',
                                $lang->get('department'),
                                array(
                                    'options' => $departments
                                )
                            );
                        ?>

                        <?php
                            echo $forms->select(
                                'data.SupportTicket.product_purchase_id',
                                $lang->get('service'),
                                array(
                                    'options' => $products
                                )
                            );
                        ?>

                        <?php
                            echo $forms->textarea(
                                'data.SupportTicket.SupportPost.body',
                                $lang->get('message'),
                                array(
                                    'rows' => 8
                                )
                            );
                        ?>

                        <?php
                            if (\App::checkInstalledAddon('uploader')):
                                // load the uploader plugin form
                                echo $this->fetch(
                                    'uploader::elements/form.php',
                                    array(
                                        'model_name' => 'SupportPost'
                                    )
                                );
                            endif;
                        ?>

                        <?php
                            echo $forms->submit(
                                'reply',
                                $lang->get('supportticket_add'),
                                array(
                                    'type' => 'submit',
                                    'wrap' => 'div',
                                    'wrap_class' => 'form-actions',
                                    'class' => 'btn btn-primary btn-lg'
                                )
                            );
                        ?>

                    <?php echo $forms->close(); ?>
                </div>
            </div>
        </div>
    </div>

<?php echo $view->fetch('elements/footer.php'); ?>