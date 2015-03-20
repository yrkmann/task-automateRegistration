<?php

/**
 * Common class for application.
 */
class App {

	private static $curl_handlers = array();

	private static $imap_search_since = null;

	private static $alerts = array();

	private static $logs = array();

	/**
	 * Initiates application.
	 */
	public static function init() {
		if (!extension_loaded('curl')) {
			self::alert('CURL extension not loaded.');
		}
		if (!extension_loaded('imap')) {
			self::alert('IMAP extension not loaded.');
		}
		register_shutdown_function(array(__CLASS__, 'closeCurlHandlers'));
	}

	/**
	 * Close CURL handlers.
	 */
	public static function closeCurlHandlers() {
		foreach (self::$curl_handlers as $k => $ch) if ($ch) {
			curl_close($ch);
		}
	}

	/**
	 * Set since time for IMAP search.
	 */
	public static function setImapSearchSince() {
		self::$imap_search_since = date('d-M-Y H:i O (T)', strtotime(APP_SERVICE_MAIL_SINCE));
	}

	/**
	 * Get since time for IMAP search.
	 */
	public static function getImapSearchSince() {
		return self::$imap_search_since;
	}

	/**
	 * Write message to alerts log.
	 *
	 * @param string $message
	 */
	public static function alert($message) {
		self::$alerts[] = array('time' => time(), 'message' => $message);
	}

	/**
	 * Get alerts.
	 *
	 * @return array
	 */
	public static function alerts() {
		return self::$alerts;
	}

	/**
	 * Write message to log.
	 *
	 * @param string $message
	 */
	public static function log($message) {
		self::$logs[] = array('time' => time(), 'message' => $message);
	}

	/**
	 * Get log messages.
	 *
	 * @return array
	 */
	public static function logs() {
		return self::$logs;
	}

	/**
	 * Common output.
	 */
	public static function response() {
		echo ob_get_clean();
	}

	/**
	 * Output template file.
	 *
	 * @param string $___template Template filename.
	 * @param array $___vars Custom variables passed to scope of template.
	 */
	public static function includeTemplate($___template, $___vars) {
		if (is_array($___vars)) {
			extract($___vars, EXTR_SKIP);
		}
		ob_start();
		include APP_DIR . '/tpl/' . pathinfo($___template, PATHINFO_BASENAME) . '.php';
		echo ob_get_clean();
	}

	/**
	 * Generates random password string.
	 *
	 * @return string
	 */
	public static function makeServicePassword() {
		$result = '';
		for ($i = 0; $i < 12; $i++) {
			$result .= mt_rand(0, 1) ? chr(mt_rand(ord('a'), ord('z'))) : chr(mt_rand(ord('A'), ord('Z')));
		}
		return $result;
	}

	/**
	 * Executes HTTP request.
	 * @param string $method HEAD|GET|POST|PUT|DELETE or custom value
	 * @param string $url
	 * @param string|array $vars Post fields.
	 * @return mixed
	 */
	public static function http_request($method, $url, $vars = null) {
		$method = mb_strtoupper($method);

		if (!array_key_exists($method, self::$curl_handlers)) {
			self::$curl_handlers[$method] = curl_init();
		}

		if (is_array($vars)) $vars = http_build_query($vars, '', '&');

		// set request method
		switch ($method) {
			case 'HEAD':
				curl_setopt(self::$curl_handlers[$method], CURLOPT_NOBODY, true);
				break;
			case 'GET':
				curl_setopt(self::$curl_handlers[$method], CURLOPT_HTTPGET, true);
				break;
			case 'POST':
				curl_setopt(self::$curl_handlers[$method], CURLOPT_POST, true);
				break;
			default:
				curl_setopt(self::$curl_handlers[$method], CURLOPT_CUSTOMREQUEST, $method);
				break;
		}

		// set reqest options
		curl_setopt(self::$curl_handlers[$method], CURLOPT_URL, $url);
		curl_setopt(self::$curl_handlers[$method], CURLOPT_REFERER, $url);
		if (!empty($vars)) {
			curl_setopt(self::$curl_handlers[$method], CURLOPT_POSTFIELDS, $vars);
		}

		// default options
		curl_setopt_array(self::$curl_handlers[$method], array(
			CURLOPT_HEADER => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_FAILONERROR => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
		));

		self::log('CURL ' . $method . ' ' . $url);
		if (is_array($vars)) {
			foreach ($vars as $k => $v) {
				if (is_string($v)) {
					self::log('FIELD ' . $k . ': ' . urldecode($v));
				} else {
					self::log('FIELD ' . $k . ': ' . urldecode($v));
				}
			}
		}

		$result = curl_exec(self::$curl_handlers[$method]);
		if (curl_errno(self::$curl_handlers[$method])) {
			self::log(curl_error(self::$curl_handlers[$method]));
		}

		return $result;
	}

	/**
	 * Executes HTTP HEAD request.
	 * @param string $url
	 * @param string|array $vars Post fields.
	 * @return mixed
	 */
	public static function http_head($url, $vars = null) {
		return self::http_request('HEAD', $url, $vars);
	}

	/**
	 * Executes HTTP GET request.
	 * @param string $url
	 * @param string|array $vars Query params.
	 * @return mixed
	 */
	public static function http_get($url, $vars = null) {
		if (!empty($vars)) {
			$url .= (mb_stripos($url, '?') !== false) ? '&' : '?';
			$url .= (is_string($vars)) ? $vars : http_build_query($vars, '', '&');
		}
		return self::http_request('GET', $url, $vars);
	}

	/**
	 * Executes HTTP POST request.
	 * @param string $url
	 * @param string|array $vars Post fields.
	 * @return mixed
	 */
	public static function http_post($url, $vars = null) {
		return self::http_request('POST', $url, $vars);
	}

	/**
	 * Executes HTTP PUT request.
	 * @param string $url
	 * @param string|array $vars Post fields.
	 * @return mixed
	 */
	public static function http_put($url, $vars = null) {
		return self::http_request('PUT', $url, $vars);
	}

	/**
	 * Executes HTTP DELETE request.
	 * @param string $url
	 * @param string|array $vars Post fields.
	 * @return mixed
	 */
	public static function http_delete($url, $vars = null) {
		return self::http_request('DELETE', $url, $vars);
	}

	/**
	 * Creates account on remote service.
	 *
	 * @param string $email Account email.
	 * @param string $password Account password.
	 * @return boolean
	 */
	public static function createServiceAccount($email, $password) {
		self::log('Begin task: Create service account');

		$result = false;
		do {
			$body = self::http_post(APP_SERVICE_FORM_URL, array(
				'register[email]' => $email,
				'register[password]' => $password,
				'register[password2]' => $password,
				'register[rules]' => '1',
			));
			if (!$body) {
				break;
			}
			if (!preg_match('#' . preg_quote(APP_SERVICE_ACCOUNT_CREATED_TAG, '#') . '#uis', $body)) {
				self::log('Text tag on result page was not found');
				break;
			} else {
				self::log('Text tag on result page found');
			}
			$result = true;

		} while(false);

		if (!$result) {
			self::log('Result: Account was not created');
		} else {
			self::log('Result: Account was successfully created');
		}

		self::log('End task: Create service account');
		return $result;
	}

	/**
	 * Activates account on remote service.
	 *
	 * @param string $email Account email.
	 * @param string $pass IMAP password for account email.
	 * @return boolean
	 */
	public static function activateServiceAccount($email, $pass) {
		self::log('Begin task: Activate service account');

		$result = false;
		do {
			if (preg_match('#@gmail\.com$#isu', $email)) {
				$imap_mailbox = '{imap.gmail.com:993/imap/ssl}INBOX';
			} else if (preg_match('#@mail\.ru$#isu', $email)) {
				$imap_mailbox = '{imap.mail.ru:993/imap/ssl}INBOX';
			} else {
				self::log('Work scheme for this email provider is not implemented yet');
				break;
			}

			self::log('IMAP open ' . $imap_mailbox);
			$imap_stream = imap_open($imap_mailbox, $email, $pass, OP_READONLY, 1);
			if (!$imap_stream) {
				self::log(imap_last_error());
				break;
			}

			$imap_search_query = 'FROM "' . APP_SERVICE_MAIL_FROM . '" SINCE "' . self::$imap_search_since . '"';
			self::log('IMAP search: ' . $imap_search_query);
			$imap_result = imap_search($imap_stream, $imap_search_query, SE_UID, 'UTF-8');
			if (!$imap_result) {
				self::log('Nothing found');
				break;
			}

			self::log('Found ' . count($imap_result) . ' letters');
			sort($imap_result, SORT_NUMERIC);
			$imap_result = array_reverse($imap_result);

			$url = null;
			foreach ($imap_result as $uid) {
				$imap_body = imap_body($imap_stream, $uid, FT_UID | FT_PEEK);
				$m = null;
				if (preg_match('#\shref="(https?://' . preg_quote(APP_SERVICE_MAIL_LINK_ACTIVATE_TAG, '#') . '.*)"#isuU', $imap_body, $m) && !empty($m[1])) {
					$url = $m[1];
					break;
				}
			}

			if (!empty($url)) {
				self::log('Activation link found');
			} else {
				self::log('Activation link not found');
				break;
			}

			self::log('IMAP close');
			imap_close($imap_stream);

			$body = self::http_get($url);
			if (!$body) {
				break;
			}

			if (!preg_match('#' . preg_quote(APP_SERVICE_ACCOUNT_ACTIVATED_TAG1, '#') . '|' . preg_quote(APP_SERVICE_ACCOUNT_ACTIVATED_TAG2, '#') . '#uis', $body)) {
				self::log('Text tag on result page was not found');
				self::log('Result: Account was not activated');
				break;
			} else {
				self::log('Text tag on result page found');
				self::log('Result: Account was successfully activated');
			}

			$result = true;

		} while (false);

		self::log('End task: Activate service account');

		return $result;
	}

}