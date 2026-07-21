<?php

declare(strict_types=1);

namespace WpNoticoel;

/**
 * Listens for one or more WP hooks fired by any theme or plugin on the site
 * and forwards the received event to Noticoel. A producer (theme, plugin)
 * doesn't need to know anything about Noticoel beyond the Event shape — if
 * this plugin is deactivated, `do_action(...)` stays a harmless no-op.
 *
 * The hooks listened to are read from the NOTICOEL_EVENT_ACTIONS constant
 * (an array of strings), defined in config/application.php (Bedrock) via
 * Config::define(), or directly in wp-config.php otherwise. Defaults to
 * `noticoel/event` alone.
 */
final readonly class EventListener
{
    private const array DEFAULT_ACTIONS = ['noticoel/event'];

    private Client $client;

    public function __construct()
    {
        $this->client = new Client(
            defined('NOTICOEL_URL') ? (string) NOTICOEL_URL : '',
            defined('NOTICOEL_TOKEN') ? (string) NOTICOEL_TOKEN : '',
        );
    }

    public function register(): void
    {
        foreach ($this->actions() as $action) {
            add_action($action, $this->handle(...));
        }
    }

    /**
     * @param array{source: string, category?: string, type: string, severity?: string, title: string, message: string, metadata?: array<string, string>} $event
     */
    public function handle(array $event): void
    {
        $event['severity'] ??= 'info';

        $this->client->send($event);
    }

    /**
     * @return string[]
     */
    private function actions(): array
    {
        if (defined('NOTICOEL_EVENT_ACTIONS') && is_array(NOTICOEL_EVENT_ACTIONS)) {
            return NOTICOEL_EVENT_ACTIONS;
        }

        return self::DEFAULT_ACTIONS;
    }
}
