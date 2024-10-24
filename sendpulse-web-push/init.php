<?php
/**
* Plugin Name: SendPulse Free Web Push
* Plugin URI: https://sendpulse.com/integrations/cms/wordpress 
* Description: SendPulse Free Web Push plugin adds your web push integration code into the &lt;head&gt; section of your website. The plugin will enable web push subscription requests to your website visitors and optionally pass  emails and names of logged in users for segmentation and personalization. To get started: 1)Click the "Activate" link to the left of this description, 2) Sign up for a free <a href="https://sendpulse.com/webpush/register?utm_source=wordpress&utm_medium=referral&utm_campaign=wordpresspush">Sendpulse account</a>, and 3) Add your website to SendPulse, copy and paste the integation code into the plugin settings section
* Version: 1.3.7
* Author: SendPulse
* Author URI: https://sendpulse.com/webpush?utm_source=wordpress
* License: GPLv2
* Text Domain: sendpulse-webpush
*/

namespace SendpulseWebPush;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

define('SENDPULSE_WEBPUSH_ABS_PATH', get_public_dir(basename(dirname(__FILE__))));
define('SENDPULSE_WEBPUSH_PUBLIC_PATH', str_replace(DIRECTORY_SEPARATOR, '/', str_replace(str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT']), '', dirname(__FILE__))));

load_plugin_textdomain('sendpulse-webpush', false, basename(dirname(__FILE__)) . '/languages');

// Check for forwardslash/backslash in folder path to structure paths
function get_public_dir($url = '') {
    $url = strval($url);
    if (!empty($url) && !preg_match('#(\\\\|/)$#', $url)) {
        return $url . '/';
    } else if (empty($url)) {
        return '/';
    } else {
        return $url;
    }
}

class SendpulseWebPush {
    /** launch the hooks */
    public function __construct() {
        require_once (plugin_dir_path(__FILE__) . 'faq.php');
        require_once (plugin_dir_path(__FILE__) . 'settings.php');
        require_once (plugin_dir_path(__FILE__) . 'sendpulse-webpush.php');
        
        // add actions init
        add_action('admin_menu', array($this, 'sp_webpush_settings'), 30);
        add_action('admin_assets', array($this, 'sp_webpush_assets'), 30);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'sp_push_settings_link');
    }
    

    public function sp_push_settings_link($links) {
        $url = esc_url( 
            add_query_arg(
                'page',
                'sendpulse-web-push/settings.php',
                get_admin_url() . 'admin.php'
            )
        );

        $plugin_links = array(
            '<a href="' . $url . '">' . __('Settings', 'webpush-sendpulse') . '</a>',
        );
    
        return array_merge($plugin_links, $links);
    }
    
    
    /** settings page */
    public function sp_webpush_settings() {
        $allowed_group = 'administrator';
        add_menu_page( 
            __('Sendpulse WebPush', 'sendpulse-webpush'), 
            __('WebPush', 'sendpulse-webpush'), 
            $allowed_group, 
            'sendpulse-web-push/settings.php', 
            'sendpulse_config',
            plugins_url('sendpulse-web-push/img/menu_icon.png'),
            '30'
        );

        add_submenu_page(
            'sendpulse-web-push/settings.php', 
            __('Settings', 'sendpulse-webpush'), 
            __('Settings', 'sendpulse-webpush'), 
            $allowed_group, 
            'manage_options', 
            'sendpulse_config'
        );
        
        add_submenu_page(
            'sendpulse-web-push/settings.php',
            __('FAQ', 'sendpulse-webpush'), 
            __('FAQ', 'sendpulse-webpush'), 
            $allowed_group, 
            'sendpulse-web-push/faq.php', 
            'sendpulse_faq'
        );
    }

}

$webpush = new SendpulseWebPush();