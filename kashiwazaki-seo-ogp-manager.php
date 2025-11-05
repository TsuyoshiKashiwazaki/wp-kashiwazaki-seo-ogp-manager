<?php
/**
 * Plugin Name: Kashiwazaki SEO OGP Manager
 * Plugin URI: https://www.tsuyoshikashiwazaki.jp
 * Description: SEO対策とSNSシェアを最適化する高機能OGP管理プラグイン。投稿ごとのOGP設定、Twitter Cards対応、プレビュー機能を搭載。
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: 柏崎剛 (Tsuyoshi Kashiwazaki)
 * Author URI: https://www.tsuyoshikashiwazaki.jp/profile/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: kashiwazaki-seo-ogp-manager
 * Domain Path: /languages
 *
 * @package KashiwazakiSeoOgpManager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin version.
if ( ! defined( 'KSOM_VERSION' ) ) {
	define( 'KSOM_VERSION', '1.0.0' );
}

// Plugin root file.
if ( ! defined( 'KSOM_PLUGIN_FILE' ) ) {
	define( 'KSOM_PLUGIN_FILE', __FILE__ );
}

// Plugin base name.
if ( ! defined( 'KSOM_PLUGIN_BASENAME' ) ) {
	define( 'KSOM_PLUGIN_BASENAME', plugin_basename( KSOM_PLUGIN_FILE ) );
}

// Plugin directory path.
if ( ! defined( 'KSOM_PLUGIN_PATH' ) ) {
	define( 'KSOM_PLUGIN_PATH', plugin_dir_path( KSOM_PLUGIN_FILE ) );
}

// Plugin directory URL.
if ( ! defined( 'KSOM_PLUGIN_URL' ) ) {
	define( 'KSOM_PLUGIN_URL', plugin_dir_url( KSOM_PLUGIN_FILE ) );
}

// Includes directory path.
if ( ! defined( 'KSOM_INCLUDES_PATH' ) ) {
	define( 'KSOM_INCLUDES_PATH', KSOM_PLUGIN_PATH . 'includes/' );
}

// Admin directory path.
if ( ! defined( 'KSOM_ADMIN_PATH' ) ) {
	define( 'KSOM_ADMIN_PATH', KSOM_PLUGIN_PATH . 'admin/' );
}

// Public directory path.
if ( ! defined( 'KSOM_PUBLIC_PATH' ) ) {
	define( 'KSOM_PUBLIC_PATH', KSOM_PLUGIN_PATH . 'public/' );
}

// Assets directory URL.
if ( ! defined( 'KSOM_ASSETS_URL' ) ) {
	define( 'KSOM_ASSETS_URL', KSOM_PLUGIN_URL . 'assets/' );
}

// Option name.
if ( ! defined( 'KSOM_OPTION_NAME' ) ) {
	define( 'KSOM_OPTION_NAME', 'ksom_options' );
}

/**
 * Get default options.
 *
 * @return array Default options.
 */
function ksom_get_default_options() {
	return array(
		'enable_ogp'                => true,
		'enable_twitter_card'       => true,
		'enable_robots_max_image'   => true,
		'default_og_type'           => 'website',
		'default_twitter_card_type' => 'summary_large_image',
		'site_name'                 => get_bloginfo( 'name' ),
		'fb_app_id'                 => '',
		'twitter_site'              => '',
		'default_image'             => '',
		'enable_for_post_types'     => array( 'post', 'page' ),
	);
}

/**
 * Class autoloader.
 *
 * @param string $class_name Class name to autoload.
 */
function ksom_autoloader( $class_name ) {
	// Check if the class belongs to this plugin.
	if ( strpos( $class_name, 'KSOM_' ) !== 0 ) {
		return;
	}

	// Convert class name to file path.
	$class_name = str_replace( 'KSOM_', '', $class_name );
	$class_name = strtolower( $class_name );
	$class_name = str_replace( '_', '-', $class_name );

	// Possible file paths.
	$paths = array(
		KSOM_INCLUDES_PATH . 'class-' . $class_name . '.php',
		KSOM_ADMIN_PATH . 'class-' . $class_name . '.php',
		KSOM_PUBLIC_PATH . 'class-' . $class_name . '.php',
	);

	// Try to load the file.
	foreach ( $paths as $path ) {
		if ( file_exists( $path ) ) {
			require_once $path;
			return;
		}
	}
}
spl_autoload_register( 'ksom_autoloader' );

/**
 * Main plugin class.
 */
class Kashiwazaki_Seo_Ogp_Manager {

	/**
	 * Singleton instance.
	 *
	 * @var Kashiwazaki_Seo_Ogp_Manager|null
	 */
	private static $instance = null;

	/**
	 * Options cache.
	 *
	 * @var array|null
	 */
	private $options = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Kashiwazaki_Seo_Ogp_Manager Singleton instance.
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
		// Initialize admin and metabox classes early (for admin area only).
		if ( is_admin() ) {
			KSOM_OGP_Admin::get_instance();
			KSOM_OGP_Metabox::get_instance();
		}

		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		// Core hooks.
		add_action( 'init', array( $this, 'init' ) );

		// Frontend hooks.
		add_action( 'wp_head', array( $this, 'output_ogp_tags' ), 1 );

		// Activation/Deactivation hooks.
		register_activation_hook( KSOM_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( KSOM_PLUGIN_FILE, array( $this, 'deactivate' ) );
	}

	/**
	 * Initialize plugin.
	 */
	public function init() {
		// Load text domain.
		load_plugin_textdomain(
			'kashiwazaki-seo-ogp-manager',
			false,
			dirname( KSOM_PLUGIN_BASENAME ) . '/languages'
		);

		// Load dependencies.
		$this->load_dependencies();
	}

	/**
	 * Load dependencies.
	 */
	private function load_dependencies() {
		// Classes will be autoloaded via ksom_autoloader.
		// No explicit require_once needed.
	}

	/**
	 * Output OGP tags.
	 */
	public function output_ogp_tags() {
		$options = $this->get_options();

		if ( ! $options['enable_ogp'] ) {
			return;
		}

		// Initialize OGP Manager.
		$ogp_manager = KSOM_OGP_Manager::get_instance();
		$ogp_manager->output_ogp_meta_tags();
	}

	/**
	 * Get options.
	 *
	 * @return array Plugin options.
	 */
	public function get_options() {
		if ( null === $this->options ) {
			$this->options = wp_parse_args(
				get_option( KSOM_OPTION_NAME, array() ),
				ksom_get_default_options()
			);
		}
		return $this->options;
	}

	/**
	 * Plugin activation.
	 */
	public function activate() {
		// Check minimum requirements.
		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			deactivate_plugins( KSOM_PLUGIN_BASENAME );
			wp_die(
				esc_html__( 'このプラグインはPHP 7.4以上が必要です。', 'kashiwazaki-seo-ogp-manager' ),
				esc_html__( 'プラグイン有効化エラー', 'kashiwazaki-seo-ogp-manager' ),
				array( 'back_link' => true )
			);
		}

		global $wp_version;
		if ( version_compare( $wp_version, '6.0', '<' ) ) {
			deactivate_plugins( KSOM_PLUGIN_BASENAME );
			wp_die(
				esc_html__( 'このプラグインはWordPress 6.0以上が必要です。', 'kashiwazaki-seo-ogp-manager' ),
				esc_html__( 'プラグイン有効化エラー', 'kashiwazaki-seo-ogp-manager' ),
				array( 'back_link' => true )
			);
		}

		// Add default options if not exist.
		if ( ! get_option( KSOM_OPTION_NAME ) ) {
			add_option( KSOM_OPTION_NAME, ksom_get_default_options() );
		}

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation.
	 */
	public function deactivate() {
		// Flush rewrite rules.
		flush_rewrite_rules();

		// Cleanup transients if any.
		delete_transient( 'ksom_cache' );
	}

}

/**
 * Initialize the plugin.
 *
 * @return Kashiwazaki_Seo_Ogp_Manager Plugin instance.
 */
function ksom_init() {
	return Kashiwazaki_Seo_Ogp_Manager::get_instance();
}
add_action( 'plugins_loaded', 'ksom_init' );
