<?php
declare(strict_types=1);

/**
 * Plugin activation handler
 *
 * @package YandexIDLogin
 * @author  Evgeni Sh. <codeispoetry@ya.ru>
 * @link    https://github.com/wpadmin/yandex-id-login/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin activation handler class
 *
 * Handles plugin activation tasks including setting up default options.
 *
 * @package  YandexIDLogin
 * @author   Evgeni Sh. <codeispoetry@ya.ru>
 * @license  GPL-2.0-or-later https://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://github.com/wpadmin/yandex-id-login/
 * @since    1.0.0
 */
class Yandex_ID_Activator {

	/**
	 * Activate plugin
	 *
	 * Sets up default plugin options.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function activate(): void {
		$default_options = [
			'client_id'         => '',
			'client_secret'     => '',
			'button_text'       => __( 'Sign in with Yandex', 'yandex-id-login' ),
			'show_on_login'     => true,
			'show_on_register'  => true,
			'auto_create_users' => true,
		];

		add_option( 'yandex_id_options', $default_options );
		update_option( 'yandex_id_version', YANDEX_ID_VERSION );
	}
}
