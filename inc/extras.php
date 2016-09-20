<?php
/**
 * Extras
 *
 * Actions and filters used to modify WordPress and other
 * functionality. Not used directly in templates.
 *
 * @package   gwen
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

function gwen_body_classes( $classes ) {
	if ( ! display_header_text() ) {
		$classes[] = 'hidden-header-text';
	}

	return $classes;
}

add_filter( 'body_class', 'gwen_body_classes' );

/**
 * Register Widget Scripts
 *
 * Adds the media upload scripts to the widget page. Used
 * in the About Widget.
 *
 * @param string $hook
 *
 * @since 1.0
 * @return void
 */
function gwen_widget_scripts( $hook ) {
	if ( $hook != 'widgets.php' ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script( 'gwen-image-upload', get_template_directory_uri() . '/assets/js/media-upload.min.js', array( 'jquery' ), '1.0', true );
}

add_action( 'admin_enqueue_scripts', 'gwen_widget_scripts' );

/**
 * Create Custom CSS
 *
 * Generates CSS based on Customizer settings.
 *
 * @since 1.0
 * @return string
 */
function gwen_get_custom_css() {
	$css = '';

	// Primary Colour
	$colour = get_theme_mod( 'primary_colour' );
	if ( $colour ) {
		$css .= sprintf(
			'.entry-title:before, .comments-title:before, #reply-title:before { background: %1$s; }
			a, #tinymce h2, .entry-content h2, #tinymce h2, .entry-content h4, #tinymce h2, .entry-content h6, blockquote:before, #commentform .form-submit input[type="submit"] { color: %1$s; }
			a:hover, #commentform .form-submit input[type="submit"]:hover { color: %2$s; }',
			esc_html( $colour ),
			esc_html( gwen_adjust_brightness( $colour, - 30 ) )
		);
	}

	// BG Colour
	$bg_colour = get_background_color();
	if ( $bg_colour && $bg_colour != 'ffffff' ) {
		$css .= sprintf(
			'.entry-title > a, .entry-title > span, .comments-title > a, .comments-title > span, #reply-title > a, #reply-title > span { background-color: %s; border-right-color: %s; }',
			esc_html( $bg_colour )
		);
	}

	return apply_filters( 'gwen/custom-css', $css );
}

/**
 * Adjust Colour Brightness
 *
 * @param string $hex   Base hex colour
 * @param int    $steps Number between -255 (darker) and 255 (lighter)
 *
 * @since 1.0
 * @return string
 */
function gwen_adjust_brightness( $hex, $steps ) {
	$steps = max( - 255, min( 255, $steps ) );

	// Normalize into a six character long hex string
	$hex = str_replace( '#', '', $hex );
	if ( strlen( $hex ) == 3 ) {
		$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1 ), 2 );
	}

	// Split into three parts: R, G and B
	$color_parts = str_split( $hex, 2 );
	$return      = '#';

	foreach ( $color_parts as $color ) {
		$color = hexdec( $color ); // Convert to decimal
		$color = max( 0, min( 255, $color + $steps ) ); // Adjust color
		$return .= str_pad( dechex( $color ), 2, '0', STR_PAD_LEFT ); // Make two char hex code
	}

	return $return;
}

/**
 * Comment Layout
 *
 * @param WP_Comment $comment
 * @param array      $args
 * @param int        $depth
 *
 * @since 1.0
 * @return void
 */
function gwen_comment_layout( $comment, $args, $depth ) {
	echo '<li ' . comment_class( '', $comment, null, false ) . ' id="comment-' . get_comment_ID() . '">';

	?>
	<div class="comment-wrap">
		<?php echo get_avatar( $comment, $args['avatar_size'] ); ?>

		<article class="comment-inner">
			<header class="comment-header vcard">
				<?php printf(
					_x( '%s <span class="comment-meta">on %s</span>', '(commenter name) on (comment date)', 'gwen' ),
					'<span class="fn">' . get_comment_author_link() . '</span>',
					'<a href="' . esc_url( get_comment_link( $comment->comment_ID ) ) . '"><time datetime="' . esc_attr( get_comment_time( 'c' ) ) . '">' . get_comment_date() . '</time></a>'
				); ?>

				<?php edit_comment_link( __( '(Edit)' ), '  ', '' ); ?>
			</header>

			<div class="comment-body">
				<?php if ( $comment->comment_approved == '0' ) : ?>
					<div class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'gwen' ); ?></div>
				<?php endif; ?>

				<?php comment_text(); ?>
			</div>

			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array(
					'depth'      => $depth,
					'max_depth'  => $args['max_depth'],
					'reply_text' => __( 'Reply &raquo', 'gwen' )
				) ) ); ?>
			</div>
		</article>
	</div>
	<?php
}

/**
 * Register TinyMCE Stylesheet
 */
function gwen_editor_style() {
	$font_url = str_replace( ',', '%2C', 'https://fonts.googleapis.com/css?family=Balthazar|Roboto:100,300,700,700i' );
	add_editor_style( array(
		$font_url,
		'editor-style.css'
	) );
}

add_action( 'admin_init', 'gwen_editor_style' );

/**
 * Get Social Media Sites
 *
 * Returns an array of all the social sites supported by this theme.
 * Key should be the name of a Font Awesome icon.
 *
 * @since 1.0
 * @return array
 */
function gwen_get_social_sites() {
	$sites = array(
		'twitter'     => __( 'Twitter', 'gwen' ),
		'facebook'    => __( 'Facebook', 'gwen' ),
		'instagram'   => __( 'Instagram', 'gwen' ),
		'pinterest-p' => __( 'Pinterest', 'gwen' ),
		'google-plus' => __( 'Google+', 'gwen' ),
		'heart'       => __( 'Bloglovin\'', 'gwen' ),
		'rss'         => __( 'RSS', 'gwen' )
	);

	return apply_filters( 'gwen/get-social-sites', $sites );
}