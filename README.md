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

    foreach ($accounts as $account) {
        echo "{$account['name']}\n";
    }

} catch (Exception $e) {
    echo $e->getMessage()."\n";
}
```
