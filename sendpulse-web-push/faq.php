<?php 
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

function sendpulse_faq() { ?>
<h2><?php echo __('Installation', 'sendpulse-webpush');?></h2>
<ol>
    <li><?php echo __('Add the plugin to WordPress by searching and installing, uploading a zip, FTP copy, or some other way, and activate it.', 'sendpulse-webpush');?></li>
    <li><?php echo __('After activation complete click on Setting link under plugin name.', 'sendpulse-webpush');?></li>
    <li><?php echo __('If you have an account at <a href="https://sendpulse.com/" target="blank">SendPulse</a> go to Push tab. If not register at <a href="https://sendpulse.com/register" target="blank">Sendpulse</a>.', 'sendpulse-webpush');?></li>
    <li><?php echo __('Connect your Site.', 'sendpulse_webpush');?></li>
    <li><?php echo __('Copy code generated after site connection. Example: <code>&lt;sсript charset="UTF-8" src="//web.webpushs.com/js/push/your_generated_hash_id.js" async&gt;&lt;/&gt;</code>', 'sendpulse-webpush');?></li>
    <li><?php echo __('Past generated code on Settings page', 'sendpulse-webpush');?></li>
    <li><?php echo __('Mark the checkbox if you want to pass information about your registered users to your account.', 'sendpulse-webpush');?></li>
    <li><?php echo __('That\'s all. You connected Push Messages to your site.', 'sendpulse-webpush');?></li>
</ol>
<?php
}