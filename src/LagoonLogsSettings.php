<?php

  namespace wp_lagoon_logs\lagoon_logs;

  class LagoonLogsSettings {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct() {
      add_action('admin_menu', [$this, 'add_plugin_page']);
      add_action('admin_init', [$this, 'page_init']);
    }

    /**
     * Add options page under "Settings"
     */
    public function add_plugin_page() {
      add_options_page(
        __('Settings Admin', 'wp-lagoon-logs'),
        __('WP Lagoon logs Settings', 'wp-lagoon-logs'),
        'manage_options',
        'wp-lagoon-logs-admin',
        [$this, 'wp_lagoon_logs_settings_page']
      );
    }

    /**
     * Options page callback
     */
    public function wp_lagoon_logs_settings_page() {
      $this->options = get_option('wp_ll_settings');
      ?>
        <div class="wrap">
            <h1><?php _e('WP Lagoon logs configuration settings.', 'wp-lagoon-logs'); ?></h1>
            <form method="post" action="options.php">
              <?php
                settings_fields('wp_ll_option_group');
                do_settings_sections('wp-lagoon-logs-admin');
              ?>
            </form>
        </div>
      <?php
    }

    /**
     * Register and add settings
     */
    public function page_init() {
      register_setting('wp_ll_option_group', 'wp_ll_settings');

      // ... rest of your code ...

    }

    /**
     * Print the Section text
     */
    public function wp_ll_description() {
      _e('This page simply lists the current settings for the Lagoon Logs module. The defaults are set in configuration, this page is meant primarily for troubleshooting.', 'wp-lagoon-logs');
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function text_field_callback($name) {
      $value = isset($this->options[$name]) ? esc_attr($this->options[$name]) : '';
      printf('<div class="ll-settings-key-value"><input type="text" name="wp_ll_settings[%s]" disabled="disabled" value="%s" /></div>', esc_attr($name), $value);
    }

  }
