<?php
namespace BTNB;

if ( !defined( 'ABSPATH' ) ) { exit; }

/**
 * Functions class
 * Cleans block attributes before render.php hands them to the front end.
 *
 * Block attributes live as JSON inside the block comment, so KSES never sees
 * them when a post is saved. Anything that ends up as markup has to be cleaned here.
 *
 * @package BTNB
 */
class Functions {
	/**
	 * Cleans every attribute of a button block before render.php prints it into
	 * data-attributes for the front-end script.
	 *
	 * @param mixed $attributes Block attributes as WordPress hands them to render.php.
	 * @param int   $post_id    Post holding the block, 0 when unknown.
	 * @return array Attributes that are safe to hand to the browser.
	 */
	public static function sanitizeAttributes( $attributes, $post_id = 0 ) {
		$attributes = is_array( $attributes ) ? $attributes : [];

		// Only registered attributes reach the page, anything else added to the block comment is dropped
		$blockType = \WP_Block_Type_Registry::get_instance()->get_registered( 'btn/button' );
		if ( $blockType && is_array( $blockType->attributes ) ) {
			$attributes = array_intersect_key( $attributes, $blockType->attributes );
		}

		foreach ( $attributes as $key => $value ) {
			switch ( $key ) {
				// Choices: action, alignment, animation, position, size, style, target, security and role
				case 'align':
				case 'actionType':
				case 'alignment':
				case 'animationType':
				case 'iconPos':
				case 'security':
				case 'securityRole':
				case 'size':
				case 'target':
				case 'tooltipPos':
				case 'type':
					$attributes[ $key ] = is_string( $value ) ? sanitize_key( $value ) : $value;
					break;

				// Button text is edited with bold, italic and link only, the same tags the front end allows
				case 'text':
					$attributes[ $key ] = is_string( $value ) ? wp_kses( $value, self::textAllowedHtml() ) : '';
					break;

				case 'tooltip':
				case 'downloadFileName':
					$attributes[ $key ] = is_string( $value ) ? sanitize_text_field( $value ) : '';
					break;

				case 'addID':
					$attributes[ $key ] = is_string( $value ) ? sanitize_html_class( $value ) : '';
					break;

				case 'addCSS':
					$attributes[ $key ] = is_string( $value ) ? wp_strip_all_tags( $value ) : '';
					break;

				case 'cPostId':
					$attributes[ $key ] = absint( $value );
					break;

				case 'animationDuration':
					$attributes[ $key ] = is_numeric( $value ) ? (float) $value : 0.4;
					break;

				case 'isDownload':
				case 'isUpIcon':
				case 'isFullWidth':
					$attributes[ $key ] = (bool) $value;
					break;

				case 'hideOn':
					$attributes[ $key ] = is_array( $value ) ? array_map( 'boolval', array_intersect_key( $value, array_flip( [ 'desktop', 'tablet', 'mobile' ] ) ) ) : [];
					break;

				case 'attrs':
					$attributes[ $key ] = self::sanitizeLinkAttrs( $value );
					break;

				case 'icon':
					$attributes[ $key ] = self::sanitizeIcon( $value );
					break;

				case 'upIcon':
					$attributes[ $key ] = self::sanitizeUpIcon( $value );
					break;

				case 'popup':
					$attributes[ $key ] = self::sanitizePopup( $value, $post_id );
					break;

				// Style objects only ever hold CSS values
				case 'typography':
				case 'colors':
				case 'hovColors':
				case 'padding':
				case 'border':
				case 'shadow':
					$attributes[ $key ] = self::sanitizeCssValues( $value );
					break;
			}
		}

		return $attributes;
	}

	/**
	 * Tags the button text may hold, matching sanitizeHTML() in the front-end script.
	 *
	 * @return array Allowed HTML for wp_kses.
	 */
	public static function textAllowedHtml() {
		$common = [ 'class' => true, 'style' => true ];

		return [
			'b'      => $common,
			'strong' => $common,
			'i'      => $common,
			'em'     => $common,
			'span'   => $common,
			'br'     => [],
			'a'      => $common + [ 'href' => true, 'target' => true, 'rel' => true ],
		];
	}

	/**
	 * Sanitizes the link attributes: only rel and referrer policy are kept.
	 *
	 * @param mixed $attrs Raw attribute value.
	 * @return array Link attributes with a safe rel and a known referrer policy.
	 */
	public static function sanitizeLinkAttrs( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : [];
		$policies = [ '', 'no-referrer', 'no-referrer-when-downgrade', 'origin', 'origin-when-cross-origin', 'same-origin', 'strict-origin', 'strict-origin-when-cross-origin', 'unsafe-url' ];
		$clean = [];

		if ( isset( $attrs['rel'] ) ) {
			// rel is a space separated list of keywords, such as "nofollow noopener"
			$clean['rel'] = is_string( $attrs['rel'] ) ? trim( preg_replace( '/[^a-z0-9 _-]/', '', strtolower( $attrs['rel'] ) ) ) : '';
		}
		if ( isset( $attrs['referrerpolicy'] ) ) {
			$clean['referrerpolicy'] = in_array( $attrs['referrerpolicy'], $policies, true ) ? $attrs['referrerpolicy'] : 'no-referrer';
		}

		return $clean;
	}

	/**
	 * Sanitizes a style value, or every value of a nested style object.
	 * Markup and the characters that could close the CSS rule are removed.
	 *
	 * @param mixed $value Raw value.
	 * @return mixed The value with every string made safe for a CSS declaration.
	 */
	public static function sanitizeCssValues( $value ) {
		if ( is_array( $value ) ) {
			return array_map( [ __CLASS__, 'sanitizeCssValues' ], $value );
		}
		if ( is_string( $value ) ) {
			return str_replace( [ '<', '>', '{', '}' ], '', wp_strip_all_tags( $value ) );
		}

		return is_int( $value ) || is_float( $value ) || is_bool( $value ) || is_null( $value ) ? $value : '';
	}

	/**
	 * Strips everything but a safe, static subset of SVG from a string.
	 * No <script>, no SMIL (<animate>, <set>), no event handler attributes,
	 * no <use>/<image>/<foreignObject> references.
	 *
	 * @param mixed $svg Raw SVG markup from a block attribute.
	 * @return string Sanitized SVG markup, or '' if the input is not SVG markup.
	 */
	public static function sanitizeSvg( $svg ) {
		if ( ! is_string( $svg ) || '' === trim( $svg ) ) {
			return '';
		}

		// Entity-encoded markup (what kses leaves a Contributor) is not an SVG, so
		// it is dropped rather than handed to the browser to decode.
		if ( 0 !== stripos( ltrim( $svg ), '<svg' ) ) {
			return '';
		}

		$common = [
			'id' => true, 'class' => true, 'style' => true, 'transform' => true, 'opacity' => true,
			'fill' => true, 'fill-opacity' => true, 'fill-rule' => true,
			'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true,
			'stroke-opacity' => true, 'stroke-dasharray' => true, 'stroke-dashoffset' => true,
			'd' => true, 'x' => true, 'y' => true, 'cx' => true, 'cy' => true, 'r' => true, 'rx' => true, 'ry' => true,
			'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'points' => true,
			'width' => true, 'height' => true, 'clip-path' => true, 'clip-rule' => true, 'mask' => true,
		];

		// wp_kses lower-cases attribute names, so viewBox must be listed as viewbox.
		$allowed = [
			'svg'            => $common + [ 'xmlns' => true, 'viewbox' => true, 'preserveaspectratio' => true, 'aria-hidden' => true, 'role' => true, 'focusable' => true, 'version' => true ],
			'g'              => $common,
			'path'           => $common,
			'rect'           => $common,
			'circle'         => $common,
			'ellipse'        => $common,
			'line'           => $common,
			'polyline'       => $common,
			'polygon'        => $common,
			'defs'           => [],
			'clippath'       => [ 'id' => true, 'clippathunits' => true ],
			'lineargradient' => [ 'id' => true, 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'gradientunits' => true, 'gradienttransform' => true ],
			'radialgradient' => [ 'id' => true, 'cx' => true, 'cy' => true, 'r' => true, 'fx' => true, 'fy' => true, 'gradientunits' => true, 'gradienttransform' => true ],
			'stop'           => [ 'offset' => true, 'stop-color' => true, 'stop-opacity' => true, 'style' => true ],
			'title'          => [],
			'desc'           => [],
		];

		return wp_kses( $svg, $allowed );
	}

	/**
	 * Sanitizes the icon attribute: inline SVG or a Font Awesome class list.
	 *
	 * @param mixed $icon Raw attribute value.
	 * @return array Icon with a safe svg and class.
	 */
	public static function sanitizeIcon( $icon ) {
		$icon = is_array( $icon ) ? $icon : [];

		if ( isset( $icon['svg'] ) ) {
			$icon['svg'] = self::sanitizeSvg( $icon['svg'] );
		}
		if ( isset( $icon['class'] ) ) {
			// Font Awesome classes come as a space separated list, such as "fa-solid fa-star"
			$icon['class'] = is_string( $icon['class'] ) ? trim( preg_replace( '/[^A-Za-z0-9 _-]/', '', $icon['class'] ) ) : '';
		}

		return $icon;
	}

	/**
	 * Sanitizes the uploaded icon attribute.
	 *
	 * @param mixed $upIcon Raw attribute value.
	 * @return array Uploaded icon with a safe url, alt and title.
	 */
	public static function sanitizeUpIcon( $upIcon ) {
		$upIcon = is_array( $upIcon ) ? $upIcon : [];

		if ( isset( $upIcon['url'] ) ) {
			$upIcon['url'] = is_string( $upIcon['url'] ) ? esc_url_raw( $upIcon['url'] ) : '';
		}
		foreach ( [ 'alt', 'title' ] as $key ) {
			if ( isset( $upIcon[ $key ] ) ) {
				$upIcon[ $key ] = is_string( $upIcon[ $key ] ) ? sanitize_text_field( $upIcon[ $key ] ) : '';
			}
		}

		return $upIcon;
	}

	/**
	 * Sanitizes the popup attribute.
	 *
	 * A content popup holds serialized blocks, which are rendered here. A Contributor
	 * can put raw HTML in them through the block comment, so the result goes through
	 * KSES unless the post author is allowed unfiltered HTML. Every other type holds a URL.
	 *
	 * @param mixed $popup   Raw attribute value.
	 * @param int   $post_id Post holding the block, 0 when unknown.
	 * @return array Popup with a known type and safe content and caption.
	 */
	public static function sanitizePopup( $popup, $post_id = 0 ) {
		$popup = is_array( $popup ) ? $popup : [];
		$types = [ 'image', 'audio', 'video', 'content', 'document', 'iframe' ];

		$popup['type'] = isset( $popup['type'] ) && in_array( $popup['type'], $types, true ) ? $popup['type'] : 'image';
		$content = isset( $popup['content'] ) && is_string( $popup['content'] ) ? $popup['content'] : '';

		if ( 'content' === $popup['type'] ) {
			$rendered = '';
			foreach ( parse_blocks( $content ) as $contentBlock ) {
				$rendered .= render_block( $contentBlock );
			}

			$popup['content'] = ( $post_id && author_can( $post_id, 'unfiltered_html' ) ) ? $rendered : wp_kses_post( $rendered );
		} else {
			$popup['content'] = esc_url_raw( $content );
		}

		if ( isset( $popup['width'] ) ) {
			$popup['width'] = self::sanitizeCssValues( $popup['width'] );
		}
		if ( isset( $popup['caption'] ) ) {
			$popup['caption'] = is_string( $popup['caption'] ) ? wp_kses_post( $popup['caption'] ) : '';
		}

		return $popup;
	}
}
