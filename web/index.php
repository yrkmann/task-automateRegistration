<?php

define('APP_SERVICE_FORM_URL', 'https://ssl.olx.by/account/register/');
define('APP_SERVICE_MAIL_FROM', 'noreply@olx.by');
define('APP_SERVICE_MAIL_SINCE', '-5 minutes');
define('APP_SERVICE_MAIL_LINK_ACTIVATE_TAG', 'olx.by/account/confirm/');
define('APP_SERVICE_ACCOUNT_CREATED_TAG', 'Сейчас вы должны активировать ваш аккаунт');
define('APP_SERVICE_ACCOUNT_ACTIVATED_TAG1', 'Пароль создан успешно');
define('APP_SERVICE_ACCOUNT_ACTIVATED_TAG2', 'Этот аккаунт уже активирован');

require_once __DIR__ . '/../lib/bootstrap.php';

$input = array(
	'service_pass' => App::makeServicePassword(),
);
$messages = array();

do {

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		if (in_array($_REQUEST['action'], array('register', 'create'))) {

			$input['service_user'] = filter_input(INPUT_POST, 'service_user', FILTER_SANITIZE_EMAIL);
			if (!$input['service_user']) {
				$messages[] = array('type' => 'warning', 'message' => 'Bad email address.');
				break;
			}

			$input['service_pass'] = filter_input(INPUT_POST, 'service_pass');
			if (!$input['service_pass']) {
				$messages[] = array('type' => 'warning', 'message' => 'Bad password.');
				break;
			}

			App::setImapSearchSince();

			if (!App::createServiceAccount($input['service_user'], $input['service_pass'])) {
				$messages[] = array('type' => 'danger', 'message' => 'Account was not created.');
				break;
			};

			$messages[] = array('type' => 'success', 'message' => 'Account was successfully created.');

			if (in_array($_REQUEST['action'], array('register'))) {
				App::log('Sleep 5s');
				sleep(5);
			}

		}

		if (in_array($_REQUEST['action'], array('register', 'activate'))) {

			$since = App::getImapSearchSince();
			if (empty($since)) {
				App::setImapSearchSince();
			}

			$input['service_user'] = filter_input(INPUT_POST, 'service_user', FILTER_SANITIZE_EMAIL);
			if (!$input['service_user']) {
				$messages[] = array('type' => 'warning', 'message' => 'Bad email address.');
				break;
			}

			$input['imap_pass'] = filter_input(INPUT_POST, 'imap_pass');
			if (!$input['imap_pass']) {
				$messages[] = array('type' => 'warning', 'message' => 'Bad email password for IMAP access.');
				break;
			}

			if (!App::activateServiceAccount($input['service_user'], $input['imap_pass'])) {
				$messages[] = array('type' => 'danger', 'message' => 'Account was not activated.');
				break;
			};

			$messages[] = array('type' => 'success', 'message' => 'Account was successfully activated.');

		}

	}

} while(false);

App::includeTemplate('page', array('input' => $input, 'messages' => $messages));

App::response();