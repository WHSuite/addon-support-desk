<div class="modal fade" id="<?php echo (isset($modal_id)) ? $modal_id : 'ticket-field-edit'; ?>" tabindex="-1" role="dialog" aria-labelledby="securityModal" aria-hidden="true">
    <?php
        echo $forms->open(
            array(
                'action' => $router->generate('admin-supportticket-edit'),
                'id' => (isset($modal_id) ? $modal_id . '-field-edit' : 'ticket-field-edit'),
                'class' => 'form-horizontal'
            )
        );

        echo $forms->hidden(
            'data.ticket_id',
            array(
                'value' => $ticket->id
            )
        );
    ?>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo $lang->get('supportticket_edit'); ?></h4>
            </div>
            <div class="modal-body">

                <?php
                    echo $forms->select(
                        'data.SupportTicket.' . $field,
                        $label,
                        array(
                            'options' => $options,
                            'value' => $ticket->{$field}
                        )
                    );
                ?>

            </div>
            <div class="modal-footer">

                <?php
                    echo $forms->submit(
                        'submit',
                        $lang->get('save'),
                        array(
                            'class' => 'btn btn-primary pull-left',
                            'id' => (isset($modal_id) ? $modal_id . '-submit' : 'submit')
                        )
                    );
                ?>
                <?php
                    echo $forms->button(
                        'close',
                        $lang->get('close'),
                        array(
                            'data-dismiss' => 'modal',
                            'class' => 'btn',
                            'id' => (isset($modal_id) ? $modal_id . '-close' : 'close')
                        )
                    );
                ?>
            </div>
        </div>
    </div>
    <?php echo $forms->close(); ?>
</div>
