<?php

namespace PuddinqOrderList\Helpers;

/**
 * Constants, Strings, values shared by the plugin
 *
 * @author Stefan Schotvanger
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class Constants
{

    const PLUGIN_NAME = 'puddinq-order-list';
    const VERSION = '0.2.1';
    const SETTINGS_GROUP = 'puddinq-order-list-settings';

    // Plugin Path for includes
    public static function getPath()
    {
        return WP_PLUGIN_DIR . '/' . Constants::PLUGIN_NAME . '/';
    }

    // Plugin URL for assets enqueing
    public static function getUrl()
    {
        return plugin_dir_url(dirname(dirname(__FILE__)));
    }
}
