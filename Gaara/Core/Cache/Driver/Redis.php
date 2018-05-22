<?php

declare(strict_types = 1);
namespace Gaara\Core\Cache\Driver;

use Gaara\Core\Cache\DriverInterface;
use redis as php_redis;

class Redis implements DriverInterface {

	private $handler = null;

	/**
	 * redis链接
	 * @param array $options
	 */
	public function __construct(array $options = []) {
		$this->handler	 = new php_redis();
		$connect		 = (CLI === true) ? 'pconnect' : 'connect';
		$this->handler->$connect(
		$options['host'] ?? '127.0.0.1', (int) ($options['port'] ?? 6379)
		);
		if (isset($options['passwd']) && !empty($options['passwd']))
			$this->handler->auth($options['passwd']);
	}

	/**
	 * 读取缓存
	 * @param string $key 键
	 * @return string|false
	 */
	public function get(string $key) {
		return $this->handler->get($key);
	}

	/**
	 * 设置缓存
	 * @param string $key 键
	 * @param string $value 值
	 * @param int $expire 缓存有效时间 , -1表示无过期时间
	 * @return bool
	 */
	public function set(string $key, string $value, int $expire): bool {
		return ($expire === -1) ? $this->handler->set($key, $value) : $this->handler->setex($key, $expire, $value);
	}

	/**
	 * 删除单一缓存
	 * @param string $key 键
	 * @return bool
	 */
	public function rm(string $key): bool {
		return $this->handler->delete($key) === 0 ? false : true;
	}

	/**
	 * 批量清除缓存
	 * 以scan替代keys, 解决大数据时redis堵塞的问题, 但是存在数据不准确(清除数据不完整)的情况
	 * @param string $key
	 * @return bool
	 */
	public function clear(string $key): bool {
		$it			 = \null; /* Initialize our iterator to NULL */
		$type		 = 1;
		while ($arr_keys	 = $this->handler->scan($it, $key . '*', 10000)) {
			foreach ($arr_keys as $str_key) {
				$type &= $this->handler->delete($str_key);
			}
		}
		return $type === 1 ? true : false;
	}

	/**
	 * 得到一个key的剩余有效时间
	 * @param string $key
	 * @return int 0表示过期, -1表示无过期时间, -2表示未找到key
	 */
	public function ttl(string $key): int {
		return $this->handler->ttl($key);
	}

	public function __call(string $func, array $pars = []) {
		return call_user_func_array([$this->handler, $func], $pars);
	}

}