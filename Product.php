<?php
/*
Plugin Name: Product Post Type
Plugin URI: http://www.pajajaransolutions.com
Description: Plugin untuk menampilkan posting berupa product
Version: 1.0
Author: Rahmat Awaludin
Author URI: http://rahmatawaludin.wordpress.com/
*/

/**
 * Copyright (c) 2012 Rahmat Awaludin. All rights reserved.
 */

add_action( 'init', 'create_product_post_types' );

function create_product_post_types() {
	register_post_type( 'product', 
		array(
			'labels' => array(
				'name' => __( 'Products' ),
				'singular_name' => __( 'Product' ),
				'add_new' => __('Add New'),
				'add_new_item' => __( 'Add New Product' ),
				'edit' => __( 'Edit' ),
				'edit_item' => __( ' Edit Product' ),
				'new_item' => __( 'New Product'),
				'view' => __( 'View Product' ),
				'view_item' => __( 'View Product' ),
				'search_item' => __( 'Search Product' ),
				'not_found' => __( 'No product found' ),
				'not_found_in_trash' => __( 'No product found in Trash' ),
				'parent' => __( 'Parent Product' )
			),
			'description' => __( 'Product posted here will be published in product page.'),
			'public' => true,

			/* Menu icon */
			'menu_icon' => plugins_url( 'images/pil_icon.png', __FILE__ ),

			/* biar bisa diambil pakai query_posts() */
			'query_var' => true,

			/* dialog/opsi yang akan muncul ketika edit product */
			'supports' => array('title', 'editor', 'thumbnail', 'revisions', 'page-attibutes'),

			/* biar url nya jadi www.domain.com/products/nama-product */
			'rewrite' => array ( 'slug'=>'product', 'with_front'=>false),

			/* Memunculkan dialog tag dan kategori. Disini akan diubah menjadi taxonomi dari kategori product */
			'taxonomies' => array ('category')
		)
	);
}

add_shortcode( 'prd', 'product_display' );
function product_display() {
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'select2', plugins_url( 'js/select2/select2.min.js', __FILE__ ) );
	
	// Embed javascript to make ajax request
	wp_enqueue_script( 'ajax_get_category', plugins_url( 'js/ajax_get_category.js',__FILE__ ) );

	// declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
	wp_localize_script( 'ajax_get_category', 'categoryAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

	/* Create drop down */
	$criteria = array(
		'parent' => 0, // HANYA direct child dari category 0 (top parent)
		'hide_empty' => 0, // Tampilkan category walaupun post nya kosong
		// 'hierarchical' => true, // Tree
		'exclude' => 1, // Hilangkan categori dgn id 0
		'echo' => 0, // Lempar output ke variable
		'show_option_none' => __('None'),
		'name' => 'selectcat1' // beri id cat1 utk form select
		);
	echo wp_dropdown_categories($criteria);

}

function print_r_html ($arr) {
        ?><pre><?php
        print_r($arr);
        ?></pre><?php
}

/* Buat Call ajax product_get_category yang mengarah ke fungsi get_category */
add_action('wp_ajax_product_get_category', 'product_get_category'); // akses ajax oleh user yang sudah login
add_action('wp_ajax_nopriv_product_get_category', 'product_get_category'); // akses ajax oleh user yang belum login

function product_get_category() {
	global $wpdb; // this is how you get access to the database
	$cat_id = intval($_POST['cat_id']);
	$form_name = 'selectcat'.$cat_id;
	
	if (category_has_children($cat_id)) {
		$cat_criteria = array(
			'parent' => $cat_id, // HANYA direct child dari category 0 (top parent)
			'hide_empty' => 0, // Tampilkan category walaupun post nya kosong
			// 'hierarchical' => true, // Tree
			'exclude' => 1, // Hilangkan categori dgn id 0 (uncategorized)
			'echo' => 0, // Lempar output ke variable
			'show_option_none' => __('None'),
			'name' => $form_name // nama dari form
		);
		echo wp_dropdown_categories($cat_criteria); // kirim select berdasarkan id category yang dikirim
	} else {
		$post_criteria = array(
			'cat' => $cat_id,
			'post_type' => 'product'
			);

		$posts = query_posts( $post_criteria );
		if ($posts) {
			echo '<div id="content_product">';
			foreach ($posts as $post) {
				echo "<a href='$post->guid'>$post->post_title</a> <br>";
			}
			echo '</div>';
		}
		
	}

	die(); // this is required to return a proper result
}

// fungsi utk check apakah category punya children
function category_has_children($cat_id) {
	global $wpdb;
	$category_children_check = $wpdb->get_results(" SELECT * FROM wp_term_taxonomy WHERE parent = '$cat_id' ");
	     if ($category_children_check) {
	          return true;
	     } else {
	          return false;
	     }
}

?>