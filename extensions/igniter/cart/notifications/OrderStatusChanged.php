<?php

namespace Igniter\Cart\Notifications;

use Igniter\Notify\Classes\BaseNotification;

class OrderStatusChanged extends BaseNotification
{
    public function templateDetails()
    {
        return [
            'name' => 'Order status update notification',
            'description' => '',
        ];
    }

    public function defineFormFields()
    {
        return [
            'data[sms][content]' => [
                'tab' => 'SMS',
                'label' => 'Content',
                'type' => 'textarea',
                'default' => 'Order {order_id} status has been updated to: {status_name} ({status_comment})',
            ],
            'data[alert][subject]' => [
                'tab' => 'Alert (eg. slack)',
                'label' => 'Subject',
                'type' => 'text',
                'default' => 'Order status update!',
            ],
            'data[alert][title]' => [
                'tab' => 'Alert (eg. slack)',
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Order ID: {order_id}',
            ],
            'data[alert][content]' => [
                'tab' => 'Alert (eg. slack)',
                'label' => 'Content',
                'type' => 'textarea',
                'default' => 'Order {order_id} status has been updated to {status_name}.',
            ],
        ];
    }

    public function defineValidationRules()
    {
        return [
            ['data.sms.content', 'SMS Content', 'required|string|max:255'],
            ['data.alert.subject', 'Alert Subject', 'required|string|max:255'],
            ['data.alert.title', 'Alert Title', 'required|string|max:255'],
            ['data.alert.content', 'Alert Content', 'required|string|max:255'],
        ];
    }

    public function getActionUrl($notifiable)
    {
        return admin_url('order/edit/'.$this->parameters->get('order_id'));
    }
}