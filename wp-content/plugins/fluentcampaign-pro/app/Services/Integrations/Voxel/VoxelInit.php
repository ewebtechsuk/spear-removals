<?php

namespace FluentCampaign\App\Services\Integrations\Voxel;

class VoxelInit
{
    public function init()
    {
        // custom handler for event "New order placed by customer"
        add_action('voxel/app-events/products/orders/customer:order_placed', [$this, 'handlePurchaseComplete']);

        add_filter('fluent_crm/purchase_history_providers', [$this, 'registerPurchaseHistoryProvider']);
        add_filter('fluent_crm/purchase_history_voxel', [$this, 'voxelOrders'], 10, 2);

    }

    public function handlePurchaseComplete($event)
    {
        error_log(print_r([$event], 1));
    }

    public function registerPurchaseHistoryProvider($providers)
    {
        $providers['voxel'] = [
            'title' => __('Voxel Purchase History', 'fluentcampaign-pro'),
            'name' => __('Voxel', 'fluentcampaign-pro'),
        ];

        return $providers;
    }

    public function voxelOrders($data, $subscriber)
    {
        if (!$subscriber->user_id) {
            return $data;
        }

        $page    = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $perPage = isset($_REQUEST['per_page']) ? $_REQUEST['per_page'] : 10;

        $sort_by   = sanitize_sql_orderby($_REQUEST['sort_by']);
        $sort_type = sanitize_sql_orderby($_REQUEST['sort_type']);

        $valid_columns = ['id', 'date_created', 'total'];
        $valid_directions = ['ASC', 'DESC'];

        if (!in_array($sort_by, $valid_columns)) {
            $sort_by = 'id';
        }
        if (!in_array(strtoupper($sort_type), $valid_directions)) {
            $sort_type = 'DESC';
        }

        $vxOrders = fluentCrmDb()->table('vx_orders')
            ->where('customer_id', $subscriber->user_id)
            ->orderBy($sort_by, $sort_type)
            ->get();

        $totalCount = fluentCrmDb()->table('vx_orders')
            ->where('customer_id', $subscriber->user_id)
            ->orderBy($sort_by, $sort_type)
            ->count();

//
        $orders = [];
        foreach ($vxOrders as $vxOrder) {
            $orderActionHtml = '<a target="_blank" href="' . add_query_arg('id', $vxOrder->id, admin_url('admin.php?page=voxel-orders&order_id='.$vxOrder->id)) . '">' . __('View Order', 'fluent-crm') . '</a>';

            $details = json_decode($vxOrder->details, true);
            $orders[] = [
                'order'  => '#' . $vxOrder->id,
                'date'   => date_i18n(get_option('date_format'), strtotime($vxOrder->created_at)),
                'total'  => \Voxel\currency_format( intval($details['pricing']['total'] * 100), $details['pricing']['currency'] ),
                'status' => $vxOrder->status,
                'action' => $orderActionHtml
            ];
        }

        $posts = get_posts([
            'author'        => $subscriber->user_id,
            'post_type'     => 'profile',
            'numberposts'   => 1, // Get only one post
        ]);
        $profileUrl = admin_url('post.php?post='.$posts[0]->ID.'&action=edit');

        $returnData = [
            'data'           => $orders,
            'total'          => $totalCount,
            'per_page'       => $perPage,
            'columns_config' => [
                'order'  => [
                    'label'    => __('Order', 'fluent-crm'),
                    'width'    => '100px',
                    'sortable' => true,
                    'key'      => 'id'
                ],
                'date'   => [
                    'label'    => __('Date', 'fluent-crm'),
                    'sortable' => true,
                    'key'      => 'edd_orders'
                ],
                'status' => [
                    'label'    => __('Status', 'fluent-crm'),
                    'width'    => '140px',
                    'sortable' => false
                ],
                'total'  => [
                    'label'    => __('Total', 'fluent-crm'),
                    'width'    => '120px',
                    'sortable' => true,
                    'key'      => 'total'
                ],
                'action' => [
                    'label'    => __('Actions', 'fluent-crm'),
                    'width'    => '100px',
                    'sortable' => false
                ]
            ]
        ];

        if (!empty($posts) && $orders) {
            $returnData['after_html'] = '<p><a target="_blank" rel="noopener" href="'.$profileUrl.'">' . esc_html__('View Customer Profile', 'fluent-crm') . '</a></p>';
        }

        return $returnData;

    }

}