<?php

use \Illuminate\Support\Str;
use \Whsuite\Inputs\Post as PostInput;

class SupportTicketsController extends \ClientController
{
    protected function indexActions()
    {
        return array(
            'view' => array(
                'url_route' => 'client-supportticket-view',
                'link_class' => 'btn btn-primary btn-small pull-right',
                'icon' => 'fa fa-chevron-right',
                'label' => 'view',
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
     * ticket listing
     * Will display different template if client is not logged in
     */
    public function index($page = 1, $per_page = null)
    {
        // get the sectin header language
        $page_title = $this->lang->get('supportticket_management');
        $this->view->set('title', $page_title);

        // build the breadcrumb
        $this->indexBreadcrumb('SupportTicket', $page_title);

        // check if we are dealing with a logged in user or not
        if (! is_object($this->client_user)) {

            return $this->view->display('support_desk::client/tickets/guestIndex.php');
        }

        // if no per pages are set, set default
        if (empty($per_page)) {

            $per_page = App::get('configs')->get('settings.general.results_per_page');
        }

        // get the actual data
        $data = SupportTicket::clientTicketList(
            $this->client_user,
            $per_page,
            $page
        );

        // set the variables
        $this->view->set(array(
            'data' => $data,
            'title' => $page_title,
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
        $this->view->display('support_desk::client/tickets/index.php');
    }


    /**
     * guest viewing a support ticket
     * validates the guest user hash and id before passing to the main view function
     */
    public function guestView($id, $hash)
    {
        $SupportTicket = SupportTicket::where('id', '=', $id)
            ->where('unique_hash', '=', $hash)
            ->with('Client')
            ->first();

        if (
            is_object($SupportTicket) &&
            $SupportTicket->id > 0 &&
            is_object($SupportTicket->Client) &&
            $SupportTicket->Client->id > 0 &&
            $SupportTicket->Client->guest_account == 1
        ) {

            $this->guest_account = $SupportTicket->Client;
            return $this->view($SupportTicket->id);
        } else {

            // something isn't valid
            App::get('session')->setFlash('error', $this->lang->get('item_not_found'));
            return header("Location: ".App::get('router')->generate('client-supportticket'));
        }
    }


    /**
     * view a support ticket
     * also handles the guest account ticket views
     * once the guestView has verified it's a valid request and setup the $this->guest_account var
     */
    public function view($id)
    {
        if (isset($this->client_user) && is_object($this->client_user)) {

            // mark the client as the logged in client
            $Client = $this->client_user;

        } elseif (
            (! isset($this->client_user) || ! is_object($this->client_user)) &&
            isset($this->guest_account) && is_object($this->guest_account)
        ) {

            // mark the client as the guest account
            $Client = $this->guest_account;

        } else {

            // nothing fits, redirect to error
            App::get('session')->setFlash('error', $this->lang->get('item_not_found'));
            return header("Location: ".App::get('router')->generate('client-supportticket'));
        }

        $data = SupportTicket::where('id', '=', $id)
            ->where('client_id', '=', $Client->id)
            ->with('Client', 'SupportTicketPriority', 'SupportDepartment', 'SupportPost.Staff')
            ->first();

        // check if it's a valid item
        if (! $data) {

            App::get('session')->setFlash('error', $this->lang->get('item_not_found'));
            return header("Location: ".App::get('router')->generate('client-supportticket'));
        }

        $title = $data->subject . ' (#' . $data->id . ')';
        $this->formBreadcrumb('SupportTicket', $title);

        // set the ticket statuses for the reply form
        $status = SupportTicket::statusDropDown('client');

        $this->view->set(array(
            'ticket' => $data,
            'title' => $title,
            'toolbar' => $this->formToolbar($data),
            'date_format' => \App::get('configs')->get('settings.localization.short_date_format'),
            'datetime_format' => \App::get('configs')->get('settings.localization.short_datetime_format'),
            'status' => $status,
            'Client' => $Client
        ));

        // add support desk css
        $this->assets->addStyle('support_desk::ticket-view.css');

        $this->view->display('support_desk::client/tickets/view.php');
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


    /**
     * create a new support ticket
     *
     * If not logged in, it will try and create a "guest account" for the guest.
     * If one already exists, will use that account, or will tell them to login if the account
     * has already been turned from guest account -> valid account
     */
    public function form($id = null)
    {
        $this->render_view = 'support_desk::client/tickets/form.php';

        $data = PostInput::get('data');

        if (! empty($data) && isset($_POST['data']) && ! empty($_POST['data'])) {

            // are we dealing with a registered client or not?
            if (isset($this->client_user) && $this->client_user->id > 0) {

                PostInput::set('data.SupportTicket.client_id', $this->client_user->id);
                $Client = Client::find($this->client_user->id);
            } else {

                $client_ticket = PostInput::get('data.SupportTicket.Client');
                if (! empty($client_ticket)) {

                    $Client = Client::where('email', '=', $client_ticket['email'])
                        ->first();

                    // no client at all, create guest account
                    if (empty($Client)) {

                        $Client = new Client;
                        $Client->first_name = $client_ticket['first_name'];
                        $Client->last_name = $client_ticket['last_name'];
                        $Client->email = $client_ticket['email'];
                        $Client->password = 'guest_password';
                        $Client->guest_account = 1;
                        $Client->html_emails = 1;
                        $Client->save();

                        PostInput::set('data.SupportTicket.client_id', $Client->id);

                    } elseif ($Client->guest_account == 1) {

                        // we've got a guest account, use it
                        PostInput::set('data.SupportTicket.client_id', $Client->id);
                    } else {

                        // it's actually a valid account but they are not logged in

                        unset($_POST);
                        \App\Libraries\Message::set(
                            $return_error = $this->lang->get('email_exists_login'), 'fail'
                        );
                    }
                }
            }

            PostInput::set('data.SupportTicket.status', '0');
        }

        self::$return_on_success = true;
        $return = parent::form($id);

        if ($return) {

            // check if we have just created a ticket with a guest account
            if ($Client->guest_account == 1) {

                $SupportTicket = SupportTicket::find($return);
                if (is_object($SupportTicket)) {

                    // guest, show them the guest link
                    return header("Location: " . App::get('router')->generate(
                        'client-supportticket-guest-view',
                        array(
                            'id' => $return,
                            'hash' => $SupportTicket->unique_hash
                        )
                    ));
                } else {

                    // fall back error, hopefully never get to this but just incase!
                    App::get('session')->setFlash('error', $this->lang->get('scaffolding_save_error'));
                    return header("Location: " . App::get('router')->generate(
                        'client-supportticket'
                    ));
                }

            } else {

                return header("Location: " . App::get('router')->generate(
                    'client-supportticket-view',
                    array(
                        'id' => $return
                    )
                ));
            }
        }
    }

    /**
     * redefne the afterSave to save the actual post body
     */
    protected function afterSave(&$main_model)
    {
        parent::afterSave($main_model);

        $SupportPost = new SupportPost;
        $SupportPost->body = PostInput::get('data.SupportTicket.SupportPost.body');

        $SupportPost = $main_model->SupportPost()->save($SupportPost);

        if (\App::checkInstalledAddon('uploader')) {

            // process any uploads
            \Addon\Uploader\Libraries\Process::uploads('SupportPost', $SupportPost);
        }

        // ticket notification
        \Addon\SupportDesk\Libraries\Notifications::adminNewReply($main_model->id);
    }


    /**
     * redefine the getExtraData function to get the priorities and departments
     *
     */
    protected function getExtraData($model)
    {
        parent::getExtraData($model);

        // check if we need to hide the client only departments
        $department_conditions = array(
            array(
                'column' => 'is_active',
                'operator' => '=',
                'value' => 1
            )
        );
        if (! isset($this->client_user) || ! is_object($this->client_user)) {

            $department_conditions[] = array(
                'column' => 'clients_only',
                'operator' => '=',
                'value' => 0
            );
        }
        $departments = SupportDepartment::formattedList(
            'id',
            'name',
            $department_conditions
        );

        $priorities = SupportTicketPriority::formattedList(
            'id',
            'name',
            array(
                array(
                    'column' => 'is_active',
                    'operator' => '=',
                    'value' => 1
                )
            ),
            'sort',
            'asc'
        );

        // get their products so we can assign a product to a ticket
        $products = array(
            '0' => $this->lang->get('not_available')
        );

        if (is_object($this->client_user) && $this->client_user->id > 0) {

            $purchases = ProductPurchase::where('client_id', '=', $this->client_user->id)
                ->where('status', '=', '1')
                ->get();

            foreach ($purchases as $purchase) {

                $products[$purchase->id] = ProductPurchase::getProductName($purchase->id);
            }
        }

        $this->view->set(array(
            'departments' => $departments,
            'priorities' => $priorities,
            'products' => $products
        ));
    }
}
