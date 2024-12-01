<?php
//* Start the engine
include_once( get_template_directory() . '/lib/init.php' );

//* Setup Theme
include_once( get_stylesheet_directory() . '/lib/theme-defaults.php' );

//* Subpage Header Code
require_once('subpage-header.php');

//* Set Localization (do not remove)
load_child_theme_textdomain( 'parallax', apply_filters( 'child_theme_textdomain', get_stylesheet_directory() . '/languages', 'parallax' ) );

//* Add Image upload to WordPress Theme Customizer
add_action( 'customize_register', 'parallax_customizer' );
function parallax_customizer(){
	require_once( get_stylesheet_directory() . '/lib/customize.php' );
}

//* Include Section Image CSS
include_once( get_stylesheet_directory() . '/lib/output.php' );

global $blogurl;
$blogurl = get_stylesheet_directory_uri();

//* Enqueue scripts and styles
add_action( 'wp_enqueue_scripts', 'parallax_enqueue_scripts_styles' );
function parallax_enqueue_scripts_styles() {
	// Styles
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style( 'custom', get_stylesheet_directory_uri() . '/css/allstyles.css', array() );
	wp_enqueue_style( 'googlefonts', '//fonts.googleapis.com/css?family=Chonburi|Roboto+Slab:400,700&display=swap', array() );
	wp_enqueue_style( 'icomoon-fonts', get_stylesheet_directory_uri() . '/icomoon.css', array() );
	
	// Scripts
	//wp_enqueue_script( 'scripts', get_stylesheet_directory_uri() . '/js/scripts.js', array() );
	
}

// Removes Query Strings from scripts and styles
function remove_script_version( $src ){
  if ( strpos( $src, 'uploads/bb-plugin' ) !== false || strpos( $src, 'uploads/bb-theme' ) !== false ) {
    return $src;
  }
  else {
    $parts = explode( '?ver', $src );
    return $parts[0];
  }
}
add_filter( 'script_loader_src', 'remove_script_version', 15, 1 );
add_filter( 'style_loader_src', 'remove_script_version', 15, 1 );


//* Add HTML5 markup structure
add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );

//* Add viewport meta tag for mobile browsers
add_theme_support( 'genesis-responsive-viewport' );

//* Reposition the primary navigation menu
//remove_action( 'genesis_after_header', 'genesis_do_nav' );
//add_action( 'genesis_header', 'genesis_do_nav', 12 );

// Add Search to Primary Nav
//add_filter( 'genesis_header', 'genesis_search_primary_nav_menu', 10 );
function genesis_search_primary_nav_menu( $menu ){
    locate_template( array( 'searchform-header.php' ), true );
}

//* Add support for structural wraps
add_theme_support( 'genesis-structural-wraps', array(
	'header',
	'nav',
	'subnav',
	'breadcrumb',
	'footer-widgets',
	'footer',
) );

// Add Read More Link to Excerpts
add_filter('excerpt_more', 'get_read_more_link');
add_filter( 'the_content_more_link', 'get_read_more_link' );
function get_read_more_link() {
   return '...&nbsp;<a class="readmore" href="' . get_permalink() . '">Read&nbsp;More &raquo;</a>';
}

//* Add support for 4-column footer widgets
add_theme_support( 'genesis-footer-widgets', 0 );

//* Customize the entry meta in the entry header (requires HTML5 theme support)
add_filter( 'genesis_post_info', 'sp_post_info_filter' );
function sp_post_info_filter($post_info) {
	$post_info = '[post_date] [post_comments] [post_edit]';
	return $post_info;
}

//* Custom Breadcrumb Hook 
function breadcrumb_hook() {
	do_action('breadcrumb_hook');
}

//* Remove breadcrumbs and reposition them
remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );
add_action( 'breadcrumb_hook', 'genesis_do_breadcrumbs', 12 );

// Modify Breadcrumbs Args
add_filter( 'genesis_breadcrumb_args', 'malcolm_breadcrumb_args' );
function malcolm_breadcrumb_args( $args ) {
	$args['prefix'] = '<div class="breadcrumbs"><div class="wrap">';
	$args['suffix'] = '</div></div>';
	$args['sep'] = ' <span class="bread-sep">></span> ';
	$args['heirarchial_attachments'] = true;
	$args['heirarchial_categories'] = true;
	$args['display'] = true;
	$args['labels']['prefix'] = '';
    return $args;
}

// Blog Widgets
genesis_register_sidebar( array(
	'id'			=> 'blog-sidebar',
	'name'			=> __( 'Blog Widgets', 'thrive' ),
	'description'	=> __( 'This is latest news widget', 'thrive' ),
) );

// Add Header Links Widget to Header
//add_action( 'genesis_before', 'header_widget', 1 );
	function header_widget() {
	if (is_active_sidebar( 'header-links' ) ) {
 	genesis_widget_area( 'header-links', array(
		'before' => '<div class="header-links">',
		'after'  => '</div>',
	) );
}}

// Unregister unused sidebar
//unregister_sidebar( 'header-right' );

// Previous / Next Post Navigation Filter For Genesis Pagination
add_filter( 'genesis_prev_link_text', 'gt_review_prev_link_text' );
function gt_review_prev_link_text() {
        $prevlink = '&laquo;';
        return $prevlink;
}
add_filter( 'genesis_next_link_text', 'gt_review_next_link_text' );
function gt_review_next_link_text() {
        $nextlink = '&raquo;';
        return $nextlink;
}

/* Subpage Header Backgrounds - Utilizes: Featured Images & Advanced Custom Fields Repeater Fields */

// AFC Repeater Setup - NOTE: Set Image Return Value to ID
// Row Field Name:
$rows = '';
$rows = get_field('subpage_header_backgrounds', 5);
// Counts the rows and selects a random row
$row_count = count($rows);
$i = rand(0, $row_count - 1);
// Set Image size to be returned
$image_size = 'subpage-header';
// Get Image ID from the random row
$image_id = $rows[ $i ]['background_image'];
// Use Image ID to get Image Array
$image_array = wp_get_attachment_image_src($image_id, $image_size);
// Set "Default BG" to first value of the Image Array. $image_array[0] = URL;
$default_bg = $image_array[0]; 


// Custom function for getting background images
function custom_background_image($postID = "") {
	// Variables
	global $default_bg;
	global $postID;
	global $blog_slug;
	
	$currentID = get_the_ID();
	$blogID = get_option( 'page_for_posts');
	$parentID = wp_get_post_parent_id( $currentID );

	// is_home detects if you're on the blog page- must be set in admin area
	if( is_home() ) {
		$currentID = $blogID;
	} 
	// Else if post page, set ID to BlogID.
	elseif( is_home() || is_single() || is_archive() || is_search() ) {
		$currentID = $blogID;
	}

	// Try to get custom background based on current page/post
	$currentBackground = wp_get_attachment_image_src(get_post_thumbnail_id($currentID), 'subpage-header');
	//Current page/post has no custom background loaded
	if(!$currentBackground) {
		// Find blog ID
		$blog_page = get_page_by_path($blog_slug, OBJECT, 'page');
		if ($blog_page) {
			$blogID = $blogID;
			$currentID = $blogID;
		}
		// Else if post page, set ID to BlogID.
		elseif(is_single() || is_archive()) {
			$currentID = $blogID; 
		}

		// Current page has a parent
		if($parentID) {
			// Try to get parents custom background
			$parent_background = wp_get_attachment_image_src(get_post_thumbnail_id($parentID), 'subpage-header');
			// Set parent background if it exists
			if($parent_background) {
				$background_image = $parent_background[0];
			}
			// Set default background
			else {
				$background_image = $default_bg;
			}
		}
		// NO parent or no parent background: set default bg.
		else {
			$background_image = $default_bg;
		}
	}
	// Current Page has a custom background: use that
	else {
		$background_image = $currentBackground[0];
	}
	return $background_image;
}

//* Reposition the primary navigation menu
remove_action( 'genesis_after_header', 'genesis_do_nav' );
add_action( 'genesis_after_header', 'genesis_do_nav', 12 );

// Add Additional Image Sizes
add_image_size( 'genesis-post-thumbnail', 163, 108, true );
add_image_size( 'subpage-header', 1600, 162, true );
add_image_size( 'news-thumb', 260, 150, false );
add_image_size( 'news-full', 800, 300, false );
add_image_size( 'sidebar-thumb', 200, 150, false );
add_image_size( 'mailchimp', 564, 9999, false );
add_image_size( 'amp', 600, 9999, false  );
add_image_size( 'woo-thumb', 162, 212, false );


// Gravity Forms confirmation anchor on all forms
add_filter( 'gform_confirmation_anchor', '__return_true' );


// Button Shortcode
// Usage: [button url="https://www.google.com"] Button Shortcode [/button]
function button_shortcode($atts, $content = null) {
  extract( shortcode_atts( array(
	  'url' => '#',
	  'target' => '_self',
	  'onclick' => '',

  ), $atts ) 
);
return '<a target="' . $target . '" href="' . $url . '" class="button" onClick="' . $onclick . '"><span>' . do_shortcode($content) . '</span></a>';
}
add_shortcode('button', 'button_shortcode');

// Link Shortcode
// Usage: [link url=”tel:1-817-447-9194″ onClick=”onClick=”ga(‘send’, ‘event’, { eventCategory: ‘Click to Call’, eventAction: ‘Clicked Phone Number’, eventLabel: ‘Header Number’});”]
function link_shortcode($atts, $content = null) {
  extract( shortcode_atts( array(
	  'url' => '#',
	  'target' => '_self',
	  'onclick' => '',
  ), $atts ) 
);
return '<a target="' . $target . '" href="' . $url . '" onClick="' . $onclick . '">' . do_shortcode($content) . '</a>';
}
add_shortcode('link', 'link_shortcode');

//* Declare WooCommerce support
add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
    add_theme_support( 'woocommerce' );
}

// Run shortcodes in Text Widgets
add_filter('widget_text', 'do_shortcode');


/*Site optimizations*/
function remove_home_assets() {
  if (is_front_page()) { // allow widget style only in front page
	  wp_dequeue_style('wc-block-style');
	  wp_dequeue_style('woocommerce-layout');
	  wp_dequeue_style('woocommerce-smallscreen');
	  wp_dequeue_style('woocommerce-general');
	  wp_dequeue_style('yoast-seo-adminbar');
	  wp_dequeue_style('wpautoterms_css');
	  wp_dequeue_style('addtoany');
	  wp_dequeue_style('font-awesome-5');
	  wp_dequeue_style('font-awesome');
  }
};
add_action( 'wp_enqueue_scripts', 'remove_home_assets', 99 );

function wpfiles_dequeue() {
	if (current_user_can( 'update_core' )) {
		return;
	}
	wp_dequeue_style('yoast-seo-adminbar');
	wp_deregister_script('wp-embed');
}
add_action( 'wp_enqueue_scripts', 'wpfiles_dequeue', 999 );

// Woocommerce
add_action( 'wp_enqueue_scripts', 'child_manage_woocommerce_styles', 99 );
function child_manage_woocommerce_styles() {
 //remove generator meta tag
 remove_action( 'wp_head', array( $GLOBALS['woocommerce'], 'generator' ) );
 
 //first check that woo exists to prevent fatal errors
 if ( function_exists( 'is_woocommerce' ) ) {
 //dequeue scripts and styles
 if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() ) {
	wp_dequeue_script( 'wc-add-to-cart' );
 	wp_dequeue_script( 'wc-cart-fragments' );
	
	 wp_dequeue_style( 'woocommerce_frontend_styles' );
	 wp_dequeue_style( 'woocommerce_fancybox_styles' );
	 wp_dequeue_style( 'woocommerce_chosen_styles' );
	 wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
	 wp_dequeue_script( 'wc_price_slider' );
	 wp_dequeue_script( 'wc-single-product' );
	 wp_dequeue_script( 'wc-add-to-cart' );
	 wp_dequeue_script( 'wc-cart-fragments' );
	 wp_dequeue_script( 'wc-checkout' );
	 wp_dequeue_script( 'wc-add-to-cart-variation' );
	 wp_dequeue_script( 'wc-single-product' );
	 wp_dequeue_script( 'wc-cart' );
	 wp_dequeue_script( 'wc-chosen' );
	 wp_dequeue_script( 'woocommerce' );
	 wp_dequeue_script( 'prettyPhoto' );
	 wp_dequeue_script( 'prettyPhoto-init' );
	 wp_dequeue_script( 'jquery-blockui' );
	 wp_dequeue_script( 'jquery-placeholder' );
	 wp_dequeue_script( 'fancybox' );
	 wp_dequeue_script( 'jqueryui' );
	
 }
 }
 
}

//Removing unused Default Wordpress Emoji Script - Performance Enhancer
function disable_emoji_dequeue_script() {
    wp_dequeue_script( 'emoji' );
}
add_action( 'wp_print_scripts', 'disable_emoji_dequeue_script', 100 );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 ); 
remove_action( 'wp_print_styles', 'print_emoji_styles' );

// Removes Emoji Scripts 
add_action('init', 'remheadlink');
function remheadlink() {
	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wp_generator');
	remove_action('wp_head', 'index_rel_link');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'feed_links', 2);
	remove_action('wp_head', 'feed_links_extra', 3);
	remove_action('wp_head', 'parent_post_rel_link', 10, 0);
	remove_action('wp_head', 'start_post_rel_link', 10, 0);
	remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
	remove_action('wp_head', 'wp_shortlink_header', 10, 0);
	remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
}

// Add "nav-primary" class to Main Menu as this gets removed when we reposition the menu inside header/widget area
add_filter( 'genesis_attr_nav-header', 'thrive_custom_nav_id' );
function thrive_custom_nav_id( $attributes ) {
 	$attributes['class'] = 'nav-primary';
 	return $attributes;
}

//Sets the number of revisions for all post types
add_filter( 'wp_revisions_to_keep', 'revisions_count', 10, 2 );
function revisions_count( $num, $post ) {
	$num = 3;
    return $num;
}

// Enable Featured Images in RSS Feed and apply Custom image size so it doesn't generate large images in emails
function featuredtoRSS($content) {
global $post;
if ( has_post_thumbnail( $post->ID ) ){
$content = '<div>' . get_the_post_thumbnail( $post->ID, 'mailchimp', array( 'style' => 'margin-bottom: 15px;' ) ) . '</div>' . $content;
}
return $content;
}
 
add_filter('the_excerpt_rss', 'featuredtoRSS');
add_filter('the_content_feed', 'featuredtoRSS');

/* 
 * Dequeue Gutenberg-hooked CSS file `wp-block-library.css` file from `wp_head()`
 *
 * @author Thrive Agency
 * @since  12182018
 * @uses   wp_dequeue_style
 */
add_action( 'wp_enqueue_scripts', function() {
  wp_dequeue_style( 'wp-block-library' );
});

// Remove price,add to cart options
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

/*****************Product Tab Functionality**************/

/****** Start product TAB functionality for specifications*******/
	add_filter( 'woocommerce_product_tabs', 'woo_new_specification_product_tab' );
	function woo_new_specification_product_tab( $tabspec ) {
		// check if the repeater field has rows of data
			if( get_field('new_specification_editor') ){
			
				$tabspec['specific'] = array(
					'title'     => __( 'SPECIFICATIONS', 'woocommerce' ),
					'priority'  => 30,
					'callback'  => 'woo_new_product_specification_tab_content'
				);
				
				return $tabspec;
			}else{
				return $tabspec;
			}
		//}
	}

	// Add Content to SPECIFICATIONS tab
	function woo_new_product_specification_tab_content() {
		echo'<div class="speci_cus" id="specification_tab">';
			
			if( get_field('new_specification_editor') ):
					//echo'<ul class="specification_content">';
						
			?>			
							<?php the_field('new_specification_editor');?>
			<?php		
						
					//echo'</ul>';	
				endif;		
			
		echo'</div>';	
	}
/****** End product TAB functionality for Specification*******/

add_action( 'genesis_after_header', 'product_header' );
/**
* Add Product title below header.
*/
function product_header() {
if( is_product() ) {	
 echo '<div class="single-product-header"><div class="wrap">';
	breadcrumb_hook();
 echo '<div class="title"><h1>'. get_the_title() . '</h1></div>';
 echo '</div></div>';
 } 
}

/**
* Add store button.
*/
add_action( 'woocommerce_single_product_summary', 'add_data_after_description', 50 );
	function add_data_after_description(){
?>
				<div class="sale-store">
					<a class="button" href="/our-stores">Buy in store</a>
				</div>
<?php
}

/**
* Add stock count.
*/
add_action( 'woocommerce_single_product_summary', 'add_data_before_description', 10 );
	function add_data_before_description(){
?>
			<?php if( get_field('add_stock_availability') ): ?>
			<div class="stock-title">
				
				<h4><?php the_field('add_stock_availability'); ?></h4>
				
			</div>
			<?php endif; ?>

			<?php if( get_field('add_verification_image') ): ?>
			<div class="verify-image">
				
				<?php the_field('add_verification_image'); ?>
				
			</div>
			<?php endif; ?>

<?php
}

/**
 * Change number of related products output
 */ 
function woo_related_products_limit() {
  global $product;
	
	$args['posts_per_page'] = 3;
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'jk_related_products_args', 20 );
  function jk_related_products_args( $args ) {
	$args['posts_per_page'] = 3; // 3 related products
	$args['columns'] = 3; // arranged in 3 columns
	return $args;
}