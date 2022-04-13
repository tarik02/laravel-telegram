<?php

namespace Tarik02\LaravelTelegram\Api;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Client\Factory as HttpClient;
use Tarik02\Telegram\Methods\Method;
use Tarik02\Telegram\Traits\CallsMethods;

use Tarik02\LaravelTelegram\{
    Contracts\TelegramApi,
    Events\MethodCalled,
    Events\MethodCalling,
    Exceptions\TelegramApiRequestException,
    ExceptionFactory
};

/**
 * Class BaseTelegramApi
 * @package Tarik02\LaravelTelegram\Api
 */
abstract class BaseTelegramApi implements TelegramApi
{
    use CallsMethods;

    /**
     * @var string
     */
    protected string $token;

    /**
     * @var Dispatcher
     */
    protected Dispatcher $dispatcher;

    /**
     * @param HttpClient $http
     * @param string $token
     * @param Dispatcher $dispatcher
     * @return void
     */
    public function __construct(string $token, Dispatcher $dispatcher)
    {
        $this->token = $token;
        $this->dispatcher = $dispatcher;
    }
    /**
     * @param Method $method
     * @return mixed
     */
    final public function call(Method $method)
    {
        $event = new MethodCalling($method);
        $this->dispatcher->dispatch($event);
        $method = $event->method;

        $payload = $method->toPayload();

        $response = $this->performJsonRequest(
            'POST',
            $this->buildUrlForMethod($method),
            $payload
        );

        if (! \is_array($response) || ! ($response['ok'] ?? false) || ! isset($response['result'])) {
            if (isset($response['description']) && \is_string($response['description'])) {
                throw (new ExceptionFactory)->createTelegramException(
                    $this,
                    $method,
                    $response['error_code'] ?? 0,
                    $response['description']
                );
            }

            throw new TelegramApiRequestException($this, $payload, $response['description'] ?? '', $response['error_code'] ?? 0);
        }

        $event = new MethodCalled($method, $method::createResponse($response['result']));
        $this->dispatcher->dispatch($event);
        return $event->result;
    }

    /**
     * @param Method $method
     * @return string
     */
    protected function buildUrlForMethod(Method $method): string
    {
        return \sprintf(
            '/bot%s/%s',
            $this->token,
            $method->methodName()
        );
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $data
     * @return array
     */
    abstract protected function performJsonRequest(string $method, string $url, array $data): array;
}
