# partner-api-client

## Пример

```php
$api_client = new ApiClient();

$login = ''; // Логин пользователя в click.ru
$password = ''; // Пароль пользователя в click.ru

try {
    // 1. Авторизуемся
	$api_client->login($login, $password);
	// 2. Получаем список аккаунтов
	$accounts = $api_client->getAccounts();

	// Массив аккаунтов яндекса
	$yandex_client_logins = [];
	foreach ($accounts as $account) {
		echo "{$account['name']}\n";

		if ($account['service'] == 'DIRECT') {
			$yandex_client_logins[] = $account['service_login'];
		}
	}

	foreach ($yandex_client_logins as $client_login) {
		// Получить информацию о кампаниях из директа
		$response = $api_client->queryYandexDirect(
			$client_login,
			'https://api.direct.yandex.com/json/v5/campaigns',
			[
				'method' => 'get',
				'params' => [
					'SelectionCriteria' => [
						'Types' => ['TEXT_CAMPAIGN']
					],
					'FieldNames' => ['Id']
				],
			]

		);

		var_dump($response);

		// Получить отчет о кампаниях из директа
		$response = $api_client->queryYandexDirect(
			$client_login,
			'https://api.direct.yandex.com/json/v5/reports',
			[
				'params' => [
					"SelectionCriteria" => [
						"DateFrom" => "2018-06-01",
						"DateTo" => "2018-06-30"
					],
					"FieldNames" => ["Date", "CampaignName", "LocationOfPresenceName", "Impressions", "Clicks", "Cost"],
					"ReportName" => "НАЗВАНИЕ_ОТЧЕТА",
					"ReportType" => "CAMPAIGN_PERFORMANCE_REPORT",
					"DateRangeType" => "CUSTOM_DATE",
					"Format" => "TSV",
					"IncludeVAT" => "NO",
					"IncludeDiscount" => "NO"
				],
			]

		);

		var_dump($response);
	}
} catch (Exception $e) {
    echo $e->getMessage()."\n";
}
```
