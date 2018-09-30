<?php

App::uses('DataSource', 'Model/Datasource');

class RedisSource extends DataSource {

  protected $_connection = null;

  public function __construct($config = null, $autoConnect = true) {
    parent::__construct($config);
    if (!$this->enabled()) {
      throw new MissingConnectionException(array(
        'class' => get_class($this),
        'message' => __d('cake_dev', 'Selected driver is not enabled'),
        'enabled' => false
      ));
    }
    if ($autoConnect) {
      return $this->connect();
    }
  }

  public function enabled() {
    return extension_loaded('redis');
  }

  public function connect() {
    $config = $this->config;
    $this->connected = false;

    try {
      $this->_connection = new Redis();

      if (!$this->_connection->connect($this->config['host'], $this->config['port'])) {
        throw new Exception(sprintf('Could not connect to Redis at %s:%d', $this->config['host'], $this->config['port']));
      }

      if (isset($this->config['db']) && !$this->_connection->select($this->config['db'])) {
        throw new Exception(sprintf('Could not change to %d for the current connection.', $this->config['db']));
      }

      $this->connected = true;

    } catch (Exception $e) {
      throw new MissingConnectionException(array(
        'class' => get_class($this),
        'message' => $e->getMessage(),
      ));
    }

    return $this->connected;
  }

  public function close() {
    if ($this->connected == true) {
      $this->_connection->close();
      unset($this->_connection);
    }
    $this->connected = false;
    return true;
  }

  public function query($method, $params, $model) {
    return call_user_func_array(array($this->_connection, $method), $params);
  }

  public function listSources($data = null) {
  }
}
