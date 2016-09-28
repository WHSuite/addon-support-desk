<?php echo $view->fetch('elements/header.php'); ?>

    <div class="content-inner">
        <div class="container">

            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><?php echo $lang->get('ticket_overview'); ?></div>
                        <div class="panel-content panel-table">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <td width="15%">
                                                <b><?php echo $lang->get('client'); ?>:</b>
                                            </td>
                                            <td>
                                                <?php
                                                    echo $ticket->Client->first_name . ' ' . $ticket->Client->last_name;

                                                    if ($ticket->Client->guest_account != 1):

                                                        $route = $router->generate(
                                                            'admin-client-profile',
                                                            array(
                                                                'id' => $ticket->Client->id
                                                            )
                                                        );

                                                        echo ' (<a href="' . $route . '" target="_blank">' . $lang->get('view') . '</a>)';
                                                    endif;
                                                ?>
                                            </td>
                                            <td width="15%"><b><?php echo $lang->get('assigned_staff'); ?>:</b></td>
                                            <td width="35%">
                                                <?php
                                                    if ($ticket->staff_id > 0) {

                                                        echo $ticket->Staff->first_name . ' ' .
                                                        $ticket->Staff->last_name . ' ('.
                                                        $ticket->Staff->email . ')';

                                                    } else {

                                                        echo $lang->get('not_available');
                                                    }
                                                ?>
                                                <a href="#adminModal" class="showSecurityModal">
                                                    (<?php echo $lang->get('edit'); ?>)
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="15%"><b><?php echo $lang->get('date_created'); ?>:</b></td>
                                            <td width="35%">
                                                <?php
                                                    $date = \Carbon\Carbon::parse(
                                                        $ticket->created_at,
                                                        \App::get('configs')->get('settings.localization.timezone')
                                                    );
                                                    echo $date->format($date_format);
                                                ?>
                                            </td>
                                            <td width="15%"><b><?php echo $lang->get('updated_at'); ?>:</b></td>
                                            <td width="35%">
                                                <?php
                                                    $date = \Carbon\Carbon::parse(
                                                        $ticket->updated_at,
                                                        \App::get('configs')->get('settings.localization.timezone')
                                                    );
                                                    echo $date->format($date_format);
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><b><?php echo $lang->get('priority'); ?>:</b></td>
                                            <td>
                                                <?php
                                                    echo SupportTicketPriority::wrapColor($ticket->SupportTicketPriority);
                                                ?>
                                                <a href="#priorityModal" class="showSecurityModal">
                                                    (<?php echo $lang->get('edit'); ?>)
                                                </a>
                                            </td>
                                            <td><b><?php echo $lang->get('department'); ?>:</b></td>
                                            <td>
                                                <?php
                                                    echo $ticket->SupportDepartment->name;
                                                ?>
                                                <a href="#departmentModal" class="showSecurityModal">
                                                    (<?php echo $lang->get('edit'); ?>)
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><b><?php echo $lang->get('status'); ?>:</b></td>
                                            <td>
                                                <?php
                                                    echo SupportTicket::wrapStatusColor($ticket, 'admin');
                                                ?>
                                                <a href="#statusModal" class="showSecurityModal">
                                                    (<?php echo $lang->get('edit'); ?>)
                                                </a>
                                            </td>
                                            <td><b><?php echo $lang->get('service'); ?>:</b></td>
                                            <td>
                                                <?php
                                                    if (! empty($ticket->product_purchase_id)):

                                                        echo ProductPurchase::getProductName($ticket);
                                                    else:

                                                        echo $lang->get('not_available');
                                                    endif;
                                                ?>
                                                <a href="#serviceModal" class="showSecurityModal">
                                                    (<?php echo $lang->get('edit'); ?>)
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-secondary ticket">
                        <div class="panel-heading"><?php echo $lang->get('ticket_discussion'); ?></div>
                        <div class="panel-content nopadding">

                            <?php if (isset($ticket->SupportPost) && ! empty($ticket->SupportPost)): ?>

                                <?php foreach ($ticket->SupportPost as $SupportPost): ?>

                                    <?php
                                        if (isset($SupportPost->staff_id) && $SupportPost->staff_id == -1):

                                            $email = '';
                                            $name = \App::get('translation')->get('automation');
                                            $class = 'staff';

                                        elseif (! isset($SupportPost->staff_id) || empty($SupportPost->staff_id)):

                                            $email = $ticket->Client->email;
                                            $name = $ticket->Client->first_name . ' ' . $ticket->Client->last_name;
                                            $class = 'customer';

                                        else:

                                            $email = $SupportPost->Staff->email;
                                            $name = $SupportPost->Staff->first_name . ' ' . $SupportPost->Staff->last_name;
                                            $class = 'staff';

                                        endif;
                                    ?>

                                    <div class="row nomargin ticket-item <?php echo $class; ?>">
                                        <div class="col-md-2 text-center ticket-sidebar">

                                            <img src="http://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($email))); ?>?s=100">
                                            <h5><?php echo $name; ?></h5>

                                            <small>
                                                <span class="text-muted">
                                                    <?php if (! isset($SupportPost->staff_id) || empty($SupportPost->staff_id)): ?>

                                                        <?php if ($ticket->Client->guest_account == 1): ?>

                                                            (<?php echo $lang->get('guest'); ?>)
                                                        <?php else: ?>

                                                            (<?php echo $lang->get('customer'); ?>)
                                                        <?php endif; ?>

                                                    <?php else: ?>
                                                        (<?php echo $lang->get('staff'); ?>)
                                                    <?php endif; ?>
                                                </span>
                                            </small>
                                            <br>
                                            <span>
                                                <?php
                                                    $date = \Carbon\Carbon::parse(
                                                        $SupportPost->created_at,
                                                        \App::get('configs')->get('settings.localization.timezone')
                                                    );
                                                    echo $date->format($datetime_format);
                                                ?>
                                            </span>
                                        </div>
                                        <div class="col-md-10 ticket-body">
                                            <?php echo \Addon\SupportDesk\Libraries\TicketView::processBody($SupportPost->body); ?>

                                            <?php
                                                if (\App::checkInstalledAddon('uploader')):
                                                    // load the uploader plugin form
                                                    echo $this->fetch(
                                                        'uploader::elements/listing.php',
                                                        array(
                                                            'model_name' => 'SupportPost',
                                                            'model_id' => $SupportPost->id
                                                        )
                                                    );
                                                endif;
                                            ?>

                                        </div>
                                    </div>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary" id="account-details">
                        <div class="panel-heading"><?php echo $lang->get('submit_reply'); ?></div>
                        <div class="panel-content">
                            <?php
                                echo $forms->open(array(
                                    'method' => 'files',
                                    'class' => '',
                                    'action' => $router->generate('admin-supportticket-reply')
                                ));
                            ?>
                                <?php
                                    echo $forms->hidden(
                                        'data.SupportPost.support_ticket_id',
                                        array(
                                            'value' => $ticket->id
                                        )
                                    );
                                ?>

                                <?php echo $forms->textarea('data.SupportPost.body', false, array('rows' => 8)); ?>

                                <?php
                                    echo $forms->select(
                                        'data.SupportTicket.status',
                                        $lang->get('set_ticket_status'),
                                        array(
                                            'options' => $status,
                                            'value' => 1
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
                                        $lang->get('reply'),
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
    <?php
        echo $view->fetch(
            'support_desk::admin/elements/modal_edits.php',
            array(
                'modal_id' => 'priorityModal',
                'field' => 'support_ticket_priority_id',
                'ticket' => $ticket,
                'label' => $lang->get('priority'),
                'options' => $priorities
            )
        );
        echo $view->fetch(
            'support_desk::admin/elements/modal_edits.php',
            array(
                'modal_id' => 'departmentModal',
                'field' => 'support_department_id',
                'ticket' => $ticket,
                'label' => $lang->get('department'),
                'options' => $departments
            )
        );
        echo $view->fetch(
            'support_desk::admin/elements/modal_edits.php',
            array(
                'modal_id' => 'statusModal',
                'field' => 'status',
                'ticket' => $ticket,
                'label' => $lang->get('status'),
                'options' => $status
            )
        );
        echo $view->fetch(
            'support_desk::admin/elements/modal_edits.php',
            array(
                'modal_id' => 'serviceModal',
                'field' => 'product_purchase_id',
                'ticket' => $ticket,
                'label' => $lang->get('service'),
                'options' => $products
            )
        );

        echo $view->fetch(
            'support_desk::admin/elements/modal_assign_staff.php',
            array(
                'modal_id' => 'adminModal',
                'field' => 'staff_id',
                'ticket' => $ticket,
                'label' => $lang->get('assign_staff'),
                'options' => array()
            )
        );
    ?>

<?php echo $view->fetch('elements/footer.php'); ?>