<?php

declare(strict_types = 1);
namespace Gaara\Core\Cache\Driver;

use Gaara\Contracts\Cache\DriverInterface;
use Gaara\Core\Conf;
use redis as php_redis;

class Redis implements DriverInterface {

	// 当前redis连接名
	public $connection;
	// php拓展的redis对象
	protected $driver;

	/**
	 * Redis constructor.
	 * @param string $connection redis连接名
	 * @throws \Gaara\Exception\BindingResolutionException
	 * @throws \ReflectionException
	 */
	public function __construct(string $connection) {
		$this->connection = $connection;
		// 取连接
		$options = obj(Conf::class)->getDriverConnection('redis', $connection);

		// 连接对象
		$this->driver = new php_redis();

		// 连接类型
		$connect = (app()->cli === true) ? 'pconnect' : 'connect';

		// ip 端口
		$this->driver->$connect($options['host'] ?? '127.0.0.1', (int)(($options['port'] ?? 6379)));

		// 密码
		if (isset($options['password']) && !empty($options['password']))
			$this->driver->auth($options['passwd']);

		// 数据库
		$this->driver->select((int)($options['database'] ?? 0));
	}

	/**
	 * 读取缓存
	 * @param string $key 键
	 * @return string|false
	 */
	public function get(string $key) {
		return $this->driver->get($key);
	}

	/**
	 * 设置缓存
	 * @param string $key 键
	 * @param string $value 值
	 * @param int $expire 缓存有效时间 , -1表示无过期时间
	 * @return bool
	 */
	public function set(string $key, string $value, int $expire): bool {
		return ($expire === -1) ? $this->driver->set($key, $value) : $this->driver->setex($key, $expire, $value);
	}

	/**
	 * 删除单一缓存
	 * @param string $key 键
	 * @return bool
	 */
	public function rm(string $key): bool {
		return $this->driver->del($key) === 0 ? false : true;
	}

	/**
	 * 批量清除缓存
	 * 以scan替代keys, 解决大数据时redis堵塞的问题, 但是存在数据不准确(清除数据不完整)的情况
	 * @param string $key
	 * @return bool
	 */
	public function clear(string $key): bool {
		$it   = null; /* Initialize our iterator to NULL */
		$type = 1;
		while ($arr_keys = $this->driver->scan($it, $key . '*', 10000)) {
			foreach ($arr_keys as $str_key) {
				$type &= $this->driver->delete($str_key);
			}
		}
		return $type === 1;
	}

	/**
	 * 得到一个key的剩余有效时间
	 * @param string $key
	 * @return int 0表示过期, -1表示无过期时间, -2表示未找到key
	 */
	public function ttl(string $key): int {
		return $this->driver->ttl($key);
	}

	/**
	 * 自增 (原子性)
	 * @param string $key
	 * @param int $step
	 * @return int 自增后的值
	 */
	public function increment(string $key, int $step = 1): int {
		return $this->driver->incrby($key, $step);
	}

	/**
	 * 自减 (原子性)
	 * @param string $key
	 * @param int $step
	 * @return int 自减后的值
	 */
	public function decrement(string $key, int $step = 1): int {
		return $this->driver->decrby($key, $step);
	}

	public function __call(string $func, array $pars = []) {
		return call_user_func_array([$this->driver, $func], $pars);
	}

}
