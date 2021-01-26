# Lagoon Logs

This module aims to be as close to a zero-configuration logging system for WP sites running on the the Amazee.io Lagoon platform.

## Installation

Installation should in WP MU space download and store in mu-plugins folder.

Alternatively, install with composer:
`composer require wp_lagoon_logs/wp_lagoon_logs`

It's installed by adding the following code in load.php file
```
if (file_exists(WPMU_PLUGIN_DIR.'/wp_lagoon_logs/wp_lagoon_logs.php')) {
  require WPMU_PLUGIN_DIR.'/wp_lagoon_logs/wp_lagoon_logs.php';
}
```

## Use/configuration

Lagoon Logs is meant to be a Zero Configuration setup for Amazee.IO Lagoon projects.

Once the prerequisite modules and libraries have been installed, it will, by default send its logs to a Logstash instance at "application-logs.lagoon.svc:5140".

## License note

The plugin is based on [Wonolog package](https://github.com/inpsyde/Wonolog) which uses the [MIT license](https://github.com/inpsyde/Wonolog/blob/master/LICENSE).
