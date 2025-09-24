```json
"repositories": [
{
"type": "vcs",
"url": "https://github.com/Propaganistas/Laravel-Sms"
}
]
```

```
composer require propaganistas/laravel-sms
```

Install vendor packages as needed:

| Provider    | Package                                     |
|-------------|---------------------------------------------|
| Amazon SNS  | `composer require aws/aws-sdk-php`          |
| MessageBird | `composer require messagebird/php-rest-api` |

```
php artisan vendor:publish --tag=laravel-sms
```