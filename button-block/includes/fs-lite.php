<?php
if ( !defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'btnb_fs' ) ) {
	/**
	 * Initializes and returns the Freemius Lite SDK instance for the free version.
	 *
	 * @return \FSLite The Freemius Lite SDK instance.
	 */
	function btnb_fs() {
		global $btnb_fs;

		if ( !isset( $btnb_fs ) ) {
			require_once BTNB_DIR_PATH . '/vendor/freemius-lite/start.php';

			$disableAdminMenu = 'true' === get_option( 'button_block_option', 'false' );

			$btnb_fs = fs_lite_dynamic_init( [
				'id'					=> '13491',
				'slug'					=> 'button-block',
				'__FILE__'				=> BTNB_DIR_PATH . 'index.php',
				'premium_slug'			=> 'button-block-pro',
				'type'					=> 'plugin',
				'public_key'			=> 'pk_8fb5be7805414bb29e5b06c24566a',
				'is_premium'			=> false,
				'menu'					=> [
					'slug'			=> $disableAdminMenu ? 'button-block' : 'edit.php?post_type=button-block',
					'first-path'	=> $disableAdminMenu ? 'tools.php?page=button-block' : 'edit.php?post_type=button-block&page=button-block',
					'parent'		=> $disableAdminMenu ? [
						'slug'	=> 'tools.php'
					] : null
				]
			] );
		}

		return $btnb_fs;
	}

	btnb_fs();
	do_action( 'btnb_fs_loaded' );
}
