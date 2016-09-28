<?php

use \Illuminate\Support\Str;
use \Whsuite\Inputs\Post as PostInput;

class SupportDepartmentsController extends \AdminController
{
    protected function indexToolbar()
    {
        return array(
            array(
                'url_route' => 'admin-supportdepartment',
                'link_class' => '',
                'icon' => 'fa fa-list-ul',
                'label' => 'supportdepartment_management'
            ),
            array(
                'url_route' => 'admin-supportdepartment-add',
                'link_class' => '',
                'icon' => 'fa fa-plus',
                'label' => 'supportdepartment_add'
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
                'field' => 'notification_email'
            ),
            array(
                'field' => 'piping'
            ),
            array(
                'field' => 'clients_only'
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
                'url_route' => 'admin-supportdepartment-edit',
                'link_class' => 'btn btn-primary btn-small pull-right',
                'icon' => 'fa fa-pencil',
                'label' => 'edit',
                'params' => array('id')
            ),
            'delete' => array(
                'url_route' => 'admin-supportdepartment-delete',
                'link_class' => 'btn btn-danger btn-small pull-right',
                'icon' => 'fa fa-remove',
                'label' => 'delete',
                'params' => array('id')
            )
        );
    }

    /**
     * getExtraData
     *
     * function to allow call to get and assign any extra data needed on the form
     *
     * @param object $model Model object for the controller we are in
     */
    protected function getExtraData($model)
    {
        $piping_settings_type = array(
            'script' => 'Script'
/*            'pop3' => 'POP3',
            'imap' => 'IMAP'*/
        );

        $piping_settings_security = array(
            'none' => 'None',
            'ssl' => 'SSL',
            'tls' => 'TLS'
        );

        $piping_settings_mark_as = array(
            'read' => $this->lang->get('read'),
            'delete' => $this->lang->get('delete')
        );

        // get the staff groups
        $groups = $this->admin_auth->findAllGroups();

        $this->view->set(compact(
            'piping_settings_type',
            'piping_settings_security',
            'piping_settings_mark_as',
            'groups'
        ));

        // check for edit and decode piping settings if present
        if (! empty($model->piping_settings)) {

            $data = PostInput::get('data.SupportDepartment');

            $piping_settings = json_decode($model->piping_settings);
            foreach ($piping_settings as $field => $value) {

                $data['piping_settings_' . $field] = $value;
            }

            PostInput::set('data.SupportDepartment', $data);
        }
    }


    protected function formFields()
    {
        return array(
            'SupportDepartment.id',
            'SupportDepartment.name',
            'SupportDepartment.description',
            'SupportDepartment.notification_email' => array(
                'type' => 'text'
            ),
            'SupportDepartment.clients_only',
            'SupportDepartment.piping' => array(
                'class' => 'piping-toggle'
            ),

            'SupportDepartment.piping_settings_type' => array(
                'label' => 'type',
                'type' => 'hidden'
            ),
            'SupportDepartment.piping_settings_security' => array(
                'label' => 'security',
                'type' => 'hidden'
            ),
            'SupportDepartment.piping_settings_host' => array(
                'label' => 'host',
                'type' => 'hidden'
            ),
            'SupportDepartment.piping_settings_port' => array(
                'label' => 'port',
                'type' => 'hidden'
            ),
            'SupportDepartment.piping_settings_username' => array(
                'label' => 'username',
                'type' => 'hidden'
            ),
            'SupportDepartment.piping_settings_password' => array(
                'label' => 'password',
                'type' => 'hidden'
            ),
            'SupportDepartment.piping_settings_inbox_name' => array(
                'label' => 'inbox_name',
                'type' => 'hidden'
            ),
            'SupportDepartment.piping_settings_mark_as' => array(
                'label' => 'mark_as',
                'type' => 'select',
                'type' => 'hidden'
            ),
            'SupportDepartment.piping_settings_piping_email' => array(
                'label' => 'piping_email'
            ),


            'SupportDepartment.is_active' => array(
                'label' => 'active'
            ),
            'SupportDepartment.StaffGroup'
        );
    }

    /**
     * processData
     *
     * Function called as the form data is assigned to the model.
     * useful for any processing needed on the field
     *
     * @param string $field - the field name we are dealing with
     * @param mixed $data - the data we are assigning to the model
     * @param object $main_model - the main model we are saving (passed by reference so we can affect it!)
     * @return mixed - data to assign to the model
     */
    protected function processData($field, $data, &$main_model)
    {
        if ($field == 'StaffGroup') {

            $data = false;

        } elseif (Str::contains($field, 'piping_settings_')) {

            $data = false;

        } elseif ($field == 'piping') {

            if ($data == 1) {

                $department = PostInput::get('data.SupportDepartment');
                $settings = array();

                foreach ($department as $key => $value) {

                    if (Str::contains($key, 'piping_settings_')) {

                        $key = str_replace('piping_settings_', '', $key);

                        $settings[$key] = $value;
                    }
                }

                $main_model->piping_settings = json_encode($settings);

            } else {

                $main_model->piping_settings = '';
            }
        }

        return $data;
    }


    /**
     * afterSave callback
     *
     * Called once the save was successful
     *
     * @param object $main_model - the main model we are saving (passed by reference so we can affect it!)
     */
    protected function afterSave(&$main_model)
    {
        // update and save the related groups
        $groups = PostInput::get('data.SupportDepartment.StaffGroup');
        foreach ($groups as $key => $value) {

            if ($value == 1) {

                $groups[$key] = $key;
            } else {

                unset($groups[$key]);
            }
        }

        $main_model->StaffGroup()->sync($groups);
    }


    public function form($id = null)
    {
        $this->assets->addStyle('support_desk::support-form.css');
        $this->assets->addScript('support_desk::departments-form.js');

        $this->render_view = 'support_desk::admin/departments/form.php';

        // get the users selected groups if editing
        if (! is_null($id) && ! is_array(PostInput::get('data.SupportDepartment.StaffGroup'))) {

            $department = SupportDepartment::find($id);
            $groups = $department->StaffGroup()->get();

            $staff_groups = array();
            foreach ($groups as $group) {

                $staff_groups[$group->id] = 1;
            }

            PostInput::set('data.SupportDepartment.StaffGroup', $staff_groups);
        }

        parent::form($id);
    }


}
