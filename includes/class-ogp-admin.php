<?php
/**
 * OGP Admin Class
 *
 * Handles admin menu registration, settings page, and options management.
 *
 * @package KashiwazakiSeoOgpManager
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KSOM_OGP_Admin class.
 *
 * Manages admin interface for OGP settings.
 *
 * @since 1.0.0
 */
class KSOM_OGP_Admin {

	/**
	 * Singleton instance.
	 *
	 * @var KSOM_OGP_Admin|null
	 */
	private static $instance = null;

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Settings page hook suffix.
	 *
	 * @var string
	 */
	private $page_hook = '';

	/**
	 * Get singleton instance.
	 *
	 * @return KSOM_OGP_Admin Singleton instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->load_options();
		$this->init_hooks();
	}

	/**
	 * Load plugin options.
	 */
	private function load_options() {
		$this->options = wp_parse_args(
			get_option( KSOM_OPTION_NAME, array() ),
			ksom_get_default_options()
		);
	}

	/**
	 * Initialize WordPress hooks.
	 */
	private function init_hooks() {
		// Admin menu.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Admin init for settings registration.
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add settings link to plugin page.
		add_filter( 'plugin_action_links_' . KSOM_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );
	}

	/**
	 * Add admin menu.
	 */
	public function add_admin_menu() {
		$this->page_hook = add_menu_page(
			__( 'Kashiwazaki SEO OGP Manager', 'kashiwazaki-seo-ogp-manager' ),
			__( 'Kashiwazaki SEO OGP Manager', 'kashiwazaki-seo-ogp-manager' ),
			'manage_options',
			'kashiwazaki-seo-ogp-manager',
			array( $this, 'render_settings_page' ),
			'dashicons-share',
			81
		);
	}

	/**
	 * Register settings.
	 */
	public function register_settings() {
		// Register setting.
		register_setting(
			'ksom_settings_group',
			KSOM_OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'show_in_rest'      => false,
			)
		);

		// Basic settings section.
		add_settings_section(
			'ksom_basic_settings',
			__( '基本設定', 'kashiwazaki-seo-ogp-manager' ),
			array( $this, 'render_basic_settings_section' ),
			'kashiwazaki-seo-ogp-manager'
		);

		// Enable OGP.
		add_settings_field(
			'enable_ogp',
			__( 'OGPを有効化', 'kashiwazaki-seo-ogp-manager' ),
			array( $this, 'render_enable_ogp_field' ),
			'kashiwazaki-seo-ogp-manager',
			'ksom_basic_settings'
		);

		// Enable Twitter Card.
		add_settings_field(
			'enable_twitter_card',
			__( 'Twitter Cardを有効化', 'kashiwazaki-seo-ogp-manager' ),
			array( $this, 'render_enable_twitter_card_field' ),
			'kashiwazaki-seo-ogp-manager',
			'ksom_basic_settings'
		);

		// Enable robots max-image-preview.
		add_settings_field(
			'enable_robots_max_image',
			__( 'Robots Meta (max-image-preview)', 'kashiwazaki-seo-ogp-manager' ),
			array( $this, 'render_enable_robots_max_image_field' ),
			'kashiwazaki-seo-ogp-manager',
			'ksom_basic_settings'
		);

		// Site name.
		add_settings_field(
			'site_name',
			__( 'サイト名', 'kashiwazaki-seo-ogp-manager' ),
			array( $this, 'render_site_name_field' ),
			'kashiwazaki-seo-ogp-manager',
			'ksom_basic_settings'
		);

		// Default OG type.
		add_settings_field(
			'default_og_type',
			__( 'デフォルトOGタイプ', 'kashiwazaki-seo-ogp-manager' ),
			array( $this, 'render_default_og_type_field' ),
			'kashiwazaki-seo-ogp-manager',
			'ksom_basic_settings'
		);

		// Default Twitter Card type.
		add_settings_field(
			'default_twitter_card_type',
			__( 'デフォルトTwitter Cardタイプ', 'kashiwazaki-seo-ogp-manager' ),
			array( $this, 'render_default_twitter_card_type_field' ),
			'kashiwazaki-seo-ogp-manager',
			'ksom_basic_settings'
		);

		// Social media section.
		add_settings_section(
			'ksom_social_settings',
			__( 'ソーシャルメディア設定', 'kashiwazaki-seo-ogp-manager' ),
			array( $this, 'render_social_settings_section' ),
			'kashiwazaki-seo-ogp-manager'
		);

		// Facebook App ID.
		add_settings_field(
			'fb_app_id',
			__( 'Facebook App ID', 'kashiwazaki-seo-ogp-manager' ),
			array( $this, 'render_fb_app_id_field' ),
			'kashiwazaki-seo-ogp-manager',
			'ksom_social_settings'
		);

		// Twitter site.
		add_settings_field(
			'twitter_site',
			__( 'Twitterユーザー名', 'kashiwazaki-seo-ogp-manager' ),
			array( $this, 'render_twitter_site_field' ),
			'kashiwazaki-seo-ogp-manager',
			'ksom_social_settings'
		);

		// Image settings section.
		add_settings_section(
			'ksom_image_settings',
			__( '画像設定', 'kashiwazaki-seo-ogp-manager' ),
			array( $this, 'render_image_settings_section' ),
			'kashiwazaki-seo-ogp-manager'
		);

		// Default image.
		add_settings_field(
			'default_image',
			__( 'デフォルトOGP画像', 'kashiwazaki-seo-ogp-manager' ),
			array( $this, 'render_default_image_field' ),
			'kashiwazaki-seo-ogp-manager',
			'ksom_image_settings'
		);

		// Post types section.
		add_settings_section(
			'ksom_post_types_settings',
			__( '投稿タイプ設定', 'kashiwazaki-seo-ogp-manager' ),
			array( $this, 'render_post_types_settings_section' ),
			'kashiwazaki-seo-ogp-manager'
		);

		// Enable for post types.
		add_settings_field(
			'enable_for_post_types',
			__( 'OGPを有効化する投稿タイプ', 'kashiwazaki-seo-ogp-manager' ),
			array( $this, 'render_enable_for_post_types_field' ),
			'kashiwazaki-seo-ogp-manager',
			'ksom_post_types_settings'
		);
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		// Check user permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'このページにアクセスする権限がありません。', 'kashiwazaki-seo-ogp-manager' ) );
		}

		// Reload options to get latest values.
		$this->load_options();
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'ksom_settings_group' );
				do_settings_sections( 'kashiwazaki-seo-ogp-manager' );
				submit_button();
				?>
			</form>

			<!-- Plugin info -->
			<div class="ksom-plugin-info" style="margin-top: 30px; padding: 15px; background: #f5f5f5; border-left: 4px solid #0073aa;">
				<h3><?php esc_html_e( 'About Kashiwazaki SEO OGP Manager', 'kashiwazaki-seo-ogp-manager' ); ?></h3>
				<p><?php esc_html_e( 'Version:', 'kashiwazaki-seo-ogp-manager' ); ?> <strong><?php echo esc_html( KSOM_VERSION ); ?></strong></p>
				<p>
					<?php
					printf(
						/* translators: %s: Author website URL */
						esc_html__( 'Developed by %s', 'kashiwazaki-seo-ogp-manager' ),
						'<a href="https://www.tsuyoshikashiwazaki.jp" target="_blank">Tsuyoshi Kashiwazaki</a>'
					);
					?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {
		// Only load on our settings page.
		if ( $this->page_hook !== $hook ) {
			return;
		}

		// Enqueue media uploader.
		wp_enqueue_media();

		// Enqueue admin CSS.
		wp_enqueue_style(
			'ksom-admin-style',
			KSOM_ASSETS_URL . 'css/admin-style.css',
			array(),
			KSOM_VERSION
		);

		// Enqueue admin JS.
		wp_enqueue_script(
			'ksom-admin-script',
			KSOM_ASSETS_URL . 'js/admin-script.js',
			array( 'jquery', 'media-upload' ),
			KSOM_VERSION,
			true
		);

		// Localize script.
		wp_localize_script(
			'ksom-admin-script',
			'ksomAdmin',
			array(
				'selectImage'  => __( 'Select Image', 'kashiwazaki-seo-ogp-manager' ),
				'useThisImage' => __( 'Use This Image', 'kashiwazaki-seo-ogp-manager' ),
				'homeUrl'      => home_url( '/' ),
			)
		);
	}

	/**
	 * Add settings link to plugin page.
	 *
	 * @param array $links Existing links.
	 * @return array Modified links.
	 */
	public function add_settings_link( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'options-general.php?page=kashiwazaki-seo-ogp-manager' ),
			__( '設定', 'kashiwazaki-seo-ogp-manager' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $input Input settings.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		// Verify nonce.
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'ksom_settings_group-options' ) ) {
			add_settings_error(
				KSOM_OPTION_NAME,
				'nonce_failed',
				__( 'セキュリティチェックに失敗しました。もう一度お試しください。', 'kashiwazaki-seo-ogp-manager' ),
				'error'
			);
			return $this->options;
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			add_settings_error(
				KSOM_OPTION_NAME,
				'permission_denied',
				__( 'この設定を更新する権限がありません。', 'kashiwazaki-seo-ogp-manager' ),
				'error'
			);
			return $this->options;
		}

		$sanitized = array();

		// Boolean fields - sanitize to ensure they are proper booleans.
		$sanitized['enable_ogp']              = isset( $input['enable_ogp'] ) && ! empty( $input['enable_ogp'] ) ? true : false;
		$sanitized['enable_twitter_card']     = isset( $input['enable_twitter_card'] ) && ! empty( $input['enable_twitter_card'] ) ? true : false;
		$sanitized['enable_robots_max_image'] = isset( $input['enable_robots_max_image'] ) && ! empty( $input['enable_robots_max_image'] ) ? true : false;
		$sanitized['auto_generate_image']     = isset( $input['auto_generate_image'] ) && ! empty( $input['auto_generate_image'] ) ? true : false;

		// Site name - sanitize text field and strip tags.
		if ( isset( $input['site_name'] ) ) {
			$sanitized['site_name'] = wp_strip_all_tags( sanitize_text_field( $input['site_name'] ) );
		} else {
			$sanitized['site_name'] = '';
		}

		// Default OG type - validate against whitelist.
		if ( isset( $input['default_og_type'] ) ) {
			$allowed_og_types             = array( 'website', 'article', 'blog', 'product', 'video' );
			$og_type                      = sanitize_text_field( $input['default_og_type'] );
			$sanitized['default_og_type'] = in_array( $og_type, $allowed_og_types, true ) ? $og_type : 'website';
		} else {
			$sanitized['default_og_type'] = 'website';
		}

		// Default Twitter Card type - validate against whitelist.
		if ( isset( $input['default_twitter_card_type'] ) ) {
			$allowed_card_types                     = array( 'summary', 'summary_large_image' );
			$card_type                              = sanitize_text_field( $input['default_twitter_card_type'] );
			$sanitized['default_twitter_card_type'] = in_array( $card_type, $allowed_card_types, true ) ? $card_type : 'summary_large_image';
		} else {
			$sanitized['default_twitter_card_type'] = 'summary_large_image';
		}

		// Facebook App ID - sanitize and validate format (numeric only).
		if ( isset( $input['fb_app_id'] ) ) {
			$fb_app_id = sanitize_text_field( trim( $input['fb_app_id'] ) );
			// FB App ID should be numeric.
			if ( ! empty( $fb_app_id ) && ! ctype_digit( $fb_app_id ) ) {
				add_settings_error(
					KSOM_OPTION_NAME,
					'invalid_fb_app_id',
					__( 'Facebook App IDは数字のみである必要があります。無効な値は保存されませんでした。', 'kashiwazaki-seo-ogp-manager' ),
					'warning'
				);
				$sanitized['fb_app_id'] = '';
			} else {
				$sanitized['fb_app_id'] = $fb_app_id;
			}
		} else {
			$sanitized['fb_app_id'] = '';
		}

		// Twitter site - sanitize and ensure @ prefix.
		if ( isset( $input['twitter_site'] ) ) {
			$twitter_site = sanitize_text_field( trim( $input['twitter_site'] ) );
			// Remove any existing @ and special characters.
			$twitter_site = preg_replace( '/[^a-zA-Z0-9_]/', '', $twitter_site );
			// Validate Twitter username format (alphanumeric and underscore only, max 15 chars).
			if ( ! empty( $twitter_site ) && strlen( $twitter_site ) > 15 ) {
				add_settings_error(
					KSOM_OPTION_NAME,
					'invalid_twitter_username',
					__( 'Twitterユーザー名が長すぎます（最大15文字）。切り詰められました。', 'kashiwazaki-seo-ogp-manager' ),
					'warning'
				);
				$twitter_site = substr( $twitter_site, 0, 15 );
			}
			$sanitized['twitter_site'] = $twitter_site;
		} else {
			$sanitized['twitter_site'] = '';
		}

		// Default image URL - sanitize and validate URL.
		if ( isset( $input['default_image'] ) ) {
			$image_url = esc_url_raw( trim( $input['default_image'] ) );
			// Validate that it's a valid image URL.
			if ( ! empty( $image_url ) && ! filter_var( $image_url, FILTER_VALIDATE_URL ) ) {
				add_settings_error(
					KSOM_OPTION_NAME,
					'invalid_image_url',
					__( 'デフォルト画像のURLが無効です。有効なURLを入力してください。', 'kashiwazaki-seo-ogp-manager' ),
					'error'
				);
				$sanitized['default_image'] = '';
			} else {
				$sanitized['default_image'] = $image_url;
			}
		} else {
			$sanitized['default_image'] = '';
		}

		// Post types - sanitize array and validate against existing post types.
		if ( isset( $input['enable_for_post_types'] ) && is_array( $input['enable_for_post_types'] ) ) {
			$valid_post_types = get_post_types( array( 'public' => true ), 'names' );
			$sanitized_types  = array_map( 'sanitize_text_field', $input['enable_for_post_types'] );
			$validated_types  = array();

			foreach ( $sanitized_types as $post_type ) {
				// Only include valid post types that exist.
				if ( in_array( $post_type, $valid_post_types, true ) ) {
					$validated_types[] = $post_type;
				}
			}

			// Ensure at least one post type is selected.
			if ( empty( $validated_types ) ) {
				add_settings_error(
					KSOM_OPTION_NAME,
					'no_post_types',
					__( '少なくとも1つの投稿タイプを選択する必要があります。デフォルトの投稿タイプが復元されました。', 'kashiwazaki-seo-ogp-manager' ),
					'warning'
				);
				$sanitized['enable_for_post_types'] = array( 'post', 'page' );
			} else {
				$sanitized['enable_for_post_types'] = array_values( array_unique( $validated_types ) );
			}
		} else {
			$sanitized['enable_for_post_types'] = array( 'post', 'page' );
		}

		return $sanitized;
	}

	// Settings section callbacks.

	/**
	 * Render basic settings section.
	 */
	public function render_basic_settings_section() {
		echo '<p>' . esc_html__( 'OGPとTwitter Cardの基本設定を行います。', 'kashiwazaki-seo-ogp-manager' ) . '</p>';
	}

	/**
	 * Render social settings section.
	 */
	public function render_social_settings_section() {
		echo '<p>' . esc_html__( 'ソーシャルメディアプラットフォームの設定を行います。', 'kashiwazaki-seo-ogp-manager' ) . '</p>';
	}

	/**
	 * Render image settings section.
	 */
	public function render_image_settings_section() {
		echo '<p>' . esc_html__( 'OGPタグ用のデフォルト画像を設定します。推奨サイズ: 1200x630px。', 'kashiwazaki-seo-ogp-manager' ) . '</p>';
	}

	/**
	 * Render post types settings section.
	 */
	public function render_post_types_settings_section() {
		echo '<p>' . esc_html__( 'OGP機能を有効化する投稿タイプを選択します（管理画面のメタボックス表示とフロントエンドのOGPタグ出力の両方が制御されます）。', 'kashiwazaki-seo-ogp-manager' ) . '</p>';
	}

	// Settings field callbacks.

	/**
	 * Render enable OGP field.
	 */
	public function render_enable_ogp_field() {
		$checked = ! empty( $this->options['enable_ogp'] ) ? 'checked' : '';
		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( KSOM_OPTION_NAME ); ?>[enable_ogp]" value="1" <?php echo esc_attr( $checked ); ?> />
			<?php esc_html_e( 'Open Graph Protocolタグを有効化する', 'kashiwazaki-seo-ogp-manager' ); ?>
		</label>
		<?php
	}

	/**
	 * Render enable Twitter Card field.
	 */
	public function render_enable_twitter_card_field() {
		$checked = ! empty( $this->options['enable_twitter_card'] ) ? 'checked' : '';
		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( KSOM_OPTION_NAME ); ?>[enable_twitter_card]" value="1" <?php echo esc_attr( $checked ); ?> />
			<?php esc_html_e( 'Twitter Cardタグを有効化する', 'kashiwazaki-seo-ogp-manager' ); ?>
		</label>
		<?php
	}

	/**
	 * Render enable robots max-image-preview field.
	 */
	public function render_enable_robots_max_image_field() {
		$checked = ! empty( $this->options['enable_robots_max_image'] ) ? 'checked' : '';
		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( KSOM_OPTION_NAME ); ?>[enable_robots_max_image]" value="1" <?php echo esc_attr( $checked ); ?> />
			<?php esc_html_e( 'Robots Metaタグ（max-image-preview:large）を出力する', 'kashiwazaki-seo-ogp-manager' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Google検索結果で大きい画像プレビューを表示するためのメタタグです。', 'kashiwazaki-seo-ogp-manager' ); ?>
			<a href="#" class="ksom-check-robots-duplicate" style="margin-left: 10px;"><?php esc_html_e( '重複チェック', 'kashiwazaki-seo-ogp-manager' ); ?></a>
			<span class="ksom-robots-check-result" style="margin-left: 10px;"></span>
		</p>
		<?php
	}

	/**
	 * Render site name field.
	 */
	public function render_site_name_field() {
		$value = isset( $this->options['site_name'] ) ? $this->options['site_name'] : '';
		?>
		<input type="text" name="<?php echo esc_attr( KSOM_OPTION_NAME ); ?>[site_name]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
		<p class="description"><?php esc_html_e( '空欄の場合はサイトタイトルを使用します。', 'kashiwazaki-seo-ogp-manager' ); ?></p>
		<?php
	}

	/**
	 * Render default OG type field.
	 */
	public function render_default_og_type_field() {
		$value   = isset( $this->options['default_og_type'] ) ? $this->options['default_og_type'] : 'website';
		$options = array(
			'website' => __( 'ウェブサイト', 'kashiwazaki-seo-ogp-manager' ),
			'article' => __( '記事', 'kashiwazaki-seo-ogp-manager' ),
			'blog'    => __( 'ブログ', 'kashiwazaki-seo-ogp-manager' ),
		);
		?>
		<select name="<?php echo esc_attr( KSOM_OPTION_NAME ); ?>[default_og_type]">
			<?php foreach ( $options as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php
			esc_html_e( 'カスタム投稿タイプなど、個別にOGタイプが設定されていないページで使用されます。標準投稿（post）は常に「記事」として扱われます。', 'kashiwazaki-seo-ogp-manager' );
			?>
		</p>
		<?php
	}

	/**
	 * Render default Twitter Card type field.
	 */
	public function render_default_twitter_card_type_field() {
		$value   = isset( $this->options['default_twitter_card_type'] ) ? $this->options['default_twitter_card_type'] : 'summary_large_image';
		$options = array(
			'summary'             => __( 'サマリー', 'kashiwazaki-seo-ogp-manager' ),
			'summary_large_image' => __( '大きい画像付きサマリー', 'kashiwazaki-seo-ogp-manager' ),
		);
		?>
		<select name="<?php echo esc_attr( KSOM_OPTION_NAME ); ?>[default_twitter_card_type]">
			<?php foreach ( $options as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php
			esc_html_e( 'Twitterでシェアされた際の表示形式です。「大きい画像付きサマリー」の方が目立ちやすくおすすめです。', 'kashiwazaki-seo-ogp-manager' );
			?>
		</p>
		<?php
	}

	/**
	 * Render Facebook App ID field.
	 */
	public function render_fb_app_id_field() {
		$value = isset( $this->options['fb_app_id'] ) ? $this->options['fb_app_id'] : '';
		?>
		<input type="text" name="<?php echo esc_attr( KSOM_OPTION_NAME ); ?>[fb_app_id]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
		<p class="description"><?php esc_html_e( 'オプション。Facebook Insights用のFacebook App IDを入力します。', 'kashiwazaki-seo-ogp-manager' ); ?></p>
		<?php
	}

	/**
	 * Render Twitter site field.
	 */
	public function render_twitter_site_field() {
		$value = isset( $this->options['twitter_site'] ) ? $this->options['twitter_site'] : '';
		?>
		<input type="text" name="<?php echo esc_attr( KSOM_OPTION_NAME ); ?>[twitter_site]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" placeholder="@username" />
		<p class="description"><?php esc_html_e( 'Twitterのユーザー名を入力します（例: @username）。', 'kashiwazaki-seo-ogp-manager' ); ?></p>
		<?php
	}

	/**
	 * Render default image field.
	 */
	public function render_default_image_field() {
		$value = isset( $this->options['default_image'] ) ? $this->options['default_image'] : '';
		?>
		<div class="ksom-image-upload">
			<input type="url" name="<?php echo esc_attr( KSOM_OPTION_NAME ); ?>[default_image]" id="ksom_default_image" value="<?php echo esc_url( $value ); ?>" class="regular-text" />
			<button type="button" class="button ksom-upload-image-button"><?php esc_html_e( '画像を選択', 'kashiwazaki-seo-ogp-manager' ); ?></button>
			<button type="button" class="button ksom-remove-image-button"><?php esc_html_e( '削除', 'kashiwazaki-seo-ogp-manager' ); ?></button>
			<?php if ( $value ) : ?>
				<div class="ksom-image-preview">
					<img src="<?php echo esc_url( $value ); ?>" alt="<?php esc_attr_e( 'デフォルトOGP画像', 'kashiwazaki-seo-ogp-manager' ); ?>" style="max-width: 300px; height: auto; margin-top: 10px;" />
				</div>
			<?php endif; ?>
			<p class="description"><?php esc_html_e( '推奨サイズ: 1200x630px。他の画像が利用できない場合に使用されます。', 'kashiwazaki-seo-ogp-manager' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Render enable for post types field.
	 */
	public function render_enable_for_post_types_field() {
		$selected_types = isset( $this->options['enable_for_post_types'] ) ? $this->options['enable_for_post_types'] : array( 'post', 'page' );
		$post_types     = get_post_types( array( 'public' => true ), 'objects' );

		if ( empty( $post_types ) ) {
			echo '<p>' . esc_html__( '公開投稿タイプが見つかりませんでした。', 'kashiwazaki-seo-ogp-manager' ) . '</p>';
			return;
		}

		?>
		<div class="ksom-post-types-selector">
			<p style="margin-bottom: 10px;">
				<button type="button" class="button button-small ksom-select-all-post-types"><?php esc_html_e( 'すべて選択', 'kashiwazaki-seo-ogp-manager' ); ?></button>
				<button type="button" class="button button-small ksom-deselect-all-post-types"><?php esc_html_e( 'すべて解除', 'kashiwazaki-seo-ogp-manager' ); ?></button>
			</p>
			<fieldset class="ksom-post-types-list">
				<?php
				foreach ( $post_types as $post_type ) {
					$checked = in_array( $post_type->name, $selected_types, true ) ? 'checked' : '';
					?>
					<label style="display: block; margin-bottom: 5px;">
						<input type="checkbox" class="ksom-post-type-checkbox" name="<?php echo esc_attr( KSOM_OPTION_NAME ); ?>[enable_for_post_types][]" value="<?php echo esc_attr( $post_type->name ); ?>" <?php echo esc_attr( $checked ); ?> />
						<?php echo esc_html( $post_type->label ); ?> (<?php echo esc_html( $post_type->name ); ?>)
					</label>
					<?php
				}
				?>
			</fieldset>
			<p class="description"><?php esc_html_e( '選択した投稿タイプでのみOGPメタボックスが表示され、OGPタグが出力されます。', 'kashiwazaki-seo-ogp-manager' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Get current options.
	 *
	 * @return array Current options.
	 */
	public function get_options() {
		return $this->options;
	}
}
