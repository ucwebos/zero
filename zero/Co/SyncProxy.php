<?php

namespace Zero\Co;

use Zero\Container;

class SyncProxy {
	/**
	 * @var \Swoole\Server $srv
	 */
	private $srv;
	/**
	 * 类名
	 * @var string $class
	 */
	protected $class = '';
	/**
	 * 实例化参数
	 * @var array $args
	 */
	protected $args = [];
	/**
	 * @var float $timeout
	 */
	private $timeout = 5;

	public function __construct() {
		if (!$this->class) {
			throw new \Exception('proxy class is not allowed to be null!', 500);
		}
		$this->srv = app()->get(C_SRV);
	}

	/**
	 * @param       $class
	 * @param array $args
	 * @return object
	 */
	public function coProxy($class, $args = []) {
		$this->class = $class;
		$this->args  = $args;
		return $this;
	}

	/**
	 * @param $timeout //秒 支持小数
	 * @return $this
	 */
	public function setTimeout($timeout) {
		$this->timeout = $timeout;
		return $this;
	}

	public function __call($name, $arguments) {
		if (!isCo() || !$this->srv) {
			$obj = new $this->class(...$this->args);
			return call_user_func_array([$obj, $name], $arguments);
		}
		$task    = [
			$this->class,
			$this->args,
			$name,
			$arguments
		];
		$results = $this->srv->taskCo([$task], $this->timeout);
		$result  = $results[0];
		return $result;
	}
}
