<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

use \SendpulseWebPush\SendpulseWebPush;

// Allow <script> tags with specific attributes
function custom_kses_allowed_tags($tags) {
    $tags['script'] = array(
        'src' => true,
        'charset' => true,
        'async' => true,
    );
    return $tags;
}
add_filter('wp_kses_allowed_html', 'custom_kses_allowed_tags', 10, 1);

function sendpulse_config() {
$currenturl = esc_url($_SERVER["REQUEST_URI"]);
?>

<link rel="stylesheet" type="text/css" href="<?php echo esc_url(SENDPULSE_WEBPUSH_PUBLIC_PATH); ?>/css/custom.css" media="all"/>

<div class="wrap">
    <h2><?php _e('Insert integration code', 'sendpulse-webpush'); ?></h2>
    <h3><?php _e('The code you put in here will be inserted into the &lt;head&gt; tag on every page.', 'sendpulse-webpush'); ?></h3>

    <?php
    $html = esc_textarea(get_option('sendpulse_code', ''));

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        // Verify nonce
        if (isset($_POST['_sendpulse_settings_nonce']) && wp_verify_nonce($_POST['_sendpulse_settings_nonce'], 'sendpulse_settings_nonce')) {

            if (isset($_POST['sendpulse_active'])) {
                update_option('sendpulse_active', 'Y');
            } else {
                delete_option('sendpulse_active');
            }
            if (isset($_POST['sendpulse_addinfo'])) {
                update_option('sendpulse_addinfo', 'Y');
            } else {
                delete_option('sendpulse_addinfo');
            }

            if(isset($_POST['html'])){
                $newhtml = wp_kses_post($_POST['html']);
                if($newhtml == $html){
                    echo "<p class=\"not-edited\">".esc_html__('The code is not updated', 'sendpulse-webpush')."</p>";
                }else{
                    update_option('sendpulse_code', $newhtml);
                    $html = $newhtml;
                    printf("<p class=\"success-edited\">".esc_html__("Successfully edited %s!", 'sendpulse-webpush')."</p>", '');
                }
            }
        } else {
            // Nonce verification failed, display an error message or take appropriate action.
            echo "<p class=\"error\">".esc_html__('CSRF verification failed!', 'sendpulse-webpush')."</p>";
        }
    }

    // Output nonce field
    echo wp_nonce_field('sendpulse_settings_nonce', '_sendpulse_settings_nonce', true, false);

    $sendpulse_active = get_option('sendpulse_active', 'N');
    $sendpulse_addinfo = get_option('sendpulse_addinfo', 'N');
    ?>
    <form method="post" action="<?php echo $currenturl; ?>">
        <?php wp_nonce_field( 'sendpulse_settings_nonce', '_sendpulse_settings_nonce' ); ?>
        <?php
        if(isset($html)) { ?>
            <textarea style="white-space:pre; width:80%; min-width:600px; height:300px;" name="html">
            <?php echo esc_textarea($html); ?>
        </textarea>
            <?php
        } ?>
        <br />

        <h3><?php _e('You need to <a target="_blank" href="https://sendpulse.com/webpush?utm_source=wordpress">create a free account</a> to get the web push integration code and send web push notifications.', 'sendpulse-webpush');?></h3>
        <table>
            <?php
            $post_types = get_post_types('', 'names');
            ?>
            <tr>
                <td>
                    <input type="checkbox" name="sendpulse_addinfo" value="Y" <?php if($sendpulse_addinfo == 'Y'){ echo ' checked="checked"';} ?> />
                </td>
                <td>
                    <?php _e('Pass emails and usernames of Wordpress users for personalization.', 'sendpulse-webpush');?>
                </td>
            </tr>
        </table>
        <p><?php _e('Note: this event is triggered only when a new user signs up' , 'sendpulse-webpush'); ?></p>
        <?php submit_button();
        echo "</form>";
        ?>
</div>
<?php } ?>