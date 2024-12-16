<?php
namespace BTN\Includes;

if( !class_exists( 'BTNAdminMenu' ) ){
	class BTNAdminMenu{
		public function __construct(){
			add_action( 'admin_init', array($this, 'adminInit') );
			add_action( 'admin_enqueue_scripts', [$this, 'adminEnqueueScripts'] );
			add_action( 'admin_menu', [$this, 'adminMenu'] );
		}

		function adminInit(){
			register_setting( 'general', 'button_block_option', 'sanitize_text_field' );
	
			add_settings_field(
				'button_block_option_field',
				'Hide Button Block from admin Menu',
				[ $this , 'button_block_option_callback' ], 
				'general'
			);
		}

		function button_block_option_callback() {
			$value = get_option( 'button_block_option', 'false' ); ?>
			
			<label class='switch'>
				<input type='checkbox' id='button_block_option' name='button_block_option' value='true' <?php checked( $value, 'true' ); ?>>
			  <span class='slider round'></span>
			</label>
			<p class='description'>Turn this setting on or off.</p>
		<?php }
	
		function adminEnqueueScripts( $hook ){
			if( 'edit.php' === $hook || strpos( $hook, '_button-block' ) ){
				wp_enqueue_style( 'btn-admin', BTN_DIR_URL . 'build/admin.css', [], BTN_VERSION );
				wp_enqueue_script( 'btn-admin', BTN_DIR_URL . 'build/admin.js', [ 'react', 'react-dom' ], BTN_VERSION );
				wp_set_script_translations( 'btn-admin', 'button-block', BTN_DIR_PATH . 'languages' );
			}

			if( 'options-general.php' === $hook ){
				wp_enqueue_style( 'btn-admin-general', BTN_DIR_URL . 'build/admin-general.css', [], BTN_VERSION );
			}
		}

		function adminMenu(){
			// Help Submenu
			add_submenu_page(
				'edit.php?post_type=button-block',
				__( 'Button Block Help', 'button-block' ),
				__( 'Help', 'button-block' ),
				'manage_options',
				'button-block-help',
				[$this, 'helpPage']
			);

			// Upgrade Submenu
			// if( BTN_HAS_FREE ){
			// 	add_submenu_page(
			// 		'edit.php?post_type=button-block',
			// 		__( 'Button Block - Upgrade', 'button-block' ),
			// 		__( 'Upgrade', 'button-block' ),
			// 		'manage_options',
			// 		'btn-upgrade',
			// 		[$this, 'upgradePage']
			// 	);
			// }
		}

		function helpPage(){ ?>
			<div class='bplAdminHelpPage'></div>
		<?php }

		function upgradePage(){ ?>
			<div id='bplUpgradePage'></div>
		<?php }
	}
	new BTNAdminMenu();
}