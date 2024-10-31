<?php

namespace PuddinqOrderList;

class Plugin
{

    private $filter;

    public function __construct()
    {

        // load text domain
        add_action('admin_init', array($this, 'loadTextDomain'));

        // Check compatibility (WooCommerce plugin is activated)
        add_action('admin_init', array($this, 'checkWooCommerceIsActive'), 1);
    }

    public function run()
    {

        if (is_admin()) {
            require_once __DIR__ . '/Helpers/Constants.php';
            require_once __DIR__ . '/Admin.php';
            require_once __DIR__ . '/Filter.php';

            (new Admin());

            $this->filter = new Filter();
        }
    }

    /**
     * Load text domain
     */

    public function loadTextDomain()
    {
        load_plugin_textdomain(
            'puddinq-order-list',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }


    /**
     * Check if WooCommerce is active
     *
     * Deactivate en send a notice if not
     *
     * @return bool
     */

    public function checkWooCommerceIsActive()
    {
        // create array with active plugins
        $active_plugins = (array)get_option('active_plugins', array());

        // add site wide active multi site plugins
        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }

        // check if woocommerce is active
        if (!in_array('woocommerce/woocommerce.php', $active_plugins)) {
            deactivate_plugins('/puddinq-order-list/puddinq-order-list.php');
            add_action('admin_notices', array($this, 'wooCommerceNotice'));
            return false;
        }

        return true;
    }

    /**
     * Notice if WooCommerce is not active
     *
     * activated by check_woocommerce_is_active
     */

    public function wooCommerceNotice()
    {
        ?>
        <div class="error notice is-dismissible">
            <p>
                <b><?php _e('Deactivated Enhanced order list for WooCommerce: ', 'puddinq-order-list'); ?></b>
                <?php _e('WooCommerce needs to be installed and activated', 'puddinq-order-list'); ?>
            </p>
        </div>
        <?php
    }
}

