<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\InstagramGiveawayService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class GiveawayPickerController extends Controller
{
    public function comments(Request $request, InstagramGiveawayService $instagram)
    {
        $validated = $request->validate([
            'post_url' => ['required', 'url', 'max:2048'],
        ]);

        try {
            return response()->json($instagram->fetchCommentsForPost($validated['post_url']));
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}
