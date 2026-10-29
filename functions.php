<?php
// Enqueue parent and child theme styles and scripts
function custom_theme_scripts() {
    $parent_handle = 'Divi';
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/css/stylesheet.css',
        array( $parent_handle ),
        filemtime( get_stylesheet_directory() . '/css/stylesheet.css' )
    );
    wp_enqueue_script(
        'list',
        get_stylesheet_directory_uri() . '/js/list.js',
        null,
        null,
        true
    );
    wp_enqueue_script(
        'main',
        get_stylesheet_directory_uri() . '/js/main.min.js',
        array('list'),
        filemtime( get_stylesheet_directory() . '/js/main.min.js' ),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'custom_theme_scripts', 11 ); // 11 to ensure it runs after the parent theme's scripts
// Enqueue login styles
function my_logincustomCSSfile() {
    wp_enqueue_style('login-styles', get_stylesheet_directory_uri() . '/css/login_stylesheet.css');
}
add_action('login_enqueue_scripts', 'my_logincustomCSSfile');

// Change login logo url and title text
function my_loginURL() {
    return '/';
}
add_filter('login_headerurl', 'my_loginURL');
function my_loginURLtext() {
    return 'Friedman Amplification';
}
add_filter('login_headertitle', 'my_loginURLtext');

// Create year shortcode
function year() {
    return date("Y");
}
add_shortcode('year', 'year');

// Add artists shortcode
function artistsDirectory() {
    ob_start();
    get_template_part( '/includes/artists' );
    return ob_get_clean(); 
}
add_shortcode('artistsDirectory', 'artistsDirectory');

// Enable product search
function custom_remove_default_et_pb_custom_search() {
	remove_action( 'pre_get_posts', 'et_pb_custom_search' );
	add_action( 'pre_get_posts', 'custom_et_pb_custom_search' );
}
add_action( 'wp_loaded', 'custom_remove_default_et_pb_custom_search' );
function custom_et_pb_custom_search( $query = false ) {
	if ( is_admin() || ! is_a( $query, 'WP_Query' ) || ! $query->is_search ) {
		return;
	}
	if ( isset( $_GET['et_pb_searchform_submit'] ) ) {
		$postTypes = array();   
		if ( ! isset($_GET['et_pb_include_posts'] ) && ! isset( $_GET['et_pb_include_pages'] ) ) {
            $postTypes = array( 'post' );
        }
		if ( isset( $_GET['et_pb_include_pages'] ) ) {
            $postTypes = array( 'page' );
        }
		if ( isset( $_GET['et_pb_include_posts'] ) ) {
            $postTypes[] = 'post';
        } 
		/* BEGIN Add custom post types */
		$postTypes[] = 'product';
		/* END Add custom post types */
		$query->set( 'post_type', $postTypes );
		if ( ! empty( $_GET['et_pb_search_cat'] ) ) {
			$categories_array = explode( ',', $_GET['et_pb_search_cat'] );
			$query->set( 'category__not_in', $categories_array );
		}
		if ( isset( $_GET['et-posts-count'] ) ) {
			$query->set( 'posts_per_page', (int) $_GET['et-posts-count'] );
		}
	}
}

// Create acf manual shortcode
function manuals() {
    ob_start();
    get_template_part( '/includes/manuals' );
    return ob_get_clean(); 
}
add_shortcode('manuals', 'manuals');

// MANUALS DIRECTORY INCLUDE
$manuals_directory_file = get_theme_file_path('/includes/manuals-directory.php');
if (file_exists($manuals_directory_file)) {
    require_once $manuals_directory_file;
} else {
    error_log('Missing file: ' . $manuals_directory_file);
}

// Change sale badge text to
add_filter('woocommerce_sale_flash', 'change_sale_text');
function change_sale_text() {
    return '<span class="onsale">On Sale</span>';
}

// Add cart button to shop archive pages
add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_add_to_cart', 10);

// Change related products header
add_filter('woocommerce_product_upsells_products_heading', 'custom_related_products_heading');
function custom_related_products_heading() {
    return 'Related Products';
}

// BEFORE CHECKOUT PAYMENT
add_action( 'woocommerce_review_order_before_payment', 'order_notice' );
function order_notice() {
    get_template_part( '/includes/order_notice' );
}

// Lead time notice shortcode
function lead_time_notice() {
    return '<p><strong>Please note - due to high demand there is currently a 3-10 day lead time for this product.</strong></p>';
}
add_shortcode('lead_time_notice', 'lead_time_notice');

// TAXJAR INCLUDE
$taxjar_file = get_theme_file_path( '/includes/taxjar.php' );
if ( file_exists( $taxjar_file ) ) {
    require_once $taxjar_file;
} else {
    error_log( 'Missing file: ' . $taxjar_file );
}

// SYNERGY SOFTWARE INCLUDE
$syn_software_file = get_theme_file_path( '/includes/synergy-software-download.php' );
if ( file_exists( $syn_software_file ) ) {
    require_once $syn_software_file;
} else {
    error_log( 'Missing file: ' . $syn_software_file );
}
?>