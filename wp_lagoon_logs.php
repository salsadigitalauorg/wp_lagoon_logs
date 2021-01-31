<?php
/**
 * Plugin Name: WP lagoon logs
 * Description: Simple wonolog wrapper for Lagoon.
 * Version: 0.2
 * Author: Govind Maloo
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
  if (get_option( 'wp_ll_settings')) {
    return;
  }
  wp_lagoon_logs_default_settings();
}
add_action('init', 'wp_lagoon_logs_extension_init');

if (getenv('LAGOON_ENVIRONMENT_TYPE') && getenv('LAGOON_ENVIRONMENT_TYPE') !== 'local') {
  $options = get_option('wp_ll_settings');
  $handler = new LagoonHandler($options['ll_settings_logs_host'], $options['ll_settings_logs_port'], $options['ll_settings_logs_identifier']);
  $handler->initHandler();
}
else {
  // Start Wonolog.
  Wonolog\bootstrap();
}

// Settings page is accessible to admin user.
if (is_admin()) {
  $wp_ll_settings_page = new LagoonLogsSettings();
}
