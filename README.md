# WP Noticoel

![Version](https://img.shields.io/badge/version-0.1.0-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![PHP](https://img.shields.io/badge/php-%3E%3D8.3-777bb4)
![WordPress](https://img.shields.io/badge/wordpress-plugin-21759b)

A lightweight WordPress plugin that forwards application events to a [Noticoel](https://github.com/mzeahmed/noticoel) hub over HTTP.

## Purpose

WP Noticoel decouples WordPress themes and plugins from Noticoel itself. Any producer on the site — a theme, another plugin — fires a plain WordPress action with an event payload; it needs to know nothing about Noticoel's API, authentication, or transport. WP Noticoel listens for that action and forwards the payload to your Noticoel instance over HTTP.

If the plugin is deactivated, `do_action(...)` on an unregistered hook is a harmless no-op — producers never fail because Noticoel is unreachable or disabled.

```
Theme / Plugin                 WP Noticoel                    Noticoel
      │                              │                              │
      │  do_action('noticoel/event', │                              │
      │    [...])                   │                              │
      ├─────────────────────────────►│                              │
      │                              │  POST /api/v1/events/create  │
      │                              ├─────────────────────────────►│
      │                              │  Authorization: Bearer <token>
```

## Event shape

Producers fire an array matching Noticoel's [Event](https://github.com/mzeahmed/noticoel/blob/main/docs/architecture.md#event) shape:

```php
do_action('noticoel/event', [
    'source'   => 'portfolio',                 // required — the app publishing the event
    'category' => 'contact',                   // optional — grouping (billing, ci, auth...)
    'type'     => 'contact.message_sent',       // required — the specific event
    'severity' => 'info',                       // optional — info|warning|error|critical, defaults to "info"
    'title'    => 'New contact message',        // required
    'message'  => 'Jane Doe sent a message.',   // required
    'metadata' => [                             // optional — arbitrary producer-specific context
        'name'  => 'Jane Doe',
        'email' => 'jane@example.com',
    ],
]);
```

## Configuration

WP Noticoel is configured entirely through constants — no admin screen, no options stored in the database.

| Constant                | Type       | Required | Description                                                              |
|--------------------------|------------|:--------:|----------------------------------------------------------------------------|
| `NOTICOEL_URL`           | `string`   | Yes      | Base URL of your Noticoel instance (e.g. `https://noticoel.example.com`). |
| `NOTICOEL_TOKEN`         | `string`   | Yes      | Bearer token used to authenticate against Noticoel's API.                 |
| `NOTICOEL_EVENT_ACTIONS` | `string[]` | No       | WordPress hooks the plugin listens on. Defaults to `['noticoel/event']`.  |

If `NOTICOEL_URL` or `NOTICOEL_TOKEN` is left empty, the plugin silently skips sending — nothing breaks, events are just dropped.

### Bedrock

Define the constants in `config/application.php`, alongside the rest of the site's configuration:

```php
use function Env\env;

Config::define('NOTICOEL_URL', env('NOTICOEL_URL') ?: '');
Config::define('NOTICOEL_TOKEN', env('NOTICOEL_TOKEN') ?: '');

// Add every hook this site should forward to Noticoel.
Config::define('NOTICOEL_EVENT_ACTIONS', [
    'noticoel/event',
]);
```

Then set the actual secrets in `.env`:

```
NOTICOEL_URL='https://noticoel.example.com'
NOTICOEL_TOKEN='your-bearer-token'
```

### Plain WordPress

Define the constants directly in `wp-config.php`, before `wp-settings.php` is loaded:

```php
define('NOTICOEL_URL', 'https://noticoel.example.com');
define('NOTICOEL_TOKEN', 'your-bearer-token');
define('NOTICOEL_EVENT_ACTIONS', ['noticoel/event']);
```

## Listening to multiple hooks

`NOTICOEL_EVENT_ACTIONS` accepts any number of hook names. This lets several unrelated producers each fire their own hook without stepping on each other:

```php
Config::define('NOTICOEL_EVENT_ACTIONS', [
    'noticoel/event',
    'noticoel/contact',
    'noticoel/order',
]);
```

Every listed hook is expected to pass a single array argument matching the [Event shape](#event-shape) above.

## Development

```bash
make stan          # PHPStan
make cs            # php-cs-fixer, dry-run
make csf           # php-cs-fixer, apply fixes
make rector-check  # Rector, dry-run
make rector        # Rector, apply fixes
```

Run `make help` for the full list of available commands.
