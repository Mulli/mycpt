<?php
add_shortcode('mbo-yahavStatsNow', 'mbo_deletStatsNow');
add_shortcode('delet-stats-daily', 'delet_stats_daily');

function delet_stats_daily(){
	$time0 = time();

    $referralsCpt = get_posts( array('post_type' => 'digma_WorkplansDefs', 'posts_per_page' => -1, 'status' => 'publish') );
    if (count($referralsCpt) < 1)
		return "Got delet_stats_daily request OK, but no post of type= digma_workplans found";

	$out =  "סה''כ ". count($referralsCpt) . " מענים <br />"; 
	date_default_timezone_set("Asia/Jerusalem");
	$out .=  "תאריך ". date("d/m/Y, H:m:s") . "<br />"; 
	//$out =  "Total ". count($referralsCpt) . ' time = '. $time0 . "<br />";
	$wplans = array();
    $total = 0;
	// get all data to memory
    foreach ( $referralsCpt as $cpt ){
		$wplans[$cpt->ID] = get_field("workplans_plan_3", $cpt->ID);
		$total += 1;
    }
	$time1 = time();
	//$out .=  "got all fields time=". ($time1-$time0) . " sec <br />";

	$histres = gen_all_hists($wplans); // all genertaed histograms
	
	$time2 = time();
	$out .=  "זמן יצירת הדוח:  time=". ($time2-$time0) . " שניות <br />";
	return $out;
}

function gen_all_hists($wplans){
	$histres = array(); // all genertaed histograms
	$histconf = array( // group, field, title
					array("fg_wp_components", "wp_solution_type", "סוגי המענים"),
					array("fg_desc", "wp_status_new", "סטטוס המענים"),
					array("fg_desc", "wp_operation_status", "סטטוס הפעלה"),
					array("fg_desc", "wp_deliver", "מתכונת העברה"),
					array("fg_desc", "wp_geo_area_new", "מיקום פיזי"),
					array("fg_desc", "wp_online_new2", "מענה מקוון"),
					array("fg_intro", "wp_n_report_year", "שנת הדיווח"),
					array("fg_target_audience", "wp_targetaud", "למי מיועד המענה"),
					array("fg_intro", "wp_operator", "המפעיל"),
					array("fg_target_audience", "wp_gender_new", "מגדר"),
					array("fg_target_audience", "wp_social_section_new", "מגזר"),
				);
	// generate all histograms
	foreach ($histconf as $histConfig) {  	
    	$histres[$histConfig[2]] = get_hist($wplans, $histConfig);
		error_log('Histogram '. $histConfig[2] . ' table='. print_r($histres[$histConfig[2]], true));
	}
	return $histres;
}
// get histogram
// $wplan - ALL values in memory
function get_hist($wplans, $histConfig){
	$group = $histConfig[0];
	$fname = $histConfig[1];
	$histogram = array();
	$i=0;
	foreach ( $wplans as $wplan ){ // over all plans
		$v= isset($wplan[$group][$fname]) ? $wplan[$group][$fname] : 'לא מוגדר'; // the status as index
		if (is_array($v)){
			if (count($v) == 0) continue;
			foreach ($v as $vi){
				if ($vi == null || $vi == "" || $vi == false)
					$vi = 'לא מעודכן';
				$histogram[$vi] = isset($histogram[$vi]) ? $histogram[$vi] + 1 : 1;
			}
			continue;
		}
		if ($v == null || $v == "" || $v == false)
			$v = 'לא מעודכן';
		$histogram[$v] = isset($histogram[$v]) ? $histogram[$v] + 1 : 1;
	}
	arsort($histogram, SORT_NUMERIC); //, SORT_NUMERIC
	return $histogram;
}

function mbo_deletStatsNow( /*WP_REST_Request $request*/ ) {
	//error_log("Got mbo_deletStatsNow request");
	date_default_timezone_set('Asia/Jerusalem');
	$d = date("j/n/Y H:i:s");
	$post_id = /*$this->*/mbo_new_postat($d);
	//$this->start_end_stats($post_id);
	$fields = get_fields($post_id);

	error_log('get fileds='. print_r($fields, true));
	return print_r($fields, true);
}
//update_stat_table($referrals_key, $ref_id_key, $ref_title_key, $ref_total_key, $r['table'], $stat_cpt);

function update_stat_table($table_key, $id_key, $title_key, $total_key, $data_table, $post_id){
	// Update referrals
	$n = 1;
	foreach ($data_table as $k => $v){
		// error_log("K =". $k . "  V=" . $v);
		add_row( $table_key, array($id_key=> $n++, $title_key=> $k, $total_key => $v), $post_id );
	}
}
function mbo_new_postat($title){ // add title / file name as parameter
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

	
	
	// generate all histograms
	$referralsCpt = get_posts( array('post_type' => 'digma_WorkplansDefs', 'posts_per_page' => -1, 'status' => 'publish') );
    if (count($referralsCpt) < 1)
		return "Got delet_stats_daily request OK, but no post of type= digma_workplans found";
	$wplans = array();
	$total = 0;
	// get all data to memory
	foreach ( $referralsCpt as $cpt ){
		$wplans[$cpt->ID] = get_field("workplans_plan_3", $cpt->ID);
		$total += 1;
	}
	// $time1 = time();
	//$out .=  "got all fields time=". ($time1-$time0) . " sec <br />";

	$histres = gen_all_hists($wplans); // all genertaed histograms
// error_log("============================". print_r($histres, true));
	
	update_dtable('field_6020759fbbfbd', $histres['סוגי המענים'], $stat_cpt);
	update_dtable('field_602075babbfc1', $histres['סטטוס המענים'], $stat_cpt);
	update_dtable('field_602075cebbfc5', $histres['סטטוס הפעלה'], $stat_cpt);
	update_dtable('field_602075f2bbfc9', $histres['מתכונת העברה'], $stat_cpt);
	update_dtable('field_60207617bbfcd', $histres['מיקום פיזי'], $stat_cpt);
	update_dtable('field_60207627bbfd1', $histres['מענה מקוון'], $stat_cpt);
	update_dtable('field_6020763ebbfd5', $histres['שנת הדיווח'], $stat_cpt);
	update_dtable('field_60207659bbfd9', $histres['למי מיועד המענה'], $stat_cpt);
	update_dtable('field_60207671bbfdd', $histres['המפעיל'], $stat_cpt);
	update_dtable('field_60207684bbfe1', $histres['מגדר'], $stat_cpt);
	update_dtable('field_60207692bbfe5', $histres['מגזר'], $stat_cpt);

	$time_key = 'field_5c750fc7fa99e'; // update_time = 'field_5c750fc7fa99e'; // text as date
	update_field($time_key, $title, $stat_cpt);
	update_field('field_60209a2a1a321', $total, $stat_cpt); // total number of plans

	return $stat_cpt;
}
// update_dtable('field_6020759fbbfbd', $histres['סוגי המענים'], $stat_cpt)
function update_dtable($tablekey, $table, $post_id){
	$i = 1;
	foreach ($table as $k => $v){
		$row = array(
			'id' => $i++,
			'title'   => $k,
			'total'  => $v
		);
		//add_row('wp_solution_type', $row, $stat_cpt); 
		add_row($tablekey, $row, $post_id); 
	}
}
/*
array("fg_wp_components", "wp_solution_type", "סוגי המענים"),
					array("fg_desc", "wp_status_new", "סטטוס המענים"),
					array("fg_desc", "wp_operation_status", "סטטוס הפעלה"),
					array("fg_desc", "wp_deliver", "מתכונת העברה"),
					array("fg_desc", "wp_geo_area_new", "מיקום פיזי"),
					array("fg_desc", "wp_online_new2", "מענה מקוון"),
					array("fg_intro", "wp_n_report_year", "שנת הדיווח"),
					array("fg_target_audience", "wp_targetaud", "למי מיועד המענה"),
					array("fg_intro", "wp_operator", "המפעיל"),
					array("fg_target_audience", "wp_gender_new", "מגדר"),
					array("fg_target_audience", "wp_social_section_new", "מגזר"),
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
	*/
	

if ( ! function_exists('mbo_get_cat_by_slug')) {

	function mbo_get_cat_by_slug($slug){
		$idObj = get_category_by_slug($slug); 
		return $idObj ? $idObj->term_id : 1; // 1 is general
	}
}
/*
$str = 'סה"כ תוכניות במערכת = '. $total ."<br />";
    $final = "";
	foreach ($fields as $field => $value){
	    $s = "<h3>". $value . "</h3>";
	    $l = "";
	    foreach ($sumhist[$field] as $k => $v){
	        $l .= $k . " => " . $v . "<br />";
	    }
	    $final .= $s . $l;
	}
    //$str .= "Histograms are: " . print_r($sumhist, true);
    return $str . $final;
//////	///////// Yahav statistics
	// mbo_histogram returns table of referrals|intakes|families histogram by status
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
				$v= $x["program_info"]["program_status"]; // the status as index* /
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
		$post_id = $this->mbo_new_postat($d);
		//$this->start_end_stats($post_id);
		$fields = get_fields($post_id);

		error_log('get fileds='. print_r($fields, true));
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
	*/