<?php

namespace App\Controllers;

use Lib\Http\{Request, Response};
use Lib\Controllers\AbstractController;

class HomeController extends AbstractController
{
    public function process(Request $request): Response
    {
        $response = new Response();
        $response->statusCode = 200;
        $response->content = ' <h1>Welcome to the Home Page</h1>
<p>Navigate to :</p>
<ul>
    <li>
        <code>GET</code>
        <a href="/contact"> /contact</a>
    </li>
    <li>
        <code>GET</code>
        <a href="/contact/2026-11-22_08-44-31_daniel@ecole-decode.fr">
            /contact<code>/:filename</code>
        </a>
    </li>
</ul>
<p>Send requests to :</p>
<ul>

    <li>
        <code>DELETE</code>
        <a href="/contact/2026-11-22_08-44-31_daniel@ecole-decode.fr">/contact<code>/:filename</code>
        </a>
    </li>
    <li>
        <code>PATCH</code>
        <a href="/contact/2026-11-22_08-44-31_daniel@ecole-decode.fr">/contact<code>/:filename</code>
        </a>
    </li>
</ul>
                                ';

        return $response;
    }
}

?>