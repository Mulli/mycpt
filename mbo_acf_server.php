<?php /*d-digma.php */
if (!defined('MBO_DEBUG'))
    define('MBO_DEBUG', false); // get from national social site
if (!defined('MBO_DEBUG_QB'))
    define('MBO_DEBUG_QB', false); // get from national social site
/**
 * Class ACF_Rest_Server
 *
 * ACF rest server that allows for CRUD operations on the custom post types
 *
 */
if ( ! function_exists('write_log')) {
	function write_log ( $str, $log )  {
		error_log(str);
		if (isset($log)){
			if ( is_array( $log ) || is_object( $log ) ) 
				error_log( print_r( $log, true ) );
			else 
				error_log( $log );
			
		}
	}
}

if ( ! function_exists('mbo_get_cat_by_slug')) {

	function mbo_get_cat_by_slug($slug){
		$idObj = get_category_by_slug($slug); 
		return $idObj ? $idObj->term_id : 1; // 1 is general
	}
}

class ACF_Rest_Server extends WP_Rest_Controller {

	public $namespace = 'mbo/';
	public $version = 'v3';

	function __construct() {
//		add_action( 'rest_api_init', array( $this, 'register_routes' ) ); // records example
		add_action( 'rest_api_init', array( $this, 'register_acf_routes' ) );
		add_shortcode( 'daily-stat' , array( $this, 'daily_stat'));
		add_shortcode( 'delet-daily-stat' , array( $this, 'delet_daily_stat'));
	}
///////////////////////////////////////
	public function register_acf_routes() {
	//	$this->register_referral_routes(); was dedicated to referrals
		$this->register_cpt_routes('referrals');
		$this->register_cpt_routes('workplansdefs');
		$this->register_cpt_routes('families');
		$this->register_cpt_routes('intakes');
		$this->register_cpt_routes('combos');  // terminology / user combo terms
		//$this->register_cpt_routes('familylogs');  // terminology / user combo terms
		$this->register_cpt_routes('needsolutions');  // terminology / user combo terms
		// $this->register_cpt_routes('workplans');  // terminology / user combo terms
		$this->register_mbo_special_routes();

	}
	
	public function register_mbo_special_routes() {
		$namespace = $this->namespace . $this->version;
// write_log($namespace);
		register_rest_route( $namespace, '/' . 'ref2family', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'mbo_ref2family' ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			)
		) );
		register_rest_route( $namespace, '/' . 'ref2intake', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'mbo_ref2intake' ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			)
		) );
		register_rest_route( $namespace, '/' . 'intake2family', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'mbo_intake2family' ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			)
		) );
		/*register_rest_route( $namespace, '/' . 'family2complete', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'mbo_family2complete' ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			)
		) );*/
		register_rest_route( $namespace, '/' . 'updateFamily', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'mbo_updateFamily' ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			)
		) );
		register_rest_route( $namespace, '/' . 'createSnapshot', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'mbo_createSnapshot' ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			)
		) );
		register_rest_route( $namespace, '/' . 'processquery', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'mbo_processquery' ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			)
		) );
		register_rest_route( $namespace, '/' . 'crudquery', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'mbo_crudquery' ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			)
		) );
		register_rest_route( $namespace, '/' . 'yahavStats', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'mbo_yahavStats' ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			)
		) );
		register_rest_route( $namespace, '/' . 'yahavStatsNow', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'mbo_yahavStatsNow' ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			)
		) );
		register_rest_route( $namespace, '/' . 'getRnames', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'mbo_getRnames' ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			)
		) );
		register_rest_route( $namespace, '/' . 'getWPnames', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'mbo_getWPnames' ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			)
		) ); 
		register_rest_route( $namespace, '/' . 'getWPacfjson', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'mbo_getWPacfjson' ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			)
		) );
		register_rest_route( $namespace, '/' . 'getWPacfjson3', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'mbo_getWPacfjson3' ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			)
		) );  
        register_rest_route( $namespace, '/' . 'getInames', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'mbo_getInames' ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			)
		) );
		register_rest_route( $namespace, '/' . 'getFnames', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'mbo_getFnames' ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			)
		) );
		register_rest_route( $namespace, '/' . 'leadforms', array(
			array(
				'methods'             => WP_REST_Server::READABLE, // only for digma.me?
				'callback'            => array( $this, 'mbo_get_leadforms' ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			)
        ) );
        register_rest_route( $namespace, '/' . 'leadforms2', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'mbo_get_leadforms' ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			)
		) );
	}

	public function register_cpt_routes($cpt) {
		$namespace = $this->namespace . $this->version;
// write_log($namespace);
		register_rest_route( $namespace, '/' . $cpt, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_'. $cpt ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'edit_'. $cpt ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_'. $cpt ),
				'permission_callback' => array( $this, 'get_mbo_permission' )
			),
		) );
	}
	/*
	public function get_workplans( WP_REST_Request $request ) {

		$posts = get_posts( array(
			'post_type' => 'digma_workplans'
		) );

		if ( empty( $posts ) )
			return "Fail to find digma workplans";

		return "Only one title: " . $posts[0]->post_title . " id=" . $posts[0]->ID ;
		//return $result; // wp_load_alloptions();
	}*/

	public function get_needsolutions( WP_REST_Request $request ) {

		$posts = get_posts( array(
			'post_type' => 'digma_needsolutions'
		) );

		if ( empty( $posts ) )
			return "Fail to find digma needsolutions";

		return "Only one title: " . $posts[0]->post_title;
		//return $result; // wp_load_alloptions();
	}
/*
	public function get_familylogs( WP_REST_Request $request ) {

		$posts = get_posts( array(
			'post_type' => 'digma_familylogs'
		) );

		if ( empty( $posts ) )
			return "Fail to find digma familylogs";

		return "Only one title: " . $posts[0]->post_title;
		//return $result; // wp_load_alloptions();
	}
	*/
	public function get_combos( WP_REST_Request $request ) {

		$posts = get_posts( array(
			'post_type' => 'digma_combos'
		) );

		if ( empty( $posts ) )
			return "Fail to find digma combos";

		return "Only one title: " . $posts[0]->post_title;
		//return $result; // wp_load_alloptions();
	}
	public function get_intakes( WP_REST_Request $request ) {

		$posts = get_posts( array(
			'post_type' => 'digma_intakes'
		) );

		if ( empty( $posts ) )
			return "Fail to find digma intakes";

		return "Only one title: " . $posts[0]->post_title;
		//return $result; // wp_load_alloptions();
	}
	/* first version...
        public function mbo_get_leadforms( WP_REST_Request $request ) {
		$posts = get_posts( array(
			'post_type' => 'elementor_lead', 'post_status' => 'publish', 'numberposts' => -1, 
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'cache_results'          => false
		) );

		if ( empty( $posts ) )
			return "Fail to find lead forms";

		return $posts;
	}*/

	public function mbo_get_leadforms( WP_REST_Request $request ) {
		$body = $request->get_body();
		//write_log( $body );
		if (MBO_DEBUG_QB)
			write_log( "mbo_get_leadforms:" . $body );
		$json = json_decode( $body, true );
		//error_log('j decode body ='. print_r($json, true));
		$crud = $json['body']['crud']; // GET, UPDATE
		$fid = $json['body']['fid'];
		//error_log('crud mbo_get_leadforms crud='. $crud . ' fid='. $fid);
		if ($crud == 'DELETE'){ // TODO: do we realy need to accesses to DB???
			wp_trash_post($fid);
			return $fid;
		}

		if ($crud == 'FORMUPDATE'){ // TODO: do we realy need to accesses to DB???
			$p = get_post($fid);
			if ($p == null){
				//error_log('mbo_get_leadforms UPDATE ERROR fid='. $fid. ' get post failed');
				return ('update get lead ERROR');
			}
			$status = $this -> mbo_lead_meta($p, 'Updated');
			/*
			$pm = get_post_meta($fid, 'mbo_lead_status', true);
			$pmj = json_decode($pm);
			// error_log('GOT PM FRESH = '. print_r($pmj, true));
			if ($pmj  != false && $pmj != null){
				//error_log('pm value = '. print_r($pm));
				$pmj->lead_status = 'UPDATED';
			}
			else { // generate anyway
				error_log('mbo_get_leadforms UPDATE meta ERROR fid='. $fid. ' pm= '. print_r($pm, true));
				return ('update meta ERROR');
			}
			$npm = json_encode($pmj);
			if (!update_post_meta( $fid, 'mbo_lead_status', $npm, true )) // unique value
				error_log('mbo_lead_meta FAIL adding mbo_lead status pid='.$fid . '  pm= '. print_r($npm, true));
			*/
			return ($status);
		}
		// ASSUME is GET request - no $request parameters are used
		error_log('GET elementor lead type - no parameters not in use: crud='. $crud);
		$posts = get_posts( array(
			'post_type' => 'elementor_lead', 'post_status' => 'publish', 'numberposts' => -1, 
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'cache_results'          => false
		) );

		if ( empty( $posts ) )
            return $posts; // "Fail to find digma families";

		$res = array();
		foreach ($posts as $p){
			$pm = get_post_meta($p->ID, 'lead_data', true);
			if ($pm) {
				$upm = json_decode( maybe_unserialize( $pm ));
				//error_log('mbo_get_leadforms post meta AFTER maybe serialize = '. print_r($upm, true));
				$status = $this -> mbo_lead_meta($p, 'Received');
				$res[] = array('pid' => $p->ID, 'post_title' => $p->post_title,  'lead_status' => $status, 'form' => $upm);
			} else 	
				error_log('mbo_get_leadforms FAIL get post meta, pid = '. $p->ID . ' post= '. print_r($p, true));
		}

		return $res;
	}

	// return new or existing Mbo_lead custom field meta data
	private function mbo_lead_meta($p, $l_status){
		$pm = get_post_meta($p->ID, 'mbo_lead_status', true);
		if ($l_status != 'Updated' && $pm  != false && $pm != null) // exists, just return else generate mbo status
			return json_decode($pm);

		$m = array('status' => $l_status, 'date' => get_the_date( 'd/m/Y', $p->ID));
		$mj = json_encode($m);
		if (!update_post_meta( $p->ID, 'mbo_lead_status', $mj, true )) // unique value
			error_log('mbo_lead_meta FAIL adding mbo_lead status pid='.$p->ID . '  status= '. print_r($m, true));
		//error_log('mbo_lead_meta pm='. print_r(json_decode($mj), true));
		return $m; // decoded array
	}
// filter in program families
	public function get_families( WP_REST_Request $request ) {
		if (false !== ($res = get_transient( 'family_names_456' ))) {
			error_log("mbo_getFnames: using cache family_names_456");
			return $res; // use cache
	  	}
		$posts = get_posts( array(
			'post_type' => 'digma_families', 'post_status' => 'publish', 'numberposts' => -1, 
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'cache_results'          => false
		) );

		if ( empty( $posts ) )
			return "Fail to find digma families";

		$result = array();
		$i = 0;
		foreach ($posts as $post) {
			$acf = get_field('program_info', $post->ID);
			if ($acf === false){
				error_log("mbo_getFnames: FAIL get field, id=". $posts->ID . "  acf= >>". print_r($acf, true) . "<<");
				continue;
			}
			if ($acf['program_status'] == 'בתכנית' ||  $acf['program_status'] == '' 
				|| $acf['program_status'] == 'אחר' || $acf['program_status'] == 'לא מוגדר'){
					$result[$i++] = array ( 'id'=> $post->ID, 
											'f' => $acf["intake_family_name"],
											'w' => $acf["intake_family_woman_name"],
											'm' => $acf["intake_family_man_name"],
											's' => $acf["program_status"],
											't' => $acf["intake_yahav_member"]);
				}
		}
		set_transient( 'family_names_456', $result, 60*60*12 ); // 12 hours

		return $result;
	}

    public function get_referrals( WP_REST_Request $request ) {
/*        global $wpdb;

        $args = array(
            'post_type' => 'referrals',
            'posts_status' => 'publish'
        );
        $result = new WP_Query($args);
*/
        $posts = get_posts( array(
			'post_type' => 'digma_referrals',
			'posts_status' => 'publish'
        ) );

        if ( empty( $posts ) ) {
            return null;
        }

        return "Only one title: " . $posts[0]->post_title;
        //return $result; // wp_load_alloptions();
	}
	public function get_workplansdefs( WP_REST_Request $request ) {

        $posts = get_posts( array(
			'post_type' => 'digma_workplansdefs',
			'posts_status' => 'publish'
        ) );

        if ( empty( $posts ) ) {
            return null;
        }
        error_log('get_workplansdefs count posts ='. count($posts));
        
        $result = array();
        $i = 0;
        foreach ($posts as $post){
            $fg = get_field('workplans_plan_1', $post->ID);
            if (!isset($fg['wp_id'])){
                error_log('get_workplansdefs fg[wp_id]= NULL post ID='. $post->ID . ' title='. $post->post_title);
            }
            if ($fg['wp_id'] == ""){
                $fg['wp_id'] = $post->ID;
                update_field( 'workplans_plan_1', $fg, $post->ID );
            }
            //// error_log('get_workplansdefs fg='. print_r($fg, true));
            $result[$i++] = $fg;
        }
        return $result;
	}
/*
    public function delete_workplans( WP_REST_Request $request ) {
//		write_log($request);
		//if ( $request instanceof WP_REST_Request ) {
		$body = $request->get_body();
//		write_log( $body );
		$json = json_decode( $body, true );
		$pid = $json['pid'];
//		write_log( $pid);
		// now delete the post by pid
		wp_trash_post( $pid);
	}*/
    public function delete_needsolutions( WP_REST_Request $request ) {
//		write_log($request);
		//if ( $request instanceof WP_REST_Request ) {
		$body = $request->get_body();
//		write_log( $body );
		$json = json_decode( $body, true );
		$pid = $json['pid'];
//		write_log( $pid);
		// now delete the post by pid
		wp_trash_post( $pid);
	}
	/*
    public function delete_familylogs( WP_REST_Request $request ) {
//		write_log($request);
		//if ( $request instanceof WP_REST_Request ) {
		$body = $request->get_body();
//		write_log( $body );
		$json = json_decode( $body, true );
		$pid = $json['pid'];
//		write_log( $pid);
		// now delete the post by pid
		wp_trash_post( $pid);
	}*/

	public function delete_combos( WP_REST_Request $request ) {
//		write_log($request);
		//if ( $request instanceof WP_REST_Request ) {
		$body = $request->get_body();
//		write_log( $body );
		$json = json_decode( $body, true );
		$pid = $json['pid'];
//		write_log( $pid);
		// now delete the post by pid
		wp_trash_post( $pid);
	}
	public function delete_intakes( WP_REST_Request $request ) {
		$body = $request->get_body();
		$json = json_decode( $body, true );
		$pid = $json['pid'];
		wp_trash_post( $pid);
	}
	public function delete_families( WP_REST_Request $request ) {
//		write_log($request);
		//if ( $request instanceof WP_REST_Request ) {
		$body = $request->get_body();
//		write_log( $body );
		$json = json_decode( $body, true );
		$pid = $json['pid'];
//		write_log( $pid);
		// now delete the post by pid
		wp_trash_post( $pid);
	}

	public function delete_referrals( WP_REST_Request $request ) {
//		write_log($request);
		//if ( $request instanceof WP_REST_Request ) {
		$body = $request->get_body();
//		write_log( $body );
		$json = json_decode( $body, true );
		$pid = $json['pid'];
//		write_log( $pid);
		// now delete the post by pid
		wp_trash_post( $pid);
        delete_transient('referral_names'); // rebuild transient
	}
	public function delete_workplansdefs( WP_REST_Request $request ) {
//		write_log($request);
		//if ( $request instanceof WP_REST_Request ) {
		$body = $request->get_body();
		error_log('delete_workplansdefs body='. print_r($body, true) );
		$json = json_decode( $body, true );
		error_log('delete_workplansdefs json='. print_r($json, true) );
		$pid = $json['pid'];
//		write_log( $pid);
		// now delete the post by pid
		wp_trash_post( $pid);
        delete_transient('wp3_2_names'); // rebuild transient
	}
	
	
	public function edit_intakes( WP_REST_Request $request ) {
		return "edit intake TBD";
	}
	public function edit_families( WP_REST_Request $request ) {
		return "edit families TBD";
	}
	public function mbo_family2complete( WP_REST_Request $request ) {
		$body = $request->get_body();
		write_log("mbo_family2complete: DOING NOTHING" .  $body );
		return $json['body']['acf']; 
	}

	private function buildTable($tableId, $varray, $post_id){
		$j = add_row( $tableId, $varray, $post_id );
		//update_row( $tableId, $j, $varray, $post_id ) ;
	}

	private function parent_cpt($gender, $intake_info, $family_cpt, $referral){
		//write_log("Parent cpt, intake_info=");
		//error_log( print_r( $intake_info, true ) );

		if ($gender === "woman" && empty($intake_info['intake_family_woman_name'])) { // no woman in family
			return -1;
		}
		if ($gender === "man" && empty($intake_info['intake_family_man_name'])) { // no man in family
			return -1;
		}

		$title = $intake_info['intake_family_name'] . " ";
		$title .= ($gender === "woman") ? $intake_info['intake_family_woman_name']
									   : $intake_info['intake_family_man_name'] ;
		$new_parent_cpt = array(
			'post_status'    => 'publish',
			'post_author'    => 1,
			'post_type'      => 'digma_parents',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_title'     => $title,
			'post_category'	 => array(mbo_get_cat_by_slug("parent")), // 25 parents
			'post_content'   => "None"
		);
		$parent_cpt = wp_insert_post( $new_parent_cpt, true );
		if( is_wp_error( $parent_cpt ) )
		    return "Fail create parent:". $title ." new parent cpt reason:". $parent_cpt->get_error_message();

		update_field("parent_info",
			array(
				"parent_family_name" => $intake_info['intake_family_name'],
				"parent_first_name" => $gender === "woman" ? $intake_info['intake_family_woman_name'] 
														  : $intake_info['intake_family_man_name'] ,
				"parent_gender" => $gender === "woman" ? "נקבה" : "זכר" , // BEWARE Hebrew issues!!! condition is correct
				"parent_family" => $family_cpt,
				"parent_phone1" => $gender === "woman" ? $referral['family_referral_cell_woman'] 
														  : $referral['family_referral_cell_man'],
				"parent_merital_status" => $referral['family_referral_merita_status'],
			),
			$parent_cpt); 

		// return parent  pid
		// update family member in family cpt
		$field_key = "family_members"; // basic info
		$row = array(
			array(
				"id" => $parent_cpt,
				"family_member_cptid"	=> $parent_cpt,
				"family_member_role"	=> $gender === "woman"? "mother" : "father",
			)
		);
		// add line in subarray
		$i = add_row( "family_members", $row, $parent_cpt );
		update_row( "family_members", $i, 
			array(
				"id" => $parent_cpt,
				"family_member_cptid"	=> $parent_cpt,
				"family_member_role"	=> $gender === "woman"? "mother" : "father",
			), $parent_cpt );

		$j = add_row( "family_members", $row, $family_cpt );
		update_row( "family_members", $j,
			array(
				"id" => $parent_cpt,
				"family_member_cptid"	=> $parent_cpt,
				"family_member_role"	=> $gender === "woman"? "mother" : "father",
			), $family_cpt ) ;

		update_field("parent_hstatus", array( "id" => 1,
                "parent_health"=> "", "parent_bituachleumi"=> "", "parent_bituachleumi_temp"=> "", " parent_kupat_holim"=> "",
                "comment"=> ""),
				$parent_cpt);

       	$this->buildTable("health_issues", 
        		array("id" => 1, "health_issue_title" => "", "health_issue_limit" => "", "health_issue_start_date" => ""), 
        		$parent_cpt);

		$this->buildTable("parent_diploma", 
        		array("id" => 1, "education_qualification" => "", "education_hours" => "", "education_qual_inst" => "",
        			"education_qual_title" => "", "education_qual_year" => "" ), 
        		$parent_cpt);

		$this->buildTable("parent_employ_history", 
        		array("id" => 1, "employ_place" => "", "employ_from_date" => "", "employ_to_date" => "",
        			"employ_vetek" => "", "employ_role" => "", "employ_salary" => "" ), 
        		$parent_cpt);

		$this->buildTable("parent_employ_boz", 
        		array("id" => 1, "employ_place" => "", "employ_from_date" => "", "employ_to_date" => "",
        			"employ_vetek" => "", "employ_role" => "", "employ_skills" => "", "employ_salary" => "" ), 
        		$parent_cpt);

		update_field("parent_education", array(
                "id"=> 1, "year_of_study"=> "", "professional"=> "", "academic_education"=> "", "secular_education"=> "",
                "profession"=> "", "profession"=> ""),
				$parent_cpt);
		update_field("parent_employment", array(
                "employ_status"=> "", "employ_prev_places"=> "", "employ_main"=> "", "employ_role"=> "",
                "employ_duration"=> "", "employ_quality"=> "", "employ_current_place"=> "", "employ_cap_percent"=> "",
                "employ_cap_hours"=> "", "employ_social"=> "", "employ_bruto"=> "", "employ_neto"=> "",
                "employ_prev_exp"=> "", "comment"=> ""),
				$parent_cpt);

		update_field("parent_notemployed", array(
                "notemp_workbefore"=> "", "notemp_howlong"=> "", "notemp_why"=> "", "notemp_look4work"=> "", "notenough"=> "",
                "noreview"=> "", "nopass"=> "", "other"=> "", "places_last3y"=> ""),
				$parent_cpt);

		update_field("questionnaire_post", array(
				 "id"=> 1, "interviewer"=> "", "date"=> "", "q1"  => "", "q2"  => ""),/*, "q3"  => "", "q4"  => "", "q5"  => "", 
				 "q6"  => "", "q7"  => "", "q8"  => "", "q9"  => "", "q10"  => "", "q11"  => "", "q12"  => "", "q13"  => "", 
				 "q14"  => "", "q15"  => "", "q16"  => "", "q17"  => "", "q18"  => "", "q19"  => "", "q20"  => "", "q21"  => "", 
				 "q22"  => "", "q23"  => "", "q24"  => "", "q25"  => "", "q26"  => "", "q27"  => "", "q28"  => "", "s1"  => "", 
				 "s2"  => "", "s3"  => "", "s4"  => "", "s5"  => "", "s6"  => "", "s7"  => "", "s8"  => "", "s9"  => "", "s10"  => "", 
				 "s11"  => "", "s12"  => "", "r1"  => "", "r2"  => "", "r3"  => "", "r4"  => "", "r5"  => "", "comments"=> ""),*/
				 $parent_cpt);

		update_field("questionnaire_pre", array(
				 "id"=> 1, "interviewer"=> "", "date"=> "", "q1"  => "", "q2"  => ""),/* "q3"  => "", "q4"  => "", "q5"  => "", 
				 "q6"  => "", "q7"  => "", "q8"  => "", "q9"  => "", "q10"  => "", "q11"  => "", "q12"  => "", "q13"  => "", 
				 "q14"  => "", "q15"  => "", "q16"  => "", "q17"  => "", "q18"  => "", "q19"  => "", "q20"  => "", "q21"  => "", 
				 "q22"  => "", "q23"  => "", "q24"  => "", "q25"  => "", "q26"  => "", "q27"  => "", "q28"  => "", "s1"  => "", 
				 "s2"  => "", "s3"  => "", "s4"  => "", "s5"  => "", "s6"  => "", "s7"  => "", "s8"  => "", "s9"  => "", "s10"  => "", 
				 "s11"  => "", "s12"  => "", "r1"  => "", "r2"  => "", "r3"  => "", "r4"  => "", "r5"  => "", "comments"=> ""),*/
				 $parent_cpt);

		//write_log( "parent add row i=". $i . "  j=". $j);
		return $parent_cpt;
	}
	
	public function mbo_intake2family( WP_REST_Request $request ) {
		$body = $request->get_body();
		write_log("mbo: body=" .  $body );
		$json = json_decode( $body, true );
		
		$intake = $json['body']['acf']['intake'];
		$intake_info = $json['body']['acf']['intake_info'];
		$intake_pid = $json['body']['acf']['intake_info']['intake_code'];
		$referral_pid = $json['body']['acf']['intake_info']['referral_code'];

		$fname=$intake_info["intake_family_name"];
		$wname=$intake_info["intake_family_woman_name"];
		$mname=$intake_info["intake_family_man_name"];
	
		$referral = get_fields( $referral_pid, false);
		error_log("referral"); 		error_log(print_r($referral,true));
		$familyOsClient = $referral['family_referral_os_client'];
		//write_log("Get family_referral_os_client from referral cpt, familyOsClient=", $familyOsClient);

//		$intake_referral = get_field('intake_referral', $referral_pid);		write_log("intake_referral=", $intake_referral);

		// update intake first
		
		update_field('intake_info', $intake_info, $intake_pid);

		// new family post type

    	$title = $fname . (strlen($wname)>0 ? " ".$wname : "" ). (strlen($mname)>0? " ".$mname : "" );

		$new_intake_cpt = array(
			'post_status'    => 'publish',
			'post_author'    => 1,
			'post_type'      => 'digma_families',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_title'     => $title,
			'post_category'	 => array(mbo_get_cat_by_slug("family")), // 23 families
			'post_content'   => "None"
		);

		$family_cpt = wp_insert_post( $new_intake_cpt, true );
		if( is_wp_error( $family_cpt ) )
		    return "Fail create family:". $title ." new family cpt reason:". $family_cpt->get_error_message();

		delete_transient( 'family_names_123'); // all families in program & after
		delete_transient( 'family_names_456'); // families in program only
		delete_transient( 'MBO_QB_DBLINKS');
		// update family stuff: team member
		// basic info: family_contact
		date_default_timezone_set('Asia/Jerusalem');
		$d= date("j/n/Y");
		

		//  Program status ///////////////

		update_field("program_info",array(
				"program_date" => $d, 				"program_status"	=> "בתכנית",
				"intake_status"	=> $intake_info['intake_status'], "intake_yahav_member"	=> $intake_info["intake_yahav_member"],
				"intake_family_name" => $fname,	"intake_family_woman_name"=> $wname, "intake_family_man_name"=> $mname,
				"family_in_revaha" => $familyOsClient,
				"family_address"=> "", // $referral['family_referral_os_client']
				"family_zipcode"=> "", "family_town"=> "",
				"family_phone"=> "",
				"kids_number" => $referral['family_referral_num_kids'], 
				"man_cellphone"=> $referral['family_referral_cell_man'], 
				"woman_cellphone"=> $referral['family_referral_cell_woman'],
				"other_programs"=> $referral['family_referral_prev_plan'], 
				"other_programs_list"=> $referral['family_referral_prev_plan_name'],
				"referral_code"=> $referral_pid, "intake_code"=> $intake_pid, "program_code"=> $family_cpt),
			$family_cpt);

// generate satisfaction 1 & 2 year questionnaire
		$field_key = "questionnaire_1year"; // basic info
		$value = array(
			array(
				"id" => 1,
				"interviewer"	=> "",
				"date"	=> "",
				"family_status"	=> "",
				"intro" => "",
				//"family_in_revaha" => from referral,
			)
		);
		update_field( $field_key, $value, $family_cpt );
		for ($i = 1 ; $i < 3 ; $i++)
			update_field( $field_key."_q".$i, "", $family_cpt );

		$field_key = "questionnaire_2year"; // basic info
		update_field( $field_key, $value, $family_cpt );
		for ($i = 1 ; $i < 3 ; $i++) // cheating... only few fields to allow defaults kick in & save time?
			update_field( $field_key."_q".$i, "", $family_cpt );


		// needsolution_array ///////////////
		$field_key = "needsolution_array"; // basic info
		$value = array(
			array("id"   => 1,
					"needsolution_area" => "כלכלי",
					"needsolution_need" => "הגדלת הכנסה",
					"needsolution_solution" => "כלים למציאת עבודה"
				)		
		);
		update_field( $field_key, $value, $family_cpt );
		update_field( $field_key."_"."id", 1, $family_cpt );
		update_field( $field_key."_"."needsolution_name", "", $family_cpt );
		update_field( $field_key."_"."needsolution_role", "", $family_cpt );
		update_field( $field_key."_"."needsolution_area", $value[0]['needsolution_area'], $family_cpt );
		update_field( $field_key."_"."needsolution_need", $value[0]['needsolution_need'], $family_cpt );
		update_field( $field_key."_"."needsolution_solution", $value[0]['needsolution_solution'] , $family_cpt );
		update_field( $field_key."_"."needsolution_supplier", "", $family_cpt );
		update_field( $field_key."_"."needsolution_cost", "0", $family_cpt );
		update_field( $field_key."_"."needsolution_comment", "", $family_cpt );
		update_field( $field_key."_"."needsolution_yahav", 0, $family_cpt );
		update_field( $field_key."_"."needsolution_self", 0, $family_cpt );
		update_field( $field_key."_"."needsolution_revaha", 0, $family_cpt );
		update_field( $field_key."_"."needsolution_youth", 0, $family_cpt );
		update_field( $field_key."_"."needsolution_Zedaka", 0, $family_cpt );
		update_field( $field_key."_"."needsolution_government", 0, $family_cpt );
		update_field( $field_key."_"."needsolution_supplier_reduction", 0, $family_cpt );
		update_field( $field_key."_"."needsolution_total", 0, $family_cpt );

       
		$this->buildTable("family_expenses", 
        		array("id" => 1, "expense_purpose" => "", "expense_category" => "", "expense_sum" => ""), 
        		$family_cpt);

        $this->buildTable("family_income", 
        		array("id" => 1, "income_type" => "", "income_provider" => "", "income_frequency" => "", "income_sum" => ""), 
        		$family_cpt);

        $this->buildTable("family_meeting_summary", 
        		array("id" => 1, "meeting_date" => $d, "meeting_participants" => "", "meeting_title" => "", "meeting_summary" => ""), 
        		$family_cpt);

        $this->buildTable("family_debts", 
        		array("id" => 1, "family_debt_lawner" => "", "family_debt_orig_sum" => "", "family_debt_arrangement" => "", 
        			  "family_debt_unify" => "", "family_debt_description" => "", "family_debt_date" => ""), 
        		$family_cpt);

        $this->buildTable("family_mortgage", 
        		array("id" => 1, "mortgage_loaner" => "", "mortgage_sum" => "", "mortgage_monthly" => "", 
        			  "mortgage_duration_months" => "", "mortgage_end_date" => "", "mortgage_percent" => "", "mortgage_comment" => ""), 
        		$family_cpt);

        $this->buildTable("family_rights", 
        		array("id" => 1, "rights_provider" => "", "rights_name" => "", "rights_sum" => "", 
        			  "rights_pay_type" => "", "rights_frequency" => "", "rights_startdate" => "", "rights_enddate" => ""), 
        		$family_cpt);

        $this->buildTable("family_kids", 
        		array("id" => 1, "kid_family_name" => $fname, "kid_first_name" => "", "kid_birthdate" => "", 
        			  "kid_gender" => "", "kid_parents" => "", "kid_misgeret" => "", "kid_comment" => ""), 
        		$family_cpt);

        $this->buildTable("family_housing", 
        		array("family_house" => "", "family_persons_athome" => "", "family_room_num" => "", "family_house_status" => "", 
        			  "family_housing_comment" => ""), 
        		$family_cpt);
       /*
        $this->buildTable("familylog_array", 
        		array(
					"id"   => 1,
	                "familylog_date"   => $d,
	                "familylog_title"   => "התקבלה לתכנית",
	                "familylog_type"   => "",
	                "familylog_oldvalue"   => "סיום אינטייק",
	                "familylog_newvalue"   => "העברה לתכנית",
	                "familylog_comment"   => "אין"	
             	), 
        		$family_cpt);*/
        $task_array_comment_dflt = "שליש 1: הערות כאן....";
        $this->buildTable("familywp_finance", 
        		array("id" => 1, "familywp_goal" => "", "familywp_taskarray_description" => "", "familywp_taskarray_responsible" => "", 
        			  "familywp_taskarray_duedate" => "", "familywp_shlish1" => "", "familywp_shlish2" => "", "familywp_shlish3" => "",
        			  "familywp_shlish4" => "", "familywp_shlish5" => "", "familywp_shlish6" => "",
        			  "familywp_taskarray_comments" => $task_array_comment_dflt, "familywp_taskarray_needsolution" => ""), 
        		$family_cpt);

        $this->buildTable("familywp_family", 
        		array("id" => 1, "familywp_goal" => "", "familywp_taskarray_description" => "", "familywp_taskarray_responsible" => "", 
        			  "familywp_taskarray_duedate" => "", "familywp_shlish1" => "", "familywp_shlish2" => "", "familywp_shlish3" => "",
        			  "familywp_shlish4" => "", "familywp_shlish5" => "", "familywp_shlish6" => "",
        			  "familywp_taskarray_comments" => "", "familywp_taskarray_needsolution" => ""), 
        		$family_cpt);
        $this->buildTable("familywp_employ", 
        		array("id" => 1, "familywp_goal" => "", "familywp_taskarray_description" => "", "familywp_taskarray_responsible" => "", 
        			  "familywp_taskarray_duedate" => "", "familywp_shlish1" => "", "familywp_shlish2" => "", "familywp_shlish3" => "",
        			  "familywp_shlish4" => "", "familywp_shlish5" => "", "familywp_shlish6" => "",
        			  "familywp_taskarray_comments" => "", "familywp_taskarray_needsolution" => ""), 
        		$family_cpt);

       	update_field("family_savings",array(
				"do_u_save" => "", "monthly_savings_avg" => "0", "saving4each_child_sum" => "0","savings_comment" => ""),
				$family_cpt);

       	update_field("debts_status",
       			array("debts_total" => "0", "debts_range" => "0", "debts_bankropt" => "","debts_comment" => ""), $family_cpt);

       	update_field("family_balance",
       			array("total_expenses" => "0", "total_education" => "0", "total_shotef" => "0","total_debts_return" => "0",
       				"total_family_expenses" => "0", "total_family_balance" => "0", "balance_comment" => ""), $family_cpt);

       	 update_field("family_products_services", array("q1"  => "", "q101"  => "", "q102"  => "", "q103"  => ""), $family_cpt);

       	 update_field("family_legal", array("need_legal"  => "", "legal_explain"  => ""), $family_cpt);
       	 update_field("rights_q", array("rights_intro"  => "", "rights_extent"  => "", "rights_q1" => ""), $family_cpt);
  
		// generate parents cpt 1 or 2
		$woman_pid = $man_pid = -1;
		// write_log("woman=".$wname."<< maname=>>".$mname."<<");

		if (!empty($wname)){ // generate woman cpt
			$woman_pid = $this->parent_cpt('woman', $intake_info, $family_cpt, $referral);
			// update family memberrs
		}
		if (!empty($mname)){ // generate woman cpt
			$man_pid = $this->parent_cpt('man', $intake_info, $family_cpt, $referral);
			// update family members "family_members"
		}
		// update name, cellular, age, status?

		return $family_cpt ; // $json['body']['acf']; 
	}

	

	public function mbo_ref2intake( WP_REST_Request $request ) {
//		write_log($request);
		//if ( $request instanceof WP_REST_Request ) {
		$body = $request->get_body();
		write_log("mbo_ref2intake:" .  $body );
		$json = json_decode( $body, true );
		// write_log("mbo_ref2intake:" .  $json );

		$title = trim($json['body']['acf']['family_referral_family_name']);
		if ( trim($json['body']['acf']['family_referral_woman_firstname']) ) {
			$title .= " " . trim($json['body']['acf']['family_referral_woman_firstname']);
		}
		if ( trim($json['body']['acf']['family_referral_man_firstname']) ) {
			$title .= " " . trim($json['body']['acf']['family_referral_man_firstname']);
		}

		$new_intake_cpt = array(
			'post_status'    => 'publish',
			'post_author'    => 1,
			'post_type'      => 'digma_intakes',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_title'     => $title,
			'post_category'	 => array(mbo_get_cat_by_slug("intakes")), // 24 intakes
			'post_content'   => "None"
		);

		$post_id = wp_insert_post( $new_intake_cpt, true );
		// copy referral values into new intake cpt
		update_field('intake_referral', $json['body']['acf'], $post_id); //  update intake referral as acf array
		$json['body']['acf']['family_referral_family_code'] = $post_id; // update referral to know that intake cpt exists

		// generate intake info group values & initial for intake group
		$h = "באינטייק";
		$team = $json['body']['acf']['family_referral_team'];
		$inatakeDate = $json['body']['acf']['family_referral_intake_date'];
		$referralcode = $json['body']['acf']['family_referral_code']; //family_referral_family_code
		$fname = $json['body']['acf']['family_referral_family_name'];
		$wname = $json['body']['acf']['family_referral_woman_firstname'];
		$mname = $json['body']['acf']['family_referral_man_firstname'];

		date_default_timezone_set('Asia/Jerusalem');
		$d= date("j/n/Y");
		update_field("intake_info",array(
				"intake_date"	=> $d,
				"intake_status"	=> $h,
				"intake_yahav_member" => $team,
				"intake_qdate" => $inatakeDate,
				"intake_code" => $post_id,
				"referral_code" => $referralcode,
				"program_code" => 0,
				"intake_family_name" => $fname,
				"intake_family_woman_name" => $wname,
				"intake_family_man_name"=> $mname
			), 
			$post_id );

		update_field("intake",array(
				"intake_date" => $inatakeDate,
				"intake_team" => $team,
				"intake_code" => $post_id
			), 
			$post_id );

		// update referral with new family/intake cpt id
		$json['body']['acf']['family_referral_family_code']  =$post_id;
		//if (!update_field('family_referral_family_code', $post_id, $referralcode ))
                if (!update_field('family_referral_family_code', $post_id, $referralcode )
			|| !update_field('family_referral_status', "עברה לאינטייק", $referralcode))
			error_log("ref2intake fail to update 'family_referral_family_code' with intakeid=".$post_id. "<< on cpt id=". $referralcode);
		else {
			error_log("Ref2intake returns");
			write_log($json['body']['acf']);
		}
		delete_transient('family_intake_names');
		return $json['body']['acf']; // was get referrals/// wp_load_alloptions();
	}
	public function mbo_ref2family( WP_REST_Request $request ) {
//		write_log($request);
		//if ( $request instanceof WP_REST_Request ) {
		$body = $request->get_body();
		write_log("mbo_ref2family:" .  $body );
		$json = json_decode( $body, true );
		// write_log("mbo_ref2family:" .  $json );

		$title = trim($json['body']['acf']['family_referral_family_name']);
		if ( trim($json['body']['acf']['family_referral_woman_firstname']) ) {
			$title .= " " . trim($json['body']['acf']['family_referral_woman_firstname']);
		}
		if ( trim($json['body']['acf']['family_referral_man_firstname']) ) {
			$title .= " " . trim($json['body']['acf']['family_referral_man_firstname']);
		}

		$new_family_cpt = array(
			'post_status'    => 'publish',
			'post_author'    => 1,
			'post_type'      => 'digma_families',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_title'     => $title,
			'post_content'   => "None"
		);

		$post_id = wp_insert_post( $new_family_cpt, true );
		// update family cpt id in referral cpt....

		update_field( "family_referral_family_code", $post_id, $json['body']['acf']['family_referral_code'] );
		// copy referral values into new family cpt
		// foreach ($json['body']['acf'] as $key => $val)
		//		update_field( $key, $val, $post_id );

		update_field('acf', $json['body']['acf'], $post_id); // referral as acf array
        delete_transient('family_names_123');
        delete_transient('family_names_456');
		return $json['body']['acf']; // was get referrals/// wp_load_alloptions();
	}
	
// mbo_getFnames
	public function mbo_getFnames( WP_REST_Request $request ) {
		//$body = $request->get_body();
		//write_log( "mbo_getFnames:" . $body );
	
		//$args = array( 'posts_per_page' => -1, 'category' => 26 ); // referrals category
		if (false !== ($res = get_transient( 'family_names_123' ))) {
			error_log("mbo_getFnames: using cache family_names_123");
			return $res; // use cache
	  	}
		$referralsCpt = get_posts( array('post_type' => 'digma_families', 'posts_per_page' => -1,
						'post_status' => 'publish', 'orderby' => 'title', 'order'   => 'ASC') );
		if (count($referralsCpt) < 1)
			return "Got mbo_getFnames OK, but no referrals found";
		$res = array();
		$i = 0;
		foreach ( $referralsCpt as $cpt ){
			//$y = get_field("field_5c0cb18a6f962", $cpt->ID);
			$y = get_field("program_info", $cpt->ID);
			if ($y === false){
				error_log("mbo_getFnames: FAIL get field, id=". $cpt->ID . "  y= >>". print_r($y, true) . "<<");
				continue;
			}
			$item = array(
				"id" => $cpt->ID,
				"f" => $y["intake_family_name"],
				"w" => $y["intake_family_woman_name"],
				"m" => $y["intake_family_man_name"],
				"s" => $y["program_status"],
				"t" => $y["intake_yahav_member"]
			);
			array_push($res,$item);
			//$res[$i] = $item;
		}
		set_transient( 'family_names_123', $res, 60*60*12 ); // 12 hours

		// return json array of items
		return $res; // json_encode($res); // "Got mbo_getFnames OK, referrals=". count($referralsCpt) ;
	}

// mbo_getInames get intakes
	public function mbo_getInames( WP_REST_Request $request ) {
		//$body = $request->get_body();
		//write_log( "mbo_getInames:" . $body ); 
	
		if (false !== ($res = get_transient( 'family_intake_names' ))) {
			error_log("mbo_getInames: using cache family_intake_names");
			return $res; // use cache
	  	}
		$referralsCpt = get_posts( array('post_type' => 'digma_intakes', 'posts_per_page' => -1,
						'post_status' => 'publish', 'orderby' => 'title', 'order'   => 'ASC') );
		if (count($referralsCpt) < 1)
			return "Got mbo_getInames OK, but no intakes found";
		$res = array();
		$i = 0;
		foreach ( $referralsCpt as $cpt ){
			$y = get_field("intake_info", $cpt->ID);
			if ($y === false){
				error_log("mbo_getInames: FAIL get field, id=". $cpt->ID . "  y= >>". print_r($y, true) . "<<");
				continue;
			}
			$item = array(
				"id" => $cpt->ID,
				"f" => $y["intake_family_name"],
				"w" => $y["intake_family_woman_name"],
				"m" => $y["intake_family_man_name"],
				"s" => $y["intake_status"],
				"t" => $y["intake_yahav_member"]
			);
			array_push($res,$item);
			//$res[$i] = $item;
		}
		set_transient( 'family_intake_names', $res, 60*60*12 ); // 12 hours

		// return json array of items
		return $res; // json_encode($res); // "Got mbo_getInames OK, intakes=". count($referralsCpt) ;
	}

	// Get the acf field group definition of workplan_3.2
	public function mbo_getWPacfjson3( WP_REST_Request $request ) {
		/*if (false !== ($res = get_transient( 'acf_workplan_3' ))) {
			error_log("mbo_getRnmbo_getWPacfjson3ames: using cache");
			return $res; // use cache
		} */
		$body = $request->get_body();
		// workplans_plan_3 latest filename = group_5fdbb577d6bd3.json
		$r = file_get_contents( plugin_dir_url( __FILE__ ) . 'acf-json/group_5fdbb577d6bd3.json'); // group_5fdbb577d6bd3.json is "workplans_plan_3.2" field group
		if ($r) {
			$res = json_decode($r, true);
			// error_log( "mbo_getWPacfjson3:" . print_r($res, true) );
			return $res; // json_encode($res);
		}
		//// error_log( "mbo_getWPacfjson3: return FALSE on " . plugin_dir_url( __FILE__ ) . 'acf-json/group_5fdbb577d6bd3.json' );
		return false;
	}
	
    public function mbo_getWPacfjson( WP_REST_Request $request ) {
		/*if (false !== ($res = get_transient( 'referral_names' ))) {
			error_log("mbo_getRnames: using cache");
			return $res; // use cache
	  	} */
		$body = $request->get_body();
	    // workplans_plan_3 latest filename = group_5fdbb577d6bd3.json
	    // $r = file_get_contents( plugin_dir_url( __FILE__ ) . 'acf-json/group_5f3a38ad6d13f.json'); // group_5f3a38ad6d13f workplan group
	    $r = file_get_contents( plugin_dir_url( __FILE__ ) . 'acf-json/group_5f7ecce6ab965.json'); // group_5f7ecce6ab965.json is "workplans_plan_1" workplan/field group
        if ($r) {
            $res = json_decode($r, true);
            // error_log( "mbo_getWPacfjson:" . print_r($res, true) );
		    return $res; // json_encode($res);
        }
        //// error_log( "mbo_getWPacfjson: return FALSE on " . plugin_dir_url( __FILE__ ) . 'acf-json/group_5f3a38ad6d13f.json' );
        return false;
	}
    public function mbo_getWPnames( WP_REST_Request $request ) {
	/*	if (false !== ($res = get_transient( 'wp3_2_names' ))) {
			//error_log("mbo_getWPnames: using cache");
			return $res; // use cache
	  	}*/
		$body = $request->get_body();
		////error_log( "mbo_getWPnames: body=" . print_r($body, true) );
	
		//$args = array( 'posts_per_page' => -1, 'category' => 26 ); // referrals category
		$referralsCpt = get_posts( array('post_type' => 'digma_workplansdefs', 'posts_per_page' => -1, 'post_status' => 'publish', 
						'orderby'    => 'title', 'order'   => 'ASC') );
		if (count($referralsCpt) < 1)
			return "Got mbo_getWPnames OK, but no referrals found";
		$res = array();
		$i = 0;
		foreach ( $referralsCpt as $cpt ){
			$v = 1;
			$x = get_field("workplans_plan_1", $cpt->ID); // field_5f7ecce6b1095
			if (!$x){
				////error_log('mbo_getWPnames item= is FALSE id=' . $cpt->ID);
				$x = get_field("workplans_plan_3", $cpt->ID); // field_5f7ecce6b1095
				if (!$x)
					continue;
				$v = 3;
			} 
			//if ($v == 3 && $i++ == 0)
				//error_log('wp ='. print_r($x, true));
			//if ($v == 3) continue;
			////else
			  ////  error_log('mbo_getWPnames item=' . print_r($x, true));
			//if ($cpt->ID == ){
			//    get_fields([12906]
			//}
			if ($v == 1){
				$item = array(
					"id" => $cpt->ID,
					"f" => $x["wp_name"],
					"w" => $x["wp_gender_new"],
					"m" => $x["wp_social_section_new"],
					"s" => $x["wp_status_new"],
					"g" => $x["wp_channel"],
					"c" => $x["wp_centers"] ? $x["wp_centers"] : "",
					"geo" => $x["wp_geo_area_new"],
					"t" => $x["wp_lib_status"]
				);
			} 
			if ($v == 3){
				$item = array(
					"id" => $cpt->ID,
					"f" => $x["fg_intro"]["wp_name"],
					"all" => $x["fg_target_audience"]["wp_targetaud"], // if open to all
					"w" => $x["fg_target_audience"]["wp_gender_new"],
					"m" => isset($x["fg_target_audience"]["wp_social_section_new"]) ? $x["fg_target_audience"]["wp_social_section_new"][0] : "",
					"os" => $x["fg_desc"]["wp_operation_status"],
					"s" => $x["fg_desc"]["wp_status_new"],
					"g" => $x["fg_wp_components"]['wp_solution_type'],//$x["wp_channel"],
					"c" => $x["fg_intro"]["wp_centers"] ? $x["fg_intro"]["wp_centers"] : "",
					"geo" => $x["fg_desc"]["wp_geo_area_new"],
					"muni" => $x["fg_desc"]["wp_operation_muni"],
					"t" => $x["fg_metadata"]["wp_lib_status"]
				);
			}
			array_push($res,$item);
			
			//$res[] = $item; // $x['wp_name'];
		}
		
		set_transient( 'wp3_2_names', $res, 60*15 ); // 15 min was 60*12 - 12 hours
		// return json array of items
		
		return $res; // json_encode($res); // "Got mbo_getRnames OK, referrals=". count($referralsCpt) ;
	}
	
// mbo_getRnames
	public function mbo_getRnames( WP_REST_Request $request ) {
		if (false !== ($res = get_transient( 'referral_names' ))) {
			error_log("mbo_getRnames: using cache");
			return $res; // use cache
	  	} 
		$body = $request->get_body();
		//write_log( "mbo_getRnames:" . $body );
	
		//$args = array( 'posts_per_page' => -1, 'category' => 26 ); // referrals category
		$referralsCpt = get_posts( array('post_type' => 'digma_referrals', 'posts_per_page' => -1,
						'orderby'    => 'title', 'order'   => 'ASC') );
		if (count($referralsCpt) < 1)
			return "Got mbo_getRnames OK, but no referrals found";
		$res = array();
		$i = 0;
		foreach ( $referralsCpt as $cpt ){
			//$x = get_field("family_referral_family_name", $cpt->ID);
			$x = get_fields($cpt->ID);
			$item = array(
				"id" => $cpt->ID,
				"f" => $x["family_referral_family_name"],
				"w" => $x["family_referral_woman_firstname"],
				"m" => $x["family_referral_man_firstname"],
				"s" => $x["family_referral_status"]
			);
			array_push($res,$item);
			/////write_log($item);
			//write_log("fanme=". $x["family_referral_family_name"] . "<< womaname=" . $x["family_referral_woman_firstname"]);
			// add post id, family name & parents name & status & creation time??
		}
		
		set_transient( 'referral_names', $res, 60*60*12 ); // 12 hours 
		// return json array of items
		return $res; // json_encode($res); // "Got mbo_getRnames OK, referrals=". count($referralsCpt) ;
	}

// mbo_updateFamily
	public function mbo_updateFamily( WP_REST_Request $request ) {
		$body = $request->get_body();
		write_log( "mbo_updateFamily:" . $body );
		$json = json_decode( $body, true );
		$post_id = $json['body']['id'];
		$data = $json['body']['data']; // element data
		$dataId = $json['body']['dataId']; // element name
		$dataType = $json['body']['dataType']; // table or group

		write_log( "mbo_updateFamily:PID=" . $post_id . " data type = ". $dataType);

		// remove existng group/table
		delete_field($dataId, $post_id);

		if ($dataType == "table"){
			$mainTable = $data; // input table
			for ($row=0; $row < count($mainTable); $row++) {
				if (!update_row($dataId, $row+1, $mainTable[$row], $post_id ))
					write_log("Update row fail for " . $dataId . " row=". ($row+1));
			} 
		} else { // update group
			write_log( "mbo_updateFamily-GROUP:PID=" . $post_id . " data ID = ". $dataId);
			update_field($dataId, $data, $post_id);
                        if ($dataId == 'program_info'){ // invalidate cache names
				delete_transient( 'family_names_123'); // all families in program & after
				delete_transient( 'family_names_456'); // families in program only
			} 
		}
		return ; // $json['body']['data'];
	}

	public function edit_needsolutions( WP_REST_Request $request ) {
		$body = $request->get_body();
		// write_log( "edit_needsolutions:" . $body );
		$json = json_decode( $body, true );
		$post_id = $json['body']['id'];
		// write_log( "edit_needsolutions:PID=" . $post_id );

		$row = 1; // acf starts counting at 1 so we follow...
					// regarding received line zero as scf line #1
		foreach ($json['body']['acf'] as $line){ // replace existing rows & add new if required
				delete_row('needsolution_array', $row, $post_id );
				update_row('needsolution_array', $row, $line, $post_id );
				$row += 1;
		}
		// remove rows that should be removed
		while (delete_row('needsolution_array', $row, $post_id )) $row +=1;
				
		return $json['body']['acf'];
	}
	/*
	public function edit_familylogs( WP_REST_Request $request ) {
		$body = $request->get_body();
		// write_log( "edit_familylogs:" . $body );
		$json = json_decode( $body, true );
		$post_id = $json['body']['id'];
		// write_log( "edit_familylogs:PID=" . $post_id );

		$row = 1; // acf starts counting at 1 so we follow...
					// regarding received line zero as scf line #1
		foreach ($json['body']['acf'] as $line){ // replace existing rows & add new if required
				delete_row('familylog_array', $row, $post_id );
				update_row('familylog_array', $row, $line, $post_id );
				$row += 1;
		}
		// remove rows that should be removed
		while (delete_row('familylog_array', $row, $post_id )) $row +=1;
				
		return $json['body']['acf'];
	}
	*/
	public function edit_combos( WP_REST_Request $request ) {
		$body = $request->get_body();
		// write_log( "edit_combos:" . $body );
		$json = json_decode( $body, true );
		$post_id = $json['body']['id'];
		// write_log( "edit_combos:PID=" . $post_id );
		
		$row = 1; // acf starts counting at 1 so we follow...
					// regarding received line zero as scf line #1
		foreach ($json['body']['acf'] as $line){ // replace existing rows & add new if required
				delete_row('combo_array', $row, $post_id );
				update_row( 'combo_array', $row, $line, $post_id );
				$row += 1;
		}
		// remove rows that should be removed
		while (delete_row('combo_array', $row, $post_id )) $row +=1;
				
		return $json['body']['acf'];
	}

    public function edit_referrals( WP_REST_Request $request ) {
//		write_log($request);
        //if ( $request instanceof WP_REST_Request ) {
        $body = $request->get_body();
        write_log( "edit_referrals:" . $body );
        $json = json_decode( $body, true );

        if (!isset($json['body']) || !isset($json['body']['acf'])){
            error_log('Edit referrals - unexpected format cannot proceed request body= '. $body);
            return 'Edit referrals - unexpected format cannot proceed';
         }

        $title = trim($json['body']['acf']['family_referral_family_name']);
        if ( trim($json['body']['acf']['family_referral_woman_firstname']) ) {
	        $title .= " " . trim($json['body']['acf']['family_referral_woman_firstname']);
        }
        if ( trim($json['body']['acf']['family_referral_man_firstname']) ) {
	        $title .= " " . trim($json['body']['acf']['family_referral_man_firstname']);
        }

		$pid = $json['body']['acf']['family_referral_code'];
	    if ($pid == 999999){ // create new referral post type
 	        $my_post = array(
		        'post_status'    => 'publish',
		        'post_author'    => 1,
		        'post_type'      => 'digma_referrals',
		        'comment_status' => 'closed',
		        'ping_status'    => 'closed',
		        'post_title'     => $title,
		        'post_category'	 => array(mbo_get_cat_by_slug("referral")), // 26 referral
		        'post_content'   => "None"
	        ); // , 'meta_input' => array('acf' => $acf ));

	        $pid = wp_insert_post( $my_post, true );
	        $json['body']['acf']['family_referral_code'] = $pid; // UPDATE new PID to return!!
                delete_transient('referral_names'); // rebuild transient
        } else // in case title=family name+woman+man was changed
			wp_update_post( array('ID' => $pid, 'post_title' => $title) );
			
// error_log('edit referrals json body acf=' . print_r($json['body']['acf'], true));

	    foreach ($json['body']['acf'] as $key => $val) {
	    	if ($key == "family_referral_code")
			    update_field( $key, $pid, $pid );
			else
		        update_field( $key, $val, $pid );
	    }
        error_log( "edit_referrals:" );
        write_log( $json['body']);

	    return $json['body']['acf']; // was get referrals/// wp_load_alloptions();
    }
    /*
    public function edit_workplans( WP_REST_Request $request ) {
		$body = $request->get_body();
		error_log( "edit_workplans: body" . print_r($body, true) );
		$json = json_decode( $body, true );
		$post_id = $json['body']['acf']['wp_id'];
		$p = get_post($post_id);
		if ($p == null){
		    error_log( "Internal error, edit_workplans:PID=" . $post_id . '  does not exist' );
		    return 'Internal error post='.$post_id. '  does not exist';
		} 
		error_log( "edit_workplans:PID=" . $post_id );
		update_field('workplans_plan_1', $json['body']['acf'], $post_id);

		return $json['body']['acf'];
	}*/
		
	// create_WP_cpt('digma_referrals',  $json['body']['acf']['wp_name'] i.e. $title, $key = 'workplans_plan_1', $acf = field group conetnt
	// return pid or 0 if fail
	private function create_WP_cpt($type, $title, $cat_name, $key, $acf){
        $args = array(
	        'post_status'    => 'publish',
	        'post_author'    => 1,
	        'post_type'      => $type, // 'digma_referrals',
	        'comment_status' => 'closed',
	        'ping_status'    => 'closed',
	        'post_title'     => $title,
	        'post_category'	 => array(mbo_get_cat_by_slug($cat_name)), // cat id
	        'post_content'   => "None"
        ); // , 'meta_input' => array('acf' => $acf ));

        $pid = wp_insert_post( $args, true );
        if(is_wp_error($pid)){
            //there was an error in the post insertion, 
            error_log('Create WP FAILED with message=' . $post_id->get_error_message() . '  acf='. $acf);
            return 0; // fail
        }
		$acf['wp_id'] = $pid; // UPDATE new PID to CPT data
		$acf['fg_metadata']['wp_id'] = $pid;
	    // error_log('newWPcpt acf with wp_id'. print_r($acf, true));
	    update_field( $key, $acf, $pid );
        //delete_transient('referral_names'); // rebuild transient  wp3_2_names
        delete_transient('wp3_2_names'); // rebuild transient  
        return $acf;

// error_log('create_WP_cpt json body acf=' . print_r($json['body']['acf'], true));

	    
	}
    public function edit_workplansdefs( WP_REST_Request $request ) {
        $body = $request->get_body();
        // error_log( "edit_workplansdefs: body" . print_r($body, true) );
		$json = json_decode( $body, true );
		error_log( "edit_workplansdefs: body= " . print_r($json['body'], true) );
		// lookup is post exists
		$post_id = 0;
		if (isset($json['body']['acf']['fg_metadata']['wp_id'])){ // workplan_b3
			$post_id = intval($json['body']['acf']['fg_metadata']['wp_id']);
			$wp_key = 'field_5fdbb577da8c5';
			$post_name = $json['body']['acf']['fg_intro']['wp_name'];
		} else if (isset($json['body']['acf']['wp_id'])){ // workplan_b1
			$post_id = intval($json['body']['acf']['wp_id']);
			$wp_key = 'field_5f7ecce6b1095';
			$post_name = $json['body']['acf']['wp_name'];
		}
		// create new
        if ($post_id == 0){ // create_WP_cpt($type, $title, $cat_name, $key, $acf)
            $acf = $this->create_WP_cpt('digma_workplansdefs', $post_name, 'workplandef', $wp_key, $json['body']['acf']);
            if ($acf != 0){ // successeful created new workplan cpt
                return $acf; // $json['body']['acf'];
            }
            return 0; // failed
		} 
		// Update
		$p = get_post($post_id);

        //error_log( "edit_workplansdefs:PID=" . $post_id );
        //error_log( "edit_workplansdefs: body" . print_r($json['body'], true) );

        //$plan_title = $json['body']['acf']['wp_name'];
        if ($p->post_title != $post_name){
            $args = array(
                  'ID'           => $post_id,
                  'post_title'   => $post_name,
              );
             
            // Update the post into the database
            wp_update_post( $args );
        }

        // update_field('workplans_plan_1', $json['body']['acf'], $post_id); //field_5f3a38f2a4c08
        // current workplans_plan_1 key is field_5f7ecce6b1095
        delete_field($wp_key, $post_id);
        update_field($wp_key, $json['body']['acf'], $post_id); // old workplans_plan: field_5f3a38f2a4c08
        
        return $json['body']['acf'];
    }

/*
        $this->debug_wp($json['body']['acf']);

	private function debug_wp($acf_wp){
	    foreach ($acf_wp['wp_goals_2'] as $item){
	       if (is_object($item) || is_array($item))
	            error_log('debug_wp ARR|OBJ item=', print_r($k, true));
	       else error_log('debug_wp string item=', print_r($k, true));
	            
	    }
	    /*
	    foreach ($acf_wp as $k => $v){
	        error_log('debug_wp key=', print_r($k, true));
	        error_log('debug_wp value=', print_r($v, true));
	    }* /
	    return 0;
	}
	*/
    public function get_mbo_permission() {

	/*	if ( ! current_user_can( 'install_themes' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You do not have permissions to manage options.', 'wp-mbo' ), array( 'status' => 401 ) );
		}
    */
		return true;
	}
	
	private function mbo_histogram($type){
		$referralsCpt = get_posts( array('post_type' => 'digma_'.$type, 'posts_per_page' => -1, 'status' => 'publish') );
		if (count($referralsCpt) < 1)
			return "Got mbo_yahavStats request OK, but no post of type= ". $type ." found";

		$i = 0;
		$histogram = array();
		if ($type == "referrals") {
			foreach ( $referralsCpt as $cpt ){
				$x = get_fields($cpt->ID);
				if (!isset($x["family_referral_code"])) continue; // not counting usaved referrals
				$v= $x["family_referral_status"]; // the status as index
				if (!isset($histogram[$v])) 
					$histogram[$v] = 1;
				else $histogram[$v]++;
				$i++; 
			}
		} else if ($type == "intakes") {
			foreach ( $referralsCpt as $cpt ){
				$x = get_fields($cpt->ID);
				if (!isset($x["intake_info"])) continue; // not counting usaved referrals
				$v= $x["intake_info"]["intake_status"]; // the status as index
				if (!isset($histogram[$v])) 
					$histogram[$v] = 1;
				else $histogram[$v]++;
				$i++; 
			}
		} else if ($type == "families") {
			foreach ( $referralsCpt as $cpt ){
				//$x = get_field("field_5c0cb18a6f962", $cpt->ID);
				$x = get_field("field_5c0cb18a6f962", $cpt->ID);
				if ($x === false) { // not counting usaved referrals
					error_log("mbo_histogram: FAIL get field, id=". $cpt->ID . "  x= >>". print_r($x, true) . "<<");
					continue;
				} 
				$v= $x["program_status"]; // the status as index
				/*$x = get_fields($cpt->ID);
				if (!isset($x["program_info"])) continue; // not counting usaved referrals
				$v= $x["program_info"]["program_status"]; // the status as index*/
				if (!isset($histogram[$v])) 
					$histogram[$v] = 1;
				else $histogram[$v]++;
				$i++; 
			}
		} else return "mbo_histogram: Unknown request= <". $type ."> found";
		arsort($histogram, SORT_NUMERIC); //, SORT_NUMERIC

		$arg = array ('type' => $type, 'total' => $i, 'table' => $histogram	);
		//error_log($type. " historgram, total=". $i);
		//error_log(print_r( $histogram, true ));
		//return "Result generated"; // $json['body']['data'];
		return $arg;
	}

	//update_stat_table($referrals_key, $ref_id_key, $ref_title_key, $ref_total_key, $r['table'], $stat_cpt);

	private function update_stat_table($table_key, $id_key, $title_key, $total_key, $data_table, $post_id){
		// Update referrals
		$n = 1;
		foreach ($data_table as $k => $v){
			// error_log("K =". $k . "  V=" . $v);
			add_row( $table_key, array($id_key=> $n++, $title_key=> $k, $total_key => $v), $post_id );
		}
	}
	public function mbo_new_postat($title){ // add title / file name as parameter
		$new_stat_cpt = array(
			'post_status'    => 'publish',
			'post_author'    => 1,
			'post_type'      => 'digma_stats',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_title'     => $title,
			'post_category'	 => array(mbo_get_cat_by_slug("statistics")), // 27 statistics
			'post_content'   => "None"
		);

		$stat_cpt = wp_insert_post( $new_stat_cpt, true );
		if( is_wp_error( $stat_cpt ) )
		    return "Fail create family:". $title ." new family cpt reason:". $stat_cpt->get_error_message();

		// Get histograms
		$r = $this->mbo_histogram("referrals");
		$i = $this->mbo_histogram("intakes");
		$f = $this->mbo_histogram("families");

		// Update totlas
		$daily_stat_key = "field_5c7474802674c";
		$referrals_total = 'field_5c7474a32674d';  // number
		$intakes_total = 'field_5c7474bd2674e';  // number
                $families_total = 'field_5c7474e42674f'; // number

        update_field( $daily_stat_key, 
        		array($referrals_total => $r['total'], $intakes_total => $i['total'], 
        		  $families_total => $f['total']), $stat_cpt );

                // Update time field_5c74e5328b495
		$time_key = 'field_5c750fc7fa99e'; // update_time = 'field_5c750fc7fa99e'; // text as date
		update_field($time_key, $title, $stat_cpt);
		
		// Update tables

		// Update referrals
		$referrals_key  = "field_5c7473d426740"; // table

        $ref_id_key = 'field_5c7473f426741'; // row entry
        $ref_title_key = 'field_5c74740e26742'; // row entry
        $ref_total_key = 'field_5c74742826743';// row entry
        $this->update_stat_table($referrals_key, $ref_id_key, $ref_title_key, $ref_total_key, $r['table'], $stat_cpt);

		// Update intakes
		$intakes_key = 'field_5c74744126744';  // table

        $id_key = 'field_5c74744126745'; // row entry
        $title_key = 'field_5c74744126746'; // row entry
        $total_key = 'field_5c74744126747';// row entry
        $this->update_stat_table($intakes_key, $id_key, $title_key, $total_key, $i['table'], $stat_cpt);

		// Update families
		$families_key = 'field_5c74745f26748';  // table

        $id_key = 'field_5c74745f26749'; // row entry
        $title_key = 'field_5c74745f2674a'; // row entry
        $total_key = 'field_5c74745f2674b';// row entry
        $this->update_stat_table($families_key, $id_key, $title_key, $total_key, $f['table'], $stat_cpt);

		$this->start_end_stats($stat_cpt); // HUGE calc

		// set transient 'mbo_new_postat'
		$trans = get_fields($stat_cpt);
		set_transient( 'mbo_new_postat_daily', $trans, 60*60*24 );
		return $stat_cpt;
	}

	private function get_latest_stats(){
		$statsCpt = wp_get_recent_posts( array('post_type' => 'digma_stats', 'numberposts' => '1', 'status' => 'publish') );
		if (count($statsCpt) < 1)
			return "No post of type= digma_stats found";
		return($statsCpt[0]['ID']);
	}
	private function start_end_stats($post_id){
		/// sroi staff
		$roi_res = calc_se_roi();
		$sroi_key = 'field_5d4cfdc027974';
		add_row( 'sroi', array('id'=> 1, 'title'=> 'roi', 'total' => $roi_res[0], 'total_full' => $roi_res[0]), $post_id );
		$id = 2;
		foreach ($roi_res[1][1] as $k => $v){ // add values phase 0
			add_row( 'sroi', array('id'=> $id++, 'title'=> $k, 'total' => $v, 'total_full' => $roi_res[2][1][$k]), $post_id );
		}
		//add_row( 'sroi', array('id'=> 2, 'title'=> 'legal', 'total' => $roi_res[1][1]['legal'], 'total_full' => $roi_res[2][1]['legal']), $post_id );
		if (MBO_DEBUG){
			error_log("calc_se_roi = ". print_r($roi_res[1][1],true) );
			error_log("calc_se_roi res0= ". print_r($roi_res[1],true) );
		}
	}
	public function mbo_yahavStatsNow( WP_REST_Request $request ) {
		//error_log("Got mbo_yahavStatsNow request");
		date_default_timezone_set('Asia/Jerusalem');
		$d = date("j/n/Y H:i:s");
		//$post_id = $this->mbo_new_postat($d);
		$post_id = mbo_new_postat($d);
		//$this->start_end_stats($post_id);
		$fields = get_fields($post_id);
		set_transient( 'mbo_new_postat_daily', $fields, 60*60*24 );

		//error_log('get fileds='. print_r($fields, true));
		return $fields;
	}
	// Endpoint: "yahavStats"
	// Valid requests: [referrals|intakes|families] histogram
	public function mbo_yahavStats( WP_REST_Request $request ) {
		//$body = $request->get_body();
		//write_log( "mbo_yahavStats:" . $body );
		// get TRANSIENT 
		if (false === ($fields = get_transient('mbo_new_postat_daily'))){
			$post_id = $this->get_latest_stats();
			$fields = get_fields($post_id);
		}
		return $fields;
	}

	public function daily_stat(){
		date_default_timezone_set('Asia/Jerusalem');
		$d = date("j/n/Y H:i:s");
		$post_id = $this->mbo_new_postat($d); 
		//if ($post_id > 0)
		//	$this->start_end_stats($post_id);
		return ($post_id > 0) ? "Generated statistics ".$d : "FAILED generating statistics";
	}
	public function delet_daily_stat(){
		date_default_timezone_set('Asia/Jerusalem');
		$d = date("j/n/Y H:i:s");
		$post_id = $this->mbo_new_postat($d); 
		//if ($post_id > 0)
		//	$this->start_end_stats($post_id);
		return ($post_id > 0) ? "Generated statistics ".$d : "FAILED generating statistics";
	}

	////////////////// Snapshots
    public function snapshot($post_id, $cpt_type, $phase) {
		$title   = get_the_title($post_id);
		$srcpost = get_post($post_id);

		//$idObj = get_category_by_slug('snapshot'); 
		//$catId = $idObj ? $idObj->term_id : 0;
		$catId = $cpt_type === "digma_families" ?  mbo_get_cat_by_slug("snapshot")
				: mbo_get_cat_by_slug("psnapshot");
		$arg    = array(
			'post_status'    => 'publish',
			'post_author'    => 1,
			'post_type'      => $cpt_type.$phase,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_title'     => $title,
			'post_category'	 => array($catId), // snapshot
			'post_content'   => "None"
		);
		// $new_post_id = wp_insert_post($arg);
		$new_post_id = wp_insert_post( $arg, true );
		if( is_wp_error( $new_post_id ) ){
		    write_log("Fail create snapshot:". $title ." pase=". $phase . " Error:". $new_post_id->get_error_message());
		    return 0;
		}
		write_log("Snapshot created, newpid=". $new_post_id."  title= ".$title. "  category=". $catId);
		// Copy post metadata
		$data = get_post_custom($post_id);
		foreach ( $data as $key => $values) {
			$pos = strpos($key, 'meeting_summary');
			if ($pos !== false) {
				// error_log("Ignore=". $key);
				continue; // do not add meeting summary to snapshots
			}
			foreach ($values as $value) {
				add_post_meta( $new_post_id, $key, $value );
			}
		}
		date_default_timezone_set('Asia/Jerusalem');
		$d = date("j/n/Y H:i:s");
		// key , row: 
		//update_field(key_, )
		// $row = array('id' => $phase, 'name' => $pahse, 'date'=> $d, 'link' => $new_post_id);
		$ret = update_row( "snapshots", $phase+1,
			array('id' => $phase, 'name' => $phase, 'date'=> $d, 'link' => $new_post_id), $post_id);
		if (!$ret){ // update failed, build it
			$this->buildTable("snapshots",
				array('id' => $phase, 'name' => $phase, 'date'=> $d, 'link' => $new_post_id), $post_id);
		}
		delete_transient('MBO_QB_DBLINKS'); //
		return $new_post_id;
	}

	public function mbo_createSnapshot( WP_REST_Request $request ) {
		$body = $request->get_body();
		write_log( "mbo_createSnapshot:" . $body );
		$json = json_decode( $body, true );
		$post_id = $json['body']['id'];
		$phase = $json['body']['phase']; // phase
		// write_log( "mbo_createSnapshot:PID=" . $post_id . " data type = ". $dataType);

		$sid = $this->snapshot($post_id, 'digma_families', $phase);
		write_log( "got snapshot request for family pid=". $post_id . " phase=" . $phase . " created=". $sid);
		// get the parents & generate theirs too
		// get_table?($post_id, 'family_members')
		$x = get_fields($post_id);
		if (!isset($x["family_members"]))
			write_log("Mbo_create_snapshot Error - no parents found for pid=".$post_id) ; // ERROR no parents; 
		//generate parents snapshots
		$pars = $x["family_members"];
		foreach ($pars as $par) {
		    $pid = $this->snapshot($par["family_member_cptid"], 'digma_parents', $phase);
			write_log("Snapshot created for parent pid=".$par["family_member_cptid"]. "  phase= ".$phase);
                    $snapname = $par["family_member_role"] == "mother" ? "mlink": "plink";
	            $ret = update_row( "snapshots", $phase+1,
				array('id' => $phase, 'name' => $phase, /*'date'=> $d,*/ $snapname => $pid), $post_id);
		}
		return "got snapshot request for pid=". $post_id . " phase=" . $phase. " return ". $sid; // $json['body']['data'];
	}
	// mbo_crudquery
	public function mbo_crudquery( WP_REST_Request $request ) {
		$body = $request->get_body();
		if (MBO_DEBUG_QB)
			write_log( "mbo_crudquery:" . $body );
		$json = json_decode( $body, true );
		$crud = $json['body']['crud'];
		$qid = $json['body']['qid'];
		$qtitle = $json['body']['qtitle'];
		$rules = $json['body']['rules'];
		$cond = $json['body']['cond']; 

		if ($crud == 'CREATE'){
			if (MBO_DEBUG_QB)
				error_log( "mbo_crudquery: crud=". $crud . " qid = ". $qid . "crud = ". $crud . 
								" cond = ". $cond . " rules= ". print_r($rules, true));
			date_default_timezone_set('Asia/Jerusalem');
			$d = date("j/n/Y H:i:s");
			$title = $qtitle != "" ? $qtitle : 'digmatec' . $d;
			$query_cpt = array(
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'digma_queries',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_title'     => $title,
				'post_category'	 => array(mbo_get_cat_by_slug("query")), // 25 parents
				'post_content'   => $body // 'gcond=' . $cond . ";" . 'rules=' . json_encode($rules)
			); // maybe use , JSON_FORCE_OBJECT
			$query_cpt = wp_insert_post( $query_cpt, true );
			if( is_wp_error( $query_cpt ) )
			    return "Fail create query:". $title ." new query cpt reason:". $query_cpt->get_error_message();

			return $query_cpt; // ok as query post id
		}
		if ($crud == 'GET'){
			if (MBO_DEBUG_QB)
				error_log( "mbo_crudquery: crud=". $crud . " qid = ". $qid );
			$pCpt = get_posts( array('post_type' => 'digma_queries', 'posts_per_page' => -1, 'status' => 'publish') );
		    if (count($pCpt) < 1)
		        return -1;
		    $res = array();
		    foreach ($pCpt as $p){
		    	$res[$p->ID] = $p->post_content;
		    }
		    return $res;
		}
		if ($crud == 'DELETE'){
			$reply = wp_trash_post( $qid ); // move to trash
			if ($reply === false){
				error_log("crud DELETE Fail delete reply id:". $qid ." reason:". $reply->get_error_message());
				return 0;
			}
			//if (MBO_DEBUG_QB)
				error_log( "mbo_crudquery: DELETED, crud=". $crud . " qid = ". $qid );
		    return $qid;
		}
		return 0; // nothing to write home about
	}

	// mbo_processquery
	public function mbo_processquery( WP_REST_Request $request ) {
		$body = $request->get_body();
		write_log( "mbo_processquery:" . $body );
		$json = json_decode( $body, true );
		$rules = $json['body']['rules'];
		$cond = $json['body']['cond']; 
		//error_log( "mbo_processquery:rules=" . print_r($rules, true) . " cond = ". $cond);
		if (MBO_DEBUG_QB)
			error_log("Query: cond = ". $cond. " query=  " . print_r($json, true));
		if ($rules == "init-tabs")
			return $this->qb_request_init();

		$l = $this -> dbLinks(); // get all links for query processing
		$dbLinks = $l[0];
		$dbLinksInv = $l[1];

		// parser
		if ($cond == ""){
			$res = $this->qb_singleq($rules, 0);
			// final processing
			/*foreach ($res as $r){
				error_log("link =". $r[0]. " title= ". get_the_title($r[0]));
				error_log(print_r($r, true));
				$r[0] = get_the_title($r[0]);// replace post id with title
			}*/
			if (!empty($res)){
				for ($i = 1 ; $i < count($res); $i++ )
					$res[$i][0] = get_the_title($res[$i][0]);// replace post id with title on each row
			}
			return $res;
		}

		if ($cond == 'AND'){
			$i = 0;
			$res = array();
			$prev_res = array();
			foreach ($rules as $rule){
				// 1. calc rule
				// 2. merge result
				//error_log("AND RULE = " . $i . ' rules is '. print_r($rule, true));
				$res[] = $this->qb_singleq($rules, $i++, $prev_res);
				$prev_res = array(); // must reinitialize to remove all previous results
				if ($i < count($rules)){ // if we have more rules... else - we are done
					// Generate prev links array
					foreach ($res[$i-1] as $r){ // assuming same entity - reduce list size
						if (!is_numeric($r[0])) continue; // skip the header
						// get the main entry of prev result to dbLinks()
						$mainFlink = $dbLinksInv[$r[0]]; 
						// parse next rule
						$entity = $rules[$i]['entity'] == 'משפחה' ? 0 : 1; // note if parent get up to 2 links!!
						/*$phase = $this->get_newphase($rule[$i]['phase'], $i); // just index to dbLinks
						if ($phase < 0){
							error_log('AND loop skiiping link in rule#='.$i. ' fail to get newphase link');
							continue;
						}*/
					
						$phase = $this->qb_get_phase($rules[$i]['phase']);
						if ($phase == "now") $newphase = 0;
						else if ($phase == "phase0") $newphase = 1;
						else if ($phase == "phase1") $newphase = 2;
						else if ($phase == "phase2") $newphase = 3;
						else error_log('UNEXPECTED phase='. $phase. ' in rule i='.$i .'  rulei ='. print_r($rule[$i], true));
						// add links to array only if not zero (i.e. missing...)
						if ($entity == 0){
							//if ($i == 2){
							//	error_log('count-res='.count($res[$i-1]).' from parent='.get_the_title($r[0]). ' parentid='.$r[0] .' to family id='. 
							//		$mainFlink . ' name='. get_the_title($mainFlink));
							//}
							if ($dbLinks[$mainFlink][$newphase][0]) 
								$prev_res[] = $dbLinks[$mainFlink][$newphase][0];
						} else { // add 2 parents if exists
							if (MBO_DEBUG_QB)
								error_log("AND parents locator: mainFlink=". $mainFlink . "  newphase=". $newphase . 
									" RRRR=". print_r($r, true));
							if ($i == 0 || $rules[$i-1]['entity'] == 'משפחה' ){
								if ($dbLinks[$mainFlink][$newphase][1]) 
									$prev_res[] = $dbLinks[$mainFlink][$newphase][1];
								if ($dbLinks[$mainFlink][$newphase][2]) 
									$prev_res[] = $dbLinks[$mainFlink][$newphase][2];
							} else { // previous query was parent so take this one only
								// must know if its man or woman
								$ind = $this->is_woman($r[0]) ? 1 : 2;
								if ($dbLinks[$mainFlink][$newphase][$ind]) 
									$prev_res[] = $dbLinks[$mainFlink][$newphase][$ind];
							}
						}
					}
					$prev_res = array_unique($prev_res);
					//error_log('CURRENT count(res[])='.count($res).' $i='.$i.' generated PREV res unique count='.count($prev_res).' entity='.$entity . ' phase= '. $phase);
				}
			}
			if (MBO_DEBUG_QB){
				$j = 0;
				foreach ($res as $r)
					error_log("AND rule #". $j++ . " result is =" . print_r($r, true));
			}
			// show only last table of AND sequence, other value are known 
			// NIH / TODO - allow add more columns
	
			//return $final_res;
			//return $res[0];
			if (!empty($res[$i-1])){
				$r = $res[$i-1];
				for ($i = 1 ; $i < count($r); $i++ )
					$r[$i][0] = get_the_title($r[$i][0]);// replace post id with title
			} else return array();
			//error_log('Test Header = '. print_r($this->qb_current_header($r[0]), true)); // genhedaer
			//error_log('Test Header = '. print_r($r[0], true)); // genhedaer
			return $r;
		}

		return ("mbo_processquery request not supported. rules=" . json_encode($rules) . " cond = ". $cond);
	}
	// convert to dbLinks indices
	private function get_newphase($c_phase, $i){
		$phase = $this->qb_get_phase($c_phase);
		$newphase = -1;
		if ($phase == "now") $newphase = 0;
		else if ($phase == "phase0") $newphase = 1;
		else if ($phase == "phase1") $newphase = 2;
		else if ($phase == "phase2") $newphase = 3;
		else {
			error_log('UNEXPECTED phase='. $phase. ' in rule i='. $i .'  c_phase ='. $c_phase);
			return -1;
		}
		return $newphase;
	}
	private function is_woman ($pid){
		$info = get_field('parent_info', $pid);
		if ($info === false){
			error_log('is-woman fail on pid='. $pid);
			return false;
		}
		if (MBO_DEBUG_QB)
			error_log('is-woman =' . ($info["parent_gender"] == "נקבה"? 'woman': 'man') . 
				' on pid='. $pid . ' info= ' . print_r($info, true));
		return ($info["parent_gender"] == "נקבה");
	}
	// query "calculator" - executor
	private function qb_singleq($rules, $index = 0, $prev_res = null){
		$cr = $rules[$index]; // Current Rule = $cr
		$entity= $cr['entity'] == 'משפחה' ? 'digma_families' : 'digma_parents';
		$phase = $this->qb_get_phase($cr['phase']); // get post links from snap table name
		$ptype = get_field_object($cr['tabKey']);
		if (MBO_DEBUG_QB)
			error_log('qb_singleq = entity='. $entity . '  phase='. $phase . 'tabname= ' .
							$cr['tabName']. ' type='. $ptype['type']. ' ptype='. print_r($ptype,true));

		// Generate $plist, handle also AND, OR
		if ($index == 0 ){ // first rule in clause, no prev_list yet
			$l = $this->qb_post_phase_new($entity, $phase); // get 2 arrays of links at required phase
			$plist = $l[0];
			$dbLinksInv = $l[1];
		} else { // use prev_list
			$l = $this->qb_post_phase_new($entity, $phase); // get 2 arrays of links at required phase
			if ($prev_res !== null){
				if (MBO_DEBUG_QB)
					error_log('qb_singleq using prev_res='. print_r($prev_res, true));
				$plist = $prev_res;
			} else $plist = $l[0];
			$dbLinksInv = $l[1];
			//error_log('qb_singleq index > 0 not supported yet, prev_res='. print_r($prev_res, true));
			//return array();
		}

		// handle only forms or tables - no other types
		if ($ptype['type'] != 'repeater' && $ptype['type'] != 'group'){
			error_log("qb_singleq SKIPPING UNEXPECTED TYPE = ". $ptype['type'] . "  IN: " . print_r($ptype, true));
			return null;
		}
		// get field type from $ptype
		$ftype = "";
		foreach ($ptype['sub_fields'] as $sf){
			if ($sf['name'] == $cr['fieldName']){
				$ftype = $sf['type'] ; // to be used in all conditiond qb_eq calculations
				break;
			}
		}
		if (MBO_DEBUG_QB) error_log('qb_singleq field TYPE = '. print_r($ftype, true));
		if ($ftype == "")
			$ftype = 'text'; // default
		// ready to search for elements
		$res= array();
		$j = 0;
		$genHeader = 0;
		foreach ($plist as $p){
			//error_log('qb_singleq LOOP= plist/p tabkey='. $rules[$index]['tabKey']);
			$table = get_field($cr['tabName'], $p); // get table from post

			$familyRes = array();
			if (!$table) {
				error_log('table return false table name ='. $cr['tabName'] . '  keyfield='. $cr['tabKey']. '  pid='. get_the_title($p));
				$tmtp = get_field($cr['tabKey'], $p);
				if ($tmtp) 
					error_log('testing key SUCCESS return='. print_r($tmtp, true));
				else error_log('testing key also fail');
				//error_log('all tables' . print_r($t, true));
				continue;	// skip the family data
			} 
			if ($ptype['type'] == 'group'){ // test only one field - otherwise its a table: test all rows
				$group = $table; // its a group...
				if ($genHeader == 0) $genHeader = $this->qb_current_header($group); 

	//error_log('before qb_eq cond='.$cr['condition']. "  current value= ". $group[$cr['fieldName']] . " input= ". $cr['input']);
	//error_log('before qb_eq input group='. print_r($group, true));
				if ($this->qb_eq($cr['condition'], $group[$cr['fieldName']], $cr['input'], $ftype)){
					//$familyRes[$p] = $this->qb_copy_line($p, $group) ; // $group[$cr['fieldName']]; // the actual value
					$res[$j++] = $this->qb_copy_line($p, $group) ; // $group[$cr['fieldName']]; // the actual value
					if (MBO_DEBUG_QB) error_log('after qb_eq result='. print_r($res[$j-1], true));
				} // return false - not in results
				else {
					if (MBO_DEBUG_QB && $index > 0){ // manual test why condition failed
						error_log('qb_eq RETURN FALSE for group='. print_r($group, true));
					}
				}
				continue;
			} else { // handle table: $ptype['type'] == 'repeater'
				if (MBO_DEBUG_QB) error_log('qb_singleq ='. print_r($table, true));
				$k = 0;
				foreach ($table as $line){
					if ($genHeader == 0) $genHeader = $this->qb_current_header($line); 
					
					// $res[] = family info + result
// >> In this loop, is the place for aggregation results!! You may need to replace the loop
					if ($this->qb_eq($cr['condition'], $line[$cr['fieldName']], $cr['input'], $ftype)){
						//$r = $line; unset($r['id']); // $r
						$familyRes[$k++] = $this->qb_copy_line($p, $line); // $p true for families only SHOULD BE KEY
					}
				}
			}
			// do test on all table rows
			if (isset($familyRes) && count($familyRes) > 0){ // collect results per family if result exists
				for ($i = 0; $i < count($familyRes); $i++)
					$res[$j++] = $familyRes[$i]; 
			} 
		}
		//error_log("qb_singleq HEADER = ". print_r($genHeader, true));
		//error_log('qb_singleq RESULTS='. print_r($familyRes, true));
		$final = array();
		$final[0] = $genHeader;
		for ($i =0; $i < count($res) ; $i++)
			$final[$i+1] = $res[$i]; 

		if (MBO_DEBUG_QB){
			// the query
			error_log('qb_singleq QUERY = entity='. $entity . '  phase='. $phase . ' tabname= ' .
								$cr['tabName']. ' type='. $ptype['type'] /*. ' ptype='. print_r($ptype,true)*/);
			// result
			error_log("qb_singleq HEADER = ". print_r($genHeader, true));
			error_log('qb_singleq RESULTS='. print_r($res, true));
		}
		return $final;
	}
	private function qb_current_header($line){
					 // initializr table header
		$t = array();
		$t[0] = 'family Key';
		foreach ($line as $k => $v )
			if ($k != 'id') $t[] = $k;
		return $t;
	}
	// generate result line
	private function qb_copy_line($entityLink, $line){
		$arr = array();
		$arr[0] = $entityLink;
		foreach ($line as $k => $v){
			if ($k == 'id') continue;
			$arr[] = $v;
		}
		return $arr;
	}
	private function qb_eq($eq, $fieldVal, $inputVal, $type = 'text'){
		$dict = array('שווה' => 'EQ', 'שונה' => 'NEQ', 'גדול מ' => 'GT', 'קטן מ' => 'LT', 'גדול או שווה' => 'GTEQ',
					'קטן או שווה' => 'LTEQ', 'מכיל' => 'CONTAIN', 'לא מכיל' => 'NOTCONTAIN', 
					'בטווח' => 'INRANGE', 'מחוץ לטווח' => 'NOTINRANGE');
		if ($type == 'number'){
			$fV = intval($fieldVal); 
			$iV = intval($inputVal);
		} else { // default is text if ($type == 'text' || $type == 'textarea' || $type == 'date'){
			$fV = $fieldVal; 
			$iV = $inputVal;
		}
		if ($dict[$eq] == 'INRANGE' || $dict[$eq] == 'NOTINRANGE'){
			if ($fV == "") return false;
			$fV = preg_replace("/[^0-9]/", "", $fV );
			// error_log('qb_eq cond='. $dict[$eq] . ' fV= '. $fV . '  iV = '. $iV);
		}

		switch ($dict[$eq]){
			case 'EQ'	: return $fV == $iV;
			case 'NEQ'	: return $fV != $iV;
			case 'GT'	: return $fV > $iV;
			case 'LT'	: return $fV < $iV;
			case 'GTEQ' : return $fV >= $iV;
			case 'LTEQ' : return $fV <= $iV;
			case 'CONTAIN' : return (strpos($fV, $iV) !== false);
			case 'NOTCONTAIN' : return (strpos($fV, $iV) === false);
			case 'INRANGE' : 	return $this->qb_inrange($iV, $fV);
			case 'NOTINRANGE' : return !($this->qb_inrange($iV, $fV));
			default: error_log('qb_eq uexpected cond = '. $eq . ' list is '. print_r($dict, true));
		}
		return false; 
	}
	// integer values only, range inclusive, order not importnat
	private function qb_inrange($range, $value){
		$vals = explode('-', $range);
		// error_log('qb_inrange range='.$range . ' value= ' . $value .'==vals='. print_r($vals,true));
		if ($vals == false || count($vals) != 2) return false; // missing argument(s)
		if (intval($vals[0]) > intval($vals[1])){
			$t = $vals[0];
			$vals[0] = $vals[1];
			$vals[1] = $t;
		}
		// remove non numeric from input
		$field_value = $value; //preg_replace("/[^0-9.]/", "", $value );
		// error_log('qb_inrange input='. intval($field_value). '  min = '. intval($vals[0]). '  max = '. intval($vals[1]));
		return (intval($field_value) >= intval($vals[0]) && intval($field_value) <= intval($vals[1]));
	}
	private function qb_get_phase($inphase){
		$dict = array('תחילת תוכנית' => 'phase0', 'אמצע תוכנית' => 'phase1', 'סיום תוכנית' => 'phase2');
		if (isset($dict[$inphase]))
			return ($dict[$inphase]);
		return 'now';
	}
	// return array of links in appropriate type (family|parent) & phase#
	private function qb_post_phase_new($type, $phase){
		// get links ... indices
		$r = $this->dbLinks();
		$dbLinks = $r[0];
		$dbLinksInv = $r[1];
		// filter requires links to simple array
		$links = $this->genLinkList($dbLinks, $type, $phase);
		return array($links, $dbLinksInv) ;
	}
	// $phase: now - 0, start - 1, middle - 2, end - 3
	// $type: family | parent
	private function genLinkList($dbLinks, $type, $iphase){
		$phase = 0;
		switch($iphase){ // phase map to dbLinks ()
			case 'phase0' : $phase = 1; break;
			case 'phase1' : $phase = 2; break;
			case 'phase2' : $phase = 3; break;
			default: $phase = 0;
		}
		$links = array();
		$k = 0;
		foreach ($dbLinks as $quad){
			if ($type == 'digma_families'){
				if ($quad[$phase][0])
					$links[] = $quad[$phase][0];
			}
			else{
				//error_log('genLinkList line='. $k++ . ' quad = '. print_r($quad, true));
				if ($quad[$phase][1])
					$links[] = $quad[$phase][1];
				if ($quad[$phase][2])
					$links[] = $quad[$phase][2];				
			}
		}
		return $links;
	}
	// return array of links in appropriate type (family|parent) & phase#
	private function qb_post_phase($type, $phase){
		$pCpt = get_posts( array('post_type' => $type, 'posts_per_page' => -1, 'status' => 'publish') );
	    if (count($pCpt) < 1)
	        return -1;
	    $res = array();
	    switch ($phase){
	    	case 'now': foreach ($pCpt as $cpt)
	    					$res[$cpt->ID] = $cpt->ID;
	    				return $res;
	    	case 'phase0': return $this->get_phasei($pCpt, 0, 'family');
	    	case 'phase1': return $this->get_phasei($pCpt, 1, 'family');
	    	case 'phase2': return $this->get_phasei($pCpt, 2, 'family');
	    	default: error_log('qb_post_phase switch phase error=', $phase);
	    }
	    return false;
	}
	// return array of links to required posts 
	private function get_phasei($pCpt, $i, $type){
		$res = array();
		foreach ( $pCpt as $cpt ){
    		$snaptable = get_field('snapshots', $cpt->ID);
    		if ($snaptable == false || count($snaptable) < 1)
        		continue;
        	if ($type == 'family'){
	        	if (!isset($snaptable[$i]['link']) || $snaptable[$i]['link'] == "")
	        		continue;
	        	$res[$cpt->ID] = $snaptable[$i]['link'];
	        } else if ($type == 'parent'){
	        	if (isset($snaptable[$i]['mlink']) && $snaptable[$i]['mlink'] != "")
	        		$res[$snaptable[$i]['mlink']] = $snaptable[$i]['mlink'];
	        	if (isset($snaptable[$i]['plink']) && $snaptable[$i]['plink'] != "")
	        		$res[$snaptable[$i]['plink']] = $snaptable[$i]['plink'];
	        }
        }
        return $res;
	}
	// initialize dbLinks and dbLinksInv and generates transient!
	public function dbLinks(){
		$res = get_transient( 'MBO_QB_DBLINKS' );
		if ( false !== $res ) {
			$resinv = get_transient( 'MBO_QB_DBLINKSINV' );
			if ( false !== $resinv ){
				if (MBO_DEBUG_QB)
					error_log("Using MBO_QB_DBLINKS & MBO_QB_DBLINKSINV transients");
	     		return array($res, $resinv);
	     	} // else must recalculate
		}// else must recalculate
		
		$pCpt = get_posts( array('post_type' => 'digma_families', 'posts_per_page' => -1, 'status' => 'publish') );
	    if (count($pCpt) < 1)
	        return -1;
	    $res = array();
	    foreach ( $pCpt as $cpt ){
	    	// generate main links - "now" value
    		$fmembers = get_field('family_members', $cpt->ID);
    		if ($fmembers == false || count($fmembers) < 1){
    			error_log('ERROR: dbLinks found family w/no memebrs, cpt id='. $cpt->ID);
        		continue;
    		}
    		$now = array(0,0,0); // to initialize missing value in single parent families
    		$now[0] = $cpt->ID;
			foreach ($fmembers as $par) {
				if ($par["family_member_role"] == "mother")
					$now[1] = $par["family_member_cptid"];
				else $now[2] = $par["family_member_cptid"];
			}
    		$res[$cpt->ID][0] = $now;
    		// initilaize snapshots
			$res[$cpt->ID][1] = array(0,0,0);
    		$res[$cpt->ID][2] = array(0,0,0);
    		$res[$cpt->ID][3] = array(0,0,0);
    		// handle snapshots
    		$snaptable = get_field('snapshots', $cpt->ID);
    		if ($snaptable == false || count($snaptable) < 1) // no snapshots yet...
        		continue;
        	$cnt_snap = count($snaptable);
        	for ($i = 0; $i < $cnt_snap; $i++){ // Only 3 snapshots - Hardcoded!
	    		$res[$cpt->ID][$i+1] = array($this->_testSet($snaptable[$i]['link']), // family link
	    									 $this->_testSet($snaptable[$i]['mlink']), // woman mom-mlink
	    									 $this->_testSet($snaptable[$i]['plink'])); // man papa-plink
	    		
	    	}
        }
        //error_log('dbLinks total='. count($res). " table = ". print_r($res, true));
        $dbLinks = $res;
        $dbLinksInv = array();
        foreach ($dbLinks as $dbl){ // scan all main entries
        	foreach ($dbl as $db) // scan all phases
        		foreach ($db as $d) // scan all  entities
        			if ($d != 0)
        				$dbLinksInv[$d] = $dbl[0][0];
        }
        //error_log('dbLinksInv total='. count($dbLinksInv). " table = " . print_r($dbLinksInv, true));
        set_transient( 'MBO_QB_DBLINKS', $dbLinks, 12 * HOUR_IN_SECONDS );
		set_transient( 'MBO_QB_DBLINKSINV', $dbLinksInv, 12 * HOUR_IN_SECONDS );

        return array($dbLinks, $dbLinksInv);
	}
	private function _testSet($entry){
		if (!isset($entry) || empty($entry))
			return 0;
		return $entry;
	}

// TODO TRANSIENT -  1. delete transient whenevr ACF fields definition changes
// always rely on parent blog_id ===1 for transient - need singleton for all child sites

	private function qb_request_init(){
        if (is_multisite()){
			//$blog_id = get_current_blog_id();
			if (get_current_blog_id() > 1){
				switch_to_blog( 1 );
				$res = get_transient( 'MBO_QB_INIT' );
				restore_current_blog();
				return $res;
			}
		}
		// delete transient whenevr ACF fields definition changes
		$res = get_transient( 'MBO_QB_INIT' );
		if ( false !== $res ) {
			//if (MBO_DEBUG) 
				error_log("Using MBO_QB_INIT transient");
     		return $res;
		}

		$afg = acf_get_field_groups();
		// error_log("Query = ". print_r($afg, true));		
		$fg_arr = array();
		$xxx = 0;
		foreach ($afg as $a){
			//if ($xxx++ > 10) break;
			if (!isset($a['location'][0])){
				//$fg_arr[$a['ID']] = array('key' => $a['key'], 'title' => $a['title'].'-skipped', 'locations' => 'none');
				error_log("AFG skipped-none=". print_r($a, true));
				continue;
			}
			if ($a['location'][0][0]['value'] == 'post'){
				//$fg_arr[$a['ID']] = 
				//	array('key' => $a['key'], 'title' => $a['title'].'-skipped', 'locations' => 'post', 'fields' => null);
				if (MBO_DEBUG) error_log("AFG skipped-post=". print_r($a, true));
				continue;
			} // skip it
			//error_log("AFG no-skipped=". print_r($a, true));
			//error_log("AFG no-skipped fields=". print_r(acf_get_fields($a['key']), true));
			$loc =  array();
			foreach($a['location'] as $al)
				$loc[] = $al[0]['value'];
			$allfields = acf_get_fields($a['key']);
			$fields = array();
			foreach ($allfields as $af){
				if ( $af['type'] == 'group' || $af['type'] == 'repeater'){
					$subfields = array();
					for ($i=0 ; $i < count($af['sub_fields']); $i++){
						$afs = $af['sub_fields'][$i];
						$subfields[$i] = array('ID'=>$afs['ID'], 'key'=>$afs['key'], 'label'=>$afs['label'], 
							'name'=>$afs['name'], 'type'=>$afs['type'], 'menu_order'=>$afs['menu_order']);
					}

					$fields[] = array('ID'=>$af['ID'], 'key'=>$af['key'], 'label'=>$af['label'], 'name'=>$af['name'],
								'type'=>$af['type'], 'menu_order'=>$af['menu_order'], 'sub_fields' => $subfields);
					//$fields['sub_fields'] = $subfields;
				} else {
					$fields[] = array('ID'=>$af['ID'], 'key'=>$af['key'], 'label'=>$af['label'], 'name'=>$af['name'],
								'type'=>$af['type'], 'menu_order'=>$af['menu_order']/*, 'sub_fields' => 'simple: not expected'*/);
					if ($af['parent'] != 1091 && $af['parent'] != 8467 && $af['parent'] != 6166){ 
						// 1091 old referral definition ==> MUST CHANGE TO group
						// 8467 - daily stat time stamp single immidiate child
						// 6166 - combo - dont understand it...
						error_log('notexpected type='. $af['type'] . '  table =  '. print_r($af, true));
					}
				}
			}
			$fg_arr[$a['ID']] = array('key' => $a['key'], 'title' => $a['title'], 'locations' => $loc, 'fields' => $fields);
		}
		/*
		foreach ($fg_arr as $f){
			error_log("Query = ". print_r($f, true));
			if (isset($f['sub_fields']))	
				error_log("Query = ". $f['label'] . " arr= " . print_r($f['sub_fields'], true));
		}
		*/
		set_transient( 'MBO_QB_INIT', $fg_arr, 24 * HOUR_IN_SECONDS );

		return $fg_arr; // $json['body']['data'];
	}
	/*
	private function process_rule($rule){
		$initCpt = get_posts( array('post_type' => 'digma_families', 'posts_per_page' => -1, 'status' => 'publish') );
		if (count($initCpt) < 1)
			return -1;
		//$ddata = gen_ddata_array(); // only with all snapshots && bogrot...
		// PLAN
		// do first
		// If count(rules) == 1 - just do and return else [and|or] minimize || accumulate
		//if (count($rule['rules']) == 1)
		$snaptable = array();
		foreach ($initCpt as $cpt)
			$snaptable[$cpt->ID] = get_field('snapshots', $cpt->ID);

		$qfamily = $rule['entity'] == 'משפחה' ? true : false; // family - true parent - false
		$phase = $this->get_phase($rule['phase']); // 0, 1, 2 or now
		foreach ($snaptable as $snap){
			// now map/use tabs to get the sub tables
			// and another map to get to fiedls
		}
				getAllSnashots 
			else look at current list// look at snapshot array & pick parent|family in proper phase

			then - look for consition
		}
	}
	private function get_phase($p){
		$phase = array('תחילת תכנית' => 0 ,'אמצע תכנית' => 1 ,'סיום תוכנית' => 2 ,'עכשיו' => 3 );
		return ($pahse[$p]);
	}
	*/
}

