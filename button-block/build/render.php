<?php
extract( $attributes );

$id = wp_unique_id( 'btnButton-' );

$popup = $popup ?? [ 'type' => 'image', 'content' => '', 'caption' => '' ];
if ( 'content' === $popup['type'] ) {
	$blocks = parse_blocks( $popup['content'] );
	$popup['content'] = '';
	foreach ( $blocks as $block ) {
		$popup['content'] .= render_block( $block );
	}
} // Convert the blocks to dom elements
?>
<div
	<?php echo get_block_wrapper_attributes( [ 'class' => btnIsPremium() ? 'premium' : 'free' ] ); ?>
	id='<?php echo esc_attr( $id ); ?>'
	data-nonce='<?php echo esc_attr( wp_json_encode( wp_create_nonce( 'wp_ajax' ) ) ); ?>'
	data-attributes='<?php echo esc_attr( wp_json_encode( $attributes ) ); ?>'
	data-info='<?php echo esc_attr( wp_json_encode( [
		'userRoles' => is_user_logged_in() ? wp_get_current_user()->roles : [],
		'loginURL' => wp_login_url()
	] ) ); ?>'
></div>