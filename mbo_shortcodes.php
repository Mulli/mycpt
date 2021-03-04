<?php
include_once "shortcodes/delet2xl.php";
include_once "shortcodes/startapp.php"; // iframe for latest index.html
include_once "shortcodes/intro.php";
include_once "shortcodes/xl-intakes2.php"; // good
include_once "shortcodes/xl-intakes.php"; // bad
include_once "shortcodes/xl-referrals.php";
include_once "shortcodes/xl-program.php";
include_once "shortcodes/report-helpers.php";
include_once "shortcodes/report-referrals.php";
include_once "shortcodes/report-intakes.php";
include_once "shortcodes/report-program.php";
include_once "shortcodes/report-parents.php";
include_once "shortcodes/report-sroi.php";
include_once "shortcodes/report-bros.php";
include_once "shortcodes/report-bros2.php";
include_once "shortcodes/report-bros3.php";
include_once "shortcodes/yahav-update.php";
include_once "shortcodes/mbo-stats-daily.php";
include_once "shortcodes/get_csv.php";
include_once "shortcodes/parentqsc.php";
include_once "shortcodes/mbo-search.php";
//include_once "shortcodes/mbo_export_csv.php";
include_once "shortcodes/mbo_export_csv_new.php";
include_once "shortcodes/acf-cleanup.php";



/*  use acf get_fields loop

		$fields = get_fields($cpt->ID);

		if( $fields ){
			$str .= '<ul>';
			foreach( $fields as $name"  => $value ){
				if ( is_array( $name ) || is_object( $name ) ) continue;
				if ( is_array( $value ) || is_object( $value ) )
					$str .= '<li><b>'. $name . '</b>'. $value[0] .'</li>';
				else
					$str .= '<li><b>'. $name . '</b>'. $value .'</li>';
			}
			$str .= '</ul>';
		}
		$str .= "<br />";
continue;
*/
// mycpy/Mbo shortcodes

// daily request for cron
add_shortcode("yahav-stats-daily", 'yahav_stats_daily');
function yahav_stats_daily(){

	if (!isset($_GET['user']) || !$_GET['user'] == 'yahav-office')
		return 'aborted- not authorized'; 

	$pid = wp_get_recent_posts( array('post_type' => 'digma_stats', 'numberposts' => '1', 'status' => 'publish') );
	$latestD = date('j', strtotime($pid[0]['post_date']));
	$today = date('j');
	
	if ($today == $latestD)
		return ('aborted - already exists');

	$acfServer = new ACF_Rest_Server();

	date_default_timezone_set('Asia/Jerusalem');
	$d = date("j/n/Y H:i:s");

	//	$post_id = $this->mbo_new_postat($d);
	$post_id = $acfServer -> mbo_new_postat($d);
	$res = "Cron job, date:".$d. "  post id=".$post_id. " created";
	error_log($res);
	return "prev post date:". $pid[0]['post_date']. "<br />". $res;
}
add_shortcode("show-meta", 'mbo_show_meta'); // show all meta data for cpt

function mbo_show_meta($atts ) {
	$a = shortcode_atts( array(
		'type'  => 'digma_referrals',
		'pid'  => 4237 // known existing referral
	), $atts );
	
	if ( isset( $a['type'] ) && isset( $a['pid'] ) ) {
		$meta_type = $a['type'];
		$pid       = $a['pid'];
	} else {
		return "usage: [show-meta type=\"digma_referrals\" pid=\"4237\"]";
	}
	//$meta_values = get_metadata($meta_type, $pid); // defaults all , $meta_key, $single)
	$meta_values = get_post_meta( $pid );
	// array expected...
	if ( ! empty( $meta_values ) ) {
		if ( count( $meta_values ) > 0 ) {
			print_r( $meta_values );
		} else return "show meta values count = 0"; // $meta_values;
	}

	return "Got empty result";
}
