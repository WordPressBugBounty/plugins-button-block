<?php
namespace BTNB\Admin;

if ( !defined( 'ABSPATH' ) ) { exit; }

/**
 * AdminMenu class
 * Registers the settings field to hide the CPT menu and adds the dashboard submenu page.
 *
 * @package BTN\Admin
 */
class AdminMenu {
	public $post_type = 'button-block';

	/**
	 * Constructor.
	 * Registers hooks for admin settings, scripts, and menu.
	 */
	public function __construct() {
		add_action( 'admin_init', array($this, 'adminInit') );
		add_action( 'admin_enqueue_scripts', [$this, 'adminEnqueueScripts'] );
		add_action( 'admin_menu', [ $this, 'adminMenu' ] );
	}

	/**
	 * Registers the 'button_block_option' setting and adds a toggle field to Settings > General.
	 *
	 * @return void
	 */
	function adminInit(){
		register_setting( 'general', 'button_block_option', 'sanitize_text_field' );

		add_settings_field(
			'button_block_option_field',
			'Hide Button Block from admin Menu',
			[ $this , 'optionCallback' ], 
			'general'
		);
	}

	/**
	 * Renders the checkbox toggle for the 'Hide Button Block from admin menu' setting.
	 *
	 * @return void
	 */
	function optionCallback() {
		$value = get_option( 'button_block_option', '' ); ?>

		<label class='btnAdminHideSwitch'>
			<input type='checkbox' id='button_block_option' name='button_block_option' value='true' <?php checked( $value, 'true' ); ?>>
			<span class='slider round'></span>
		</label>
		<p class='description'>Turn this setting on or off.</p>
	<?php }

	/**
	 * Enqueues admin styles for the General Settings page.
	 *
	 * @param string $hook The current admin page hook suffix.
	 * @return void
	 */
	function adminEnqueueScripts( $hook ){
		if( 'options-general.php' === $hook ){
			wp_enqueue_style( 'btn-admin-general', BTNB_DIR_URL . 'build/admin/general.css', [], BTNB_VERSION );
		}
	}

	/**
	 * Adds the 'Help & Demos' submenu page under the button-block CPT menu.
	 *
	 * @return void
	 */
	function adminMenu() {
		add_submenu_page(
			'edit.php?post_type=' . $this->post_type,
			__( 'Help & Demos - Button Block', 'button-block' ),
			__( 'Help & Demos', 'button-block' ),
			'manage_options',
			'button-block',
			[ \BTNBPlugin::class, 'renderDashboard' ]
		);
	}
}
new AdminMenu();