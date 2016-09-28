<?php

use \Illuminate\Support\Str;
use \Whsuite\Inputs\Post as PostInput;

class SupportPostsController extends \ClientController
{
    protected function formFields()
    {
        $fields = array(
            'SupportPost.id',
            'SupportPost.support_ticket_id',
            'SupportPost.staff_id',
            'SupportPost.body'
        );

        return $fields;
    }

    /**
     * validate guest account replies before handing off to the main reply function
     */
    public function guestReply($hash)
    {
        $SupportTicket = SupportTicket::where('id', '=', PostInput::get('data.SupportPost.support_ticket_id'))
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
            return $this->reply($SupportTicket);
        } else {

            // something isn't valid
            App::get('session')->setFlash('error', $this->lang->get('item_not_found'));
            return header("Location: ".App::get('router')->generate('client-supportticket'));
        }
    }

    /**
     * handle replies to a support ticket
     * TODO: possibly refactor some of this function when looking into ticket add
     *       especially when it comes file processing
     *
     * @param object A copy of the support ticket object from guestReply
     */
    public function reply($SupportTicket = null)
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


        // post?
        $data = PostInput::get('data');

        if (! empty($data) && isset($_POST['data']) && ! empty($_POST['data'])) {

            $this->return_after_process = true;
            $return = $this->form();

            // check if anything was returned (if not null, most likely an error set flash)
            if (! empty($return)) {

                App::get('session')->setFlash('error', $return);
            }

            // can we get the ticket id? if so redirect back to ticket view
            if (
                isset($data['SupportPost']['support_ticket_id']) &&
                ! empty($data['SupportPost']['support_ticket_id'])
            ) {

                // are we redirecting logged in user or guest account?
                if (isset($this->client_user) && is_object($this->client_user)) {

                    $redirect = "Location: ".App::get('router')->generate(
                        'client-supportticket-view',
                        array(
                            'id' => $data['SupportPost']['support_ticket_id']
                        )
                    );
                } else {

                    $redirect = "Location: ".App::get('router')->generate(
                        'client-supportticket-guest-view',
                        array(
                            'id' => $data['SupportPost']['support_ticket_id'],
                            'hash' => $SupportTicket->unique_hash
                        )
                    );
                }

            } else {

                // we can't, redirect back to ticket listing
                $redirect = "Location: ".App::get('router')->generate(
                    'client-supportticket'
                );
            }

            // perform the redirect
            return header($redirect);

        } else {

            App::get('session')->setFlash('error', $this->lang->get('item_not_found'));
            return header("Location: ".App::get('router')->generate('client-supportticket'));
        }
    }

    /**
     * use the afterSave callback to update the ticket status
     */
    protected function afterSave(&$main_model)
    {
        $data = PostInput::get('data');

        // update the ticket status
        $ticket = SupportTicket::find($data['SupportPost']['support_ticket_id']);
        $ticket->status = $data['SupportTicket']['status'];
        $ticket->save();

        if (\App::checkInstalledAddon('uploader')) {

            // process any uploads
            \Addon\Uploader\Libraries\Process::uploads('SupportPost', $main_model);
        }

        // notify the admin there has been a reply.
        \Addon\SupportDesk\Libraries\Notifications::adminNewReply($main_model->support_ticket_id);
    }

}
