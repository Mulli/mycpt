<?php
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
add_shortcode("xl-intakes2", 'mbo_xl_intakes2'); // show all meta data for cpt
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
		$strh .= $value . MBO_XL_DELIM;
	foreach ($intakeq as $key  => $value)
		$strh .= $value . MBO_XL_DELIM;
	
	$str ="";
	foreach ( $intakesCpt as $cpt ){
		$meta = get_fields($cpt->ID, true);

		foreach ($meta['intake_info'] as $key  => $value)
			$str .= $value . MBO_XL_DELIM;

		foreach ($meta['intake'] as $key  => $value)
			$str .= $value . MBO_XL_DELIM;
		$str .= "<br />";
	}
	return $strh . "<br />". $str;
}
?>