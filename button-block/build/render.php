<?php
if ( !defined( 'ABSPATH' ) ) { exit; }

$btnbId = wp_unique_id( 'btnButton-' );

// Extract attributes with defaults
$btnbIcon = isset( $attributes['icon'] ) ? (array) $attributes['icon'] : [];
$btnbAnimationType = isset( $attributes['animationType'] ) ? sanitize_text_field( $attributes['animationType'] ) : '';
$btnbUrl = isset( $attributes['url'] ) ? esc_url_raw( $attributes['url'] ) : '';
$btnbPopup = isset( $attributes['popup'] ) ? (array) $attributes['popup'] : [ 'type' => 'image', 'content' => '', 'caption' => '' ];

if( isset( $btnbIcon['class'] ) && !empty( $btnbIcon['class'] ) ){
	wp_enqueue_style( 'font-awesome-7' );
}
if( !empty( $btnbAnimationType ) ){
	wp_enqueue_script( 'aos' );
	wp_enqueue_style( 'aos' );
}

$attributes['url'] = esc_url( $btnbUrl );
if ( 'content' === $btnbPopup['type'] ) {
	$btnbContentBlocks = parse_blocks( $btnbPopup['content'] ?? '' );
	$btnbPopup['content'] = '';
	foreach ( $btnbContentBlocks as $btnbContentBlock ) {
		$btnbPopup['content'] .= render_block( $btnbContentBlock );
	}
} // Convert the blocks to dom elements
?>
<div
	<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() is properly escaped ?>
	<?php echo get_block_wrapper_attributes(); ?>
	id='<?php echo esc_attr( $btnbId ); ?>'
	<?php
		$btnbAttributes = $attributes;
		if ( isset( $btnbAttributes['securityPassword'] ) ) {
			unset( $btnbAttributes['securityPassword'] );
		}
	?>
	data-attributes='<?php echo esc_attr( wp_json_encode( $btnbAttributes ?? [] ) ); ?>'
></div>