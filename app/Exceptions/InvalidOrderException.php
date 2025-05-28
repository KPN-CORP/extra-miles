<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvalidOrderException extends Exception
{
    public function __construct($message = "Invalid order request", $code = 403)
    {
        parent::__construct($message, $code);
    }

    public function render(Request $request): Response
    {
        // Customize response based on request format (JSON, HTML, etc.)
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->message,
                'errors' => ['order' => 'Invalid order details'], // Example error details
            ], $this->code);
        }

        return response()->view('errors.403', [
            'message' => $this->getMessage(), // Pass error message to the view
        ], $this->code);
    }
}
