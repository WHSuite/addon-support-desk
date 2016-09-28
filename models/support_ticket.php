<?php

class SupportTicket extends AppModel
{
    public static $rules = array(
        'subject' => 'required|max:255',
        'client_id' => 'integer|required',
        'support_department_id' => 'integer|required'
    );

    public static $status = array(
        '0' => array(
            'admin' => array(
                'label' => 'new_message',
                'class' => 'success'
            ),
            'client' => array(
                'label' => 'awaiting_reply',
                'class' => 'info'
            )
        ),
        '1' => array(
            'admin' => array(
                'label' => 'awaiting_reply',
                'class' => 'info'
            ),
            'client' => array(
                'label' => 'new_message',
                'class' => 'success'
            )
        ),
        '2' => array(
            'label' => 'on_hold',
            'class' => 'warning'
        ),
        '3' => array(
            'label' => 'closed',
            'class' => 'danger'
        )
    );

    public function SupportDepartment()
    {
        return $this->belongsTo('SupportDepartment');
    }

    public function SupportTicketPriority()
    {
        return $this->belongsTo('SupportTicketPriority');
    }

    public function SupportPost()
    {
        return $this->hasMany('SupportPost');
    }

    public function ProductPurchase()
    {
        return $this->belongsTo('ProductPurchase');
    }

    public function Client()
    {
        return $this->belongsTo('Client');
    }

    public function Staff()
    {
        return $this->belongsTo('Staff');
    }

    /**
     * redefine the ticket save to create a unique hash if none exists
     */
    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = array())
    {
        if (empty($this->id) && empty($this->unique_hash)) {

            if (! \App::check('security')) {

                \App::factory('\App\Libraries\Security');
            }

            $security = \App::get('security');
            $this->unique_hash = \App::get('security')->hash($this);
        }

        return parent::save($options);
    }

    /**
     * ticket count
     * count of tickets for given user and given status
     *
     * @param  object user object from controller->admin_user
     * @param  int    status integer to search on
     * @return int    number of matching tickets
     */
    public static function countTickets($admin_user, $status)
    {
        $instance = new static;
        $query = $instance->newQuery();

        $query->where(
            'status',
            '=',
            $status
        );

        $departments = SupportDepartment::getUsersDepartments($admin_user);
        $conditions = array();

        $query->where(function($bracket_query) use ($departments, $admin_user) {

            if (count($departments) > 0) {

                $bracket_query->whereIn(
                    'support_department_id',
                    $departments
                );
            }
            $bracket_query->orWhere(
                'staff_id',
                '=',
                $admin_user->id
            );
        });

        return $query->count();
    }


    /**
     * wrapper for the paginate function, allows us to pass in the admin user
     * in order to filter on the correct departments
     *
     * @param object user object from controller->admin_user
     * @return array
     */
    public static function adminTicketList($admin_user, $per_page, $page, $conditions = array(), $sort_by = false, $sort_order = 'desc', $route = null, $params = array())
    {
        $departments = SupportDepartment::getUsersDepartments($admin_user);
        $conditions = array();

        if (count($departments) > 0) {

            $conditions[] = array(
                'type' => 'in',
                'column' => 'support_department_id',
                'value' => $departments
            );
        }

        $conditions[] = array(
            'type' => 'or',
            'column' => 'staff_id',
            'operator' => '=',
            'value' => $admin_user->id
        );

        $sort_by = array(
            'status' => 'asc',
            'created_at' => 'asc'
        );

        return parent::paginate(
            $per_page,
            $page,
            $conditions,
            $sort_by
        );
    }

    /**
     * wrapper for the paginate function, allows us to pass in the admin user
     * in order to filter on the correct departments
     *
     * @param object user object from controller->client_user
     * @return array
     */
    public static function clientTicketList($client_user, $per_page, $page, $conditions = array(), $sort_by = false, $sort_order = 'desc', $route = null, $params = array())
    {
        if (empty($client_user->id)) {

            App::get('view')->set('pagination', '');
            return array();
        }

        $conditions[] = array(
            'column' => 'client_id',
            'operator' => '=',
            'value' => $client_user->id
        );

        $sort_by = array(
            'status' => 'asc',
            'created_at' => 'asc'
        );

        return parent::paginate(
            $per_page,
            $page,
            $conditions,
            $sort_by,
            $sort_order,
            'client-supportticket-paging'
        );
    }

    /**
     * given the status id and the section, return the status label / class
     *
     * @param   int         The status int
     * @param   string      The section we are in (admin / client)
     * @return  array|null       Array containing label / class
     */
    public static function getStatus($status_id, $section)
    {
        // check for sub section, new responses for admin / clients are different
        // 0  new response for admins but awaiting reply for clients
        // 1  new response for clients but awaiting reply for admins
        if (isset(self::$status[$status_id][$section]['label'])) {

            return self::$status[$status_id][$section];

        } elseif (isset(self::$status[$status_id]['label'])) {
            // standard, on hold / closed

            return self::$status[$status_id];
        }

        // if we haven't returned anything here, something's wrong
        return null;
    }

    /**
     * get status for drop downs
     *
     * @param   string      section
     * @return  array       array of status for populating a drop down
     */
    public static function statusDropDown($section)
    {
        $status = array();
        $lang = \App::get('translation');
        foreach (self::$status as $id => $info) {

            // check for label
            if (isset($info[$section]['label'])) {

                $label = $info[$section]['label'];
            } else {

                $label = $info['label'];
            }

            // we don't want to show the 'new message' status option for them.
            // only to set it to awaiting a reply, put it on hold or close it
            if ($label == 'new_message') {

                continue;
            }

            $status[$id] = $lang->get($label);
        }

        return $status;
    }

    /**
     * given an object of the support ticket
     * wrap a span around the status text with the correct colour
     *
     * @param   object      support ticket model object
     * @param   string      Which section we are in, so we can return the correct label (if different).
     * @param   string      class name, for whether to return as text or label (optional)
     * @return  string      html string with the ticket status name wrapped in span
     */
    public static function wrapStatusColor($ticket, $section, $type = 'text')
    {
        $status = self::getStatus($ticket->status, $section);

        $return = '<span';

        if (! empty($type)) {

            $return .= ' class="' . $type . '-' . $status['class'] . '"';
        }

        $return .= '>' . \App::get('translation')->get($status['label']) . '</span>';

        return $return;
    }

}
