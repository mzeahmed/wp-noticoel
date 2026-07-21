<?php

declare(strict_types=1);

namespace WpNoticoel;

/**
 * Client HTTP vers l'API Noticoel (POST /api/v1/events/create).
 *
 * @see https://github.com/mzeahmed/noticoel — Event shape: source, category?, type, severity, title, message, metadata?
 */
final readonly class Client
{
    public function __construct(
        private string $baseUrl,
        private string $token,
    ) {
    }

    /**
     * @param array{source: string, category?: string, type: string, severity: string, title: string, message: string, metadata?: array<string, string>} $event
     */
    public function send(array $event): bool
    {
        if ('' === $this->baseUrl || '' === $this->token) {
            return false;
        }

        $response = wp_remote_post(
            rtrim($this->baseUrl, '/') . '/api/v1/events/create',
            [
                'timeout' => 5,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json',
                ],
                'body' => wp_json_encode($event),
            ]
        );

        if (is_wp_error($response)) {
            error_log('[wp-noticoel] ' . $response->get_error_message());

            return false;
        }

        $status = (int) wp_remote_retrieve_response_code($response);

        if ($status < 200 || $status >= 300) {
            error_log(sprintf('[wp-noticoel] Noticoel a répondu HTTP %d : %s', $status, wp_remote_retrieve_body($response)));

            return false;
        }

        return true;
    }
}
