<?php

declare(strict_types=1);

namespace WpNoticoel;

/**
 * Écoute un ou plusieurs hooks WP déclenchés par n'importe quel thème ou plugin
 * du site et forwarde l'événement reçu à Noticoel. Un producteur (thème, plugin)
 * n'a besoin de rien connaître de Noticoel au-delà de la forme de l'Event — si ce
 * plugin est désactivé, `do_action(...)` reste un no-op.
 *
 * Les hooks écoutés sont pris dans la constante NOTICOEL_EVENT_ACTIONS (tableau
 * de chaînes), à définir dans config/application.php (Bedrock) via
 * Config::define(), ou directement dans wp-config.php sinon. À défaut, seul
 * `noticoel/event` est écouté.
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
