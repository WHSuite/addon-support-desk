<?php

class SupportTicketPriority extends AppModel
{
    public static $rules = array(
        'name' => 'required',
        'text_hex' => 'required'
    );

    public function SupportTicket()
    {
        return $this->hasMany('SupportTicket');
    }

    public static function paginate($per_page, $page, $conditions = array(), $sort_by = 'sort', $sort_order = 'asc', $route = null, $params = array())
    {
        return parent::paginate($per_page, $page, $conditions, $sort_by, $sort_order, $route, $params);
    }

    /**
     * given an object of the support ticket priority
     * wrap a span around the text with the specified text colour
     *
     * @param object support ticket priority model object
     * @return string html string with the priority name wrapped in span
     */
    public static function wrapColor($priority)
    {
        $return = '<span';

        if (! empty($priority->text_hex)) {

            $return .= ' style="color: #' . $priority->text_hex . '"';
        }

        $return .= '>' . $priority->name . '</span>';

        return $return;
    }
}
