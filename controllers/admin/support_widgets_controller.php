<?php

use \Illuminate\Support\Str;

class SupportWidgetsController extends \WidgetsController
{
    protected function indexActions()
    {
        return array(
            'view' => array(
                'url_route' => 'admin-supportticket-view',
                'link_class' => 'btn btn-primary btn-small pull-right',
                'icon' => 'fa fa-chevron-right',
                'label' => 'view',
                'params' => array('id')
            ),
            'delete' => array(
                'url_route' => 'admin-supportticket-delete',
                'link_class' => 'btn btn-danger btn-small pull-right',
                'icon' => 'fa fa-remove',
                'label' => 'delete',
                'params' => array('id')
            )
        );
    }

    protected function indexColumns()
    {
        return array(
            array(
                'field' => 'id',
            ),
            array(
                'field' => 'subject',
                'class' => 'ticket-subjects'
            ),
            array(
                'field' => 'SupportTicketPriority.name',
                'label' => 'priority'
            ),
            array(
                'field' => 'SupportDepartment.name',
                'label' => 'department'
            ),
            array(
                'type' => 'options',
                'field' => 'status',
                'label' => 'status'
            ),
            array(
                'field' => 'updated_at'
            ),
            array(
                'action' => 'view',
                'label' => null
            )
        );
    }

    /**
     * dashboard widget to pull in the latest active tickets
     * will show half of what the standard "results  per page" setting is
     *
     */
    public function activeTickets()
    {
        $per_page = ceil((\App::get('configs')->get('settings.general.results_per_page') / 2) );

        $data = SupportTicket::adminTicketList(
            $this->admin_user,
            $per_page,
            1
        );

        $this->view->set(array(
            'data' => $data,
            'columns' => $this->indexColumns(),
            'actions' => $this->indexActions()
        ));

        // check for overriding table template
        if (empty($this->render_view_tbl_header)) {

            $this->render_view_tbl_header = 'support_desk::common/tableHeader.php';
        }
        if (empty($this->render_view_tbl_body)) {

            $this->render_view_tbl_body = 'support_desk::common/tableBody.php';
        }

        $this->view->set(array(
            'tbl_header_tpl' => $this->render_view_tbl_header,
            'tbl_body_tpl' => $this->render_view_tbl_body
        ));

        // load the listings helper
        App::factory('\App\Libraries\ListingsHelper');

        // add support desk css
        $this->assets->addStyle('support_desk::ticket-list.css');

        // annnnnnd render.
        $this->view->display('support_desk::admin/widgets/activeTickets.php');
    }

}
