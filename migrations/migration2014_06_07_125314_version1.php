<?php

namespace Addon\SupportDesk\Migrations;

use \App\Libraries\BaseMigration;

class Migration2014_06_07_125314_version1 extends BaseMigration
{

    /**
     * migration 'up' function to install items
     *
     * @param   int     addon_id
     */
    public function up($addon_id)
    {
        // Create the departments Table
        $this->createTable('support_departments', function($table) {

            $table->increments('id');

            $table->string('name', 100);

            $table->string('description', 255);

            $table->tinyInteger('clients_only')
                ->default(0);

            $table->text('notification_email');

            $table->tinyInteger('piping')
                ->default(0);

            $table->text('piping_settings');

            $table->tinyInteger('auto_respond')
                ->default(0);

            $table->tinyInteger('is_active')
                ->default(0);

            $table->timestamps();
        });

        // Create the posts Table
       $this->createTable('support_posts', function($table) {

            $table->increments('id');

            $table->integer('support_ticket_id');

            $table->integer('staff_id');

            $table->text('body');

            $table->string('piping_email', 255);

            $table->timestamps();
        });

        // Create the priorities Table
        $this->createTable('support_ticket_priorities', function($table) {

            $table->increments('id');

            $table->string('name', 100);

            $table->string('text_hex', 6);

            $table->integer('sort');

            $table->tinyInteger('is_active')
                ->default(0);

            $table->timestamps();
        });

        // Create the tickets Table
        $this->createTable('support_tickets', function($table) {

            $table->increments('id');

            $table->string('subject', 255);

            $table->integer('client_id');

            $table->integer('support_ticket_priority_id');

            $table->integer('support_department_id');

            $table->integer('staff_id');

            $table->integer('product_purchase_id');

            $table->integer('status');

            $table->text('unique_hash');

            $table->tinyInteger('is_active')
                ->default(0);

            $table->timestamps();
        });

        // Create the staff group / support department linker Table
        $this->createTable('staff_group_support_department', function($table) {

            $table->increments('id');

            $table->integer('staff_group_id');

            $table->integer('support_department_id');
        });

        // Create the settings category
        $category = new \SettingCategory();
        $category->slug = 'supportdesk';
        $category->title = 'supportdesk_settings';
        $category->is_visible = '1';
        $category->sort = '99';
        $category->addon_id = $addon_id;
        $category->save();

        $setting = new \Setting();
        $setting->slug = 'supportdesk_auto_close';
        $setting->title = 'supportdesk_auto_close';
        $setting->field_type = 'checkbox';
        $setting->setting_category_id = $category->id;
        $setting->editable = '1';
        $setting->required = '1';
        $setting->addon_id = $addon_id;
        $setting->sort = '10';
        $setting->value = '1';
        $setting->default_value = '0';
        $setting->save();

        $setting = new \Setting();
        $setting->slug = 'supportdesk_auto_close_seconds';
        $setting->title = 'supportdesk_auto_close';
        $setting->description = 'supportdesk_auto_close_desc';
        $setting->field_type = 'text';
        $setting->rules = 'integer';
        $setting->setting_category_id = $category->id;
        $setting->editable = '1';
        $setting->required = '1';
        $setting->addon_id = $addon_id;
        $setting->sort = '10';
        $setting->value = '86400';
        $setting->default_value = '86400';
        $setting->save();

        // create some default priorities
        \SupportTicketPriority::insert(
            array(
                array(
                    'name' => 'Low',
                    'text_hex' => '',
                    'sort' => '10',
                    'is_active' => 1,
                    'created_at' => $this->date,
                    'updated_at' => $this->date
                ),
                array(
                    'name' => 'Medium',
                    'text_hex' => 'c09853',
                    'sort' => '20',
                    'is_active' => 1,
                    'created_at' => $this->date,
                    'updated_at' => $this->date
                ),
                array(
                    'name' => 'High',
                    'text_hex' => 'FF0000',
                    'sort' => '30',
                    'is_active' => 1,
                    'created_at' => $this->date,
                    'updated_at' => $this->date
                ),
                array(
                    'name' => 'Urgent',
                    'text_hex' => 'FF33CC',
                    'sort' => '40',
                    'is_active' => 1,
                    'created_at' => $this->date,
                    'updated_at' => $this->date
                )
            )
        );

        // insert default support department
        $SupportDepartment = new \SupportDepartment;
        $SupportDepartment->name = 'General';
        $SupportDepartment->description = 'General support enquiries';
        $SupportDepartment->clients_only = 0;
        $SupportDepartment->is_active = 1;
        $SupportDepartment->save();

        // get the user groups so we can add them to the department
        $staffGroupList = \StaffGroup::formattedList();
        $SupportDepartment->StaffGroup()->sync(array_keys($staffGroupList));

        // insert the widget and the shortcut
        $Widget = new \Widget();
        $Widget->unique_name = 'support_desk-active-post';
        $Widget->addon_id = $addon_id;
        $Widget->name = 'widget_support_desk_active_tickets';
        $Widget->description = 'Show a list of the current active tickets in your support desk';
        $Widget->route = 'admin-widget-support_desk-active-tickets';
        $Widget->is_active = 1;
        $Widget->save();

        $Shortcut = new \Shortcut();
        $Shortcut->unique_name = 'support_desk_tickets';
        $Shortcut->addon_id = $addon_id;
        $Shortcut->name = 'supportticket_management';
        $Shortcut->icon_class = 'fa fa-comments';
        $Shortcut->description = 'Provides shortcut straight to your ticket list, number shows the amount of tickets with new replies.';
        $Shortcut->route = 'admin-supportticket';
        $Shortcut->label_route = 'admin-shortcut-support_desk-label';
        $Shortcut->is_active = 1;
        $Shortcut->save();

        // setup parent link in menu
        $parent = new \MenuLink();
        $parent->menu_group_id = 1;
        $parent->title = 'supportdesk';
        $parent->parent_id = 0;
        $parent->is_link = 1;
        $parent->url = '#';
        $parent->sort = 2;
        $parent->clients_only = 0;
        $parent->class = '';
        $parent->addon_id = $addon_id;
        $parent->save();

        // add menu links for support desk
        \MenuLink::insert(
            array(
                array(
                    'menu_group_id' => 1,
                    'title' => 'supportdesk-tickets',
                    'parent_id' => $parent->id,
                    'is_link' => 0,
                    'url' => 'admin-supportticket',
                    'sort' => 1,
                    'clients_only' => 0,
                    'class' => '',
                    'addon_id' => $addon_id,
                    'created_at' => $this->date,
                    'updated_at' => $this->date
                ),
                array(
                    'menu_group_id' => 1,
                    'title' => 'supportdepartment_management',
                    'parent_id' => $parent->id,
                    'is_link' => 0,
                    'url' => 'admin-supportdepartment',
                    'sort' => 1,
                    'clients_only' => 0,
                    'class' => '',
                    'addon_id' => $addon_id,
                    'created_at' => $this->date,
                    'updated_at' => $this->date
                ),
                array(
                    'menu_group_id' => 1,
                    'title' => 'supportticketpriority_management',
                    'parent_id' => $parent->id,
                    'is_link' => 0,
                    'url' => 'admin-supportticketpriority',
                    'sort' => 1,
                    'clients_only' => 0,
                    'class' => '',
                    'addon_id' => $addon_id,
                    'created_at' => $this->date,
                    'updated_at' => $this->date
                ),
                // client side link
                array(
                    'menu_group_id' => 2,
                    'title' => 'supportdesk',
                    'parent_id' => 0,
                    'is_link' => 0,
                    'url' => 'client-supportticket',
                    'sort' => 1,
                    'clients_only' => 0,
                    'class' => '',
                    'addon_id' => $addon_id,
                    'created_at' => $this->date,
                    'updated_at' => $this->date
                )
            )
        );
    }

    /**
     * migration 'down' function to delete items
     *
     * @param   int     addon_id
     */
    public function down($addon_id)
    {
        $this->dropTable('support_departments');
        $this->dropTable('support_posts');
        $this->dropTable('support_ticket_priorities');
        $this->dropTable('support_tickets');
        $this->dropTable('staff_group_support_department');

        // remove the widget / shortcut
        // Remove shortcut staff links
        $shortcuts = \Shortcut::where('addon_id', '=', $addon_id)
            ->get();

        if (! empty($shortcuts)) {

            foreach ($shortcuts as $shortcut) {

                $shortcut->Staff()->sync(array());
                $shortcut->delete();
            }
        }

        // Remove widget staff links
        $widgets = \Widget::where('addon_id', '=', $addon_id)
            ->get();

        if (! empty($widgets)) {

            foreach ($widgets as $widget) {

                $widget->Staff()->sync(array());
                $widget->delete();
            }
        }

        // remove all the menu links
        \MenuLink::where('addon_id', '=', $addon_id)->delete();

        // Remove all settings
        \Setting::where('addon_id', '=', $addon_id)->delete();

        // Remove all settings groups
        \SettingCategory::where('addon_id', '=', $addon_id)->delete();
    }
}
