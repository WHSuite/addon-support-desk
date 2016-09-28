<?php

class SupportDepartment extends AppModel
{
    public static $rules = array(
        'name' => 'required',
        'description' => 'required'
    );

    public function StaffGroup()
    {
        return $this->belongsToMany('StaffGroup');
    }

    public function SupportTicket()
    {
        return $this->hasMany('SupportTicket');
    }

    public function delete()
    {
        $this->StaffGroup()->sync(array());

        return parent::delete();
    }

    /**
     * get a users departments given their user object
     *
     * @param object user object from controller->admin_user
     * @return array
     */
    public static function getUsersDepartments($admin_user)
    {
        // TODO: Is there a better way of doing this without hacking the packages?
        // get the admin groups and the consequently the departments they have access to
        $groups = $admin_user->StaffGroup()->get();

        $departments = array();

        // get all the departments each group is tagged to
        foreach ($groups as $group) {

            $tagged_departments = $group->belongsToMany('SupportDepartment')->get();

            // loop the departments
            foreach ($tagged_departments as $department) {

                $departments[] = $department->id;
            }
        }

        return $departments;
    }
}
