<?php echo $view->fetch('elements/header.php'); ?>

    <div class="content-inner">
        <div class="container">

            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><?php echo $title; ?></div>
                        <div class="panel-content">

                            <?php
                                echo $forms->open(array(
                                    'method' => 'files',
                                    'action' => $router->generate('admin-supportticket-add')
                                ));
                            ?>

                                <?php
                                    echo $forms->input(
                                        'data.client_search',
                                        $lang->get('search_client'),
                                        array(
                                            'data-search-url' => $router->generate(
                                                'admin-search-clients',
                                                array(
                                                    'type' => 'TYPE',
                                                    'search' => 'SEARCH'
                                                )
                                            )
                                        )
                                    );
                                ?>
                                <span class="help-block"><?php echo $lang->get('search_client_help'); ?></span>

                                <?php
                                    echo $forms->select(
                                        'data.SupportTicket.client_id',
                                        $lang->get('client'),
                                        array(
                                            'options' => array(),
                                            'data-empty-label' => $lang->get('select_client')
                                        )
                                    );
                                ?>

                                <?php
                                    echo $forms->select(
                                        'data.SupportTicket.product_purchase_id',
                                        $lang->get('service'),
                                        array(
                                            'options' => $products,
                                            'data-empty-label' => $lang->get('not_available'),
                                            'data-search-url' => $router->generate(
                                                'admin-search-products',
                                                array(
                                                    'clientid' => 'CLIENTID'
                                                )
                                            )
                                        )
                                    );
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

        </div>
    </div>

<?php echo $view->fetch('elements/footer.php'); ?>