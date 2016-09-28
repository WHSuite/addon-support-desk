<?php

use \Illuminate\Support\Str;
use \Whsuite\Inputs\Post as PostInput;

class SupportPostsController extends \AdminController
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
     * handle replies to a support ticket
     * TODO: possibly refactor some of this function when looking into ticket add
     *       especially when it comes file processing
     *
     */
    public function reply()
    {
        // post?
        $data = PostInput::get('data');

        if (! empty($data) && isset($_POST['data']) && ! empty($_POST['data'])) {

            // set the logged in users id to the array
            PostInput::set('data.SupportPost.staff_id', $this->admin_user->id);

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

                $redirect = "Location: ".App::get('router')->generate(
                    'admin-supportticket-view',
                    array(
                        'id' => $data['SupportPost']['support_ticket_id']
                    )
                );
            } else {

                // we can't, redirect back to ticket listing
                $redirect = "Location: ".App::get('router')->generate(
                    'admin-supportticket'
                );
            }

            // perform the redirect
            return header($redirect);

        } else {

            App::get('session')->setFlash('error', $this->lang->get('item_not_found'));
            return header("Location: ".App::get('router')->generate('admin-supportticket'));
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

        // notify the client there has been a reply.
        \Addon\SupportDesk\Libraries\Notifications::clientNewReply($main_model->support_ticket_id);
    }

}
