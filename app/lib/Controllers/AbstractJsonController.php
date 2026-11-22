<?php

namespace Lib\Controllers;

use Lib\Exception\HttpException;
use Lib\Http\JsonResponse;
use Lib\Http\Request;
use Lib\Http\Response;

abstract class AbstractJsonController extends AbstractController {

    private const string JSON_MIME = 'application/json';

    final public function process(Request $request): Response
    {
        try {
            $this->checkAcceptHeader($request);

            return $this->handle($request);
        } catch (HttpException $e) {
            return JsonResponse::error($e->statusCode, $e->getMessage(), $e->details);
        }
    }

    abstract protected function handle(Request $request): JsonResponse;

    protected function getJsonBody(Request $request): array
    {
        $contentType = $request->getHeader('Content-Type');
        $mime = strtolower(trim(explode(';', $contentType ?? '')[0]));

        if ($mime !== self::JSON_MIME) {
            throw new HttpException(415, 'the request Content-Type header must be application/json');
        }

        $body = trim($request->getBody());

        if ($body === '') {
            throw new HttpException(400, 'the request body is empty');
        }

        try {
            $data = json_decode($body, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new HttpException(400, 'the request body is not a valid json');
        }

        if ($data instanceof \stdClass === false) {
            throw new HttpException(400, 'the request body must be a json object');
        }

        return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    }

    private function checkAcceptHeader(Request $request): void
    {
        $accept = $request->getHeader('Accept');

        if ($accept === null || trim($accept) === '') {
            return;
        }

        foreach (explode(',', $accept) as $acceptedType) {
            $mime = strtolower(trim(explode(';', $acceptedType)[0]));

            if (in_array($mime, [self::JSON_MIME, 'application/*', '*/*'], true)) {
                return;
            }
        }

        throw new HttpException(406, 'this api only responds with application/json');
    }
}
