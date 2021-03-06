<?php

declare(strict_types = 1);
namespace Gaara\Core;

use Exception;
use Gaara\Contracts\ServiceProvider\Single;
use Gaara\Core\Request\Traits\{Filter, RequestInfo};
use Gaara\Core\Request\UploadFile;
use Gaara\Core\Route\Component\MatchedRouting;

class Request implements Single {

	use RequestInfo, Filter;

	protected $domain  = [];
	protected $get     = [];
	protected $post    = [];
	protected $put     = [];
	protected $delete  = [];
	protected $options = [];
	protected $head    = [];
	protected $patch   = [];
	protected $input   = [];
	protected $cookie  = [];
	protected $file    = null;

	/**
	 * 初始化参数
	 */
	final public function __construct(UploadFile $UploadFile) {
		$this->RequestInfoInit();
		$this->file = $UploadFile;
	}

	/**
	 * 路由匹配后的参数初始化
	 * @param MatchedRouting $MR
	 */
	public function setMatchedRouting(MatchedRouting $MR) {
		// 存储路由匹配对象
		$this->MatchedRouting = $MR;

		$this->alias   = $MR->alias;
		$this->methods = $MR->methods;

		// 设定参数
		$this->setDomainParameters($MR->domainParamter)->setStaticParameters($MR->staticParamter)->setRequestParameters();
	}

	/**
	 * 获取参数到当前类的属性
	 * @return Request
	 */
	protected function setRequestParameters(): Request {
		$this->cookie = $this->_htmlspecialchars($_COOKIE);

		if (($argc = $this->method) !== 'get') {
			$temp         = file_get_contents('php://input');
			$content_type = $this->contentType;

			if (stripos($content_type, 'application/x-www-form-urlencoded') !== false) {
				parse_str($temp, $this->{$argc});
				$this->{$argc} = $this->filter($this->{$argc});
			}
			elseif (stripos($content_type, 'application/json') !== false) {
				$this->{$argc} = json_decode($temp, true);
			}
			elseif (stripos($content_type, 'application/xml') !== false) {
				$this->{$argc} = obj(Tool::class)->xml_decode($temp);
			}
			else {
				$this->{$argc} = !empty($_POST) ? $this->_htmlspecialchars($_POST) :
					$this->filter($this->getStream($temp));
			}
		}
		$this->get = array_merge($this->get, $this->_htmlspecialchars($_GET));
		$this->consistentFile();
		$this->input = $this->{$argc};
		return $this;
	}

	/**
	 * 预定义的字符转换为 HTML 实体, 预定义的字符是：& （和号）, " （双引号）, ' （单引号）,> （大于）,< （小于）
	 * @param array $arr
	 * @return array
	 */
	protected function _htmlspecialchars(array $arr): array {
		$q = [];
		foreach ($arr as $k => $v) {
			if (is_string($v)) {
				$q[$k] = htmlspecialchars($v);
			}
			elseif (is_array($v)) {
				$q[$k] = $this->_htmlspecialchars($v);
			}
		}
		return $q;
	}

	/**
	 * _addslashes, _htmlspecialchars
	 * @param array $arr
	 * @return array
	 */
	protected function filter(array $arr): array {
		return $this->_addslashes($this->_htmlspecialchars($arr));
	}

	/**
	 * 在预定义字符之前添加反斜杠, 预定义字符是：单引号（'）,双引号（"）, 反斜杠（\）, NULL
	 * 默认地，PHP 对所有的 GET、POST 和 COOKIE 数据自动运行 addslashes()。
	 * 所以您不应对已转义过的字符串使用 addslashes()，因为这样会导致双层转义。
	 * 遇到这种情况时可以使用函数 get_magic_quotes_gpc() 进行检测
	 * @param array $arr
	 * @return array
	 */
	protected function _addslashes(array $arr): array {
		$q = [];
		foreach ($arr as $k => $v) {
			if (is_string($v)) {
				$q[addslashes($k)] = addslashes($v);
			}
			elseif (is_array($v)) {
				$q[addslashes($k)] = $this->_addslashes($v);
			}
		}
		return $q;
	}

	/**
	 * 分析stream获得数据, put文件上传时,php不会帮忙解析信息,只有手动了
	 * @param string $input
	 * @return array
	 */
	protected function getStream(string $input): array {
		$a_data = [];
		// grab multipart boundary from content type header
		preg_match('/boundary=(.*)$/', $this->contentType, $matches);

		// content type is probably regular form-encoded
		if (!count($matches)) {
			// we expect regular puts to containt a query string containing data
			parse_str(urldecode($input), $a_data);
			return $a_data;
		}

		// split content by boundary and get rid of last -- element
		$a_blocks = preg_split("/-+$matches[1]/", $input);
		array_shift($a_blocks);
		array_pop($a_blocks);

		// loop data blocks
		foreach ($a_blocks as $block) {
			// you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char
			// parse uploaded files
			if (strpos($block, 'filename=') !== false) {
				// match "name", then everything after "stream" (optional) except for prepending newlines
				preg_match("/name=\"([^\"]*)\".*filename=\"([^\"].*?)\".*Content-Type:\s+(.*?)[\n|\r|\r\n]+([^\n\r].*)?$/s",
					$block, $matches);
				// 兼容无文件上传的情况
				if (empty($matches))
					continue;
				$content_blob = $matches[4];
				$content_blob = substr($content_blob, 0, strlen($content_blob) - strlen(PHP_EOL) * 2);  // 移除尾部多余换行符
				$this->file->addFile([
					'key_name' => $matches[1],
					'name'     => $matches[2],
					'type'     => $matches[3],
					'size'     => strlen($content_blob),
					'content'  => $content_blob
				]);
			}
			// parse all other fields
			else {
				// match "name" and optional value in between newline sequences
				preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
				$a_data[$matches[1]] = $matches[2] ?? '';
			}
		}
		return $a_data;
	}

	/**
	 * 将$_FILES 放入 $this->file
	 * @return void
	 */
	protected function consistentFile(): void {
		if (!empty($_FILES)) {
			foreach ($_FILES as $k => $v) {
				if ($v['error'] === 0) {
					$this->file->addFile([
						'key_name' => $k,
						'name'     => $v['name'],
						'type'     => $v['type'],
						'tmp_name' => $v['tmp_name'],
						'size'     => $v['size']
					]);
				}
			}
		}
	}

	/**
	 * 设置来自url静态参数(pathInfo参数)
	 * @param array $staticParameters
	 * @return Request
	 */
	protected function setStaticParameters(array $staticParameters = []): Request {
		$this->get = $this->filter($staticParameters);
		return $this;
	}

	/**
	 * 设置来自路由的`域名`参数
	 * @param array $domainParameters
	 * @return Request
	 */
	protected function setDomainParameters(array $domainParameters = []): Request {
		$this->domain = $this->filter($domainParameters);
		return $this;
	}

	/**
	 * 设置cookie, 即时生效
	 * @param string $name
	 * @param array|string $value
	 * @param int $expire
	 * @param string $path
	 * @param string $domain
	 * @param bool $secure
	 * @param bool $httpOnly
	 * @return void
	 */
	public function setcookie(string $name, $value = '', int $expire = 0, string $path = '', string $domain = '', bool $secure = false, bool $httpOnly = true): void {
		$expire              += time();
		$this->cookie[$name] = $_COOKIE[$name] = $value;
		if (is_array($value))
			foreach ($value as $k => $v)
				setcookie($name . '[' . $k . ']', $v, $expire, $path, $domain, $secure, $httpOnly);
		else
			setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
	}

	/**
	 * 获取请求头中的内容
	 * @param string $key
	 * @return string|null
	 */
	public function header(string $key) {
		return $_SERVER[$key] ?? null;
	}

	/**
	 * 获取原始数据数组
	 * @param $property_name
	 * @return mixed
	 * @throws Exception
	 */
	public function __get($property_name) {
		if (in_array(strtolower($property_name), ['input', 'post', 'get', 'put', 'cookie', 'delete', 'file'], true))
			return $this->$property_name;
	}

	/**
	 * 后期添加对应属性
	 * @param string $property_name
	 * @param mixed $value
	 * @return void
	 * @throws Exception
	 */
	public function __set(string $property_name, $value): void {
		if (in_array(strtolower($property_name), ['input', 'post', 'get', 'put', 'cookie', 'delete', 'file'], true)) {
			throw new Exception($property_name . ' should not be modified.');
		}
		else {
			$this->{$property_name} = $value;
		}
	}

}
