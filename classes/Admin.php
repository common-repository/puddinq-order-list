<?php

namespace PuddinqOrderList;

use PuddinqOrderList\Helpers\Constants;

class Admin
{

    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueueAssets'));
    }

    public function enqueueAssets()
    {
        $screen = get_current_screen();

        if ($screen->id === 'edit-shop_order') {
            wp_enqueue_style(
                Constants::PLUGIN_NAME . '-page',
                Constants::getUrl() . 'assets/puddinq-order-list.css',
                array(),
                Constants::VERSION,
                'all'
            );
        }
    }
}
