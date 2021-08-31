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
     * @var Telegram
     */
    protected Telegram $telegram;

    /**
     * @var Kernel
     */
    protected Kernel $kernel;

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
     * @param RequestFactory $requestFactory
     * @param ExceptionHandler $exceptionHandler
     * @param Dispatcher $dispatcher
     * @return void
     */
    public function __construct(
        Telegram $telegram,
        Kernel $kernel,
        RequestFactory $requestFactory,
        ExceptionHandler $exceptionHandler,
        Dispatcher $dispatcher
    ) {
        $this->telegram = $telegram;
        $this->kernel = $kernel;
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
     * @param WebhookRequest $webhookRequest
     * @return Bot
     */
    protected function resolveBotByWebhookRequest(WebhookRequest $webhookRequest): Bot
    {
        return $this->telegram->bot(
            \preg_replace(
                '/^telegram\.webhook\./',
                '',
                $webhookRequest->route()->getName()
            )
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
