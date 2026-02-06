<?php
namespace BTN\Admin;

if ( !defined( 'ABSPATH' ) ) { exit; }

class SubMenu {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'adminMenu' ] );
	}

	function adminMenu(){
		if( 'true' === get_option( 'button_block_option', '' ) ){
			add_submenu_page(
				'tools.php',
				__('Button Block - bPlugins', 'button-block'),
				__('Button Block', 'button-block'),
				'manage_options',
				'button-block',
				[ \BTNPlugin::class, 'renderDashboard' ]
			);
		}
	}
}
new SubMenu();