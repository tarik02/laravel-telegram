<?php

namespace Tarik02\LaravelTelegram\Http\Controllers;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Events\Dispatcher;
use Illuminate\Routing\Controller;
use Response;
use Tarik02\LaravelTelegram\Http\Requests\WebhookRequest;
use Tarik02\Telegram\Entities\Update;
use Throwable;

use Illuminate\Http\{
    JsonResponse,
    Response as HttpResponse
};
use Tarik02\LaravelTelegram\{
    Contracts\Bot,
    Contracts\BotFactory,
    Contracts\Kernel,
    Contracts\RequestFactory,
    Events\WebhookMethodCalling,
    Response as TelegramResponse,
    ResponseWithReply,
    Telegram
};

/**
 * Class WebhookController
 * @package Tarik02\LaravelTelegram\Http\Controllers
 */
class WebhookController extends Controller
{
    /**
     * @var Kernel
     */
    protected Kernel $kernel;

    /**
     * @var BotFactory
     */
    protected BotFactory $botFactory;

    /**
     * @var RequestFactory
     */
    protected RequestFactory $requestFactory;

    /**
     * @var ExceptionHandler
     */
    protected ExceptionHandler $exceptionHandler;

    /**
     * @var Dispatcher
     */
    protected Dispatcher $dispatcher;

    /**
     * @param Telegram $telegram
     * @param Kernel $kernel
     * @param BotFactory $botFactory
     * @param RequestFactory $requestFactory
     * @param ExceptionHandler $exceptionHandler
     * @param Dispatcher $dispatcher
     * @return void
     */
    public function __construct(
        Kernel $kernel,
        BotFactory $botFactory,
        RequestFactory $requestFactory,
        ExceptionHandler $exceptionHandler,
        Dispatcher $dispatcher
    ) {
        $this->kernel = $kernel;
        $this->botFactory = $botFactory;
        $this->requestFactory = $requestFactory;
        $this->exceptionHandler = $exceptionHandler;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param WebhookRequest $webhookRequest
     * @return HttpResponse|JsonResponse
     */
    public function index(WebhookRequest $webhookRequest)
    {
        $bot = $this->resolveBotByWebhookRequest($webhookRequest);

        $update = Update::fromPayload($webhookRequest->all());

        $request = $this->requestFactory->create($bot, $update);

        return $this->kernel->withRequest($request, function () use ($request, $bot) {
            $response = $this->kernel->handle($request);

            return $this->convertTelegramResponseToHttpResponse($bot, $response);
        });
    }

    /**
     * @param WebhookRequest $request
     * @return Bot
     */
    protected function resolveBotByWebhookRequest(WebhookRequest $request): Bot
    {
        return $this->botFactory->createBot(
            $this->resolveBotNameByWebhookRequest($request)
        );
    }

    /**
     * @param WebhookRequest $request
     * @return string
     */
    protected function resolveBotNameByWebhookRequest(WebhookRequest $request): string
    {
        return \preg_replace(
            '/^telegram\.webhook\./',
            '',
            $request->route()->getName()
        );
    }

    /**
     * @param Bot $bot
     * @param TelegramResponse|null $response
     * @return HttpResponse|JsonResponse
     */
    protected function convertTelegramResponseToHttpResponse(Bot $bot, ?TelegramResponse $response)
    {
        if ($response instanceof ResponseWithReply) {
            if (count($response->getReplies()) === 1) {
                $reply = $response->getReplies()[0];

                $callWithWebhook = $bot->getApi()->canCallMethodWithWebhookResponse($reply);

                if ($callWithWebhook) {
                    $event = new WebhookMethodCalling($reply);
                    $this->dispatcher->dispatch($event);
                    $reply = $event->method;

                    $callWithWebhook = $event->callWithWebhook;
                }

                if ($callWithWebhook) {
                    return Response::json(\array_merge(
                        $reply->toPayload(),
                        [
                            'method' => $reply->name(),
                        ]
                    ));
                } else {
                    try {
                        $bot->getApi()->call($reply);
                    } catch (Throwable $exception) {
                        $this->exceptionHandler->report($exception);
                    }
                }
            } else {
                foreach ($response->getReplies() as $reply) {
                    try {
                        $bot->getApi()->call($reply);
                    } catch (Throwable $exception) {
                        $this->exceptionHandler->report($exception);
                    }
                }
            }
        }

        return Response::make();
    }
}
