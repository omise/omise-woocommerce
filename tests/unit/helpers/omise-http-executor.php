<?php

class Omise_Http_Executor implements OmiseHttpExecutorInterface
{
  public static $instance = null;
  private $fixture_name = null;

  public static function get_instance()
  {
    if (self::$instance === null) {
      self::$instance = new Omise_Http_Executor();
    }

    return self::$instance;
  }

  public function set_fixture_name($name) {
    $this->fixture_name = $name;
  }

  public function execute($url, $requestMethod, $key, $params = null)
  {
    return $this->load_fixture($this->fixture_name);
  }

  /**
   * Load the JSON file in `fixtures` directory to return as the response
   * @param string $name
   */
  private function load_fixture($name)
  {
    return file_get_contents(__DIR__ . "/../../fixtures/{$name}.json");
  }
}

