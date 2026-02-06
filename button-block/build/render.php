<?php
$id = wp_unique_id( 'btnButton-' );

extract( $attributes );

if( isset( $icon['class'] ) && !empty( $icon['class'] ) ){
	wp_enqueue_style( 'font-awesome-7' );
}
if( isset( $animationType ) && !empty( $animationType ) ){
	wp_enqueue_script( 'aos' );
	wp_enqueue_style( 'aos' );
}

$attributes['url'] = esc_url( $url );
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
	<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() is properly escaped ?>
	<?php echo get_block_wrapper_attributes( [ 'class' => btnIsPremium() ? 'premium' : 'free' ] ); ?>
	id='<?php echo esc_attr( $id ); ?>'
	data-nonce='<?php echo esc_attr( wp_json_encode( wp_create_nonce( 'wp_rest' ) ) ); ?>'
	data-attributes='<?php echo esc_attr( wp_json_encode( $attributes ) ); ?>'
	data-info='<?php echo esc_attr( wp_json_encode( [
		'userRoles' => is_user_logged_in() ? wp_get_current_user()->roles : [],
		'loginURL' => wp_login_url()
	] ) ); ?>'
	data-pipecheck='<?php echo esc_attr( btnIsPremium() ); ?>'
></div>