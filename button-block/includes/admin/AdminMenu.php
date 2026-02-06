<?php
namespace BTN\Admin;

if ( !defined( 'ABSPATH' ) ) { exit; }

class AdminMenu {
	public $post_type = 'button-block';

	public function __construct() {
		add_action( 'admin_init', array($this, 'adminInit') );
		add_action( 'admin_enqueue_scripts', [$this, 'adminEnqueueScripts'] );
		add_action( 'admin_menu', [ $this, 'adminMenu' ] );
	}

	function adminInit(){
		register_setting( 'general', 'button_block_option', 'sanitize_text_field' );

		add_settings_field(
			'button_block_option_field',
			'Hide Button Block from admin Menu',
			[ $this , 'optionCallback' ], 
			'general'
		);
	}

	function optionCallback() {
		$value = get_option( 'button_block_option', '' ); ?>

		<label class='btnAdminHideSwitch'>
			<input type='checkbox' id='button_block_option' name='button_block_option' value='true' <?php checked( $value, 'true' ); ?>>
			<span class='slider round'></span>
		</label>
		<p class='description'>Turn this setting on or off.</p>
	<?php }

	function adminEnqueueScripts( $hook ){
		if( 'options-general.php' === $hook ){
			wp_enqueue_style( 'btn-admin-general', BTN_DIR_URL . 'build/admin/general.css', [], BTN_VERSION );
		}
	}

	function adminMenu() {
		add_submenu_page(
			'edit.php?post_type=' . $this->post_type,
			__( 'Help & Demos - Button Block', 'button-block' ),
			__( 'Help & Demos', 'button-block' ),
			'manage_options',
			'button-block',
			[ \BTNPlugin::class, 'renderDashboard' ]
		);
	}
}
new AdminMenu();