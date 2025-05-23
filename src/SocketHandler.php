<?php

/*
 * This file is part of the Monolog package.
 *
 * It is temporarily incorporated into the Lagoon Logs module until a new
 * stable version of the Monolog package is released that incorporates the
 * ability to transmit large UDP packets without splitting them (the
 * "chunkSize" functionality below).
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the README
 * file distributed with this module
 */

declare(strict_types=1);

namespace wp_lagoon_logs\lagoon_logs;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Stores to any socket - uses fsockopen() or pfsockopen().
 *
 * @author Pablo de Leon Belloc <pablolb@gmail.com>
 * @see    http://php.net/manual/en/function.fsockopen.php
 */
class SocketHandler extends AbstractProcessingHandler {

  private $connectionString;

  private $connectionTimeout;

  private $resource;

  private $timeout = 0;

  private $writingTimeout = 10;

  private $lastSentBytes = NULL;

  private $chunkSize = NULL;

  private $persistent = FALSE;

  private $errno;

  private $errstr;

  private $lastWritingAt;

  /**
   * @param string $connectionString Socket connection string
   * @param int $level The minimum logging level at which this handler will be
   *   triggered
   * @param bool $bubble Whether the messages that are handled can bubble up
   *   the stack or not
   */
  public function __construct(
    string $connectionString,
    int $level = Logger::DEBUG,
    bool $bubble = true
  ) {
    parent::__construct($level, $bubble);
    $this->connectionString = $connectionString;
    $this->connectionTimeout = (float) ini_get('default_socket_timeout');
  }

  /**
   * Connect (if necessary) and write to the socket
   *
   * @param array $record
   *
   * @throws \UnexpectedValueException
   * @throws \RuntimeException
   */
  protected function write(array $record) {
    $this->connectIfNotConnected();
    $data = $this->generateDataStream($record);
    $this->writeToSocket($data);
  }

  /**
   * We will not close a PersistentSocket instance so it can be reused in
   * other requests.
   */
  public function close() {
    if (!$this->isPersistent()) {
      $this->closeSocket();
    }
  }

  /**
   * Close socket, if open
   */
  public function closeSocket() {
    if (is_resource($this->resource)) {
      fclose($this->resource);
      $this->resource = NULL;
    }
  }

  /**
   * Set socket connection to nbe persistent. It only has effect before the
   * connection is initiated.
   *
   * @param bool $persistent
   */
  public function setPersistent(bool $persistent): void {
    $this->persistent = (bool) $persistent;
  }

  /**
   * Set connection timeout.  Only has effect before we connect.
   *
   * @param float $seconds
   *
   * @see http://php.net/manual/en/function.fsockopen.php
   */
  public function setConnectionTimeout(float $seconds): void {
    $this->validateTimeout($seconds);
    $this->connectionTimeout = (float) $seconds;
  }

  /**
   * Set write timeout. Only has effect before we connect.
   *
   * @param float $seconds
   *
   * @see http://php.net/manual/en/function.stream-set-timeout.php
   */
  public function setTimeout(float $seconds): void {
    $this->validateTimeout($seconds);
    $this->timeout = (float) $seconds;
  }

  /**
   * Set writing timeout. Only has effect during connection in the writing
   * cycle.
   *
   * @param float $seconds 0 for no timeout
   */
  public function setWritingTimeout(float $seconds): void {
    $this->validateTimeout($seconds);
    $this->writingTimeout = (float) $seconds;
  }

  /**
   * Set chunk size. Only has effect during connection in the writing cycle.
   *
   * @param float $bytes
   */
  public function setChunkSize(float $bytes): void {
    $this->chunkSize = $bytes;
  }

  /**
   * Get current connection string
   *
   * @return string
   */
  public function getConnectionString() {
    return $this->connectionString;
  }

  /**
   * Get persistent setting
   *
   * @return bool
   */
  public function isPersistent(): bool {
    return $this->persistent;
  }

  /**
   * Get current connection timeout setting
   *
   * @return float
   */
  public function getConnectionTimeout() {
    return $this->connectionTimeout;
  }

  /**
   * Get current in-transfer timeout
   *
   * @return float
   */
  public function getTimeout() {
    return $this->timeout;
  }

  /**
   * Get current local writing timeout
   *
   * @return float
   */
  public function getWritingTimeout() {
    return $this->writingTimeout;
  }

  /**
   * Get current chunk size
   *
   * @return float
   */
  public function getChunkSize() {
    return $this->chunkSize;
  }

  /**
   * Check to see if the socket is currently available.
   *
   * UDP might appear to be connected but might fail when writing.  See
   * http://php.net/fsockopen for details.
   *
   * @return bool
   */
  public function isConnected() {
    return is_resource($this->resource)
      && !feof($this->resource);  // on TCP - other party can close connection.
  }

  /**
   * Wrapper to allow mocking
   */
  protected function pfsockopen() {
    return @pfsockopen($this->connectionString, -1, $this->errno, $this->errstr,
      $this->connectionTimeout);
  }

  /**
   * Wrapper to allow mocking
   */
  protected function fsockopen() {
    return @fsockopen($this->connectionString, -1, $this->errno, $this->errstr,
      $this->connectionTimeout);
  }

  /**
   * Wrapper to allow mocking
   *
   * @see http://php.net/manual/en/function.stream-set-timeout.php
   */
  protected function streamSetTimeout() {
    $seconds = floor($this->timeout);
    $microseconds = round(($this->timeout - $seconds) * 1e6);

    return stream_set_timeout($this->resource, $seconds, $microseconds);
  }

  /**
   * Wrapper to allow mocking
   *
   * @see http://php.net/manual/en/function.stream-set-chunk-size.php
   */
  protected function streamSetChunkSize() {
    return stream_set_chunk_size($this->resource, $this->chunkSize);
  }

  /**
   * Wrapper to allow mocking
   */
  protected function fwrite($data) {
    return @fwrite($this->resource, $data);
  }

  /**
   * Wrapper to allow mocking
   */
  protected function streamGetMetadata() {
    return stream_get_meta_data($this->resource);
  }

  private function validateTimeout($value) {
    $ok = filter_var($value, FILTER_VALIDATE_FLOAT);
    if ($ok === FALSE || $value < 0) {
      throw new \InvalidArgumentException("Timeout must be 0 or a positive float (got $value)");
    }
  }

  private function connectIfNotConnected() {
    if ($this->isConnected()) {
      return;
    }
    $this->connect();
  }

  protected function generateDataStream($record) {
    return (string) $record['formatted'];
  }

  /**
   * @return resource|null
   */
  protected function getResource() {
    return $this->resource;
  }

  private function connect() {
    $this->createSocketResource();
    $this->setSocketTimeout();
    $this->setStreamChunkSize();
  }

  private function createSocketResource() {
    if ($this->isPersistent()) {
      $resource = $this->pfsockopen();
    }
    else {
      $resource = $this->fsockopen();
    }
    if (!$resource) {
      throw new \UnexpectedValueException("Failed connecting to $this->connectionString ($this->errno: $this->errstr)");
    }
    $this->resource = $resource;
  }

  private function setSocketTimeout() {
    if (!$this->streamSetTimeout()) {
      throw new \UnexpectedValueException("Failed setting timeout with stream_set_timeout()");
    }
  }

  private function setStreamChunkSize() {
    if ($this->chunkSize && !$this->streamSetChunkSize()) {
      throw new \UnexpectedValueException("Failed setting chunk size with stream_set_chunk_size()");
    }
  }

  private function writeToSocket(string $data): void {
    $length = strlen($data);
    $sent = 0;
    $this->lastSentBytes = $sent;
    while ($this->isConnected() && $sent < $length) {
      if (0 == $sent) {
        $chunk = $this->fwrite($data);
      }
      else {
        $chunk = $this->fwrite(substr($data, $sent));
      }
      if ($chunk === FALSE) {
        throw new \RuntimeException("Could not write to socket");
      }
      $sent += $chunk;
      $socketInfo = $this->streamGetMetadata();
      if ($socketInfo['timed_out']) {
        throw new \RuntimeException("Write timed-out");
      }

      if ($this->writingIsTimedOut($sent)) {
        throw new \RuntimeException("Write timed-out, no data sent for `{$this->writingTimeout}` seconds, probably we got disconnected (sent $sent of $length)");
      }
    }
    if (!$this->isConnected() && $sent < $length) {
      throw new \RuntimeException("End-of-file reached, probably we got disconnected (sent $sent of $length)");
    }
  }

  private function writingIsTimedOut(int $sent): bool {
    $writingTimeout = (int) floor($this->writingTimeout);
    if (0 === $writingTimeout) {
      return FALSE;
    }

    if ($sent !== $this->lastSentBytes) {
      $this->lastWritingAt = time();
      $this->lastSentBytes = $sent;

      return FALSE;
    }
    else {
      usleep(100);
    }

    if ((time() - $this->lastWritingAt) >= $writingTimeout) {
      $this->closeSocket();

      return TRUE;
    }

    return FALSE;
  }
}
