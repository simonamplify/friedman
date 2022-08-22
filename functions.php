<?php
function custom_theme_scripts() {
    $parent_style = 'Divi';
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style )
    );
    wp_enqueue_script( 'list-js', get_bloginfo( 'stylesheet_directory' ) . '/js/list.js', null, null, true);
    wp_enqueue_script( 'main-js', get_bloginfo( 'stylesheet_directory' ) . '/js/main.js', array('list-js'), null, true);
}
add_action( 'wp_enqueue_scripts', 'custom_theme_scripts' );
// Enqueue login styles
function my_logincustomCSSfile() {
    wp_enqueue_style('login-styles', get_stylesheet_directory_uri() . '/css/login_stylesheet.css');
}
add_action('login_enqueue_scripts', 'my_logincustomCSSfile');
// Automatically Update Wordpress
add_filter( 'auto_update_core', '__return_true' );
// Automatically Update Theme
add_filter( 'auto_update_theme', '__return_true' );
// Change login logo url and title text
function my_loginURL() {
    return '/';
}
add_filter('login_headerurl', 'my_loginURL');
function my_loginURLtext() {
    return 'Engl Amps';
}
add_filter('login_headertitle', 'my_loginURLtext');
// Add artists shortcode
function artistsDirectory() {
    ob_start();
    get_template_part( 'artists' );
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
    get_template_part( 'manuals' );
    return ob_get_clean(); 
}
add_shortcode('manuals', 'manuals');
?>