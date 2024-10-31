<?php

namespace PuddinqOrderList;

class Filter
{
    public function __construct()
    {
        add_action('admin_head', array($this, 'puddinqOrderListColumns'));
    }

    public function puddinqOrderListColumns()
    {

        $screen = get_current_screen();

        if ($screen->id == 'edit-shop_order') {
            remove_all_actions('manage_shop_order_posts_custom_column');
            add_filter('manage_shop_order_posts_columns', array($this, 'reorderOrderColumns'), 20);
            add_action('manage_shop_order_posts_custom_column', array($this, 'renderOrderColumns'), 4);
        }
    }

    public function reorderOrderColumns($existing_columns)
    {

        $columns = array();
        $columns['cb'] = $existing_columns['cb'];
        $columns['order_number'] = __('Number', 'puddinq-order-list');
        $columns['order_date'] = __('Date', 'puddinq-order-list');
        $columns['order_products'] = __('Products', 'puddinq-order-list');
        $columns['billing_address'] = __('Billing', 'puddinq-order-list');
        $columns['shipping_address'] = __('Ship to', 'puddinq-order-list');
        $columns['customer_message'] = '<span class="notes_head tips" data-tip="' . esc_attr__(
            'Customer message',
            'puddinq-order-list'
        )
            . '">'
            . esc_attr__(
                'Customer message',
                'puddinq-order-list'
            ) . '</span>';
        $columns['order_status'] = '<span class="status_head tips" data-tip="'
            . esc_attr__(
                'Status',
                'puddinq-order-list'
            ) . '">' . esc_attr__('Status', 'puddinq-order-list') . '</span>';
        $columns['order_total'] = __('Total', 'puddinq-order-list');
        $columns['wc_actions'] = __('Actions', 'puddinq-order-list');

        return $columns;
    }

    public function renderOrderColumns($column)
    {
        /** @var \WC_Order $the_order */
        /** @var \WP_Post $post */
        global $post, $the_order;

        if (empty($the_order) || $the_order->get_id() !== $post->ID) {
            $the_order = wc_get_order($post->ID);
        }

        if ($the_order instanceof \WC_Order) {
            switch ($column) {
                case 'order_status':
                    $this->renderOrderStatus();
                    break;
                case 'order_date':
                    $this->render_order_date();
                    break;
                case 'customer_message':
                    $this->renderCustomerMessage();
                    break;
                case 'billing_address':
                    $this->render_billing_address();
                    break;
                case 'shipping_address':
                    $this->render_shipping_address();
                    break;
                case 'order_total':
                    $this->render_order_total();
                    break;
                case 'order_number':
                    $this->render_order_number();
                    break;
                case 'order_products':
                    $this->render_order_products();
                    break;
                case 'wc_actions':
                    $this->renderOrderActions();
                    break;
            }
        }
    }


    public function renderOrderActions()
    {
        global $the_order;
        $actions = array();

        $actions = apply_filters('woocommerce_admin_order_actions', $actions, $the_order);

        echo wc_render_action_buttons($actions); // WPCS: XSS ok.
        do_action('woocommerce_admin_order_actions_end', $the_order);
    }

    public function renderOrderStatus()
    {
        /** @var \WC_Order $the_order */
        global $the_order;

        $tooltip = '';
        $comment_count = get_comment_count($the_order->get_id());
        $approved_comments_count = absint($comment_count['approved']);

        if ($approved_comments_count) {
            $latest_notes = wc_get_order_notes(
                array(
                    'order_id' => $the_order->get_id(),
                    'limit' => 1,
                    'orderby' => 'date_created_gmt',
                )
            );

            $latest_note = current($latest_notes);

            if (isset($latest_note->content) && 1 === $approved_comments_count) {
                $tooltip = wc_sanitize_tooltip($latest_note->content);
            } elseif (isset($latest_note->content)) {
                /* translators: %d: notes count */
                $tooltip = wc_sanitize_tooltip($latest_note->content
                    . '<br/><small style="display:block">'
                    . sprintf(
                        _n(
                            'Plus %d other note',
                            'Plus %d other notes',
                            ($approved_comments_count - 1),
                            'puddinq-order-list'
                        ),
                        $approved_comments_count - 1
                    ) . '</small>');
            } else {
                /* translators: %d: notes count */
                $tooltip = wc_sanitize_tooltip(sprintf(
                    _n(
                        '%d note',
                        '%d notes',
                        $approved_comments_count,
                        'puddinq-order-list'
                    ),
                    $approved_comments_count
                ));
            }
        }

        if ($tooltip) {
            printf('<mark class="order-status %s tips" data-tip="%s"><span>%s</span></mark>',
                esc_attr(sanitize_html_class('status-' . $the_order->get_status())), wp_kses_post($tooltip),
                esc_html(wc_get_order_status_name($the_order->get_status())));
        } else {
            printf('<mark class="order-status %s"><span>%s</span></mark>',
                esc_attr(sanitize_html_class('status-' . $the_order->get_status())),
                esc_html(wc_get_order_status_name($the_order->get_status())));
        }

        ?><br><br>
        <p>
        <?php // actions
        do_action('woocommerce_admin_order_actions_start', $the_order);

        $actions = array();
        $status_actions = array();

        if ($the_order->has_status(array('pending'))) {
            $status_actions['on-hold'] = array(
                'url' => wp_nonce_url(admin_url('admin-ajax.php?action=woocommerce_mark_order_status&status=on-hold&order_id=' . $the_order->get_id()),
                    'woocommerce-mark-order-status'),
                'name' => __('On-hold', 'puddinq-order-list'),
                'title' => __('Change order status to on-hold', 'puddinq-order-list'),
                'action' => 'on-hold',
            );
        }

        if ($the_order->has_status(array('pending', 'on-hold'))) {
            $status_actions['processing'] = array(
                'url' => wp_nonce_url(admin_url('admin-ajax.php?action=woocommerce_mark_order_status&status=processing&order_id=' . $the_order->get_id()),
                    'woocommerce-mark-order-status'),
                'name' => __('Processing', 'puddinq-order-list'),
                'title' => __('Change order status to processing', 'puddinq-order-list'),
                'action' => 'processing',
            );
        }

        if ($the_order->has_status(array('pending', 'on-hold', 'processing'))) {
            $status_actions['complete'] = array(
                'url' =>
                    wp_nonce_url(
                        admin_url(
                            'admin-ajax.php?action=woocommerce_mark_order_status&status=completed&order_id='
                            . $the_order->get_id()
                        ),
                    'woocommerce-mark-order-status'),
                'name' => __('Completed', 'puddinq-order-list'),
                'title' => __('Change order status to completed', 'puddinq-order-list'),
                'action' => 'complete',
            );
        }

        if ($status_actions) {
            $actions['status'] = array(
                'group' => __('Change status: ', 'puddinq-order-list'),
                'actions' => $status_actions,
            );
        }

        echo wc_render_action_buttons(apply_filters('woocommerce_admin_order_preview_actions', $actions, $the_order));


        ?>
        </p><?php

    }

    public function render_order_date()
    {
        /** @var \WC_Order $the_order */
        global $the_order;
        printf('<time datetime="%s">%s</time>', esc_attr($the_order->get_date_created()->date('d m y hh:mm')),
            esc_html($the_order->get_date_created()->date_i18n(apply_filters('woocommerce_admin_order_date_format',
                get_option('date_format')))));
        echo '<br>' . get_post_time('H:i', false, $the_order->get_id(), true);
    }

    public function render_billing_address()
    {
        /** @var \WC_Order $the_order */
        global $the_order;
        if ($address = $the_order->get_formatted_billing_address()) {
            echo $address;
        } else {
            echo '&ndash;';
        }

        if ( $the_order->get_payment_method() ) {
            /* translators: %s: payment method */
            echo '<span class="description">' . sprintf( __( 'via %s', 'woocommerce' ), esc_html($the_order->get_payment_method_title() ) ) . '</span>'; // WPCS: XSS ok.
        }

        if ($the_order->get_billing_phone()) {
            echo '<small class="meta">' . __('Phone:',
                    'puddinq-order-list') . ' ' . esc_html($the_order->get_billing_phone()) . '</small>';
        }
        if ($the_order->get_billing_email()) {
            echo '<small class="meta">' . __('Email:',
                    'puddinq-order-list') . ' ' . esc_html($the_order->get_billing_email()) . '</small>';
        }
    }

    public function renderCustomerMessage()
    {
        /** @var \WC_Order $the_order */
        global $the_order;

        if ($note = $the_order->get_customer_note()) {
            echo $note;
        }
    }

    public function render_shipping_address()
    {
        /** @var \WC_Order $the_order */
        global $the_order;

        $shippingMethods = $the_order->get_shipping_methods();
        $firstShippingMethod = reset($shippingMethods);

        if ($firstShippingMethod && $firstShippingMethod->get_method_id() != 'local_pickup') {
            echo '<div style="text-align:left">';
            if ($the_order->has_shipping_address()) {
                echo '<a target="_blank" 
                href="' . esc_url($the_order->get_shipping_address_map_url()) . '"
                title="' . __('See location on google maps', 'puddinq-order-list') . '">' . $the_order->get_formatted_shipping_address() . '</a>';
            } else {
                echo '<b>Ship to billing address</b><br>';
                echo $the_order->get_formatted_billing_address();
            }
            echo '</div>';
        }
        if ($the_order->get_shipping_method()) {
            echo '<small class="meta">' . __('Via',
                    'puddinq-order-list') . ' ' . esc_html($the_order->get_shipping_method()) . '</small>';
        }

    }

    public function render_order_total()
    {
        /** @var \WC_Order $the_order */
        global $the_order;
        $costs = $the_order->get_order_item_totals();
        echo $costs['cart_subtotal']['label'] . ' ' . $costs['cart_subtotal']['value'];
        if (isset($costs['shipping'])) :
            echo '<br>' . $costs['shipping']['label'] . ' ' . $costs['shipping']['value'];
        endif;
        echo '<br>' . $costs['order_total']['label'] . ' ' . $costs['order_total']['value'];
    }

    public function render_order_number()
    {
        /** @var \WC_Order $the_order */
        global $the_order;
        $buyer = '';

        if ($the_order->get_status() === 'trash') {
            echo '<strong>#' . esc_attr($the_order->get_order_number()) . '</strong><br>';
        } else {
            echo '<a href="' . esc_url(admin_url('post.php?post=' . absint($the_order->get_id())) . '&action=edit') . '" class="order-view"><strong>#' . esc_attr($the_order->get_order_number()) . '</strong></a><br><br>';
            echo '<a href="#" class="order-preview" data-order-id="' . absint($the_order->get_id()) . '" title="' . esc_attr(__('Preview',
                    'puddinq-order-list')) . '">' . esc_html(__('Preview', 'puddinq-order-list')) . '</a>';

        }
    }

    public function render_order_products()
    {
        /** @var \WC_Order $the_order */
        global $the_order;
        $number_of_items = 0;
        $order = wc_get_order($the_order->get_id());
        foreach ($the_order->get_items() as $ikey => $item) {

            $product = apply_filters('woocommerce_order_item_product', $item->get_product(), $item);
            if ($product) :
                ?>
                <table style="border-bottom: 1px solid lightgray;width:100%">
                    <tr class="<?php echo apply_filters('woocommerce_admin_order_item_class', '', $item,
                        $the_order); ?>">
                        <?php $number_of_items += absint($item['qty']); ?>
                        <td class="qty" style="padding:0;width: 0.4em;"><?php echo absint($item['qty']); ?> x</td>
                        <td class="name" style="padding:0;text-align:left">
                            <?php
                            //echo ( wc_product_sku_enabled() && $product->get_sku() ) ? $product->get_sku() . ' - ' : '';

                            $sku = $product->get_sku();

                            switch ($product->get_type()) {
                                case 'variation':

                                    echo '<a href="' . $product->get_permalink() . '" title="SKU: ' . $sku . '">' . $product->get_title() . '</a><br>';

                                    echo str_replace(', ', '<br>', wc_get_formatted_variation($product, true));
                                    
                                    break;
                                case 'simple':
                                    echo '<a href="' . get_edit_post_link($product->get_id()) . '" title="SKU: ' . $sku . '">';
                                    echo apply_filters('woocommerce_order_item_name', $item['name'], $item, false) . '</a>';
                                    break;
                                default:
                                    echo '';
                            }

                            ?>
                        </td>
                    </tr>
                </table>
            <?php
            else:
                echo "the product has been deleted from your shop, open the order to see information about items <br>";
            endif;
        }
        ?>
        <table>
            <tr class="<?php echo apply_filters('woocommerce_admin_order_item_class', '', $item, $the_order); ?>">
                <td class="name" style="padding:0;width:100%;" colspan="2"><?= sprintf(__('%s items purchased',
                        'puddinq-order-list'), $number_of_items) ?></td>
            </tr>
        </table>
        <?php

    }
}