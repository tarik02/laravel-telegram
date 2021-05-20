# Laravel Telegram

Telegram Bots framework for Laravel.

## Installation

```
$ composer require tarik02/laravel-telegram
```

Add the following variables to your `.env`:
- `TELEGRAM_BOT_TOKEN` - your bot's telegram token.
- `TELEGRAM_BOT_USERNAME` - your bot's username.

### Option 1. Using laravel-telegram-extra
I recommend you checking out [tarik02/laravel-telegram-extra](https://github.com/Tarik02/laravel-telegram-extra).
```
$ composer require tarik02/laravel-telegram-extra
$ php artisan vendor:publish --provider="Tarik02\\LaravelTelegramExtra\\Providers\\TelegramServiceProvider"
```


### Option 2. Setup yourself
Otherwise you need to create necessary configs and classes yourself:

`app/Telegram/Dispatcher.php`:
```php
<?php

namespace App\Telegram;

use Illuminate\Contracts\Container\Container;
use Tarik02\Telegram\Methods\SendMessage;

use Tarik02\LaravelTelegram\{
    Contracts\Dispatcher as TelegramDispatcher,
    Request,
    Response
};

/**
 * Class Dispatcher
 * @package App\Telegram
 */
class Dispatcher implements TelegramDispatcher
{
    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @param Container $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @return Response|null
     */
    public function dispatch(Request $request): ?Response
    {
        $update = $request->update();

        if (null !== $message = $update->message()) {
            $text = \sprintf(
                "Hello, %s. I received your message with text:\n%s",
                $message->from()->firstName(),
                $message->text() ?? ''
            );

            return Response::reply(
                SendMessage::make()
                    ->withChatId($message->chat()->id())
                    ->withText($text)
            );
        }

        return null;
    }
}
```

`app/Telegram/Kernel.php`:
```php
<?php

namespace App\Telegram;

use Tarik02\LaravelTelegram\Kernel as TelegramKernel;

/**
 * Class Kernel
 * @package App\Telegram
 */
class Kernel extends TelegramKernel
{
    /**
     * @var string[]
     */
    protected array $middleware = [
    ];
}
```

`config/telegram.php`:
```php
<?php

return [
    'bots' => [
        'main' => [
            'token' => env('TELEGRAM_BOT_TOKEN'),
            'username' => env('TELEGRAM_BOT_USERNAME'),
            'dispatcher' => \App\Telegram\Dispatcher::class,

            'webhook' => [
                'path' => '/' . env('TELEGRAM_BOT_TOKEN'),
            ],
        ],
    ],
];
```


### Running bot

You can develop your bot using command long polling technique. To start listening for telegram updates, run the following command:
```bash
$ php artisan telegram:updates:get
```


### Setting up webhooks

For production bots, webhooks are better way to communicate with telegram servers. To use webhooks, first add `Telegram::webhookRoutes()` call to your global routes in `app/Providers/RouteServiceProvider.php`:
```php
<?php
// ...

+ use Telegram;

// ...

  $this->routes(function () {
+             Telegram::webhookRoutes();
```

This helper creates special route for each bot listen in config. Instead, you can manually create webhook route for every bot:
```php
<?php

use Tarik02\LaravelTelegram\Http\Controllers\WebhookController;

Route::post('/some-secret-path-for-example-containing-bot-token')
    ->name('telegram.webhook.main')
    ->uses([WebhookController::class, 'index']);
```

Next, you need to put your webhook to telegram servers. You can do this using special artisan command:
```bash
$ php artisan telegram:webhook:set
```
Note: your application needs to be accessible with https protocol from Internet.

Also, there's two other commands to work with webhooks:
```php
  # Get information about webhook:
$ php artisan telegram:webhook:get-info

# Delete webhook:
$ php artisan telegram:webhook:delete
```


## Multiple bots

This telegram bot framework supports using multiple bots in single application. You can configure multiple bots in configuration:
```php
<?php

return [
    'bots' => [
        'bot1' => [
            'token' => env('TELEGRAM_BOT1_TOKEN'),
            'username' => env('TELEGRAM_BOT1_USERNAME'),
            'dispatcher' => \App\Telegram\Bot1Dispatcher::class,

            'webhook' => [
                'path' => '/' . env('TELEGRAM_BOT1_TOKEN'),
            ],
        ],
        'bot2' => [
            'token' => env('TELEGRAM_BOT2_TOKEN'),
            'username' => env('TELEGRAM_BOT2_USERNAME'),
            'dispatcher' => \App\Telegram\Bot2Dispatcher::class,

            'webhook' => [
                'path' => '/' . env('TELEGRAM_BOT2_TOKEN'),
            ],
        ],
    ],
];
```

Most telegram artisan commands can receive special `--bot=` argument which allows to work with multiple bots.

## License

The project is released under the MIT license. Read the [license](https://github.com/Tarik02/laravel-telegram/blob/master/LICENSE) for more information.
