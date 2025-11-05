<?php
/**
 * OGP Manager Core Class
 *
 * Core management class for OGP functionality.
 * Handles initialization and hook registration.
 *
 * @package KashiwazakiSeoOgpManager
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KSOM_OGP_Manager class.
 *
 * Core management class that handles OGP initialization and hook registration.
 *
 * @since 1.0.0
 */
class KSOM_OGP_Manager {

	/**
	 * Singleton instance.
	 *
	 * @var KSOM_OGP_Manager|null
	 */
	private static $instance = null;

	/**
	 * Meta tags handler instance.
	 *
	 * @var KSOM_OGP_Meta_Tags|null
	 */
	private $meta_tags = null;

	/**
	 * Image handler instance.
	 *
	 * @var KSOM_OGP_Image_Handler|null
	 */
	private $image_handler = null;

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Get singleton instance.
	 *
	 * @return KSOM_OGP_Manager Singleton instance.
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
		$this->init_handlers();
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
	 * Initialize handler instances.
	 */
	private function init_handlers() {
		$this->image_handler = new KSOM_OGP_Image_Handler( $this->options );
		$this->meta_tags     = new KSOM_OGP_Meta_Tags( $this->options, $this->image_handler );
	}

	/**
	 * Initialize WordPress hooks.
	 */
	private function init_hooks() {
		// Frontend hooks.
		if ( ! is_admin() ) {
			add_action( 'wp_head', array( $this, 'output_ogp_meta_tags' ), 1 );
			add_action( 'wp_head', array( $this, 'output_twitter_card_tags' ), 2 );
		}

		// Allow other plugins to hook into OGP Manager.
		do_action( 'ksom_ogp_manager_init', $this );
	}

	/**
	 * Output OGP meta tags.
	 *
	 * @since 1.0.0
	 */
	public function output_ogp_meta_tags() {
		// Check if OGP is enabled.
		if ( ! $this->is_ogp_enabled() ) {
			return;
		}

		// Allow plugins to disable OGP output for specific pages.
		if ( ! apply_filters( 'ksom_enable_ogp_output', true ) ) {
			return;
		}

		$this->meta_tags->output_ogp_tags();
	}

	/**
	 * Output Twitter Card tags.
	 *
	 * @since 1.0.0
	 */
	public function output_twitter_card_tags() {
		// Check if Twitter Cards is enabled.
		if ( ! $this->is_twitter_card_enabled() ) {
			return;
		}

		// Allow plugins to disable Twitter Card output for specific pages.
		if ( ! apply_filters( 'ksom_enable_twitter_card_output', true ) ) {
			return;
		}

		$this->meta_tags->output_twitter_card_tags();
	}

	/**
	 * Check if OGP is enabled.
	 *
	 * @return bool True if OGP is enabled, false otherwise.
	 */
	private function is_ogp_enabled() {
		return ! empty( $this->options['enable_ogp'] );
	}

	/**
	 * Check if Twitter Card is enabled.
	 *
	 * @return bool True if Twitter Card is enabled, false otherwise.
	 */
	private function is_twitter_card_enabled() {
		return ! empty( $this->options['enable_twitter_card'] );
	}

	/**
	 * Get plugin options.
	 *
	 * @return array Plugin options.
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Get meta tags handler.
	 *
	 * @return KSOM_OGP_Meta_Tags Meta tags handler instance.
	 */
	public function get_meta_tags_handler() {
		return $this->meta_tags;
	}

	/**
	 * Get image handler.
	 *
	 * @return KSOM_OGP_Image_Handler Image handler instance.
	 */
	public function get_image_handler() {
		return $this->image_handler;
	}

	/**
	 * Refresh options cache.
	 *
	 * Useful when options are updated.
	 *
	 * @since 1.0.0
	 */
	public function refresh_options() {
		$this->load_options();

		// Reinitialize handlers with new options.
		$this->init_handlers();
	}
}
