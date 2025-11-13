<?php
/**
 * Uninstall script
 *
 * Fired when the plugin is uninstalled.
 *
 * @package YandexIDLogin
 * @author  Evgeni Sh. <codeispoetry@ya.ru>
 * @link    https://github.com/wpadmin/yandex-id-login/
 */

declare(strict_types=1);

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete plugin options
 */
delete_option( 'yandex_id_options' );
delete_option( 'yandex_id_version' );

/**
 * Delete user meta for all users
 */
delete_metadata( 'user', 0, 'yandex_id', '', true );
delete_metadata( 'user', 0, 'yandex_avatar', '', true );
