<?php

use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class CRM_Core_Error_Log
 *
 * A PSR-3 wrapper for CRM_Core_Error.
 */
class CRM_Core_Error_Log extends \Psr\Log\AbstractLogger {

  /**
   * Log mapping.
   *
   * @var array
   */
  protected $map;

  /**
   * The Monolog Logger.
   *
   * @var \Monolog\Logger
   */
  protected $logger;

  /**
   * String containing minimum severity to log to Monolog.
   *
   * @var string
   */
  protected $severity_limit;

  /**
   * CRM_Core_Error_Log constructor.
   */
  public function __construct() {
    $this->map = [
      \Psr\Log\LogLevel::DEBUG => PEAR_LOG_DEBUG,
      \Psr\Log\LogLevel::INFO => PEAR_LOG_INFO,
      \Psr\Log\LogLevel::NOTICE => PEAR_LOG_NOTICE,
      \Psr\Log\LogLevel::WARNING => PEAR_LOG_WARNING,
      \Psr\Log\LogLevel::ERROR => PEAR_LOG_ERR,
      \Psr\Log\LogLevel::CRITICAL => PEAR_LOG_CRIT,
      \Psr\Log\LogLevel::ALERT => PEAR_LOG_ALERT,
      \Psr\Log\LogLevel::EMERGENCY => PEAR_LOG_EMERG,
    ];

    // Get the necessary config from either destination.
    if (defined('CIVICRM_LOGGER_SEVERITY_LIMIT')) {
      $this->severity_limit = strtolower(CIVICRM_LOGGER_SEVERITY_LIMIT);
    }
    else {
      $config = Civi::settings()->get('logger-settings');
      if ($config != NULL) {
        $config = json_decode(utf8_decode($this->config), TRUE);
        $this->severity_limit = strtolower($config['logger_severity_limit']);
      }
      else {
        $this->severity_limit = 'debug';
      }
    }
    $this->logger = new Logger('civicrm');
    $streamHandler = new StreamHandler('php://stderr');
    $formatter = new LogstashFormatter('calibr8');
    $streamHandler->setFormatter($formatter);
    $this->logger->pushHandler($streamHandler);
  }

  /**
   * Logs with an arbitrary level.
   *
   * @param mixed $level
   * @param string $message
   * @param array $context
   */
  public function log($level, $message, array $context = []) {
    if (!empty($context)) {
      if (isset($context['exception'])) {
        $context['exception'] = CRM_Core_Error::formatTextException($context['exception']);
      }
      $message .= "\n" . print_r($context, 1);
    }

    // Check for severity level log limit, to only log from a certain level.
    $limit = $this->map[$this->severity_limit];
    $level_key = $this->map[$level];
    if ($limit >= $level_key) {
      if ($this->logger) {
        $this->logger->$level(serialize($message));
      }
      else {
        // Fallback to default logging.
        CRM_Core_Error::debug_log_message($message, FALSE, '', $this->map[$level]);
      }
    }
  }

}
