<?php
/**
 * OGP Meta Box Class
 *
 * Handles meta box registration and individual post OGP settings.
 *
 * @package KashiwazakiSeoOgpManager
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KSOM_OGP_Metabox class.
 *
 * Manages OGP meta box for posts and custom post types.
 *
 * @since 1.0.0
 */
class KSOM_OGP_Metabox {

	/**
	 * Singleton instance.
	 *
	 * @var KSOM_OGP_Metabox|null
	 */
	private static $instance = null;

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Meta box nonce name.
	 *
	 * @var string
	 */
	private $nonce_name = 'ksom_ogp_metabox_nonce';

	/**
	 * Meta box nonce action.
	 *
	 * @var string
	 */
	private $nonce_action = 'ksom_ogp_metabox_save';

	/**
	 * Get singleton instance.
	 *
	 * @return KSOM_OGP_Metabox Singleton instance.
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
		// Add meta box.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

		// Save meta box data.
		add_action( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );

		// Enqueue scripts for meta box.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_metabox_scripts' ) );
	}

	/**
	 * Add meta box.
	 */
	public function add_meta_box() {
		$post_types = isset( $this->options['enable_for_post_types'] ) ? $this->options['enable_for_post_types'] : array( 'post', 'page' );

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'ksom_ogp_metabox',
				__( 'OGP Settings', 'kashiwazaki-seo-ogp-manager' ),
				array( $this, 'render_meta_box' ),
				$post_type,
				'normal',
				'high'
			);
		}
	}

	/**
	 * Render meta box.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_meta_box( $post ) {
		// Add nonce field for security.
		wp_nonce_field( $this->nonce_action, $this->nonce_name );

		// Get current meta values.
		$og_title       = get_post_meta( $post->ID, '_ksom_og_title', true );
		$og_description = get_post_meta( $post->ID, '_ksom_og_description', true );
		$og_image       = get_post_meta( $post->ID, '_ksom_og_image', true );
		$og_type        = get_post_meta( $post->ID, '_ksom_og_type', true );
		$twitter_card   = get_post_meta( $post->ID, '_ksom_twitter_card_type', true );

		// Get default values.
		if ( empty( $og_type ) ) {
			$og_type = isset( $this->options['default_og_type'] ) ? $this->options['default_og_type'] : 'article';
		}
		if ( empty( $twitter_card ) ) {
			$twitter_card = isset( $this->options['default_twitter_card_type'] ) ? $this->options['default_twitter_card_type'] : 'summary_large_image';
		}

		?>
		<div class="ksom-metabox-wrapper">
			<style>
				.ksom-metabox-wrapper { padding: 10px 0; }
				.ksom-metabox-field { margin-bottom: 20px; }
				.ksom-metabox-field label { display: block; font-weight: 600; margin-bottom: 5px; }
				.ksom-metabox-field input[type="text"],
				.ksom-metabox-field input[type="url"],
				.ksom-metabox-field textarea,
				.ksom-metabox-field select { width: 100%; }
				.ksom-metabox-field textarea { min-height: 100px; }
				.ksom-metabox-field .description { margin-top: 5px; color: #666; font-style: italic; }
				.ksom-image-preview { margin-top: 10px; }
				.ksom-image-preview img { max-width: 300px; height: auto; border: 1px solid #ddd; }
				.ksom-button-group { margin-top: 10px; }
			</style>

			<!-- OG Title -->
			<div class="ksom-metabox-field">
				<label for="ksom_og_title">
					<?php esc_html_e( 'OG Title', 'kashiwazaki-seo-ogp-manager' ); ?>
				</label>
				<input type="text" id="ksom_og_title" name="ksom_og_title" value="<?php echo esc_attr( $og_title ); ?>" placeholder="<?php echo esc_attr( get_the_title( $post ) ); ?>" />
				<p class="description">
					<?php esc_html_e( 'Leave empty to use post title. Recommended length: 60-90 characters.', 'kashiwazaki-seo-ogp-manager' ); ?>
				</p>
			</div>

			<!-- OG Description -->
			<div class="ksom-metabox-field">
				<label for="ksom_og_description">
					<?php esc_html_e( 'OG Description', 'kashiwazaki-seo-ogp-manager' ); ?>
				</label>
				<textarea id="ksom_og_description" name="ksom_og_description" rows="4" placeholder="<?php esc_attr_e( 'Enter OG description...', 'kashiwazaki-seo-ogp-manager' ); ?>"><?php echo esc_textarea( $og_description ); ?></textarea>
				<p class="description">
					<?php esc_html_e( 'Leave empty to use post excerpt or auto-generated text. Recommended length: 150-200 characters.', 'kashiwazaki-seo-ogp-manager' ); ?>
				</p>
			</div>

			<!-- OG Image -->
			<div class="ksom-metabox-field">
				<label for="ksom_og_image">
					<?php esc_html_e( 'OG Image', 'kashiwazaki-seo-ogp-manager' ); ?>
				</label>
				<input type="url" id="ksom_og_image" name="ksom_og_image" value="<?php echo esc_url( $og_image ); ?>" placeholder="<?php esc_attr_e( 'https://example.com/image.jpg', 'kashiwazaki-seo-ogp-manager' ); ?>" />
				<div class="ksom-button-group">
					<button type="button" class="button ksom-select-image">
						<?php esc_html_e( 'Select Image', 'kashiwazaki-seo-ogp-manager' ); ?>
					</button>
					<button type="button" class="button ksom-remove-image">
						<?php esc_html_e( 'Remove Image', 'kashiwazaki-seo-ogp-manager' ); ?>
					</button>
				</div>
				<?php if ( $og_image ) : ?>
					<div class="ksom-image-preview">
						<img src="<?php echo esc_url( $og_image ); ?>" alt="<?php esc_attr_e( 'OGP Image Preview', 'kashiwazaki-seo-ogp-manager' ); ?>" />
					</div>
				<?php endif; ?>
				<p class="description">
					<?php esc_html_e( 'Leave empty to use featured image or first content image. Recommended size: 1200x630px.', 'kashiwazaki-seo-ogp-manager' ); ?>
				</p>
			</div>

			<!-- OG Type -->
			<div class="ksom-metabox-field">
				<label for="ksom_og_type">
					<?php esc_html_e( 'OG Type', 'kashiwazaki-seo-ogp-manager' ); ?>
				</label>
				<select id="ksom_og_type" name="ksom_og_type">
					<option value="article" <?php selected( $og_type, 'article' ); ?>><?php esc_html_e( 'Article', 'kashiwazaki-seo-ogp-manager' ); ?></option>
					<option value="website" <?php selected( $og_type, 'website' ); ?>><?php esc_html_e( 'Website', 'kashiwazaki-seo-ogp-manager' ); ?></option>
					<option value="blog" <?php selected( $og_type, 'blog' ); ?>><?php esc_html_e( 'Blog', 'kashiwazaki-seo-ogp-manager' ); ?></option>
					<option value="product" <?php selected( $og_type, 'product' ); ?>><?php esc_html_e( 'Product', 'kashiwazaki-seo-ogp-manager' ); ?></option>
					<option value="video" <?php selected( $og_type, 'video' ); ?>><?php esc_html_e( 'Video', 'kashiwazaki-seo-ogp-manager' ); ?></option>
				</select>
				<p class="description">
					<?php esc_html_e( 'Select the type of content for Open Graph.', 'kashiwazaki-seo-ogp-manager' ); ?>
				</p>
			</div>

			<!-- Twitter Card Type -->
			<div class="ksom-metabox-field">
				<label for="ksom_twitter_card_type">
					<?php esc_html_e( 'Twitter Card Type', 'kashiwazaki-seo-ogp-manager' ); ?>
				</label>
				<select id="ksom_twitter_card_type" name="ksom_twitter_card_type">
					<option value="summary" <?php selected( $twitter_card, 'summary' ); ?>><?php esc_html_e( 'Summary', 'kashiwazaki-seo-ogp-manager' ); ?></option>
					<option value="summary_large_image" <?php selected( $twitter_card, 'summary_large_image' ); ?>><?php esc_html_e( 'Summary with Large Image', 'kashiwazaki-seo-ogp-manager' ); ?></option>
				</select>
				<p class="description">
					<?php esc_html_e( 'Select the Twitter Card display type.', 'kashiwazaki-seo-ogp-manager' ); ?>
				</p>
			</div>

			<!-- Preview Section -->
			<div class="ksom-metabox-field">
				<h4><?php esc_html_e( 'Preview', 'kashiwazaki-seo-ogp-manager' ); ?></h4>
				<p class="description">
					<?php
					printf(
						/* translators: 1: Facebook debugger URL, 2: Twitter validator URL */
						esc_html__( 'You can test your OGP tags using %1$s and %2$s after publishing.', 'kashiwazaki-seo-ogp-manager' ),
						'<a href="https://developers.facebook.com/tools/debug/" target="_blank">' . esc_html__( 'Facebook Sharing Debugger', 'kashiwazaki-seo-ogp-manager' ) . '</a>',
						'<a href="https://cards-dev.twitter.com/validator" target="_blank">' . esc_html__( 'Twitter Card Validator', 'kashiwazaki-seo-ogp-manager' ) . '</a>'
					);
					?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Save meta box data.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_meta_box( $post_id, $post ) {
		// Verify nonce.
		if ( ! isset( $_POST[ $this->nonce_name ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $this->nonce_name ] ) ), $this->nonce_action ) ) {
			return;
		}

		// Check autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check post type.
		$enabled_post_types = isset( $this->options['enable_for_post_types'] ) ? $this->options['enable_for_post_types'] : array( 'post', 'page' );
		if ( ! in_array( $post->post_type, $enabled_post_types, true ) ) {
			return;
		}

		// Save OG Title.
		if ( isset( $_POST['ksom_og_title'] ) ) {
			$og_title = sanitize_text_field( wp_unslash( $_POST['ksom_og_title'] ) );
			$og_title = trim( $og_title );
			$og_title = wp_strip_all_tags( $og_title );

			// Limit length to recommended 90 characters.
			if ( mb_strlen( $og_title ) > 90 ) {
				$og_title = mb_substr( $og_title, 0, 90 );
			}

			if ( ! empty( $og_title ) ) {
				update_post_meta( $post_id, '_ksom_og_title', $og_title );
			} else {
				delete_post_meta( $post_id, '_ksom_og_title' );
			}
		} else {
			delete_post_meta( $post_id, '_ksom_og_title' );
		}

		// Save OG Description.
		if ( isset( $_POST['ksom_og_description'] ) ) {
			$og_description = sanitize_textarea_field( wp_unslash( $_POST['ksom_og_description'] ) );
			$og_description = trim( $og_description );
			$og_description = wp_strip_all_tags( $og_description );

			// Remove multiple spaces and line breaks.
			$og_description = preg_replace( '/\s+/', ' ', $og_description );

			// Limit length to recommended 200 characters.
			if ( mb_strlen( $og_description ) > 200 ) {
				$og_description = mb_substr( $og_description, 0, 200 );
			}

			if ( ! empty( $og_description ) ) {
				update_post_meta( $post_id, '_ksom_og_description', $og_description );
			} else {
				delete_post_meta( $post_id, '_ksom_og_description' );
			}
		} else {
			delete_post_meta( $post_id, '_ksom_og_description' );
		}

		// Save OG Image.
		if ( isset( $_POST['ksom_og_image'] ) ) {
			$og_image = wp_unslash( $_POST['ksom_og_image'] );
			$og_image = esc_url_raw( trim( $og_image ) );

			// Validate URL format and image extension.
			if ( ! empty( $og_image ) && filter_var( $og_image, FILTER_VALIDATE_URL ) && KSOM_OGP_Image_Handler::has_valid_image_extension( $og_image ) ) {
				update_post_meta( $post_id, '_ksom_og_image', $og_image );
			} else {
				// Not a valid image URL, delete meta.
				delete_post_meta( $post_id, '_ksom_og_image' );
			}
		} else {
			delete_post_meta( $post_id, '_ksom_og_image' );
		}

		// Save OG Type.
		if ( isset( $_POST['ksom_og_type'] ) ) {
			$og_type = sanitize_text_field( wp_unslash( $_POST['ksom_og_type'] ) );
			$og_type = trim( strtolower( $og_type ) );

			// Validate OG type against whitelist.
			$allowed_types = array( 'article', 'website', 'blog', 'product', 'video' );
			if ( in_array( $og_type, $allowed_types, true ) ) {
				update_post_meta( $post_id, '_ksom_og_type', $og_type );
			} else {
				// Invalid type, delete or use default.
				delete_post_meta( $post_id, '_ksom_og_type' );
			}
		} else {
			delete_post_meta( $post_id, '_ksom_og_type' );
		}

		// Save Twitter Card Type.
		if ( isset( $_POST['ksom_twitter_card_type'] ) ) {
			$twitter_card_type = sanitize_text_field( wp_unslash( $_POST['ksom_twitter_card_type'] ) );
			$twitter_card_type = trim( strtolower( $twitter_card_type ) );

			// Validate Twitter Card type against whitelist.
			$allowed_card_types = array( 'summary', 'summary_large_image' );
			if ( in_array( $twitter_card_type, $allowed_card_types, true ) ) {
				update_post_meta( $post_id, '_ksom_twitter_card_type', $twitter_card_type );
			} else {
				// Invalid type, delete or use default.
				delete_post_meta( $post_id, '_ksom_twitter_card_type' );
			}
		} else {
			delete_post_meta( $post_id, '_ksom_twitter_card_type' );
		}

		// Allow other plugins to save additional meta.
		do_action( 'ksom_save_ogp_meta', $post_id, $post );
	}

	/**
	 * Enqueue meta box scripts and styles.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_metabox_scripts( $hook ) {
		// Only load on post edit screens.
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		// Check if current post type is enabled.
		global $post;
		if ( ! $post ) {
			return;
		}

		$enabled_post_types = isset( $this->options['enable_for_post_types'] ) ? $this->options['enable_for_post_types'] : array( 'post', 'page' );
		if ( ! in_array( $post->post_type, $enabled_post_types, true ) ) {
			return;
		}

		// Enqueue media uploader.
		wp_enqueue_media();

		// Enqueue metabox CSS.
		wp_enqueue_style(
			'ksom-metabox-style',
			KSOM_ASSETS_URL . 'css/metabox-style.css',
			array(),
			KSOM_VERSION
		);

		// Enqueue metabox JS.
		wp_enqueue_script(
			'ksom-metabox-script',
			KSOM_ASSETS_URL . 'js/metabox-script.js',
			array( 'jquery', 'media-upload' ),
			KSOM_VERSION,
			true
		);

		// Localize script.
		wp_localize_script(
			'ksom-metabox-script',
			'ksomMetabox',
			array(
				'selectImage'     => __( 'Select OGP Image', 'kashiwazaki-seo-ogp-manager' ),
				'useThisImage'    => __( 'Use This Image', 'kashiwazaki-seo-ogp-manager' ),
				'removeImage'     => __( 'Remove Image', 'kashiwazaki-seo-ogp-manager' ),
				'imageFieldId'    => 'ksom_og_image',
				'previewSelector' => '.ksom-image-preview',
			)
		);
	}

	/**
	 * Get meta value for a post.
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $meta_key Meta key.
	 * @param mixed  $default  Default value if meta doesn't exist.
	 * @return mixed Meta value or default.
	 */
	public function get_meta( $post_id, $meta_key, $default = '' ) {
		$value = get_post_meta( $post_id, '_ksom_' . $meta_key, true );
		return ! empty( $value ) ? $value : $default;
	}

	/**
	 * Delete all OGP meta for a post.
	 *
	 * @param int $post_id Post ID.
	 */
	public function delete_all_meta( $post_id ) {
		delete_post_meta( $post_id, '_ksom_og_title' );
		delete_post_meta( $post_id, '_ksom_og_description' );
		delete_post_meta( $post_id, '_ksom_og_image' );
		delete_post_meta( $post_id, '_ksom_og_type' );
		delete_post_meta( $post_id, '_ksom_twitter_card_type' );

		do_action( 'ksom_delete_ogp_meta', $post_id );
	}
}
