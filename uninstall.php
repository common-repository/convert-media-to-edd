<?php
/**
 * Uninstall file
 *
 * @since 1.0
 */
if (!defined('WP_UNINSTALL_PLUGIN'))
    exit();

global $wpdb;

$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name = 'cmedd_cron_last_data_args';" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name = 'cmedd_cron_post_data';" );

