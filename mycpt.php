<?php
/**
 * Plugin Name: Digma MBO - Family Management
 * Description: Digma MBO (mycpt) custom post types with custom taxonomies and custom meta fields, exposing to the REST API. Runs with ACF PRO 5.9.4
 * Plugin URI:  http://site2goal.co.il
 * Version:     0.9.1
 * Author:      Mulli
 * Author URI:  http://site2goal.co.il
 * License:     GPLv2
 * Network:     true
 */

/*
Create Digma Custom Post Types. 
Each cpt is a database entity with several filed groups (in meta data), generated by acf & acf-pro plugin.
All support REST API at /wp-json/wp/v2/digma_<cpt-name> and will contain the data from the field groups in "acf" entry.
Plugin requires the following plugins:
	Advanced ACF (currently version 5.7.x)
	ACF to REST API (requires Advanced ACF)
	JSON Basic Authentication (for authenticaton) - ??
	WP REST API - filter fields (enables optimized interface - less data over the network)
	Wordpress post import/export - to transfer data

	Open Issues: major - definition to POST - update fields
	1. Add classes and make this propper plugin
	2. Can Custom post type UI - replace this plugin, so it is redundant???
	3. Add translation
*/
function add_cors_http_header(){
    header("Access-Control-Allow-Origin: http://localhost:3000 ");
}
add_action('init','add_cors_http_header');

//define( 'SHORTINIT', true ); // is it really faster ??
//// utils -no feed
function mbo_disable_feed() {
 wp_die( 'No feed available');
}
// NOT WORKING - increase rest-api response to 200
// Limit is hard coded in rest api parameters - fail on validation
function query_200_referrals($args, $request) {
    $args['posts_per_page'] = 200;
     error_log(print_r($args, true));
    return $args;
}
//add_filter('rest_digma_referrals_query', 'query_200_referrals', 10, 2);

// disable access to site feeds

add_action('do_feed', 'mbo_disable_feed', 1);
add_action('do_feed_rdf', 'mbo_disable_feed', 1);
add_action('do_feed_rss', 'mbo_disable_feed', 1);
add_action('do_feed_rss2', 'mbo_disable_feed', 1);
add_action('do_feed_atom', 'mbo_disable_feed', 1);
add_action('do_feed_rss2_comments', 'mbo_disable_feed', 1);
add_action('do_feed_atom_comments', 'mbo_disable_feed', 1);

// prevent site from accessing other feeds
remove_action( 'wp_head', 'feed_links_extra', 3 ); // Display the links to the extra feeds such as category feeds
remove_action( 'wp_head', 'feed_links', 2 ); // Display the links to the general feeds: Post and Comment Feed
remove_action( 'wp_head', 'rsd_link' ); // Display the link to the Really Simple Discovery service endpoint, EditURI link
remove_action( 'wp_head', 'wlwmanifest_link' ); // Display the link to the Windows Live Writer manifest file.
remove_action( 'wp_head', 'index_rel_link' ); // index link
remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 ); // prev link
remove_action( 'wp_head', 'start_post_rel_link', 10, 0 ); // start link
remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 ); // Display relational links for the posts adjacent to the current post.
remove_action( 'wp_head', 'wp_generator' ); // Display the XHTML gen

/* Custom Scripts to load */
function custom_scripts() {
	$f = filemtime(plugin_dir_path(__FILE__). 'js/mbo_chart.js');
	$ver_c = filemtime(plugin_dir_path(__FILE__). 'css/report-style.css');

	wp_enqueue_style( 'report-style', plugin_dir_url(__FILE__) . 'css/report-style.css' , null, $ver_c, 'all' );

	wp_enqueue_script('jQuery-js', 'https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js', array(), '', true);
	wp_enqueue_script( 'chart-js', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.js', array('jQuery-js'), false, false );
	wp_enqueue_script( 'chartjs-datalabels', 'https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.5.0', array('chart-js'), false, false );

	//wp_enqueue_script( 'chart-js', plugin_dir_url(__FILE__) . "js/Chart2.7.3.js", array(), false , true );
	wp_enqueue_script( 'mbo-chart-js', plugin_dir_url(__FILE__) . "js/mbo_chart.js", array('chart-js'), $f, true );
}
add_action( 'wp_enqueue_scripts', 'custom_scripts');

///////Utils
if ( ! function_exists('write_log')) {
	function write_log ( $log )  {
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ) );
		} else {
			error_log( $log );
		}
	}
}

register_activation_hook( __FILE__, 'activate_mbo' );
register_deactivation_hook( __FILE__, 'deactivate_mbo' );


// include_once  plugin_dir_path(__FILE__) . "/mbo_acf2rest_api.php" ; - if I ever use it
include_once  plugin_dir_path(__FILE__) . "/mbo_shortcodes.php" ; 
include_once  plugin_dir_path(__FILE__) . "/mbo_acf_server.php" ;
// include_once  plugin_dir_path(__FILE__) . "/mbo_acf_server_families.php" ;

$acfServer = new ACF_Rest_Server();

function jwt_auth_function($data, $user) {
	$data['user_role'] = $user->roles;
	return $data;
}
add_filter( 'jwt_auth_token_before_dispatch', 'jwt_auth_function', 10, 2 );


add_action( 'init', 'create_digma_post_types' );

$digma_post_types = array(  //"referrals" => "referral",
							//"families" => "family",
							"WorkplansDefs" => "WorkplansDef",
							"WorkplansRuns" => "WorkplansRun",
							//"parents" => "parent",
							//"intakes" => "intake",
							"stats" => "stat",
							//"families0" => "family0",
							//"families1" => "family1",
							//"families2" => "family2",
							//"families3" => "family3",
                            //"parents0" => "parent0",
							//"parents1" => "parent1",
							//"parents2" => "parent2",
							"queries"  => "query",
							//"parents3" => "parent3",
							/*"kids" => "kid",
							"services"=>"service",
							"workplans"=>"workplan",
							"meetings" => "meeting",
							"needsolutions" => "needsolution",
                                                        "familylogs" => "familylog"
                                                        */
							//"team" => "member",
							//"combos" => "combo"
							);

$did_create_digma_post_types = 0;
function create_digma_post_types() {
	global $digma_post_types;
	global $did_create_digma_post_types;
	
	// error_log("start post types================".$did_create_digma_post_types);
	if ($did_create_digma_post_types)
		return 0;
	//foreach($digma_post_types as $plural=>$single)
	//    error_log("post type=".$plural . " LEN=". strlen('digma_' . $plural));
	if (empty($digma_post_types)){
		error_log('post type list empty && did_create_digma_post_types is 0 - some Reset event??? array='. print_r($digma_post_types, true));
		return 0;
	}   
	foreach($digma_post_types as $plural=>$single) {
		register_post_type( 'digma_' . $plural,
		array(
	        'labels' => array(
	        'name' => __( $plural ),
	        'singular_name' => __( $single ),
	        'add_new' => 'Add ' . $single,
	        'all_items' => 'All '. $plural,
	        'add_new_item' => 'Add '. $single,
	        'edit_item' => 'Edit '. $single,
	        'new_item' => 'New ' . $single,
	        'view_item' => 'View ' . $single,
	        'search_items' => 'Search ' . $plural,
	        'not_found' => 'No ' . $plural . ' found',
	        'not_found_in_trash' => 'No ' . $plural . ' found in trash'
	      ),
	      'public' => true,
	      'has_archive' => true,
	      'query_var' => true,
	      'rewrite' => true,
	      'hierarchical' => false,
	      'show_in_rest' => true,
	      'rest_base' => 'digma_' . $plural,
      'show_in_graphql' => true,
      //'hierarchical' => true,
      'graphql_single_name' => $single,
      'graphql_plural_name' => $plural,
	     'rest_controller_class' => 'WP_REST_Posts_Controller',
	     'taxonomies'          => array( 'category' ),
	      'supports' => array( 'title', 'excerpt', 'custom-fields', 'page-attributes', 'author') /* , 'revisions', 'editor', 'thumbnail', */
	    )
	  );

	}
	$did_create_digma_post_types += 1;
}


//add_action( 'plugins_loaded', 'digma_field_setting' ); - changed to admin_init due to a bug
add_action( 'admin_init', 'digma_field_setting' );

// load point

function my_acf_json_load_point( $paths ) {
	// remove original path (optional)
	unset($paths[0]);
	// append path
	$paths[] = plugin_dir_path(__FILE__ ) . 'acf-json'; 
	write_log( "my_acf_json_LOAD_point: " . plugin_dir_path(__FILE__ ) . 'acf-json');
	// get_stylesheet_directory() . '/my-custom-folder';
	// return
	return $paths;
}
// save point mycpt/acf-json
function my_acf_json_save_point( $path ) {
// update path
	$path = plugin_dir_path(__FILE__ ). 'acf-json';
	write_log( "my_acf_json_SAVE_point:" . plugin_dir_path(__FILE__ ) . 'acf-json');
// return
	return $path;
}
function digma_field_setting() {
/*
	$idObj = get_category_by_slug('acf-json'); 
  if (!$idObj)		// create 	it
		$idObj = wp_create_category( 'acf-json' );
	
	$json_acf_category = $idObj->term_id;
*/
  $filename = plugin_dir_path( __FILE__ ) . 'acf-json';
	if (!file_exists($filename)) 
        mkdir($filename, 0755);
    // TODO - handle failure to create directory
	
	// make acf and acf-pro plugins aware of this directory for save & load
	// DO I NEED IT???
//	add_filter('acf/settings/save_json', 'digma_json_save_point'); // save point
	add_filter('acf/settings/save_json', 'my_acf_json_save_point');
	add_filter('acf/settings/load_json', 'my_acf_json_load_point');
//	add_filter('acf/settings/load_json', 'digma_json_load_point'); // loadpoint

	//$dir = new DirectoryIterator(get_template_directory() . '/acf-json');
	$dir = new DirectoryIterator($filename);
	// loop over files & create/update posts
/*
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
			$my_post += array(
				'ID'           => $pid, 'post_title' => $ptitle['title'], 'post_name' => $pname,
				'post_content' => $pcont, 'meta_input' => array('acffmt' => $pcont )
			);
 
			$pid = wp_insert_post( $my_post, true );

			//$s = get_post_meta($pid, 'acffmt', true);
			//var_dump($s);
			if(is_wp_error($pid)){
				//there was an error in the post insertion, 
				echo $pid->get_error_message();
			}	
			
	}
*/
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


add_filter( 'acf/rest_api/field_settings/show_in_rest', '__return_true' );
add_filter( 'acf/rest_api/field_settings/edit_in_rest', '__return_true' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-mbo2-activator.php
 */
function activate_mbo() {
//	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mbo2-activator.php';
//	Mbo2_Activator::activate();
	//mbo_create_db('mbo_stats');
}
function mbo_create_db($tablename) {

	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . $tablename;

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		views smallint(5) NOT NULL,
		clicks smallint(5) NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-mbo2-deactivator.php
 */
function deactivate_mbo() {
//	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mbo2-deactivator.php';
//	Mbo2_Deactivator::deactivate();
}

