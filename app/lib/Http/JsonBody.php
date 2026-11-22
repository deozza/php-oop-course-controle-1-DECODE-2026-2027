<?php

namespace Lib\Http;

class JsonBody
{
    public static function hasContentType(Request $request): bool
    {
        return isset($request->getHeaders()["Content-Type"]);
    }

    public static function isJson(Request $request): bool
    {
        $contentType = explode(";", $request->getHeaders()["Content-Type"]);

        return trim($contentType[0]) === "application/json";
    }

    public static function decode(Request $request): array
    {
        $json = json_decode($request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        if (is_array($json) === false) {
            throw new \UnexpectedValueException("The JSON body is not an array");
        }

        return $json;
    }
}
