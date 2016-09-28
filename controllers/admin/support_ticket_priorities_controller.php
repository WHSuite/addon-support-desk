<?php

use \Illuminate\Support\Str;
use \Whsuite\Inputs\Post as PostInput;

class SupportTicketPrioritiesController extends \AdminController
{

    protected function indexToolbar()
    {
        return array(
            array(
                'url_route' => 'admin-supportticketpriority',
                'link_class' => '',
                'icon' => 'fa fa-list-ul',
                'label' => 'supportticketpriority_management'
            ),
            array(
                'url_route' => 'admin-supportticketpriority-add',
                'link_class' => '',
                'icon' => 'fa fa-plus',
                'label' => 'supportticketpriority_add'
            )
        );
    }

    protected function indexColumns()
    {
        return array(
            array(
                'field' => 'name'
            ),
            array(
                'action' => 'edit',
                'label' => null
            ),
            array(
                'action' => 'delete',
                'label' => null
            )
        );
    }

    protected function indexActions()
    {
        return array(
            'edit' => array(
                'url_route' => 'admin-supportticketpriority-edit',
                'link_class' => 'btn btn-primary btn-small pull-right',
                'icon' => 'fa fa-pencil',
                'label' => 'edit',
                'params' => array('id')
            ),
            'delete' => array(
                'url_route' => 'admin-supportticketpriority-delete',
                'link_class' => 'btn btn-danger btn-small pull-right',
                'icon' => 'fa fa-remove',
                'label' => 'delete',
                'params' => array('id')
            )
        );
    }


    protected function formFields()
    {
        return array(
            'SupportTicketPriority.id',
            'SupportTicketPriority.name',
            'SupportTicketPriority.text_hex' => array(
                'label' => 'label_color',
                'class' => 'text-hex',
                'after' => '<div class="color-picker"><div></div></div>'
            ),
            'SupportTicketPriority.sort',
            'SupportTicketPriority.is_active' => array(
                'label' => 'active'
            )
        );
    }


    public function form($id = null)
    {
        $this->assets->addStyle(array(
            'support_desk::colorpicker.css',
            'support_desk::support-form.css'
        ));
        $this->assets->addScript(array(
            'support_desk::colorpicker.js',
            'support_desk::priorities-form.js'
        ));

        // Add the default green value to the form if nothing is set
        $hex = PostInput::get('data.SupportTicketPriority.text_hex');
        if (empty($hex)) {

            PostInput::set('data.SupportTicketPriority.text_hex', '27ae60');
        }

        return parent::form($id);
    }


}
