<?php
/*
Plugin Name: Enhanced order list for WooCommerce
Plugin URI:  https://wordpress.org/plugins/puddinq-order-list/
Description: Enhances the WooCommerce orders screen with practical information.
Version:     0.2.1
Author:      Stefan Schotvanger
Author URI:  http://www.puddinq.nl/wip/stefan-schotvanger/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: puddinq-order-list
Domain path: /languages/
WC requires at least: 3.5
WC tested up to: 8.4.0
*/

//namespace PuddinqOrderList;

defined('ABSPATH') or die('Cheating uh?');

require __DIR__ . '/classes/Plugin.php';

$OrderList = new \PuddinqOrderList\Plugin();

$OrderList->run();
