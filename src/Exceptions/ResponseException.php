<?php

namespace Tarik02\LaravelTelegram\Exceptions;

use Exception;
use Tarik02\LaravelTelegram\Response;

/**
 * Class ResponseException
 * @package Tarik02\LaravelTelegram\Exceptions
 */
class ResponseException extends Exception
{
    /**
     * @var Response
     */
    protected Response $response;

    /**
     * @param Response $response
     * @return void
     */
    public function __construct(Response $response)
    {
        parent::__construct();

        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}

