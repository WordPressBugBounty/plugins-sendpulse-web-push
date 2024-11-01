<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

use \SendpulseWebPush\SendpulseWebPush;

function sendpulse_config() {
    $currenturl = esc_url($_SERVER["REQUEST_URI"]);

    // Check if there is a legacy field with the full script
    $legacy_script = html_entity_decode(get_option('sendpulse_code', ''));

    if (!empty($legacy_script)) {
        // If the legacy field exists, extract components and migrate them to new options
        if (preg_match('/<script\s+charset="([^"]+)"\s+src="([^"]+\/)([^\/]+)"\s*(\w+="[^"]+"\s*)*(async)?\s*><\/script>/', $legacy_script, $matches)) {
            $push_url = isset($matches[2]) ? $matches[2] : '';
            $script_id = isset($matches[3]) ? $matches[3] : '';
            $script_params = isset($matches[5]) ? trim($matches[5]) : '';

            // Save components in the new fields
            update_option('sendpulse_push_url', $push_url);
            update_option('sendpulse_script_id', $script_id);
            update_option('sendpulse_script_params', $script_params);

            // Delete the legacy field since the data is now migrated
            delete_option('sendpulse_code');

            echo "<p class=\"success\">".esc_html__('Legacy script migrated successfully.', 'sendpulse-webpush')."</p>";
        }
    }

    // Retrieve the saved values (which could have been migrated)
    $push_url = get_option('sendpulse_push_url', '');
    $script_id = get_option('sendpulse_script_id', '');
    $script_params = get_option('sendpulse_script_params', '');

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        // Verify nonce
        if (isset($_POST['_sendpulse_settings_nonce']) && wp_verify_nonce($_POST['_sendpulse_settings_nonce'], 'sendpulse_settings_nonce')) {

            // Reset button clicked: clear all saved values
            if (isset($_POST['sendpulse_reset'])) {
                delete_option('sendpulse_push_url');
                delete_option('sendpulse_script_id');
                delete_option('sendpulse_script_params');
                echo "<p class=\"success\">".esc_html__('Values have been reset.', 'sendpulse-webpush')."</p>";
            }

            // If new script is submitted
            if (!empty($_POST['sendpulse_script'])) {
                $script_input = stripslashes($_POST['sendpulse_script']);

                // Extract components using regex
                if (preg_match('/<script\s+charset="([^"]+)"\s+src="([^"]+\/)([^\/]+)"\s*(\w+="[^"]+"\s*)*(async)?\s*><\/script>/', $script_input, $matches)) {
                    // Extract components and sanitize them individually

                    $push_url = isset($matches[2]) ? esc_url($matches[2]) : ''; // Base URL
                    $script_id = isset($matches[3]) ? esc_html($matches[3]) : ''; // Script ID
                    $script_params = isset($matches[5]) ? esc_html($matches[5]) : '';

                    // Save the extracted components
                    update_option('sendpulse_push_url', $push_url);
                    update_option('sendpulse_script_id', $script_id);
                    update_option('sendpulse_script_params', $script_params);

                    echo "<p class=\"success\">".esc_html__('Script successfully saved.', 'sendpulse-webpush')."</p>";
                } else {
                    echo "<p class=\"error\">".esc_html__('Invalid script format.', 'sendpulse-webpush')."</p>";
                }
            }

        }
    }

    // Display the saved script or the input form based on whether values exist
    ?>
    <div class="wrap">

        <?php if (!empty($push_url) && !empty($script_id)): ?>
            <!-- Display the saved script -->
            <div>
                <h2><?php _e('Your current integration script:', 'sendpulse-webpush'); ?></h2>
                <span style="background-color: white; padding: 5px; border: #000; color: #646970;">
                        &lt;script charset="UTF-8" src="<?php echo esc_url($push_url . $script_id); ?>" <?php echo esc_attr($script_params); ?>> &lt;/script&gt;
                    </span>
            </div>

            <div>
                <h2><?php _e('Remove current WebPush script:', 'sendpulse-webpush'); ?></h2>
                <p><?php _e('Use this button only in case you need to change WebPush Script provided by SendPulse', 'sendpulse-webpush'); ?></p>
                <!-- Button to reset (delete) the saved script values -->
                <form method="post" action="<?php echo $currenturl; ?>">
                    <?php wp_nonce_field('sendpulse_settings_nonce', '_sendpulse_settings_nonce'); ?>
                    <input type="hidden" name="sendpulse_reset" value="1" />
                    <?php submit_button(__('Remove', 'sendpulse-webpush'), 'delete'); ?>
                </form>

            </div>
        <?php else: ?>
            <h2><?php _e('Insert integration code', 'sendpulse-webpush'); ?></h2>
            <h3><?php _e('The code you put in here will be inserted into the &lt;head&gt; tag on every page.', 'sendpulse-webpush'); ?></h3>
            <!-- Show text area for input if no script is saved -->
            <form method="post" action="<?php echo $currenturl; ?>">
                <?php wp_nonce_field('sendpulse_settings_nonce', '_sendpulse_settings_nonce'); ?>
                <textarea name="sendpulse_script" style="width:80%; min-width:600px; height:100px;"></textarea>
                <?php submit_button(__('Save Script', 'sendpulse-webpush')); ?>
            </form>
        <?php endif; ?>
    </div>
    <?php
}