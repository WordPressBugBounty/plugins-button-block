<?php

/**
 * Plugin Name: Button Block
 * Description: Implement multi-functional button
 * Version: 1.2.1
 * Author: bPlugins
 * Author URI: http://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: button-block
 */
// ABS PATH
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'btn_fs' ) ) {
    btn_fs()->set_basename( false, __FILE__ );
} else {
    // Constant
    define( 'BTN_VERSION', ( isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.2.1' ) );
    define( 'BTN_DIR_URL', plugin_dir_url( __FILE__ ) );
    define( 'BTN_DIR_PATH', plugin_dir_path( __FILE__ ) );
    define( 'BTN_HAS_PRO', file_exists( dirname( __FILE__ ) . '/vendor/freemius/start.php' ) );
    if ( !function_exists( 'btn_fs' ) ) {
        function btn_fs() {
            global $btn_fs;
            if ( !isset( $btn_fs ) ) {
                if ( BTN_HAS_PRO ) {
                    require_once dirname( __FILE__ ) . '/vendor/freemius/start.php';
                } else {
                    require_once dirname( __FILE__ ) . '/vendor/freemius-lite/start.php';
                }
                $btnConfig = [
                    'id'                  => '13491',
                    'slug'                => 'button-block',
                    'premium_slug'        => 'button-block-pro',
                    'type'                => 'plugin',
                    'public_key'          => 'pk_8fb5be7805414bb29e5b06c24566a',
                    'is_premium'          => true,
                    'premium_suffix'      => 'Pro',
                    'has_premium_version' => true,
                    'has_addons'          => false,
                    'has_paid_plans'      => true,
                    'trial'               => [
                        'days'               => 7,
                        'is_require_payment' => true,
                    ],
                    'has_affiliation'     => 'all',
                    'menu'                => [
                        'slug'       => 'edit.php?post_type=button-block',
                        'first-path' => 'edit.php?post_type=button-block',
                        'contact'    => false,
                        'support'    => false,
                    ],
                ];
                $btn_fs = ( BTN_HAS_PRO ? fs_dynamic_init( $btnConfig ) : fs_lite_dynamic_init( $btnConfig ) );
            }
            return $btn_fs;
        }

        btn_fs();
        do_action( 'btn_fs_loaded' );
    }
    require_once BTN_DIR_PATH . 'includes/AdminMenu.php';
    require_once BTN_DIR_PATH . 'includes/CustomPost.php';
    function btnIsPremium() {
        return ( BTN_HAS_PRO ? btn_fs()->can_use_premium_code() : false );
    }

    if ( BTN_HAS_PRO && btn_fs()->can_use_premium_code() ) {
        require_once BTN_DIR_PATH . 'includes/EmailLead.php';
    }
    // Button Block
    if ( !class_exists( 'BTNPlugin' ) ) {
        class BTNPlugin {
            function __construct() {
                add_action( 'enqueue_block_assets', [$this, 'enqueueBlockAssets'] );
                add_action( 'init', [$this, 'onInit'] );
                add_action( 'wp_ajax_btnPipeChecker', [$this, 'btnPipeChecker'] );
                add_action( 'wp_ajax_nopriv_btnPipeChecker', [$this, 'btnPipeChecker'] );
                add_action( 'wp_ajax_btnUserRoles', [$this, 'userRoles'] );
                add_action( 'wp_ajax_nopriv_btnUserRoles', [$this, 'userRoles'] );
            }

            function enqueueBlockAssets() {
                wp_register_style(
                    'fontAwesome',
                    BTN_DIR_URL . 'public/css/font-awesome.min.css',
                    [],
                    '6.4.2'
                );
                wp_register_style(
                    'aos',
                    BTN_DIR_URL . 'public/css/aos.css',
                    [],
                    '3.0.0'
                );
                wp_register_script(
                    'aos',
                    BTN_DIR_URL . 'public/js/aos.js',
                    [],
                    '3.0.0',
                    true
                );
            }

            function onInit() {
                register_block_type( __DIR__ . '/build' );
            }

            function btnPipeChecker() {
                if ( !wp_verify_nonce( sanitize_text_field( wp_unslash( ( isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : null ) ) ), 'wp_rest' ) ) {
                    wp_send_json_error( 'Invalid Request' );
                }
                wp_send_json_success( [
                    'isPipe' => btnIsPremium(),
                ] );
            }

            function userRoles() {
                if ( !wp_verify_nonce( sanitize_text_field( wp_unslash( ( isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : null ) ) ), 'wp_rest' ) ) {
                    wp_send_json_error( 'Invalid Request' );
                }
                global $wp_roles;
                wp_send_json_success( $wp_roles->get_names() );
            }

        }

        new BTNPlugin();
    }
}