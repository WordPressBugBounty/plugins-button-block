<?php
namespace BTN\Includes;

if( !class_exists( 'BTNCustomPost' ) ){
	class BTNCustomPost{
		public $post_type = 'button-block';

		public function __construct(){
			add_action( 'init', [$this, 'onInit'] );
			add_action( 'post_row_actions', [$this, 'postRowActions'], 10, 2 );
			add_action( 'admin_action_duplicate_btn_block_post', [$this, 'duplicatePost'] );
			add_filter( 'manage_button-block_posts_columns', [$this, 'manageBTNBlockPostsColumns'], 10 );
			add_action( 'manage_button-block_posts_custom_column', [$this, 'manageBTNBlockPostsCustomColumns'], 10, 2 );
			add_shortcode( 'btn_block', [$this, 'onAddShortcode'] );
			add_action( 'use_block_editor_for_post', [$this, 'useBlockEditorForPost'], 999, 2 );
		}
	
		function onInit(){
			$menuIcon = "<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 48 48' fill='#fff'><path d='M7 34Q5.75 34 4.875 33.125Q4 32.25 4 31V17Q4 15.75 4.875 14.875Q5.75 14 7 14H41Q42.25 14 43.125 14.875Q44 15.75 44 17V31Q44 32.25 43.125 33.125Q42.25 34 41 34H37.8V31H41Q41 31 41 31Q41 31 41 31V17Q41 17 41 17Q41 17 41 17H7Q7 17 7 17Q7 17 7 17V31Q7 31 7 31Q7 31 7 31H20.2V34ZM29 38 27.2 34 23.2 32.2 27.2 30.4 29 26.4 30.8 30.4 34.8 32.2 30.8 34ZM34 27.4 32.95 25.05 30.6 24 32.95 22.95 34 20.6 35.05 22.95 37.4 24 35.05 25.05Z'></path></svg>";

			$hide_form_menu = get_option( 'button_block_option', 'false' );
    		$show_ui = ( $hide_form_menu === 'true' ) ? false : true;

			register_post_type( $this->post_type, [
				'labels'			=> [
					'name'			=> __( 'Button Block', 'button-block' ),
					'singular_name'	=> __( 'Button Block', 'button-block' ),
					'add_new'		=> __( 'Add New', 'button-block' ),
					'add_new_item'	=> __( 'Add New Button', 'button-block' ),
					'edit_item'		=> __( 'Edit Button', 'button-block' ),
					'new_item'		=> __( 'New Button', 'button-block' ),
					'view_item'		=> __( 'View Button', 'button-block' ),
					'search_items'	=> __( 'Search Button', 'button-block' ),
					'not_found'		=> __( 'Sorry, we couldn\'t find any item you are looking for.', 'button-block' )
				],
				'public'				=> false,
				'show_ui'				=> $show_ui,
				'publicly_queryable'	=> false,
				'exclude_from_search'	=> false,
				'show_in_rest'			=> true,
				'menu_position'			=> 14,
				'menu_icon'				=> 'data:image/svg+xml;base64,' . base64_encode($menuIcon),	
				'has_archive'			=> false,
				'hierarchical'			=> false,
				'capability_type'		=> 'page',
				'rewrite'				=> [ 'slug' => 'button-block' ],
				'supports'				=> [ 'title', 'editor' ],
				'template'				=> [ ['btn/button'] ],
				'template_lock'			=> 'all'
			] ); // Register Post Type
		}

		function postRowActions( $actions, $post ){
			if ( $post->post_type == $this->post_type ) {
				$actions['duplicate'] = '<a href="' . admin_url( "admin.php?action=duplicate_btn_block_post&post={$post->ID}" ) . '">Duplicate</a>';
			}

			return $actions;
		}
	
		function duplicatePost(){
			if ( !isset( $_GET['post'] ) || !current_user_can( 'edit_posts') ) {
				wp_die( 'Permission denied' );
			}

			$postId = $_GET['post'];
			$post = get_post( $postId );

			if ( !$post ) {
				wp_die( 'Invalid post ID' );
			}

			if ( $post && !current_user_can( 'edit_post', $postId ) ) {
				wp_die( 'You do not have permission to duplicate this post.' );
			}

			$newPost = [
				'post_title' => $post->post_title . '(copy)',
				'post_content' => $post->post_content,
				'post_status' => $post->post_status,
				'post_type' => $post->post_type,
			];

			$newPostId = wp_insert_post( $newPost );
			wp_redirect( admin_url( "post.php?action=edit&post={$newPostId}" ) );
			exit;
		}

		function manageBTNBlockPostsColumns( $defaults ) {
			unset( $defaults['date'] );
			$defaults['shortcode'] = 'ShortCode';
			$defaults['date'] = 'Date';
			return $defaults;
		}
  
		function manageBTNBlockPostsCustomColumns( $column_name, $post_ID ) {
			if ( $column_name == 'shortcode' ) {
				echo "<div class='bPlAdminShortcode' id='bPlAdminShortcode-$post_ID'>
					<input value='[btn_block id=$post_ID]' onclick='copyBPlAdminShortcode($post_ID)'>
					<span class='tooltip'>Copy To Clipboard</span>
				</div>";
			}
		}
  
		function onAddShortcode( $atts ) {
			$post_id = $atts['id'];
			$post = get_post( $post_id );
	
			if ( !$post ) {
				return '';
			}
	
			if ( post_password_required( $post ) ) {
				return get_the_password_form( $post );
			}
	
			switch ( $post->post_status ) {
				case 'publish':
					return $this->displayContent( $post );

				case 'private':
					if (current_user_can('read_private_posts')) {
						return $this->displayContent( $post );
					}
					return '';

				case 'draft':
				case 'pending':
				case 'future':
					if ( current_user_can( 'edit_post', $post_id ) ) {
						return $this->displayContent( $post );
					}
					return '';

				default:
					return '';
			}
	  	}
  
	  	function displayContent( $post ){
			$blocks = parse_blocks( $post->post_content );
			return render_block( $blocks[0] );
		}

		function useBlockEditorForPost( $use, $post ){
			if ( $this->post_type === $post->post_type ) {
				return true;
			}
			return $use;
		}
	}
	new BTNCustomPost();
}