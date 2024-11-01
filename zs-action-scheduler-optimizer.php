<?php
/*
 * Plugin Name:       ZS Action Scheduler Optimizer
 * Description:       Optimizes Action Scheduler by clearing Action Scheduler Actions table and truncating the logs, also modifies retention period. You can find the plugin under Tools menu.
 * Version:           1.0.2
 * Author:            Zafer Oz
 * Author URI:        https://zafersoft.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: zs-aso
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

function zsaso_action_scheduler_optimizer_menu() {
    add_submenu_page(
        'tools.php',
        __('ZS Action Scheduler Optimizer', 'zs-aso'), // Translatable plugin title
        __('ZS Action Scheduler Optimizer', 'zs-aso'), // Translatable menu title
        'manage_options',
        'action-scheduler-optimizer',
        'zsaso_action_scheduler_optimizer_admin_page'
    );
}
add_action( 'admin_menu', 'zsaso_action_scheduler_optimizer_menu' );

function zsaso_action_scheduler_optimizer_admin_page(){
    global $wpdb;

    // Nonce field for security
    wp_nonce_field('zsaso_action_scheduler_optimizer_nonce_action', 'zsaso_action_scheduler_optimizer_nonce');

    //Check for the tables
    $table_name_actions = $wpdb->prefix . 'actionscheduler_actions';
    $table_name_logs = $wpdb->prefix . 'actionscheduler_logs';

    // Check if the tables exist
    $actions_table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name_actions)) === $table_name_actions;
    $logs_table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name_logs)) === $table_name_logs;

    // Proceed only if both tables exist
    if (!$actions_table_exists || !$logs_table_exists) {
        echo '<div class="error"><p>' . __('One or both of the required tables do not exist in the database. Please ensure Action Scheduler is installed and activated.', 'zs-aso') . '</p></div>';
        return; // Exit the function to prevent further execution
    }

    //User input
    if(isset($_POST['submit']) && check_admin_referer('zsaso_action_scheduler_optimizer_nonce_action', 'zsaso_action_scheduler_optimizer_nonce')){
        //Delete actions
        if ($_POST['delete_actions'] == "Yes" && current_user_can('manage_options')) {
            $wpdb->query($wpdb->prepare("DELETE FROM `{$table_name_actions}` WHERE `status` IN ('complete','failed','canceled')"));
            $wpdb->query("TRUNCATE `{$table_name_logs}`");            
            $message1 = __("Removed all completed, failed and cancelled actions from the table and truncated the logs", 'zs-aso');
        }
        //Set retention time
        $old_retention_period = get_option( 'action_scheduler_retention_period' );
        $new_retention_period = sanitize_text_field($_POST['retention_period']);

        if ($old_retention_period != $new_retention_period && current_user_can('manage_options')) {
            update_option( 'action_scheduler_retention_period', $new_retention_period );
            $message2 = __("Retention period updated successfully", 'zs-aso');
        }
    }

    //Fetch saved settings
    $retention_period = get_option( 'action_scheduler_retention_period', WEEK_IN_SECONDS );

    //Fetch table sizes safely
    $actions_table_size = $wpdb->get_var($wpdb->prepare("SELECT round(((data_length + index_length) / 1024 / 1024), 2) as MB FROM information_schema.TABLES WHERE table_schema = %s AND table_name = %s", DB_NAME, $table_name_actions));
    $logs_table_size = $wpdb->get_var($wpdb->prepare("SELECT round(((data_length + index_length) / 1024 / 1024), 2) as MB FROM information_schema.TABLES WHERE table_schema = %s AND table_name = %s", DB_NAME, $table_name_logs));

    //Fetch number of rows
    $actions_row_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `{$table_name_actions}`"));
    $logs_row_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `{$table_name_logs}`"));

    echo "<h1>" . __('ZS Action Scheduler Optimizer', 'zs-aso') . "</h1>";
    echo '<p style="color:darkblue"><strong>' . __('Warning:', 'zs-aso') . ' </strong>' . __('The action you are about to perform will delete data. Proceed with care.', 'zs-aso') . '</p>';
    echo '<p>' . sprintf(__('Size of the Actions table: %sMB / Number of records: %s', 'zs-aso'), esc_html($actions_table_size), esc_html($actions_row_count)) . '</p>';
    echo '<p>' . sprintf(__('Size of the Logs table: %sMB / Number of records: %s', 'zs-aso'), esc_html($logs_table_size), esc_html($logs_row_count)) . '</p>';
    echo '<form method="post">';
    wp_nonce_field('zsaso_action_scheduler_optimizer_nonce_action', 'zsaso_action_scheduler_optimizer_nonce');
    echo '<p>' . __('Delete completed, failed and canceled actions:', 'zs-aso') . ' <select name="delete_actions"><option>' . __('No', 'zs-aso') . '</option><option>' . __('Yes', 'zs-aso') . '</option></select></p>';
    echo '<p>' . sprintf(__('Current retention period: %s Day(s)', 'zs-aso'), esc_html($retention_period/DAY_IN_SECONDS)) . '</p>';
    echo '<p>' . __('Set the action scheduler purge period:', 'zs-aso') . ' <select name="retention_period">';
    for($i=1; $i<=6; $i++){
        $day_label = ($i == 1) ? __(" Day", 'zs-aso') : __(" Days", 'zs-aso');
        echo '<option '.selected($retention_period, DAY_IN_SECONDS*$i, false).' value="'.esc_attr(DAY_IN_SECONDS*$i).'">'.esc_html($i . $day_label) . '</option>';
    }
    echo '<option '.selected($retention_period, WEEK_IN_SECONDS, false).' value="'.esc_attr(WEEK_IN_SECONDS).'">'.__("1 Week", 'zs-aso').'</option>';
    echo '<option '.selected($retention_period, 2*WEEK_IN_SECONDS, false).' value="'.esc_attr(2*WEEK_IN_SECONDS).'">'.__("2 Weeks", 'zs-aso').'</option>';
    echo '<option '.selected($retention_period, 3*WEEK_IN_SECONDS, false).' value="'.esc_attr(3*WEEK_IN_SECONDS).'">'.__("3 Weeks", 'zs-aso').'</option>';
    echo '<option '.selected($retention_period, MONTH_IN_SECONDS, false).' value="'.esc_attr(MONTH_IN_SECONDS).'">'.__("1 Month", 'zs-aso').'</option>';
    echo '</select></p>';
    if (!empty($message1)) echo "<p style='color:red;'><strong>".esc_html($message1)."</strong></p>";
    if (!empty($message2)) echo "<p style='color:red;'><strong>".esc_html($message2)."</strong></p>";
    echo '<input name="submit" type="submit" class="button button-primary" style="padding:5px 30px;" value="'.__('Save Changes', 'zs-aso').'"></form>';
}

add_filter( 'action_scheduler_retention_period', 'zsaso_change_retention_period_option' );

function zsaso_change_retention_period_option() {
    return get_option( 'action_scheduler_retention_period', WEEK_IN_SECONDS );
}
?>
