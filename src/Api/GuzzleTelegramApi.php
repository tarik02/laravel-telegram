<?php

namespace Tarik02\LaravelTelegram\Api;

use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;
use Tarik02\Telegram\Entities\InputFile;
use Tarik02\Telegram\Methods\Method;

use GuzzleHttp\{
    ClientInterface,
    RequestOptions
};

/**
 * Class GuzzleTelegramApi
 * @package Tarik02\LaravelTelegram\Api
 */
class GuzzleTelegramApi extends BaseTelegramApi
{
    /**
     * @var ClientInterface
     */
    protected ClientInterface $client;

    /**
     * @var array
     */
    protected array $files = [];

    /**
     * @param string $token
     * @param Dispatcher $dispatcher
     * @param ClientInterface $client
     * @return void
     */
    public function __construct(string $token, Dispatcher $dispatcher, ClientInterface $client)
    {
        parent::__construct($token, $dispatcher);

        $this->client = $client;
    }

    /**
     * @param string $fileId
     * @return InputFile
     */
    public function attachFileById(string $fileId): InputFile
    {
        return InputFile::make()
            ->withPayload($fileId);
    }

    /**
     * @param string $url
     * @return InputFile
     */
    public function attachFileByUrl(string $url): InputFile
    {
        return InputFile::make()
            ->withPayload($url);
    }

    /**
     * @param string $contents
     * @param string|null $filename
     * @return InputFile
     */
    public function attachFileFromContents(string $contents, ?string $filename = null): InputFile
    {
        $attachmentName = sprintf(
            'file%s',
            \count($this->files) + 1
        );

        $this->files[] = [
            'name' => $attachmentName,
            'contents' => $contents,
            'filename' => $filename,
        ];

        return InputFile::make()
            ->withPayload('attach://' . $attachmentName);
    }

    /**
     * @param string $localPath
     * @param string|null $filename
     * @return InputFile
     */
    public function attachFileFromLocalPath(string $localPath, ?string $filename = null): InputFile
    {
        $attachmentName = sprintf(
            'file%s',
            \count($this->files) + 1
        );

        $this->files[] = [
            'name' => $attachmentName,
            'contents' => \fopen($localPath, 'r'),
            'filename' => $filename ?? \basename($localPath),
        ];

        return InputFile::make()
            ->withPayload('attach://' . $attachmentName);
    }

    /**
     * @param resource $resource
     * @param string|null $filename
     * @return InputFile
     */
    public function attachFileFromResource($resource, ?string $filename = null): InputFile
    {
        $attachmentName = sprintf(
            'file%s',
            \count($this->files) + 1
        );

        $this->files[] = [
            'name' => $attachmentName,
            'contents' => $resource,
            'filename' => $filename,
        ];

        return InputFile::make()
            ->withPayload('attach://' . $attachmentName);
    }

    /**
     * @param Method $method
     * @return bool
     */
    public function canCallMethodWithWebhookResponse(Method $method): bool
    {
        return empty($this->files);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $data
     * @return array
     */
    protected function performJsonRequest(string $method, string $url, array $data): array
    {
        switch ($method) {
            case 'GET':
                $response = $this->client
                    ->request('get', $url, [
                        RequestOptions::QUERY => $data,
                        RequestOptions::HTTP_ERRORS => false,
                    ]);
                break;

            case 'POST':
                $multipart = $this->files;
                $this->files = [];

                if (\count($multipart) > 0) {
                    foreach ($data as $key => $value) {
                        if (\is_array($value)) {
                            $multipart[] = [
                                'name' => $key,
                                'contents' => \json_encode($value),
                            ];
                        } else {
                            $multipart[] = [
                                'name' => $key,
                                'contents' => $value,
                            ];
                        }
                    }
                    $data = null;
                }

                $response = $this->client
                    ->request('post', $url, [
                        RequestOptions::JSON => $data,
                        RequestOptions::HTTP_ERRORS => false,
                        RequestOptions::MULTIPART => $multipart,
                    ]);
                break;

            default:
                throw new InvalidArgumentException(
                    \sprintf('Argument "method" value expected to be one of: "GET", "POST". Got: "%s"', $method)
                );
        }

        if ($response->getStatusCode() === 200) {
            return \json_decode($response->getBody(), true, 512, \JSON_THROW_ON_ERROR);
        }

        $bodyContents = $response->getBody()->__toString();

        if ($response->getHeader('Content-Type') === 'application/json') {
            $body = \json_decode($bodyContents, true, 512, \JSON_THROW_ON_ERROR);

            return [
                'ok' => false,
                'error_code' => $body['error_code'] ?? $response->getStatusCode(),
                'description' => $body['description'] ?? $response->getReasonPhrase(),
            ];
        }

        return [
            'ok' => false,
            'error_code' => $response->getStatusCode(),
            'description' => $response->getReasonPhrase(),
        ];
    }
}
