<?php

use \Illuminate\Support\Str;
use \Whsuite\Inputs\Post as PostInput;

class SupportTicketsController extends \AdminController
{
    protected function indexToolbar()
    {
        return array(
            array(
                'url_route' => 'admin-supportticket',
                'link_class' => '',
                'icon' => 'fa fa-list-ul',
                'label' => 'supportticket_management'
            ),
            array(
                'url_route' => 'admin-supportticket-add',
                'link_class' => '',
                'icon' => 'fa fa-plus',
                'label' => 'supportticket_add'
            )
        );
    }

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


    protected function formToolbar($data)
    {
        $toolbar = parent::formToolbar($data);

        if (isset($data->id) && ! empty($data->id)) {

            $toolbar[] = array(
                'url_route' => 'admin-supportticket-delete',
                'link_class' => '',
                'icon' => 'fa fa-remove',
                'label' => 'supportticket_delete',
                'route_params' => array('id' => $data->id)
            );
        }

        return $toolbar;
    }

    public function index($page = 1, $per_page = null)
    {
        // get the sectin header language
        $page_title = $this->lang->get('supportticket_management');

        // build the breadcrumb
        $this->indexBreadcrumb('SupportTicket', $page_title);

        // if no per pages are set, set default
        if (empty($per_page)) {

            $per_page = App::get('configs')->get('settings.general.results_per_page');
        }

        // get the actual data
        $data = SupportTicket::adminTicketList(
            $this->admin_user,
            $per_page,
            $page
        );

        // set the variables
        $this->view->set(array(
            'data' => $data,
            'title' => $page_title,
            'toolbar' => $this->indexToolbar(),
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
        $this->view->display('support_desk::admin/tickets/index.php');
    }

    public function view($id)
    {
        $data = SupportTicket::where('id', '=', $id)
            ->where(function($query) {

                $query->whereIn('support_department_id', SupportDepartment::getUsersDepartments($this->admin_user))
                    ->Orwhere('staff_id', '=', $this->admin_user->id);
            })
            ->with('Client', 'SupportTicketPriority', 'SupportDepartment', 'SupportPost.Staff', 'Staff')
            ->first();

        // check if it's a valid item
        if (! $data) {

            App::get('session')->setFlash('error', $this->lang->get('item_not_found'));
            return header("Location: ".App::get('router')->generate('admin-supportticket'));
        }

        $title = $data->subject . ' (#' . $data->id . ')';
        $this->formBreadcrumb('SupportTicket', $title);

        // set the ticket statuses for the reply form
        $status = $status = SupportTicket::statusDropDown('admin');

        $this->view->set(array(
            'ticket' => $data,
            'title' => $title,
            'toolbar' => $this->formToolbar($data),
            'date_format' => \App::get('configs')->get('settings.localization.short_date_format'),
            'datetime_format' => \App::get('configs')->get('settings.localization.short_datetime_format'),
            'status' => $status
        ));

        // add support desk css
        $this->assets->addStyle('support_desk::ticket-view.css');

        // get the extra data, for the modal edits
        $this->getExtraData($data);

        // render the view
        $this->view->display('support_desk::admin/tickets/view.php');
    }


    /**
     * redefine the scaffolding form so we can get the ticket template
     */
    protected function formFields()
    {
        $fields = array(
            'SupportTicket.id',
            'SupportTicket.subject',
            'SupportTicket.support_ticket_priority_id',
            'SupportTicket.support_department_id',
            'SupportTicket.product_purchase_id',
            'SupportTicket.client_id',
            'SupportTicket.status'
        );

        return $fields;
    }

    public function form($id = null)
    {
        $this->render_view = 'support_desk::admin/tickets/form.php';

        $data = PostInput::get('data');

        if (! empty($data) && isset($_POST['data']) && ! empty($_POST['data'])) {

            PostInput::set('data.SupportTicket.status', '0');
        }

        $this->assets->addScript('support_desk::admin-ticket-create.js');

        return parent::form($id);
    }

    protected function afterSave(&$main_model)
    {
        parent::afterSave($main_model);

        $SupportPost = new SupportPost;
        $SupportPost->body = PostInput::get('data.SupportTicket.SupportPost.body');
        $SupportPost->staff_id = $this->admin_user->id;

        $SupportPost = $main_model->SupportPost()->save($SupportPost);

        if (\App::checkInstalledAddon('uploader')) {

            // process any uploads
            \Addon\Uploader\Libraries\Process::uploads('SupportPost', $SupportPost);
        }

        // ticket notification
        \Addon\SupportDesk\Libraries\Notifications::clientNewReply($main_model->id);
    }

    /**
     * redefine the getExtraData function to get the priorities and departments
     *
     */
    protected function getExtraData($model)
    {
        parent::getExtraData($model);

        $departments = SupportDepartment::formattedList();
        $priorities = SupportTicketPriority::formattedList('id', 'name', array(), 'sort', 'asc');

        // get their products so we can assign a product to a ticket
        $products = array(
            '0' => $this->lang->get('not_available')
        );

        $this->view->set(array(
            'departments' => $departments,
            'priorities' => $priorities,
            'products' => $products
        ));
    }

    /**
     *  function called by the ajax to get list of staff
     *
     * @param   array         Array of params from the URL
     *                        0 - type ('id' or 'name-email')
     *                        1 - what we're searching for
     * @return  json|null    Json return to jQuery
     */
    public function ajaxFindStaff($type, $search)
    {
        $Http = new \Whsuite\Http\Http;
        $Response = $Http->newResponse('json');

        $Response->setContent(array());

        $search = str_replace('/', '', trim($search));

        // check we have a search term
        if (isset($type) && ! empty($type)) {

            if (isset($type) && $type == 'id') {

                $data = Staff::where('id', '=', $search)->get();

            } elseif (isset($type) && $type == 'name-email') {

                $data = Staff::where('first_name', 'LIKE', "%" . $search . "%")
                    ->Orwhere('last_name', 'LIKE', "%" . $search . "%")
                    ->Orwhere('email', 'LIKE', "%" . $search . "%")
                    ->get();
            }

            if (isset($data)) {

                $list = array();

                foreach ($data as $row) {

                    $list[$row->id] =
                        '(#' . $row->id .') ' .
                        $row->first_name . ' ' .
                        $row->last_name .
                        ' (e: ' . $row->email . ')';
                }

                // we have a list, json_encode and return
                if (! empty($list)) {

                    $Response->setContent($list);
                }
            }
        }

        // send the response
        $Http->send($Response);
    }



    /**
     *  function called by the ajax to get list of clients
     *
     * @param   array         Array of params from the URL
     *                        0 - type ('id' or 'name-email')
     *                        1 - what we're searching for
     * @return  json|null    Json return to jQuery
     */
    public function ajaxFindClients($type, $search)
    {
        $Http = new \Whsuite\Http\Http;
        $Response = $Http->newResponse('json');

        $Response->setContent(array());

        $search = str_replace('/', '', trim($search));

        // check we have a search term
        if (isset($type) && ! empty($type)) {

            if (isset($type) && $type == 'id') {

                $data = Client::where('id', '=', $search)->get();

            } elseif (isset($type) && $type == 'name-email') {

                $data = Client::where('first_name', 'LIKE', "%" . $search . "%")
                    ->Orwhere('last_name', 'LIKE', "%" . $search . "%")
                    ->Orwhere('email', 'LIKE', "%" . $search . "%")
                    ->get();
            }

            if (isset($data)) {

                $list = array();

                foreach ($data as $row) {

                    if ($row->guest_account == 1) {

                        $list[$row->id] = '(' . $this->lang->get('guest') . ') ';
                    } else {

                        $list[$row->id] = '(#' . $row->id .') ';
                    }

                    $list[$row->id] .= $row->first_name . ' ' . $row->last_name . ' (e: ' . $row->email . ')';
                }

                // we have a list, json_encode and return
                if (! empty($list)) {

                    $Response->setContent($list);
                }
            }
        }

        // send the response
        $Http->send($Response);
    }


    /**
     *  function called by the ajax to get list of products by client
     *
     * @param   int          Client ID whose products we are getting
     * @return  json|null    Json return to jQuery
     */
    public function ajaxFindProducts($client_id)
    {
        $Http = new \Whsuite\Http\Http;
        $Response = $Http->newResponse('json');

        $Response->setContent(array());

        $purchases = ProductPurchase::where('client_id', '=', $client_id)
            ->where('status', '=', '1')
            ->get();

        if (isset($purchases)) {

            $list = array();

            foreach ($purchases as $purchase) {

                $list[$purchase->id] = ProductPurchase::getProductName($purchase->id);
            }

            // we have a list, json_encode and return
            if (! empty($list)) {

                $Response->setContent($list);
            }
        }

        // send the response
        $Http->send($Response);
    }


    /**
     * update a ticket priority, department, service
     *
     */
    public function updateTicket()
    {
        $data = PostInput::get('data');

        // list of allow fields this function can edit
        $allowedFields = array(
            'support_ticket_priority_id',
            'support_department_id',
            'product_purchase_id',
            'staff_id',
            'status'
        );

        // pop off the field name that we have saved
        $field = array_keys($data['SupportTicket']);
        $field = array_shift($field);

        if (
            ! empty($data['ticket_id']) &&
            ! empty($data['SupportTicket']) &&
            in_array($field, $allowedFields)
        ) {
            $ticket_id = $data['ticket_id'];

            $Ticket = SupportTicket::find($ticket_id);
            $Ticket->{$field} = $data['SupportTicket'][$field];

            if ($Ticket->save()) {

                \App::get('session')->setFlash('success', $this->lang->get('scaffolding_save_success'));

                // if we've added a new staff member, email them
                if ($field == 'staff_id') {

                    \Addon\SupportDesk\Libraries\Notifications::assignedAdmin($ticket_id);
                }

            } else {

                \App::get('session')->setFlash('error', $this->lang->get('scaffolding_save_error'));
            }

            return header(
                "Location: " . App::get('router')->generate(
                    'admin-supportticket-view',
                    array(
                        'id' => $ticket_id
                    )
                )
            );
        } else {

            return header(
                "Location: " . App::get('router')->generate('admin-supportticket')
            );
        }
    }
}
