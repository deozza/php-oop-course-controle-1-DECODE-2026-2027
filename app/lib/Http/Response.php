<?php

namespace Lib\Http;



class Response
{
    public int $statusCode;
    public string $content;

    /**
     * @param int $statusCode HTTP status code 
     * @param string $content Content of the response 
     */
    public function sendResponse(int $statusCode, string $content): static
    {
        $this->statusCode = $statusCode;
        $this->content = $content;
        return $this;
    }
}
