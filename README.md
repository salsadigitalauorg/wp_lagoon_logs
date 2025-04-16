# Lagoon Logs

A WordPress plugin providing a zero-configuration logging system for WordPress sites running on the Amazee.io Lagoon platform. This plugin integrates with the [Wonolog](https://github.com/inpsyde/Wonolog) package to send WordPress logs directly to Lagoon's logging infrastructure.

## Features

- Zero-configuration setup for Amazee.io Lagoon projects
- Automatically sends logs to Logstash in Lagoon environments
- Configurable logging settings through WordPress admin interface
- Falls back to standard WordPress logging in local environments
- Supports custom log hosts, ports, and identifiers

## Requirements

- PHP 7.4 or higher
- WordPress 5.0 or higher
- Running on Amazee.io Lagoon platform

## Installation

### As a Must-Use Plugin (Recommended)

1. Download or clone this repository to your WordPress site's `wp-content/mu-plugins/` directory:
   ```
   git clone https://github.com/salsadigitalauorg/wp_lagoon_logs.git wp-content/mu-plugins/wp_lagoon_logs
   ```

2. Create or edit `wp-content/mu-plugins/load.php` file and add:
   ```php
   if (file_exists(WPMU_PLUGIN_DIR.'/wp_lagoon_logs/wp_lagoon_logs.php')) {
     require WPMU_PLUGIN_DIR.'/wp_lagoon_logs/wp_lagoon_logs.php';
   }
   ```

### Using Composer

1. Add the repository to your project's `composer.json`:
   ```bash
   composer require salsadigitalauorg/wp_lagoon_logs
   ```

2. If using as a must-use plugin, make sure to configure the loading as described above.

## Configuration

Lagoon Logs is designed to work with minimal configuration for Amazee.IO Lagoon projects.

### Default Configuration

By default, the plugin will:
- Connect to Logstash at "application-logs.lagoon.svc:5140"
- Identify logs with the prefix "wordpress"
- Only send logs in non-local Lagoon environments

### Admin Configuration

The plugin adds a settings page in the WordPress admin under "Settings > Lagoon Logs" where you can configure:
- Log host
- Log port
- Log identifier

## How It Works

- In Lagoon environments (when LAGOON_ENVIRONMENT_TYPE is set and not 'local'), logs are sent to the configured Logstash instance
- In local environments, it falls back to standard Wonolog/WordPress logging
- Uses socket connections to send logs to Logstash in JSON format

## License

This plugin is licensed under [GPL-2.0+](LICENSE).

The plugin is based on [Wonolog package](https://github.com/inpsyde/Wonolog) which uses the [MIT license](https://github.com/inpsyde/Wonolog/blob/master/LICENSE).
