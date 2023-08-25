<?php

namespace wp_lagoon_logs\lagoon_logs;

use Inpsyde\Wonolog;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Formatter\LogstashFormatter;

class LagoonHandler {

  public const LAGOON_LOGS_DEFAULT_IDENTIFIER = 'wordpress';
  public const LAGOON_LOGS_DEFAULT_SAFE_BRANCH = 'safe_branch_unset';
  public const LAGOON_LOGS_DEFAULT_LAGOON_PROJECT = 'project_unset';
  public const LAGOON_LOGS_DEFAULT_CHUNK_SIZE_BYTES = 15000; //will be used when new release of monolog is available

  protected $hostName;
  protected $hostPort;
  protected $logFullIdentifier;

  public function __construct(string $host, int $port, string $logFullIdentifier) {
    // You might want to add validation here, e.g., check if the host is a valid hostname.
    $this->hostName = $host;
    $this->hostPort = $port;
    $this->logFullIdentifier = $logFullIdentifier;
  }

  /**
   * Initialize lagoon socket handler to log.
   */
  public function initHandler(): void {
    $formatter = new LogstashFormatter($this->getHostProcessIndex(), null, null, 'ctxt_', 1);

    // Create socket handler.
    $connectionString = sprintf("udp://%s:%s", $this->hostName, $this->hostPort);
    // Ensure SyslogUdpHandler is what we intend to use.
    $udpHandler = new SyslogUdpHandler($connectionString);

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
  protected function getHostProcessIndex(): string {
    return implode('-', [
      getenv('LAGOON_PROJECT') ?: self::LAGOON_LOGS_DEFAULT_LAGOON_PROJECT,
      getenv('LAGOON_GIT_SAFE_BRANCH') ?: self::LAGOON_LOGS_DEFAULT_SAFE_BRANCH,
    ]);
  }
}
