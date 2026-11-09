<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class InstagramGiveawayService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'https://graph.facebook.com/' . config('services.instagram.graph_version', 'v26.0');
    }

    public function fetchCommentsForPost(string $permalink): array
    {
        $token = config('services.instagram.access_token');
        $userId = config('services.instagram.user_id');

        if (!$token || !$userId) {
            throw new RuntimeException('Konfigurasi Instagram belum lengkap.');
        }

        $target = $this->normalizePermalink($permalink);
        $mediaId = $this->findMediaId($userId, $token, $target);

        if (!$mediaId) {
            throw new RuntimeException(
                'Postingan tidak ditemukan pada akun Instagram yang terhubung.'
            );
        }

        $comments = [];

        $rawMainComments = 0;
        $rawReplies = 0;
        $importedMainComments = 0;
        $importedReplies = 0;
        $missingUsername = 0;
        $emptyText = 0;
        $excludedComments = 0;
        $mainPages = 0;
        $replyPages = 0;

        $appendComment = function (array $item, string $type) use (
            &$comments,
            &$importedMainComments,
            &$importedReplies,
            &$missingUsername,
            &$emptyText,
            &$excludedComments
        ) {
            $username = $item['username']
                ?? $item['from']['username']
                ?? null;

            $text = trim((string) ($item['text'] ?? ''));

            $hasUsername = !empty($username);
            $hasText = $text !== '';

            if (!$hasUsername) {
                $missingUsername++;
            }

            if (!$hasText) {
                $emptyText++;
            }

            if (!$hasUsername || !$hasText) {
                $excludedComments++;
                return;
            }

            $comments[] = [
                'username' => ltrim($username, '@'),
                'text' => $text,
                'id' => $item['id'] ?? null,
                'created_at' => $item['timestamp'] ?? null,
                'comment_type' => $type,
            ];

            if ($type === 'reply') {
                $importedReplies++;
            } else {
                $importedMainComments++;
            }
        };

        $url = $this->baseUrl . '/' . $mediaId . '/comments';

        $params = [
            'fields' => 'id,text,username,timestamp,replies{id,text,username,timestamp}',
            'limit' => 100,
            'access_token' => $token,
        ];

        $hasNext = true;
        $stopReason = 'unknown';

        while ($hasNext && count($comments) < 50000) {
            $response = $this->request($url, $params);
            $mainPages++;

            $pageData = $response['data'] ?? [];
            $rawMainComments += count($pageData);

            foreach ($pageData as $comment) {

                $appendComment($comment, 'main');

                // Replies halaman pertama yang ikut dalam field expansion.
                $replyData = data_get($comment, 'replies.data', []);
                $rawReplies += count($replyData);

                foreach ($replyData as $reply) {
                    $appendComment($reply, 'reply');
                }

                // Ambil halaman replies berikutnya jika tersedia.
                $replyNextUrl = data_get($comment, 'replies.paging.next');

                while ($replyNextUrl && count($comments) < 50000) {
                    $replyResponse = $this->request($replyNextUrl);
                    $replyPages++;

                    $nextReplies = $replyResponse['data'] ?? [];
                    $rawReplies += count($nextReplies);

                    foreach ($nextReplies as $reply) {
                        $appendComment($reply, 'reply');
                    }

                    $replyNextUrl = $replyResponse['paging']['next'] ?? null;
                }
            }

            $after = $response['paging']['cursors']['after'] ?? null;
            $hasNext = !empty($response['paging']['next']) && !empty($after);

            if ($hasNext) {
                $params = [
                    'fields' => 'id,text,username,timestamp,replies{id,text,username,timestamp}',
                    'limit' => 100,
                    'access_token' => $token,
                    'after' => $after,
                ];
            } else {
                $stopReason = !empty($response['paging']['next'])
                    ? 'next_url_without_cursor'
                    : 'no_next_page';
            }
        }

        if (count($comments) >= 50000) {
            $stopReason = 'safety_limit_reached';
        }

        $summary = [
            'main_pages_fetched' => $mainPages,
            'reply_pages_fetched' => $replyPages,
            'raw_main_comments' => $rawMainComments,
            'raw_replies' => $rawReplies,
            'imported_main_comments' => $importedMainComments,
            'imported_replies' => $importedReplies,
            'total_imported' => count($comments),
            'missing_username' => $missingUsername,
            'empty_text' => $emptyText,
            'excluded_comments' => $excludedComments,
            'stop_reason' => $stopReason,
        ];

        Log::info('Instagram Giveaway comments and replies diagnostics', [
            'media_id' => $mediaId,
            'api_version' => config('services.instagram.graph_version'),
            ...$summary,
        ]);

        return [
            'media_id' => $mediaId,
            'comments' => $comments,
            'total' => count($comments),
            'summary' => $summary,
        ];
    }

    private function findMediaId(string $userId, string $token, string $target): ?string
    {
        $url = $this->baseUrl . '/' . $userId . '/media';
        $params = [
            'fields' => 'id,permalink',
            'limit' => 100,
            'access_token' => $token,
        ];

        do {
            $response = $this->request($url, $params);
            foreach ($response['data'] ?? [] as $media) {
                if (!empty($media['permalink']) && $this->normalizePermalink($media['permalink']) === $target) {
                    return $media['id'] ?? null;
                }
            }
            $url = $response['paging']['next'] ?? null;
            $params = [];
        } while ($url);

        return null;
    }

    private function request(string $url, array $params = []): array
    {
        $response = Http::acceptJson()->timeout(45)->get($url, $params);
        $data = $response->json();

        if (!$response->successful()) {
            $message = data_get($data, 'error.message', 'Permintaan ke Instagram Graph API gagal.');
            $code = data_get($data, 'error.code');
            throw new RuntimeException($message . ($code ? ' (kode ' . $code . ')' : ''));
        }

        return is_array($data) ? $data : [];
    }

    private function normalizePermalink(string $url): string
    {
        $parts = parse_url(trim($url));
        if (!is_array($parts) || !in_array(strtolower($parts['host'] ?? ''), ['instagram.com', 'www.instagram.com', 'm.instagram.com'], true)) {
            throw ValidationException::withMessages(['post_url' => 'Masukkan link postingan Instagram yang valid.']);
        }

        return 'https://www.instagram.com' . rtrim($parts['path'] ?? '/', '/') . '/';
    }
}
