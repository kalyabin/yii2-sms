# yii2-sms

Набор компонентов для отправки SMS-сообщений из приложения Yii2 через сторонние сервисы типа [websms.ru](http://websms.ru) или [smsc.ru](http://smsc.ru).
Для отправки SMS-сообщений используются SOAP-интерфейсы сервисов.

На данный момент модуль поддерживает только базовые функции:

* отправка сообщений
* получение баланса

## Установка

```bash
composer requre "kalyabin/yii2-sms:*"
```

## Конфигурация приложения

### Пример для ```websms.ru```:

```php
return [
    ...
    'components' => [
        ...
        'smsApi' => [
            'class' => 'kalyabin\sms\components\WebSmsSoapApi',
            'wsdl' => 'http://smpp3.websms.ru:8183/soap?WSDL',
            'login' => '<login>', // логин, выданный в websms.ru
            'password' => '<password>', // пароль, выданный в websms.ru
            'useHttpAuthorization' => false,
            'useTestingMode' => false, // включить или отключить тестовый режим отправки
        ],
        ...
    ],
    ...
];
```

### Пример для ```smsc.ru```:

```php
return [
    ...
    'components' => [
        ...
        'smsApi' => [
            'class' => 'kalyabin\sms\components\SmsCSoapApi',
            'wsdl' => 'http://smsc.ru/sys/soap.php?wsdl',
            'login' => '<login>', // логин, выданный в smsc.ru
            'password' => '<password>', // пароль, выданный в smsc.ru
            'useHttpAuthorization' => false
        ],
        ...
    ],
    ...
];
```

## Использование

### Запрос баланса

```php
echo 'Баланс на счёте SMS: ' . Yii::$app->smsApi->getBalance();
```

### Отправка SMS

Простая отправка с отправителем по умолчанию (настраивается в профиле сервиса):

```php
// номер телефона, на который отправить сообщение
// поддерживается любой формат, доступный в вышеуказанных сервисах
$to = '71231231111';
// текст сообщения
$text = 'Код подтверждения: 123123';

$result = Yii::$app->smsApi->sendSms($to, $text);

if ($result->isSent) {
    echo 'SMS успешно отправлено';
} else {
    echo 'Не удалось отправить SMS. Дамп ответа от сервиса: ' . var_dump($result->rawProviderData);
}
```

Отправка с другим отправителем:

```php
// имя отправителя
$from = 'MY-SERVICE';
// номер телефона, на который отправить сообщение
// поддерживается любой формат, доступный в вышеуказанных сервисах
$to = '71231231111';
// текст сообщения
$text = 'Код подтверждения: 123123';

$result = Yii::$app->smsApi->sendSmsFrom($from, $to, $text);

if ($result->isSent) {
    echo 'SMS успешно отправлено';
} else {
    echo 'Не удалось отправить SMS. Дамп ответа от сервиса: ' . var_dump($result->rawProviderData);
}
```

## TODO

* Логирование и разбор полётов
* Расширение каждого отдельно взятого сервиса
* Подключение сервиса sms.ru
