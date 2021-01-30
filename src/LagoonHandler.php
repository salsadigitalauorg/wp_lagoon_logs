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

  const LAGOON_LOGS_DEFAULT_CHUNK_SIZE_BYTES = 15000; //will be used when new release of monolog is available

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

    // Create socket handler.
    $connectionString = sprintf("udp://%s:%s", $this->hostName, $this->hostPort);
    $udpHandler = new SocketHandler($connectionString);

    // Monolog has a change waiting for release that allows us to have large UDP packets.
    $udpHandler->setChunkSize(self::LAGOON_LOGS_DEFAULT_CHUNK_SIZE_BYTES);
    $udpHandler->setFormatter($formatter);

    Wonolog\bootstrap($udpHandler)
      ->use_default_processor()
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
