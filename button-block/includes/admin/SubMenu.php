<?php
namespace BTNB\Admin;

if ( !defined( 'ABSPATH' ) ) { exit; }

/**
 * SubMenu class
 * Adds a fallback submenu under Tools when the CPT admin menu is hidden.
 *
 * @package BTN\Admin
 */
class SubMenu {
	/**
	 * Constructor.
	 * Registers the admin_menu hook.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'adminMenu' ] );
	}

	/**
	 * Adds the 'Button Block' submenu under Tools when the CPT menu is hidden.
	 *
	 * @return void
	 */
	function adminMenu(){
		if( 'true' === get_option( 'button_block_option', '' ) ){
			add_submenu_page(
				'tools.php',
				__('Button Block - bPlugins', 'button-block'),
				__('Button Block', 'button-block'),
				'manage_options',
				'button-block',
				[ \BTNBPlugin::class, 'renderDashboard' ]
			);
		}
	}
}
new SubMenu();