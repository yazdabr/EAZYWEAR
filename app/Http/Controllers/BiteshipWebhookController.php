<?php

namespace App\Http\Controllers;

use App\Services\BiteshipWebhookSignatureVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class BiteshipWebhookController extends Controller
{
    public function __construct(
        private BiteshipWebhookSignatureVerifier $signatureVerifier,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        try {
            if (! $this->signatureVerifier->verify($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Webhook signature tidak valid.',
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'Webhook diterima.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Webhook belum dapat diproses.',
            ], 503);
        }
    }
}
