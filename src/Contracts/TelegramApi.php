<?php

namespace Tarik02\LaravelTelegram\Contracts;

use Tarik02\Telegram\Contracts\CallsMethods;
use Tarik02\Telegram\Entities\InputFile;
use Tarik02\Telegram\Methods\Method;

/**
 * Interface TelegramApi
 * @package Tarik02\LaravelTelegram\Contracts
 */
interface TelegramApi extends CallsMethods
{
    /**
     * @param string $fileId
     * @return InputFile
     */
    public function attachFileById(string $fileId): InputFile;

    /**
     * @param string $url
     * @return InputFile
     */
    public function attachFileByUrl(string $url): InputFile;

    /**
     * @param string $contents
     * @param string|null $filename
     * @return InputFile
     */
    public function attachFileFromContents(string $contents, ?string $filename = null): InputFile;

    /**
     * @param string $localPath
     * @param string|null $filename
     * @return InputFile
     */
    public function attachFileFromLocalPath(string $localPath, ?string $filename = null): InputFile;

    /**
     * @param resource $resource
     * @param string|null $filename
     * @return InputFile
     */
    public function attachFileFromResource($resource, ?string $filename = null): InputFile;

    /**
     * @param Method $method
     * @return bool
     */
    public function canCallMethodWithWebhookResponse(Method $method): bool;
}
