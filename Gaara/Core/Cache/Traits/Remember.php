<?php

declare(strict_types = 1);
namespace Gaara\Core\Cache\Traits;

use Closure;
use Gaara\Core\Cache\Traits;

trait Remember {

	use Traits\Call;

	/**
	 * 按键, 获取一个缓存, 若不存在, 则设置缓存后返回
	 * @param bool $nocache 不读缓存
	 * @param string $key
	 * @param mixed $value
	 * @param int $expire
	 * @return mixed
	 */
	protected function rememberEverythingWithKey(bool $nocache = false, string $key, $value, int $expire = null) {
		if ($nocache)
			return $this->set($key, $value, $expire) ? $this->get($key) : null;
		else
			return ($content = $this->driver->get($key)) ? $this->unserialize($content) :
				($this->set($key, $value, $expire) ? $this->get($key) : null);
	}

	/**
	 * 自动生成键, 获取一个缓存, 若不存在, 则设置缓存后返回
	 * @param bool $nocache 不读缓存
	 * @param Closure $callback
	 * @param int $expire
	 * @return mixed
	 */
	protected function rememberClosureWithoutKey(bool $nocache = false, Closure $callback, int $expire = null) {
		$class = $this->analysisClosure($callback);
		$debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
		$func  = '__';
		foreach ($debug as $v) {
			// 调用类自身调用
			if (isset($v['file']) && strpos($v['file'], str_replace('\\', '/', $class))) {
				$func = 'Closure_' . $v['line'];
				break;
			}
			// 调用类父类调用
			elseif ($v['class'] !== get_class($this)) {
				$class .= '\parent\\' . $v['class'];
				$func  = 'Closure_' . $v['line'];
				break;
			}
		}
		$key = $this->makeKey($class, $func);
		return $this->rememberEverythingWithKey($nocache, $key, $callback, $expire);
	}

	/**
	 * 返回闭包函数的this指向的类名
	 * @param Closure $closure
	 * @return string
	 */
	protected function analysisClosure(Closure $closure): string {
		ob_start();
		var_dump($closure);
		$info = ob_get_contents();
		ob_end_clean();
		$info  = str_replace([" ", "　", "\t", "\n", "\r"], '', $info);
		$class = '';
		\preg_replace_callback("/\[\"this\"\]=>object\((.*?)\)\#/is", function($matches) use (&$class) {
			$class = $matches[1];
		}, $info);
		return $class;
	}

}
