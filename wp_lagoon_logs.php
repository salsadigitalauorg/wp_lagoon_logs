<?php
/**
 * Plugin Name: WP lagoon logs
 * Description: Simple wonolog wrapper for Lagoon.
 * Version: 0.5
 * Authors: Govind Maloo, Ivan Grynenko
 * Author URI: http://drupal.org/u/govind.maloo
 * License: GPL2
 */

defined('ABSPATH') or die('ABSPATH is not defined');

// Require autoload file.
if (file_exists('/app/vendor/autoload.php')) {
  require_once '/app/vendor/autoload.php';
}
elseif (file_exists(dirname( __FILE__ ) . '/vendor/autoload.php')) {
  require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

use wp_lagoon_logs\lagoon_logs\LagoonLogsSettings;
use wp_lagoon_logs\lagoon_logs\LagoonHandler;
use Inpsyde\Wonolog;

if (!defined( 'Inpsyde\Wonolog\LOG')) {
  error_log('Inpsyde\Wonolog\LOG is not defined in WP Lagoon logs plugin', 0);
  return;
}

// Set default values here
function wp_lagoon_logs_default_settings() {
  // Check if WordPress is in the installation process
  // wp_installing() returns true during installation process
  if (function_exists('wp_installing') && wp_installing()) {
    return;
  }

  // Check if the wp_options table exists before trying to update options
  global $wpdb;
  $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->options}'");

  if (!$table_exists) {
    return;
  }

  $default = [
    'll_settings_logs_host' => 'application-logs.lagoon.svc',
    'll_settings_logs_port' => 5140,
    'll_settings_logs_identifier' => 'wordpress',
  ];
  update_option('wp_ll_settings', $default);
}

/**
 * Plugin init action because activation hook won't trigger in MU plugin.
 */
function wp_lagoon_logs_extension_init() {
  // Check if WordPress is in the installation process
  if (function_exists('wp_installing') && wp_installing()) {
    return;
  }

  // Check if the database is ready before accessing options
  global $wpdb;
  $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->options}'");

  if (!$table_exists) {
    return;
  }

  if (get_option( 'wp_ll_settings')) {
    return;
  }
  wp_lagoon_logs_default_settings();
}
add_action('init', 'wp_lagoon_logs_extension_init');

if (getenv('LAGOON_ENVIRONMENT_TYPE') && getenv('LAGOON_ENVIRONMENT_TYPE') !== 'local') {
  // Check if WordPress is in the installation process
  if (function_exists('wp_installing') && wp_installing()) {
    // Just bootstrap Wonolog during installation
    Wonolog\bootstrap();
    return;
  }

  // Check if the database is ready
  global $wpdb;
  $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->options}'");

  if (!$table_exists) {
    // Just bootstrap Wonolog if database tables don't exist yet
    Wonolog\bootstrap();
    return;
  }

  $options = get_option('wp_ll_settings', []);

  if (isset($options['ll_settings_logs_host'], $options['ll_settings_logs_port'], $options['ll_settings_logs_identifier'])) {
    $handler = new LagoonHandler($options['ll_settings_logs_host'], $options['ll_settings_logs_port'], $options['ll_settings_logs_identifier']);
    $handler->initHandler();
  }
  else {
    // Option values are not set or are incomplete.
    add_action('admin_notices', 'wp_ll_settings_admin_notice');
  }
}
else {
  // Start Wonolog.
  Wonolog\bootstrap();
}

// Settings page is accessible to admin user.
if (is_admin()) {
  $wp_ll_settings_page = new LagoonLogsSettings();
}

function wp_ll_settings_admin_notice() {
  ?>
  <div class="notice notice-warning">
    <p><?php _e('Warning: WP Lagoon Logs settings are incomplete. Please ensure that all required settings are configured.', 'text-domain'); ?></p>
  </div>
  <?php
}
