<?php
/**
 * Main Plugin Class
 *
 * @package AIPromptsLibrary
 */

namespace AIPromptsLibrary;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 *
 * @since 1.0.0
 */
class Plugin {
	/**
	 * Plugin instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Plugin
	 */
	private static $instance = null;

	/**
	 * Get plugin instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->init_hooks();
		$this->init_classes();
	}

	/**
	 * Initialize WordPress hooks.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		add_action( 'init', array( $this, 'register_meta_fields' ) );
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'init', array( $this, 'register_block_bindings' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Initialize plugin classes.
	 *
	 * @since 1.0.0
	 */
	private function init_classes() {
		new AdminColumns();
		new SearchEnhancement();
		new ImportExport();
	}

	/**
	 * Register custom post type.
	 *
	 * @since 1.0.0
	 */
	public function register_post_type() {
		PostType::register();
	}

	/**
	 * Register custom taxonomy.
	 *
	 * @since 1.0.0
	 */
	public function register_taxonomy() {
		Taxonomy::register();
	}

	/**
	 * Register meta fields.
	 *
	 * @since 1.0.0
	 */
	public function register_meta_fields() {
		MetaFields::register();
	}

	/**
	 * Register blocks.
	 *
	 * @since 1.0.0
	 */
	public function register_blocks() {
		// Register the prompt content block.
		register_block_type(
			AI_PROMPTS_LIBRARY_PATH . 'build/prompt-content'
		);
	}

	/**
	 * Register block bindings.
	 *
	 * @since 1.0.0
	 */
	public function register_block_bindings() {
		BlockBindings::register();
	}

	/**
	 * Register REST API routes.
	 *
	 * @since 1.0.0
	 */
	public function register_rest_routes() {
		RestApi::register_routes();
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		// Enqueue admin CSS.
		wp_enqueue_style(
			'ai-prompts-library-admin',
			AI_PROMPTS_LIBRARY_URL . 'assets/css/admin.css',
			array(),
			AI_PROMPTS_LIBRARY_VERSION
		);

		// Enqueue admin JS.
		wp_enqueue_script(
			'ai-prompts-library-admin',
			AI_PROMPTS_LIBRARY_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			AI_PROMPTS_LIBRARY_VERSION,
			true
		);
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_frontend_assets() {
		// Frontend assets are enqueued by the block when needed.
	}
}
