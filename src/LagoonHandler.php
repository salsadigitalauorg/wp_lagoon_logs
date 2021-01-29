<?php

namespace wp_lagoon_logs\lagoon_logs;

use Inpsyde\Wonolog;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Logger;

class LagoonHandler {

  const LAGOON_LOGS_DEFAULT_IDENTIFIER = 'wordpress';

  const LAGOON_LOGS_DEFAULT_SAFE_BRANCH = 'safe_branch_unset';

  const LAGOON_LOGS_DEFAULT_LAGOON_PROJECT = 'project_unset';

  protected $hostName;

  protected $hostPort;

  protected $logFullIdentifier;

  protected $parser;

  public function __construct($host, $port, $logFullIdentifier) {
    $this->hostName = $host;
    $this->hostPort = $port;
    $this->logFullIdentifier = $logFullIdentifier;
  }

  /**
   * Initialize lagoon socket handler to log.
   */
  public function initHandler() {
    $formatter = new LogstashFormatter($this->getHostProcessIndex(), null, null, 'ctxt_', 1);
    //make sure to set your URL and port here
    $lagoonSocket = new SyslogUdpHandler($this->hostName, $this->hostPort, LOG_USER, Logger::DEBUG, TRUE, self::LAGOON_LOGS_DEFAULT_IDENTIFIER);
    $lagoonSocket->setFormatter($formatter);
    Wonolog\bootstrap($lagoonSocket, Wonolog\USE_DEFAULT_PROCESSOR)
      ->log_php_errors()
      ->use_default_hook_listeners();
  }

  /**
   * Get Lagoon project identifier.
   *
   * @return string
   */
  protected function getHostProcessIndex() {
    return implode('-', [
      getenv('LAGOON_PROJECT') ?: self::LAGOON_LOGS_DEFAULT_LAGOON_PROJECT,
      getenv('LAGOON_GIT_SAFE_BRANCH') ?: self::LAGOON_LOGS_DEFAULT_SAFE_BRANCH,
    ]);
  }
}
