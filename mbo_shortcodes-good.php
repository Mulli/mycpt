<?php
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
add_shortcode("xl-referrals", 'mbo_xl_referrals'); // show all meta data for cpt

function mbo_xl_referrals(){
	error_log("mbo_xl_referrals");
	$ReferralColDef = array (
		'family_referral_date' => 'תאריך',  
		'family_referral_family_name' =>'שם משפחה',  
		'family_referral_woman_firstname' => 'שם האשה',  
		'family_referral_man_firstname' => 'שם הגבר',  
		'family_referral_code' =>'קוד הפניה',  
		'family_referral_family_code' =>'קוד משפחה',  
		'family_referral_team' => 'מלווה',  
		'family_referral_status' => 'סטטוס הפניה',  
		'family_referral_cell_woman' => 'נייד אשה', 
		'family_referral_cell_man' => 'נייד גבר', 
		'family_referral_initiator' => 'גורם מפנה', 
		'family_referral_age_woman' => 'גיל אשה',
		'family_referral_age_man' => 'גיל גבר',
		'family_referral_merita_status' => 'סטטוס אישי',   
		'family_referral_num_kids' => 'מספר ילדים',  
		'family_refferal_woman_workplace' => 'מקום עבודה אשה',
		'family_refferal_man_workplace' => 'מקום עבודה גבר', 
		'family_referral_debts' => 'חובות משפחה', 
		'family_referral_os_client' => 'לקוחה של הרווחה',  
		'family_referral_prev_plan' => 'השתתפה בתכנית שיפור',  
		'family_referral_prev_plan_name' => 'באיזה תכנית השתתפה', 
		'family_referral_phone_call' => 'האם התבצעה שיחת טלפון', 
		'family_referral_intake_set' => 'האם נקבע אינטייק',
		'family_referral_intake_date' => 'תאריך אינטייק', 
		'family_referral_reason' => 'סיבה מרכזית',
		'family_referral_r1' => 'קשיים בהתנהלות כלכלית שוטפת שיפור', 
		'family_referral_r2' => 'חובות כבדים', 
		'family_referral_r3' => 'הכנסות נמוכות',
		'family_referral_r4' => 'אבטלה או תעסוקה לא איכותית', 
		'family_referral_r5' => 'מצב משפחתי רעוע', 
		'family_referral_r6' => 'קשיים של ילדים',
		'family_referral_mgr_decision' => 'החלטת מנהלת',  
		'family_referral_family_decision' => 'החלטת משפחה', 
		'family_referral_family_explain' => 'הסבר החלטת משפחה', 
		'family_referral_mgr_explain' => 'הסבר החלטת מנהלת'     
	);

	$arg = array('post_type' => 'digma_referrals',
				 'status' => 'publish',
				 'posts_per_page' => -1,
				 'orderby'    => 'title', 
				 'order'   => 'ASC');
	$referralsCpt = get_posts( $arg );
	if (count($referralsCpt) < 1)
		return "mbo_xl_referrals: no referrals found";

	$i = 0;
	$str1=""; $str="";
	$farray = array(array());
	$findex = 0;
	foreach ( $referralsCpt as $cpt ){
		$meta = get_post_meta( $cpt->ID, '', true);
		// set table column headers
		/*if ($i++ == 0){ // only header
			foreach ($meta as $key => $value)
				if ($key[0]==='_')
					continue; // skip
				else
					$str1 .= $ReferralColDef[$key] . "^";
		}*/

		// loop over all families
		foreach ($meta as $key => $value){
			if ($key[0]== '_') continue;
			if ( is_array( $key ) || is_object( $key ) )
			   error_log("Got non string object");
			//else error_log("ID=".$cpt->ID." K =". $key);
			if ( is_array( $value ) || is_object( $value ) ){
				$str .= $value[0] . "^";
				$farray[$findex][$key] = $value[0];
			}else {
				$str .= $value . "^";
				$farray[$findex][$key] = $value;
			}
			// error_log("findex = ". $findex . " key=". $key . " res=". $farray[$findex][$key]);
		}
		$findex++;
		$str .= "<br />";
	}
	// set heaeder line
	$strh = "";
	foreach ($ReferralColDef as $key => $value){
		$strh .= $value . "^";
	}

	$j = 0; $str2="";
	for ($j = 0; $j < $findex ; $j++){
		// print_r($farray[$j]);
		foreach ($ReferralColDef as $key => $value){
			$v = isset($farray[$j][$key]) ? $farray[$j][$key] : "";
			//error_log("Key =". $key . " value =" . $farray[$j][$key]);
			$str2 .= $v . "^";
		}
		$str2 .= "<br />";
	}
	// return json array of items
	//return $str1 . '<br />' . $str . '<br />==========<br />'. $str2; 
	return $strh . '<br />' . $str2 ;
}
/*  use acf get_fields loop

		$fields = get_fields($cpt->ID);

		if( $fields ){
			$str .= '<ul>';
			foreach( $fields as $name => $value ){
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
// [show-meta type="digma_referrals" pid="4237"]
add_shortcode("show-meta", 'mbo_show_meta'); // show all meta data for cpt

function mbo_show_meta($atts ) {
	$a = shortcode_atts( array(
		'type' => 'digma_referrals',
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

$intake_referrals = array(
	"family_referral_date"  => "ת. הפניה",
	"family_referral_family_name"  => "שם משפחה",
	"family_referral_woman_firstname"  => "אשה פרטי",
	"family_referral_man_firstname"  => "גבר פרטי",
	"family_referral_code"  => "קוד אינטייק",
	"family_referral_family_code"  => "קוד הפניה",
	"family_old_code"  => "קוד ישן",
	"family_referral_team"  => "מלווה",
	"family_referral_status"  => "סטטוס אינטייק",
	"family_referral_cell_woman"  => "נייד אשה",
	"family_referral_cell_man"  => "נייד גבר",
	"family_referral_initiator"  => "גורם מפנה",
	"family_referral_age_woman"  => "גיל אשה",
	"family_referral_age_man"  => "גיל גבר",
	"family_referral_merita_status"  => "סטטוס",
	"family_referral_num_kids"  => "מספר ילדים",
	"family_refferal_woman_workplace"  => "מקום עבודה אשה",
	"family_refferal_man_workplace"  => "מקום עבודה גבר",
	"family_referral_debts"  => "גובה חובות",
	"family_referral_os_client"  => "לקוחת רווחה",
	"family_referral_prev_plan"  => "השתתפה בתוכניות קודמות",
	"family_referral_prev_plan_name"  => "תוכניות קודמות",
	"family_referral_phone_call"  => "בוצעה שיחת טלפון",
	"family_referral_intake_set"  => "נקבע אינטייק",
	"family_referral_intake_date"  => "תאריך אינטייק",
	"family_referral_reason"  => "סיבה להפניה",
	"family_referral_mgr_decision"  => "קבלה לאינטייק",
	"family_referral_mgr_explain"  => "הסבר מנהלת",
	"family_referral_family_decision"  => "המשך לאינטייק",
	"family_referral_family_explain"  => "הסבר משפחה"
);

$intake_info = array(
	"intake_date"  => "תאריך אינטייק",
	"intake_status"  => "סטטוס לתכנית",
	"intake_yahav_member"  => "מלווה",
	"intake_qdate"  => "תאריך שאלון",
	"intake_mgr_decision"  => "החלטת מנהל",
	"intake_mgr_decision_explain"  => "הסבר החלטת מנהל",
	"intake_comments"  => "הערות",
	"intake_family_decision"  => "החלטת משפחה",
	"intake_family_name"  => "שם משפחה",
	"intake_family_woman_name"  => "שם אשה",
	"intake_family_man_name"  => "שם גבר",
	"referral_code"  => "קוד הפניה",
	"intake_code"  => "קוד אינטייק",
	"program_code"  => "קוד תכנית"
);

$intakeq = array(
	"intake_date" => "date",
	"intake_team" => "team",
	"intake_q1" => "q1",
	"intake_q2" => "q2",
	"intake_q3" => "q3",
	"intake_q3_other" => "q3other",
	"intake_q4" => "q4",
	"intake_q4_other" => "q4other",
	"intake_q5" => "q5",
	"intake_q5_other" => "q5other",
	"intake_q6" => "q6",
	"intake_q61" => "q61",
	"intake_q62" => "q62",
	"intake_q63" => "q63",
	"intake_q64" => "q64",
	"intake_q65" => "q65",
	"intake_q66" => "q66",
	"intake_q67" => "q67",
	"intake_q68" => "q68",
	"intake_q69" => "q69",
	"intake_q610" => "q610",
	"intake_q611" => "q611",
	"intake_q612" => "q612",
	"intake_q7" => "q7",
	"intake_q71" => "q71",
	"intake_q72" => "q72",
	"intake_q73" => "q73",
	"intake_q74" => "q74",
	"intake_q75" => "q75",
	"intake_q76" => "q76",
	"intake_q77" => "q77",
	"intake_q78" => "q78",
	"intake_q79" => "q79",
	"intake_q710" => "q710",
	"intake_q711" => "q711",
	"intake_q712" => "q712",
	"intake_q713" => "q713",
	"intake_q714" => "q714",
	"intake_q715" => "q715",
	"intake_q716" => "q716",
	"intake_q717" => "q717",
	"intake_q718" => "q718",
	"intake_q719" => "q719",
	"intake_q7_other" => "q7other",
	"intake_q801" => "q801",
	"intake_q802" => "q801",
	"intake_q803" => "q803",
	"intake_q804" => "q804",
	"intake_q805" => "q805",
	"intake_q806" => "q806",
	"intake_q807" => "q807",
	"intake_q8" => "q8",
	"intake_q9" => "q9",
	"intake_q10" => "q10",
	"intake_q11" => "q11",
	"intake_q12" => "q12",
	"intake_q13" => "q13",
	"intake_q14" => "q14",
	"intake_q141" => "q141",
	"intake_q142" => "q142",
	"intake_q143" => "q143",
	"intake_q144" => "q144",
	"intake_q145" => "q145",
	"intake_q146" => "q146",
	"intake_q147" => "q147",
	"intake_q151" => "q151",
	"intake_q152" => "q152",
	"intake_q153" => "q153",
	"intake_q154" => "q154",
	"intake_q155" => "q155",
	"intake_q156" => "q156",
	"intake_q157" => "q157",
	"intake_q16" => "q16",
	"intake_q17" => "q17",
	"intake_q18" => "q18",
	"intake_code" => "קוד אינטייק",
	"intake_family_code" => "קוד משפחה"
);
add_shortcode("xl-intakes", 'mbo_xl_intakes2'); // show all meta data for cpt
function mbo_xl_intakes2(){
	global $intake_info, $intakeq;
	error_log("mbo_xl_intakes2");
	$arg = array('post_type'  => 'digma_intakes',
				 'status'  => 'publish',
				 'posts_per_page'  => -1,
				 'orderby'    => 'title', 
				 'order'   => 'ASC');
	$intakesCpt = get_posts( $arg );
	if (count($intakesCpt) < 1)
		return "mbo_xl_intakes: no intakes found";

	$strh = "";
	foreach ($intake_info as $key  => $value)
		$strh .= $value . "^";
	foreach ($intakeq as $key  => $value)
		$strh .= $value . "^";
	
	$str ="";
	foreach ( $intakesCpt as $cpt ){
		$meta = get_fields($cpt->ID, true);

		foreach ($meta['intake_info'] as $key  => $value)
			$str .= $value . "^";

		foreach ($meta['intake'] as $key  => $value)
			$str .= $value . "^";
		$str .= "<br />";
	}
	return $strh . "<br />". $str;
}