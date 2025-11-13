<?php
declare(strict_types=1);

/**
 * Plugin Name:       Yandex ID Login
 * Plugin URI:        https://github.com/wpadmin/yandex-id-login/
 * Description:       Мгновенная авторизация через Яндекс ID для WordPress
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Evgeni Sh.
 * Author URI:        https://wpadmin.github.io/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       yandex-id-login
 * Domain Path:       /languages
 *
 * This plugin follows WordPress Coding Standards and best practices:
 *
 * @see https://developer.wordpress.org/coding-standards/
 * @see https://developer.wordpress.org/plugins/plugin-basics/best-practices/
 * @see https://developer.wordpress.org/
 *
 * @package YandexIDLogin
 * @author  Evgeni Sh. <codeispoetry@ya.ru>
 * @link    https://github.com/wpadmin/yandex-id-login/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'YANDEX_ID_VERSION', '1.0.0' );
define( 'YANDEX_ID_PLUGIN_FILE', __FILE__ );
define( 'YANDEX_ID_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'YANDEX_ID_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'YANDEX_ID_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once YANDEX_ID_PLUGIN_DIR . 'includes/class-activator.php';
require_once YANDEX_ID_PLUGIN_DIR . 'includes/class-core.php';

register_activation_hook( __FILE__, [ 'Yandex_ID_Activator', 'activate' ] );

add_action(
	'plugins_loaded',
	function () {
		Yandex_ID_Core::instance();
	}
);
