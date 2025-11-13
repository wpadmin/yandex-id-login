# Yandex ID Login

WordPress plugin for Yandex ID authentication.

## Requirements

- WordPress 6.0+
- PHP 7.4+

## Installation

1. Upload plugin to `/wp-content/plugins/yandex-id-login/`
2. Activate plugin in WordPress admin panel
3. Setup OAuth application at [Yandex OAuth](https://oauth.yandex.ru/)
4. Enter Client ID and Client Secret in plugin settings

## Development

```bash
# Install dependencies
composer install

# Check code
composer run lint

# Auto format
composer run format
```

## License

GPL v2 or later
