<?php

class SupportPost extends AppModel
{
    public static $rules = array(
        'body' => 'required'
    );

    public function SupportTicket()
    {
        return $this->belongsTo('SupportTicket');
    }

    public function Staff()
    {
        return $this->belongsTo('Staff');
    }


    /**
     * Delete
     *
     * Override the default delete method so that we can delete any uploaded files
     *
     * @return bool true if the delete was successful
     */
    public function delete()
    {
        $result = parent::delete();

        if (\App::checkInstalledAddon('uploader')) {

            $pk = $this->getKeyname();
            \Addon\Uploader\Libraries\Process::delete('SupportPost', $this->{$pk});
        }

        return $result;
    }

}
