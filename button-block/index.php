<?php
/**
 * Plugin Name: Button Block
 * Description: Implement multi-functional button
 * Version: 1.2.5
 * Author: bPlugins
 * Author URI: http://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: button-block
 * Requires at least: 6.5
 * Tested up to: 7.0
 * Requires PHP: 7.4
 */

// ABS PATH
if ( !defined( 'ABSPATH' ) ) { exit; }

if ( function_exists( 'btnb_fs' ) ) {
	btnb_fs()->set_basename( true, __FILE__ );
}else{
	define( 'BTNB_VERSION', ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? time() : '1.2.5' );
	define( 'BTNB_DIR_URL', plugin_dir_url( __FILE__ ) );
	define( 'BTNB_DIR_PATH', plugin_dir_path( __FILE__ ) );
	define( 'BTNB_HAS_PRO', false );
	define( 'BTNB_OPTIONS_KEY', 'btn_options' );

	require_once BTNB_DIR_PATH . 'includes/fs-lite.php';

	require_once BTNB_DIR_PATH . 'includes/admin/CPT.php';
	require_once BTNB_DIR_PATH . 'includes/admin/AdminMenu.php';
	require_once BTNB_DIR_PATH . 'includes/admin/SubMenu.php';
	require_once BTNB_DIR_PATH . 'includes/Options.php';

	if( !class_exists( 'BTNBPlugin' ) ){
		/**
		 * Main plugin class responsible for initialization, enqueuing assets, and registering block types.
		 *
		 * @package BTN
		 */
		class BTNBPlugin{
			/**
			 * Constructor.
			 * Registers hooks for plugin initialization, asset enqueuing, and AJAX handlers.
			 */
			function __construct(){
				add_action( 'init', [$this, 'onInit'] );
				add_action( 'admin_enqueue_scripts', [$this, 'adminEnqueueScripts'] );
				add_action( 'enqueue_block_editor_assets', [$this, 'enqueueBlockEditorAssets'] );
				add_action( 'enqueue_block_assets', [$this, 'enqueueBlockAssets'] );

				add_filter( 'plugin_action_links', [$this, 'pluginActionLinks'], 10, 2 );
				add_filter( 'default_title', [$this, 'defaultTitle'], 10, 2 );
				add_filter( 'default_content', [$this, 'defaultContent'], 10, 2 );
			}
			
			/**
			 * Filters the default post title for new pages created via the dashboard "Start Now" link.
			 *
			 * @param string   $title The default post title.
			 * @param \WP_Post $post  The post object.
			 * @return string The filtered post title.
			 */
			function defaultTitle( $title, $post ) {
				if ( 'page' === $post->post_type && isset( $_GET['title'] ) ) {
					$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';

					if ( wp_verify_nonce( $nonce, 'btnCreatePage' ) ) {
						return sanitize_text_field( wp_unslash( $_GET['title'] ) );
					}
				}
				return $title;
			}

			/**
			 * Filters the default post content for new pages created via the dashboard "Start Now" link.
			 *
			 * @param string   $content The default post content.
			 * @param \WP_Post $post    The post object.
			 * @return string The filtered post content.
			 */
			function defaultContent( $content, $post ) {
				if ( 'page' === $post->post_type && isset( $_GET['content'] ) ) {
					$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';

					if ( wp_verify_nonce( $nonce, 'btnCreatePage' ) ) {
						return wp_kses_post( wp_unslash( $_GET['content'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					}
				}
				return $content;
			}

			/**
			 * Adds custom action links to the plugin entry on the Plugins page.
			 *
			 * @param array  $links The existing action links.
			 * @param string $file  The plugin file path.
			 * @return array Modified action links.
			 */
			function pluginActionLinks( $links, $file ) {
				if( plugin_basename( __FILE__ ) === $file ) {
					$helpDemosLink = admin_url( 'true' !== get_option( 'button_block_option', '' ) ? 'edit.php?post_type=button-block&page=button-block#/welcome' : 'tools.php?page=button-block#/welcome' );

					$links['help-and-demos'] = sprintf( '<a href="%s" style="%s">%s</a>', $helpDemosLink, 'color:#FF7A00;font-weight:bold', __( 'Help & Demos', 'button-block' ) );
				}
	
				return $links;
			}

			/**
			 * Initializes the plugin by registering the block type.
			 *
			 * @return void
			 */
			function onInit() {
				register_block_type( __DIR__ . '/build' );
			}

			/**
			 * Enqueues scripts and styles for the admin dashboard pages.
			 *
			 * @param string $hook The current admin page hook suffix.
			 * @return void
			 */
			function adminEnqueueScripts( $hook ) {
				if( false !== strpos( $hook, 'button-block' ) ){
					wp_enqueue_style( 'btn-admin-dashboard', BTNB_DIR_URL . 'build/admin/dashboard.css', [], BTNB_VERSION );

					$asset_file = include BTNB_DIR_PATH . 'build/admin/dashboard.asset.php';
					wp_enqueue_script( 'btn-admin-dashboard', BTNB_DIR_URL . 'build/admin/dashboard.js', array_merge( $asset_file['dependencies'], [ 'wp-util' ] ), BTNB_VERSION, true );
					wp_set_script_translations( 'btn-admin-dashboard', 'button-block', BTNB_DIR_PATH . 'languages' );
				}
			}

			/**
			 * Enqueues inline scripts for the block editor with premium status and pricing URL.
			 *
			 * @return void
			 */
			function enqueueBlockEditorAssets(){
				// FIXED: Extract URL separately and use wp_json_encode() for safe escaping
				$pricing_page = 'true' !== get_option( 'button_block_option', '' )
					? 'edit.php?post_type=button-block&page=button-block#/pricing'
					: 'tools.php?page=button-block#/pricing';
				$pricing_url = admin_url( $pricing_page );

				wp_add_inline_script( 'btn-button-editor-script',
					'const btnpricingurl = ' . wp_json_encode( $pricing_url ) . ';',
					'before'
				);
			}

			/**
			 * Registers shared block assets (Font Awesome, AOS) for both editor and frontend.
			 *
			 * @return void
			 */
			function enqueueBlockAssets(){
				wp_register_style( 'font-awesome-7', BTNB_DIR_URL . 'public/css/font-awesome.min.css', [], '7.1.0' );
				wp_register_style( 'aos', BTNB_DIR_URL . 'public/css/aos.css', [], '3.0.0' );
				wp_register_script( 'aos', BTNB_DIR_URL . 'public/js/aos.js', [], '3.0.0', true );
			}

			/**
			 * Renders the dashboard container div with plugin info as a data attribute.
			 *
			 * @return void
			 */
			static function renderDashboard(){ ?>
				<div
					id='btnDashboard'
					data-info='<?php echo esc_attr( wp_json_encode( [
						'version' => BTNB_VERSION,
						'adminUrl' => admin_url(),
						'startUrl' => admin_url( 'post-new.php?post_type=page&title=' . rawurlencode( 'Button Block' ) . '&content=' . rawurlencode( '<!-- wp:btn/button /-->' ) . '&nonce=' . wp_create_nonce( 'btnCreatePage' ) ),
						'licenseActiveNonce' => wp_create_nonce( 'bPlLicenseActivation' ),
						'deleteDataOnUninstall' => (bool) \BTNB\Options::getOptions()['delete_data_on_uninstall'],
						'uninstallNonce' => wp_create_nonce( 'btnSaveUninstallOption' )
					] ) ); ?>'
				></div>
			<?php }
		}
		new BTNBPlugin;
	}
}