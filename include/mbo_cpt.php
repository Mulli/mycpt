<?php

/*
 *  mbo - cpt, custom post types
 *  Generate MBO post types: "referrals", "families", "parents", "kids", "services", "workplans", "team"
 *  Expose to rest api
 */

add_action( 'init', 'create_digma_post_types' );
$digma_post_types = ["referrals", "families", "parents", "kids", "services", "workplans", "team"];
$digma_cpt_single = ["referrals" => "referral",
						"families" => "family", 
						"parents" => "parent", 
						"kids" => "kid", 
						"services"=>"service",
						"workplans"=>"workplan", 
						"team" => "member"];

function create_digma_post_types() {
	global $digma_post_types, $digma_cpt_single;
	foreach ($digma_post_types as $dpt) {
		$dpt1 = $digma_cpt_single[$dpt]; // single
	  register_post_type( 'digma_' . $dpt,
	    array(
	        'labels' => array(
	        'name' => __( $dpt ),
	        'singular_name' => __( $dpt1 ),
	        'add_new' => 'Add ' . $dpt1,
	        'all_items' => 'All '. $dpt,
	        'add_new_item' => 'Add '. $dpt1,
	        'edit_item' => 'Edit '. $dpt1,
	        'new_item' => 'New ' . $dpt1,
	        'view_item' => 'View ' . $dpt1,
	        'search_items' => 'Search ' . $dpt,
	        'not_found' => 'No ' . $dpt . ' found',
	        'not_found_in_trash' => 'No ' . $dpt . ' found in trash'
	      ),
	      'public' => true,
	      'has_archive' => true,
	      'query_var' => true,
	      'rewrite' => true,
	      'hierarchical' => true,
	      'supports' => array( 'title', 'excerpt', 'custom-fields', 'page-attributes' ) /* 'editor', 'thumbnail', */
	    )
	  );
	  
	}
}

// Add our custom post type products to the REST API
add_action( 'init', 'add_digma_rest_support', 25 );
function add_digma_rest_support() {
  global $wp_post_types;
  	global $digma_post_types;

 foreach ($digma_post_types as $dpt) {
	  $post_type_name = 'digma_'. $dpt;
	  if( isset( $wp_post_types[ $post_type_name ] ) ) {
	    $wp_post_types[$post_type_name]->show_in_rest = true;
	    $wp_post_types[$post_type_name]->rest_base = $post_type_name;
	    $wp_post_types[$post_type_name]->rest_controller_class = 'WP_REST_Posts_Controller';
	  }
	}
}

//add_action( 'plugins_loaded', 'digma_field_setting' ); - changed to admin_init due to a bug
add_action( 'admin_init', 'digma_field_setting' );
 
function digma_field_setting() {
	$idObj = get_category_by_slug('acf-json'); 
  if (!$idObj)		// create 	it
		$idObj = wp_create_category( 'acf-json' );
	
	$json_acf_category = $idObj->term_id;

  $filename = plugin_dir_path( __FILE__ ) . 'acf-json';
	if (!file_exists($filename)) 
    mkdir($filename, 0755);
    // TODO - handle failure to create directory
	
	// make acf and acf-pro plugins aware of this directory for save & load
	add_filter('acf/settings/save_json', 'digma_json_save_point'); // save point
	add_filter('acf/settings/load_json', 'digma_json_load_point'); // loadpoint

	//$dir = new DirectoryIterator(get_template_directory() . '/acf-json');
	$dir = new DirectoryIterator($filename);
	// loop over files & create/update posts
	foreach ($dir as $fileinfo) {
			if (strlen($fileinfo->getFilename()) < 18)
					continue;

			$my_post = array('post_status' => 'publish', 'post_author'   => 1, 'post_category' => array($json_acf_category),
											'comment_status' => 'closed',	'ping_status'	 => 'closed');
			$pname = $fileinfo->getFilename();
			$ptime = filemtime($filename . '/' . $pname);

			$pid = post_exists_by_slug( $pname, $ptime );
			// var_dump($pid); 
			if ($pid < 0) // exists & updated. nothing to do
				continue;
	
			// prepare data
			$pcont = file_get_contents($filename . '/' . $pname);
			$ptitle = json_decode($pcont, true);
			//var_dump($ptitle['title']);
			$my_post += [ 'ID' => $pid, 'post_title' => $ptitle['title'], 'post_name' => $pname, 
							'post_content' => $pcont, 'meta_input' => array('acffmt' => $pcont )];
 
			$pid = wp_insert_post( $my_post, true );

			//$s = get_post_meta($pid, 'acffmt', true);
			//var_dump($s);
			if(is_wp_error($pid)){
				//there was an error in the post insertion, 
				echo $pid->get_error_message();
			}	
			
	}
}

// -1 if exists & already updated - do nothing
// 0 if does not exists  - create new
// <0  exists - need update
function post_exists_by_slug( $post_slug, $ptime ) {
	$args = array(
		'name'           => $post_slug,
		'post_type'      => 'post',
		'post_status'    => 'any',
		'posts_per_page' => 1,
	);
	$my_posts = get_posts($args);

	if( $my_posts ){
		$post_time = get_post_modified_time( 'U', 'false', $my_posts[0]->ID );
		// var_dump($post_time);		var_dump($ptime);
		if ($post_time < $ptime)
			return $my_posts[0]->ID; // need update 
		return -1; // nothing to do
	}
	return 0; // create new
}

?>