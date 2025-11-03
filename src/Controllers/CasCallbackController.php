<?php

declare(strict_types=1);

namespace EcDoris\LaravelCas\Controllers;

use Illuminate\Http\Response;

class CasCallbackController extends Controller
{
    public function __invoke(): Response
    {
        // This page is intentionally left blank. The `cas.auth` middleware handles
        // the ticket validation and subsequent redirection. This controller
        // just provides a valid route for the CAS server to redirect to.
        return new Response('', 200);
    }
}
