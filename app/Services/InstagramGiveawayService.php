<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
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
            throw new RuntimeException('Konfigurasi INSTAGRAM_ACCESS_TOKEN atau INSTAGRAM_USER_ID belum lengkap di .env.');
        }

        $target = $this->normalizePermalink($permalink);
        $mediaId = $this->findMediaId($userId, $token, $target);

        if (!$mediaId) {
            throw new RuntimeException('Postingan tidak ditemukan pada daftar media akun Instagram yang terhubung. Pastikan link berasal dari akun tersebut dan akun System User memiliki akses aset.');
        }

        $comments = [];
        $url = $this->baseUrl . '/' . $mediaId . '/comments';
        $params = [
            'fields' => 'id,text,username',
            'limit' => 100,
            'access_token' => $token,
        ];

        do {
            $response = $this->request($url, $params);
            foreach ($response['data'] ?? [] as $comment) {
                $username = $comment['username'] ?? $comment['from']['username'] ?? null;
                $text = trim((string) ($comment['text'] ?? ''));
                if ($username && $text !== '') {
                    $comments[] = [
                        'username' => ltrim($username, '@'),
                        'text' => $text,
                        'id' => $comment['id'] ?? null,
                    ];
                }
            }

            $url = $response['paging']['next'] ?? null;
            $params = [];
        } while ($url && count($comments) < 50000);

        return [
            'media_id' => $mediaId,
            'comments' => $comments,
            'total' => count($comments),
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
