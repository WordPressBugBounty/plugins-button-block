<?php
if ( !defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'btn_fs' ) ) {
	function btn_fs() {
		global $btn_fs;

		if ( !isset( $btn_fs ) ) {
			require_once BTN_DIR_PATH . '/vendor/freemius-lite/start.php';

			$disableAdminMenu = 'true' === get_option( 'button_block_option', 'false' );

			$btn_fs = fs_lite_dynamic_init( [
				'id'					=> '13491',
				'slug'					=> 'button-block',
				'__FILE__'				=> BTN_DIR_PATH . 'index.php',
				'premium_slug'			=> 'button-block-pro',
				'type'					=> 'plugin',
				'public_key'			=> 'pk_8fb5be7805414bb29e5b06c24566a',
				'is_premium'			=> false,
				'premium_suffix'		=> 'Pro',
				'has_premium_version'	=> true,
				'has_addons'			=> false,
				'has_paid_plans'		=> true,
				'menu'					=> [
					'slug'			=> $disableAdminMenu ? 'button-block' : 'edit.php?post_type=button-block',
					'first-path'	=> $disableAdminMenu ? 'tools.php?page=button-block' : 'edit.php?post_type=button-block&page=button-block',
					'parent'		=> $disableAdminMenu ? [
						'slug'	=> 'tools.php'
					] : null,
					'contact'		=> false,
					'support'		=> false
				]
			] );
		}

		return $btn_fs;
	}

	btn_fs();
	do_action( 'btn_fs_loaded' );
}
