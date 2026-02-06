<?php
/**
 * Plugin Name: Button Block
 * Description: Implement multi-functional button
 * Version: 1.2.3
 * Author: bPlugins
 * Author URI: http://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: button-block
   */

// ABS PATH
if ( !defined( 'ABSPATH' ) ) { exit; }

if ( function_exists( 'btn_fs' ) ) {
	btn_fs()->set_basename( false, __FILE__ );
}else{
	// Constant
	define( 'BTN_VERSION', isset( $_SERVER['HTTP_HOST'] ) && ( 'localhost' === $_SERVER['HTTP_HOST'] || 'plugins.local' === $_SERVER['HTTP_HOST'] ) ? time() : '1.2.3' );
	define( 'BTN_DIR_URL', plugin_dir_url( __FILE__ ) );
	define( 'BTN_DIR_PATH', plugin_dir_path( __FILE__ ) );
	define( 'BTN_HAS_PRO', file_exists( dirname(__FILE__) . '/vendor/freemius/start.php' ) );

	if ( BTN_HAS_PRO ) {
		require_once BTN_DIR_PATH . 'includes/fs.php';

		if( btn_fs()->can_use_premium_code() ){
			require_once BTN_DIR_PATH . 'includes/admin/EmailLead.php';
		}
	}else{
		require_once BTN_DIR_PATH . 'includes/fs-lite.php';
	}
	
	require_once BTN_DIR_PATH . 'includes/admin/CPT.php';
	require_once BTN_DIR_PATH . 'includes/admin/AdminMenu.php';
	require_once BTN_DIR_PATH . 'includes/admin/SubMenu.php';

	function btnIsPremium(){
		return BTN_HAS_PRO ? btn_fs()->can_use_premium_code() : false;
	}

	// Button Block
	if( !class_exists( 'BTNPlugin' ) ){
		class BTNPlugin{
			function __construct(){
				add_action( 'init', [$this, 'onInit'] );
				add_action( 'admin_enqueue_scripts', [$this, 'adminEnqueueScripts'] );
				add_action( 'enqueue_block_editor_assets', [$this, 'enqueueBlockEditorAssets'] );
				add_action( 'enqueue_block_assets', [$this, 'enqueueBlockAssets'] );
				add_action( 'wp_ajax_btnUserRoles', [$this, 'userRoles'] );
				add_action( 'wp_ajax_nopriv_btnUserRoles', [$this, 'userRoles'] );
			}

			function onInit() {
				register_block_type( __DIR__ . '/build' );
			}

			function adminEnqueueScripts( $hook ) {
				if( false !== strpos( $hook, 'button-block' ) ){
					wp_enqueue_style( 'btn-admin-dashboard', BTN_DIR_URL . 'build/admin/dashboard.css', [], BTN_VERSION );
					wp_enqueue_script( 'btn-admin-dashboard', BTN_DIR_URL . 'build/admin/dashboard.js', [ 'react', 'react-dom' ], BTN_VERSION, true );
					wp_set_script_translations( 'btn-admin-dashboard', 'button-block', BTN_DIR_PATH . 'languages' );
				}
			}

			function enqueueBlockEditorAssets(){
				wp_add_inline_script( 'btn-button-editor-script', 'const btnpipecheck = ' . wp_json_encode( btnIsPremium() ) .'; const btnpricingurl = "'. admin_url( 'true' !== get_option( 'button_block_option', '' ) ? 'edit.php?post_type=button-block&page=button-block#/pricing' : 'tools.php?page=button-block#/pricing' ) .'";', 'before' );
			}

			function enqueueBlockAssets(){
				wp_register_style( 'font-awesome-7', BTN_DIR_URL . 'public/css/font-awesome.min.css', [], '7.1.0' );
				wp_register_style( 'aos', BTN_DIR_URL . 'public/css/aos.css', [], '3.0.0' );
				wp_register_script( 'aos', BTN_DIR_URL . 'public/js/aos.js', [], '3.0.0', true );
			}

			static function renderDashboard(){ ?>
				<div
					id='btnDashboard'
					data-info='<?php echo esc_attr( wp_json_encode( [
						'version'	=> BTN_VERSION,
						'isPremium'	=> btnIsPremium(),
						'hasPro'	=> BTN_HAS_PRO
					] ) ); ?>'
				></div>
			<?php }

			function userRoles(){
				if (
					!wp_verify_nonce(
						sanitize_text_field( wp_unslash( isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : null ) ),
						'wp_rest'
					)
				) {
					wp_send_json_error( 'Invalid Request' );
				}
		
				global $wp_roles;
				wp_send_json_success( $wp_roles->get_names() );
			}
		}
		new BTNPlugin;
	}
}