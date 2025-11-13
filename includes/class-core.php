<?php
/**
 * Core plugin functionality
 *
 * @package YandexIDLogin
 * @author  Evgeni Sh. <codeispoetry@ya.ru>
 * @link    https://github.com/wpadmin/yandex-id-login/
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core plugin class
 *
 * Handles all plugin functionality including OAuth authentication,
 * settings management, and user creation.
 *
 * @package  YandexIDLogin
 * @author   Evgeni Sh. <codeispoetry@ya.ru>
 * @license  GPL-2.0-or-later https://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://github.com/wpadmin/yandex-id-login/
 * @since    1.0.0
 */
class Yandex_ID_Core {

	/**
	 * Singleton instance
	 *
	 * @var Yandex_ID_Core|null
	 */
	private static ?Yandex_ID_Core $instance = null;

	/**
	 * Plugin options
	 *
	 * @var array<string, mixed>
	 */
	private array $options;

	/**
	 * Private constructor for singleton pattern
	 *
	 * @return void
	 */
	private function __construct() {
		$this->options = get_option( 'yandex_id_options', [] );
		$this->init_hooks();
	}

	/**
	 * Get singleton instance
	 *
	 * @return Yandex_ID_Core
	 */
	public static function instance(): Yandex_ID_Core {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize WordPress hooks
	 *
	 * @return void
	 */
	private function init_hooks(): void {
		add_action( 'init', [ $this, 'load_textdomain' ] );
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'login_form', [ $this, 'render_login_button' ] );
		add_action( 'register_form', [ $this, 'render_register_button' ] );
		add_action( 'init', [ $this, 'handle_oauth_callback' ] );
	}

	/**
	 * Load plugin text domain for translations
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'yandex-id-login',
			false,
			dirname( YANDEX_ID_PLUGIN_BASENAME ) . '/languages'
		);
	}

	/**
	 * Register admin menu pages
	 *
	 * @return void
	 */
	public function register_admin_menu(): void {
		add_options_page(
			__( 'Yandex ID Login Settings', 'yandex-id-login' ),
			__( 'Yandex ID', 'yandex-id-login' ),
			'manage_options',
			'yandex-id-login',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Register plugin settings
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			'yandex_id_options',
			'yandex_id_options',
			[
				'sanitize_callback' => [ $this, 'sanitize_options' ],
			]
		);

		add_settings_section(
			'yandex_id_main',
			__( 'API Settings', 'yandex-id-login' ),
			[ $this, 'render_main_section' ],
			'yandex-id-login'
		);

		add_settings_field(
			'client_id',
			__( 'Client ID', 'yandex-id-login' ),
			[ $this, 'render_client_id_field' ],
			'yandex-id-login',
			'yandex_id_main'
		);

		add_settings_field(
			'client_secret',
			__( 'Client Secret', 'yandex-id-login' ),
			[ $this, 'render_client_secret_field' ],
			'yandex-id-login',
			'yandex_id_main'
		);

		add_settings_section(
			'yandex_id_display',
			__( 'Display Settings', 'yandex-id-login' ),
			null,
			'yandex-id-login'
		);

		add_settings_field(
			'button_text',
			__( 'Button Text', 'yandex-id-login' ),
			[ $this, 'render_button_text_field' ],
			'yandex-id-login',
			'yandex_id_display'
		);

		add_settings_field(
			'show_on_login',
			__( 'Show on Login Page', 'yandex-id-login' ),
			[ $this, 'render_show_on_login_field' ],
			'yandex-id-login',
			'yandex_id_display'
		);

		add_settings_field(
			'show_on_register',
			__( 'Show on Register Page', 'yandex-id-login' ),
			[ $this, 'render_show_on_register_field' ],
			'yandex-id-login',
			'yandex_id_display'
		);

		add_settings_field(
			'auto_create_users',
			__( 'Auto-create Users', 'yandex-id-login' ),
			[ $this, 'render_auto_create_users_field' ],
			'yandex-id-login',
			'yandex_id_display'
		);
	}

	/**
	 * Sanitize options before saving
	 *
	 * @param array<string, mixed> $input Raw input data.
	 * @return array<string, mixed> Sanitized data.
	 */
	public function sanitize_options( array $input ): array {
		return [
			'client_id'         => sanitize_text_field( $input['client_id'] ?? '' ),
			'client_secret'     => sanitize_text_field( $input['client_secret'] ?? '' ),
			'button_text'       => sanitize_text_field( $input['button_text'] ?? __( 'Sign in with Yandex', 'yandex-id-login' ) ),
			'show_on_login'     => ! empty( $input['show_on_login'] ),
			'show_on_register'  => ! empty( $input['show_on_register'] ),
			'auto_create_users' => ! empty( $input['auto_create_users'] ),
		];
	}

	/**
	 * Render settings page
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'yandex_id_options' );
				do_settings_sections( 'yandex-id-login' );
				submit_button( __( 'Save Settings', 'yandex-id-login' ) );
				?>
			</form>

			<div class="card">
				<h2><?php echo esc_html__( 'Setup Instructions', 'yandex-id-login' ); ?></h2>
				<ol>
					<li>
						<?php
						echo wp_kses_post(
							sprintf(
								/* translators: %s: URL to Yandex OAuth */
								__( 'Register your application at %s', 'yandex-id-login' ),
								'<a href="https://oauth.yandex.ru/" target="_blank" rel="noopener noreferrer">https://oauth.yandex.ru/</a>'
							)
						);
						?>
					</li>
					<li>
						<?php
						echo wp_kses_post(
							sprintf(
								/* translators: %s: Callback URL */
								__( 'Set callback URL to: %s', 'yandex-id-login' ),
								'<code>' . esc_url( home_url( '/yandex-id-callback/' ) ) . '</code>'
							)
						);
						?>
					</li>
					<li><?php echo esc_html__( 'Copy Client ID and Client Secret to the fields above', 'yandex-id-login' ); ?></li>
					<li><?php echo esc_html__( 'Save settings and test the login button', 'yandex-id-login' ); ?></li>
				</ol>
			</div>
		</div>
		<?php
	}

	/**
	 * Render main section description
	 *
	 * @return void
	 */
	public function render_main_section(): void {
		echo '<p>' . esc_html__( 'Enter your Yandex OAuth application credentials.', 'yandex-id-login' ) . '</p>';
	}

	/**
	 * Render client ID field
	 *
	 * @return void
	 */
	public function render_client_id_field(): void {
		$value = $this->options['client_id'] ?? '';
		printf(
			'<input type="text" name="yandex_id_options[client_id]" value="%s" class="regular-text" required>',
			esc_attr( $value )
		);
	}

	/**
	 * Render client secret field
	 *
	 * @return void
	 */
	public function render_client_secret_field(): void {
		$value = $this->options['client_secret'] ?? '';
		printf(
			'<input type="password" name="yandex_id_options[client_secret]" value="%s" class="regular-text" required>',
			esc_attr( $value )
		);
	}

	/**
	 * Render button text field
	 *
	 * @return void
	 */
	public function render_button_text_field(): void {
		$value = $this->options['button_text'] ?? __( 'Sign in with Yandex', 'yandex-id-login' );
		printf(
			'<input type="text" name="yandex_id_options[button_text]" value="%s" class="regular-text">',
			esc_attr( $value )
		);
	}

	/**
	 * Render show on login checkbox
	 *
	 * @return void
	 */
	public function render_show_on_login_field(): void {
		$checked = ! empty( $this->options['show_on_login'] ) ? 'checked' : '';
		printf(
			'<label><input type="checkbox" name="yandex_id_options[show_on_login]" value="1" %s> %s</label>',
			esc_attr( $checked ),
			esc_html__( 'Display Yandex login button on WordPress login page', 'yandex-id-login' )
		);
	}

	/**
	 * Render show on register checkbox
	 *
	 * @return void
	 */
	public function render_show_on_register_field(): void {
		$checked = ! empty( $this->options['show_on_register'] ) ? 'checked' : '';
		printf(
			'<label><input type="checkbox" name="yandex_id_options[show_on_register]" value="1" %s> %s</label>',
			esc_attr( $checked ),
			esc_html__( 'Display Yandex login button on WordPress registration page', 'yandex-id-login' )
		);
	}

	/**
	 * Render auto-create users checkbox
	 *
	 * @return void
	 */
	public function render_auto_create_users_field(): void {
		$checked = ! empty( $this->options['auto_create_users'] ) ? 'checked' : '';
		printf(
			'<label><input type="checkbox" name="yandex_id_options[auto_create_users]" value="1" %s> %s</label>',
			esc_attr( $checked ),
			esc_html__( 'Automatically create WordPress accounts for Yandex users', 'yandex-id-login' )
		);
	}

	/**
	 * Render Yandex login button
	 *
	 * @return void
	 */
	public function render_login_button(): void {
		if ( empty( $this->options['show_on_login'] ) ) {
			return;
		}

		$this->render_button();
	}

	/**
	 * Render Yandex register button
	 *
	 * @return void
	 */
	public function render_register_button(): void {
		if ( empty( $this->options['show_on_register'] ) ) {
			return;
		}

		$this->render_button();
	}

	/**
	 * Render the actual button HTML
	 *
	 * @return void
	 */
	private function render_button(): void {
		if ( empty( $this->options['client_id'] ) ) {
			return;
		}

		$auth_url    = $this->get_authorization_url();
		$button_text = $this->options['button_text'] ?? __( 'Sign in with Yandex', 'yandex-id-login' );
		?>
		<div class="yandex-id-login-wrapper" style="margin: 20px 0;">
			<a href="<?php echo esc_url( $auth_url ); ?>" class="button button-large" style="width: 100%; text-align: center; background: #fc0; border-color: #fc0; color: #000;">
				<span class="dashicons dashicons-admin-users" style="margin-top: 3px;"></span>
				<?php echo esc_html( $button_text ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Get Yandex OAuth authorization URL
	 *
	 * @return string
	 */
	private function get_authorization_url(): string {
		$params = [
			'response_type' => 'code',
			'client_id'     => $this->options['client_id'],
			'redirect_uri'  => home_url( '/yandex-id-callback/' ),
			'state'         => wp_create_nonce( 'yandex_id_oauth' ),
		];

		return 'https://oauth.yandex.ru/authorize?' . http_build_query( $params );
	}

	/**
	 * Handle OAuth callback from Yandex
	 *
	 * @return void
	 */
	public function handle_oauth_callback(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is verified below.
		if ( ! isset( $_GET['code'] ) || ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- URL validation done by str_contains.
		if ( ! str_contains( wp_unslash( $_SERVER['REQUEST_URI'] ), '/yandex-id-callback/' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is verified below.
		$code = sanitize_text_field( wp_unslash( $_GET['code'] ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is the nonce to verify.
		$state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';

		if ( ! wp_verify_nonce( $state, 'yandex_id_oauth' ) ) {
			wp_die( esc_html__( 'Invalid security token.', 'yandex-id-login' ) );
		}

		$token = $this->exchange_code_for_token( $code );

		if ( ! $token ) {
			wp_die( esc_html__( 'Failed to get access token from Yandex.', 'yandex-id-login' ) );
		}

		$user_info = $this->get_user_info( $token );

		if ( ! $user_info ) {
			wp_die( esc_html__( 'Failed to get user information from Yandex.', 'yandex-id-login' ) );
		}

		$user = $this->find_or_create_user( $user_info );

		if ( ! $user ) {
			wp_die( esc_html__( 'Failed to create user account.', 'yandex-id-login' ) );
		}

		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID, true );
		do_action( 'wp_login', $user->user_login, $user );

		wp_safe_redirect( admin_url() );
		exit;
	}

	/**
	 * Exchange authorization code for access token
	 *
	 * @param string $code Authorization code.
	 * @return string|false Access token or false on failure.
	 */
	private function exchange_code_for_token( string $code ) {
		$response = wp_remote_post(
			'https://oauth.yandex.ru/token',
			[
				'body' => [
					'grant_type'    => 'authorization_code',
					'code'          => $code,
					'client_id'     => $this->options['client_id'],
					'client_secret' => $this->options['client_secret'],
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return $body['access_token'] ?? false;
	}

	/**
	 * Get user information from Yandex API
	 *
	 * @param string $token Access token.
	 * @return array<string, mixed>|false User info array or false on failure.
	 */
	private function get_user_info( string $token ) {
		$response = wp_remote_get(
			'https://login.yandex.ru/info',
			[
				'headers' => [
					'Authorization' => 'OAuth ' . $token,
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return is_array( $body ) ? $body : false;
	}

	/**
	 * Find existing user or create new one
	 *
	 * @param array<string, mixed> $user_info User information from Yandex.
	 * @return \WP_User|false User object or false on failure.
	 */
	private function find_or_create_user( array $user_info ) {
		$yandex_id = $user_info['id'] ?? '';

		if ( ! $yandex_id ) {
			return false;
		}

		// Try to find user by Yandex ID meta.
		$users = get_users(
			[
				'meta_key'   => 'yandex_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value' => $yandex_id,  // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'number'     => 1,
			]
		);

		if ( ! empty( $users ) ) {
			return $users[0];
		}

		// Try to find by email.
		$email = $user_info['default_email'] ?? '';

		if ( $email ) {
			$user = get_user_by( 'email', $email );

			if ( $user ) {
				update_user_meta( $user->ID, 'yandex_id', $yandex_id );
				return $user;
			}
		}

		// Create new user.
		if ( empty( $this->options['auto_create_users'] ) ) {
			return false;
		}

		$username     = sanitize_user( $user_info['login'] ?? 'yandex_' . $yandex_id );
		$display_name = $user_info['display_name'] ?? $username;

		// Ensure unique username.
		$original_username = $username;
		$counter           = 1;

		while ( username_exists( $username ) ) {
			$username = $original_username . $counter;
			++$counter;
		}

		$user_id = wp_create_user( $username, wp_generate_password(), $email );

		if ( is_wp_error( $user_id ) ) {
			return false;
		}

		wp_update_user(
			[
				'ID'           => $user_id,
				'display_name' => $display_name,
				'first_name'   => $user_info['first_name'] ?? '',
				'last_name'    => $user_info['last_name'] ?? '',
			]
		);

		update_user_meta( $user_id, 'yandex_id', $yandex_id );

		if ( ! empty( $user_info['default_avatar_id'] ) ) {
			$avatar_url = 'https://avatars.yandex.net/get-yapic/' . $user_info['default_avatar_id'] . '/islands-200';
			update_user_meta( $user_id, 'yandex_avatar', $avatar_url );
		}

		return get_user_by( 'id', $user_id );
	}
}
