<?php

/**
 * Пример реализации работы с API click.ru
 *
 * @see https://api-b2b.click.ru/help
 */
class ApiClient
{
	/**
	 * Базовый URL для API.
	 */
	public const BASE_URL = 'https://api-b2b.click.ru/';

	/**
	 * Название HTTP заголовка для токена.
	 */
	private const HTTP_HEADER_TOKEN = 'clicktoken';

	/**
	 * Название HTTP заголовка для id пользователя.
	 */
	private const HTTP_HEADER_UID = 'uid';

	/**
	 * Значение токена из HTTP заголовка.
	 *
	 * @var string
	 */
	private $apiToken = '';

	/**
	 * Id пользователя из HTTP заголовка.
	 *
	 * @var int
	 */
	private $uid = 0;

	/**
	 * Авторизация в click.ru.
	 *
	 * @see http://api-b2b.click.ru/help/?class=User&function=userlogin
	 *
	 * @param string $login    Логин пользователя.
	 * @param string $password Пароль пользователя.
	 *
	 * @throws Exception
	 */
	public function login(string $login, string $password): void
	{
		$data = [
			'login'    => $login,
			'password' => $password,
		];

		$result = $this->send('login', 'POST', $data);

		if (!isset($result['errno']) || $result['errno'] != 'OK') {
			throw new Exception(sprintf('Error: %s', json_encode($result)));
		}
	}

	/**
	 * Получить список аккаунтов.
	 *
	 * @see http://api-b2b.click.ru/help/?class=Click&function=get_click_accounts
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getAccounts(): array
	{
		$result = $this->send('get_click_accounts', 'POST');

		if (!isset($result['errno']) || $result['errno'] != 'OK') {
			throw new Exception(sprintf('Error: %s', json_encode($result)));
		}

		return $result['data'];
	}

	/**
	 * Выполнить запрос к Яндекс.Директ.
	 *
	 * @param string $client_login Логин пользователя в Яндекс.Директ
	 * @param string $url          Адрес сервиса для отправки запросов (регистрозависимый).
	 * @param array  $params       Параметры запроса к серверу API Директа.
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function queryYandexDirect($client_login, $url, array $params = [])
	{
		$data = [
			'client_login' => $client_login,
			'url'          => $url,
			'params'       => $params,
		];

		$result = $this->send('query_yandex_direct', 'POST', $data);

		if (!isset($result['errno']) || $result['errno'] != 'OK') {
			throw new Exception(sprintf('Error: %s', json_encode($result)));
		}

		return $result['data'];
	}

	/**
	 * Отправить запрос в API.
	 *
	 * @param string $endpoint Название вызываемого метода.
	 * @param string $method   Метод которым отправить данные (POST, GET).
	 * @param array  $data     Данные которые отправить.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function send(string $endpoint, string $method, array $data = []): array
	{
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL            => self::BASE_URL.$endpoint,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER         => true,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => $method,
			CURLOPT_POSTFIELDS     => http_build_query($data),
			CURLOPT_HTTPHEADER     => $this->prepareHeaders(),
			CURLOPT_SSL_VERIFYPEER => false,
		]);
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
			throw new Exception($err);
		}

		list($headers, $body) = explode("\r\n\r\n", $response, 2);

		if (empty($this->apiToken) || empty($this->uid)) {
			$headers = explode("\n", $headers);
			array_shift($data);

			foreach ($headers as $header) {
				$middle = explode(":", $header);
				$key = isset($middle[0]) ? trim($middle[0]) : '';
				$value = isset($middle[1]) ? trim($middle[1]) : '';

				switch ($key) {
					case self::HTTP_HEADER_TOKEN:
						$this->apiToken = $value;
						break;

					case self::HTTP_HEADER_UID:
						$this->uid = $value;
						break;
				}
			}
		}

		return json_decode($body, true);
	}

	/**
	 * Подготовить заголовки для отправки запроса к API.
	 *
	 * @return array
	 */
	private function prepareHeaders(): array
	{
		$headers = [];

		if (!empty($this->apiToken) && !empty($this->uid)) {
			$headers[] = "clicktoken: {$this->apiToken}";
			$headers[] = "uid: {$this->uid}";
		}

		return $headers;
	}
}
