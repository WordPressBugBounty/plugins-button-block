<?php
if ( !defined( 'ABSPATH' ) ) { exit; }

$btnbId = wp_unique_id( 'btnButton-' );

// Block attributes live in the block comment, where KSES never sees them, and are
// handed to the front-end script below. Clean every one of them here so a Contributor
// can't store markup that runs on the page: the icon SVG, the popup content and URLs,
// the text and the style values. The JSON below is also hex-encoded so stored HTML
// entities can't decode back into tags.
$btnbPostId = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : (int) get_the_ID();
$attributes = BTNB\Functions::sanitizeAttributes( $attributes, $btnbPostId );

// Extract attributes with defaults
$btnbIcon = $attributes['icon'] ?? [];
$btnbAnimationType = isset( $attributes['animationType'] ) ? sanitize_text_field( $attributes['animationType'] ) : '';
$btnbUrl = isset( $attributes['url'] ) ? esc_url_raw( $attributes['url'] ) : '';

if( isset( $btnbIcon['class'] ) && !empty( $btnbIcon['class'] ) ){
	wp_enqueue_style( 'font-awesome-7' );
}
if( !empty( $btnbAnimationType ) ){
	wp_enqueue_script( 'aos' );
	wp_enqueue_style( 'aos' );
}

$attributes['url'] = esc_url( $btnbUrl );
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
	data-attributes='<?php echo esc_attr( wp_json_encode( $btnbAttributes ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) ); ?>'
></div>