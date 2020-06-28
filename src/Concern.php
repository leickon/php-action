<?php

namespace Leickon\Action;

use StdClass;
use Leickon\Action\RequiredException;

trait Concern {
  /**
   * Define a required parameter
   * 
   * @param string $param Parameter name
   * 
   * @api
   * @return void
   */
  protected function require(string $param) {
    $this->params[$param] = [false, null];
  }

  /**
   * Define an optional parameter
   * 
   * @param string $param Parameter name
   * @param any $default Alternative value
   * 
   * @api
   * @return void
   */
  protected function optional(string $param, $default = null) {
    $this->params[$param] = [true, $default];
  }

  /**
   * Bind attributes to action object
   * 
   * @param array $input Values to be used by procedure
   * 
   * @internal
   * @return void
   * @throws RequiredException
   */
  private function setup(array $input) {
    foreach($this->params as $name => [$optional, $default]) {
      $exists = array_key_exists($name, $input);

      if(!$exists && !$optional) {
        throw new RequiredException("{ $name : required }");
      }

      $this->$name = !$exists ? $default : $input[$name];
    }
  }

  /**
   * Start the action procedure
   * 
   * @param array $input Values to be used by procedure
   * 
   * @api
   * @return StdClass
   */
  public static function call(array $input = []) {
    $instance = new static();
    $result = new StdClass();
    $instance->init();
    $instance->setup($input);

    try {
      $result->success = true;
      $result->value = $instance->define();
    } catch(Failure $exc) {
      $result->success = false;
      $result->error = $exc->value;
    }

    $result->failure = !$result->success;
    return $result;
  }
}