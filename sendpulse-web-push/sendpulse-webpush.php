<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

use \SendpulseWebPush\SendpulseWebPush;

function sp_webpush_get_domain() {
    return ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
}

function sp_webpush_admin_notices() {
    if ($notices = get_option('send_pulse_deferred_admin_notices')) {
        foreach ($notices as $notice) {
            echo "<div class='updated'><p>" . esc_html($notice) . "</p></div>";
        }
        delete_option('send_pulse_deferred_admin_notices');
    }
}

add_action('admin_notices', 'sp_webpush_admin_notices');

add_action('wp_head', 'sendpulse_display', 1000);
add_action('login_enqueue_scripts', 'sendpulse_display'); // Write our JS below here
function sendpulse_display() {
    $legacy_script = html_entity_decode(get_option('sendpulse_code', ''));

    $charset = 'UTF-8';
    $push_url = html_entity_decode(esc_url(get_option('sendpulse_push_url', '')));
    $script_id = html_entity_decode(esc_url(get_option('sendpulse_script_id', '')));
    $script_params = html_entity_decode(esc_attr(get_option('sendpulse_script_params', '')));

    $script = '<script charset="'. $charset .'" src="' . $push_url . $script_id . '" '.$script_params.'></script>';

    if (!empty($legacy_script)) {
        echo $legacy_script;
    } elseif( !empty($push_url) && !empty($script_id) && !empty($script_params)) {
        echo $script;
    } else {

    }
}

add_action('wp_footer', 'sendpulse_user_reg_action'); // Write our JS below here
add_action('login_enqueue_scripts', 'sendpulse_user_reg_action'); // Write our JS below here

function sendpulse_user_reg_action() {
    $sendpulse_addinfo = get_option('sendpulse_addinfo', 'N');
    if ($sendpulse_addinfo != 'Y')
        return;
    if(!is_admin()) {
        if (isset($_COOKIE['sendpulse_webpush_addinfo'])) {
            list($login, $email, $user_id) = explode('|', $_COOKIE['sendpulse_webpush_addinfo']);
            $domain = sp_webpush_get_domain();
            ?>
            <script src="<?php echo SENDPULSE_WEBPUSH_PUBLIC_PATH;?>/js/utils.js" type="text/javascript" ></script>
            <script type="text/javascript" >
                domReady(function() {
                    var domain = '<?php echo $domain; ?>';
                    window.addEventListener("load", function() {
                        oSpP.push("Name","<?php echo $login; ?>");
                        oSpP.push("Email","<?php echo $email; ?>");
                    });
                })
            </script><?php
            $domain = sp_webpush_get_domain();
            $secure = empty($_SERVER["HTTPS"]) ? 0 : 1;
            setcookie("sendpulse_webpush_addinfo", NULL, (strtotime('-1 Year', time())), '/', $domain, $secure);
        }
    }
}

add_action('user_register', 'sendpulseplugin_registration_save', 10, 1);

function sendpulseplugin_registration_save($user_id) {
    $sendpulse_addinfo = get_option('sendpulse_addinfo', 'N');
    if ($sendpulse_addinfo != 'Y') {
        return;
    }

    $login = ! empty($_REQUEST["user_login"]) ? $_REQUEST["user_login"] : '';
    $email = ! empty($_REQUEST["user_email"]) ? $_REQUEST["user_email"] : '';
    $expire = time()+3600*24*7;
    $domain = sendpulse_webpush_get_domain();
    $data = array(trim($login), $email, $user_id);
    $secure = empty($_SERVER["HTTPS"]) ? 0 : 1;
    setcookie("sendpulse_webpush_addinfo", implode('|', $data), $expire, "/", $domain, $secure);
}

//Installation
register_activation_hook(__FILE__, 'SendPulseInstallStep1');
function SendPulseInstallStep1() {
    include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'installdeinstall.php');
    SendPulseInstallStep2();
}

//Deactivation
register_deactivation_hook(__FILE__, 'SendPulseDeactivationStep1');
function SendPulseDeactivationStep1() {
    delete_option('send_pulse_deferred_admin_notices');
}

//Deinstallation
register_uninstall_hook(__FILE__, 'SendPulseDeinstallStep1');
function SendPulseDeinstallStep1() {
    include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'installdeinstall.php');
    SendPulseDeinstallStep2();
}

/** Add Settings Link on Plugin Page */
function sp_push_settings_link($links) {
    $url = esc_url( add_query_arg(
        'page',
        'sendpulse-web-push/settings.php',
        get_admin_url() . 'admin.php'
    ) );
    $plugin_links = array(
        '<a href="' . $url . '">' . __('Settings', 'webpush-sendpulse') . '</a>',
    );

    return array_merge($plugin_links, $links);
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'sp_push_settings_link');

/** Add Faq Link on Plugin Page */
add_filter( 'plugin_row_meta', 'sendpulse_webpush_row_meta', 10, 2 );

function sendpulse_webpush_row_meta( $links, $file ) {
    if ( strpos( $file, 'init.php' ) !== false ) {
        $new_links = array(
            'register'    => '<a style="color:red" href="https://sendpulse.com/webpush?utm_source=wordpress" target="_blank">'. __('Register on Sendpulse', 'sendpulse-webpush') .'</a>',
            'faq'       => '<a href="'.get_admin_url() . 'admin.php?page=sendpulse-web-push/faq.php'.'" target="_blank">'. __('FAQ', 'sendpulse-webpush') .'</a>'
        );

        $links = array_merge( $links, $new_links );
    }

    return $links;
}