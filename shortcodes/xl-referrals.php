<?php 
/* xl-referrals */
add_shortcode("xl-referrals", 'mbo_xl_referrals'); // show all meta data for cpt

function mbo_xl_referrals(){
	error_log("mbo_xl_referrals");
	$ReferralColDef = array (
		"family_referral_date"  => 'תאריך',  
		"family_referral_family_name"  =>'שם משפחה',  
		"family_referral_woman_firstname"  => 'שם האשה',  
		"family_referral_man_firstname"  => 'שם הגבר',  
		"family_referral_code"  =>'קוד הפניה',  
		"family_referral_family_code"  =>'קוד משפחה',  
		"family_referral_team"  => 'מלווה',  
		"family_referral_status"  => 'סטטוס הפניה',  
		"family_referral_cell_woman"  => 'נייד אשה', 
		"family_referral_cell_man"  => 'נייד גבר', 
		"family_referral_initiator"  => 'גורם מפנה', 
		"family_referral_age_woman"  => 'גיל אשה',
		"family_referral_age_man"  => 'גיל גבר',
		"family_referral_merita_status"  => 'סטטוס אישי',   
		"family_referral_num_kids"  => 'מספר ילדים',  
		"family_refferal_woman_workplace"  => 'מקום עבודה אשה',
		"family_refferal_man_workplace"  => 'מקום עבודה גבר', 
		"family_referral_debts"  => 'חובות משפחה', 
		"family_referral_os_client"  => 'לקוחה של הרווחה',  
		"family_referral_prev_plan"  => 'השתתפה בתכנית שיפור',  
		"family_referral_prev_plan_name"  => 'באיזה תכנית השתתפה', 
		"family_referral_phone_call"  => 'האם התבצעה שיחת טלפון', 
		"family_referral_intake_set"  => 'האם נקבע אינטייק',
		"family_referral_intake_date"  => 'תאריך אינטייק', 
		"family_referral_reason"  => 'סיבה מרכזית',
		"family_referral_r1"  => 'קשיים בהתנהלות כלכלית שוטפת שיפור', 
		"family_referral_r2"  => 'חובות כבדים', 
		"family_referral_r3"  => 'הכנסות נמוכות',
		"family_referral_r4"  => 'אבטלה או תעסוקה לא איכותית', 
		"family_referral_r5"  => 'מצב משפחתי רעוע', 
		"family_referral_r6"  => 'קשיים של ילדים',
		"family_referral_mgr_decision"  => 'החלטת מנהלת',  
		"family_referral_family_decision"  => 'החלטת משפחה', 
		"family_referral_family_explain"  => 'הסבר החלטת משפחה', 
		"family_referral_mgr_explain"  => 'הסבר החלטת מנהלת'     
	);

	$arg = array('post_type'  => 'digma_referrals',
				 'status'  => 'publish',
				 'posts_per_page'  => -1,
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
		//$meta = get_post_meta( $cpt->ID, '', true);
		$meta = get_post_meta( $cpt->ID, '', true);
		// set table column headers
		/*if ($i++ == 0){ // only header
			foreach ($meta as $key"  => $value)
				if ($key[0]==='_')
					continue; // skip
				else
					$str1 .= $ReferralColDef[$key] . ",";
		}*/

		// loop over all "families
		foreach ($meta as $key  => $value){
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
	foreach ($ReferralColDef as $key  => $value){
		$strh .= $value . "^";
	}

	$j = 0; $str2="";
	for ($j = 0; $j < $findex ; $j++){
		// print_r($farray[$j]);
		foreach ($ReferralColDef as $key  => $value){
			$v = isset($farray[$j][$key]) ? $farray[$j][$key] : "";
			// error_log("Key =". $key . " value =" . $farray[$j][$key]);
			$str2 .= $v . "^";
		}
		$str2 .= "<br />";
	}
	// return json array of items
	//return $str1 . '<br />' . $str . '<br />==========<br />'. $str2; 
	return $strh . '<br />' . $str2 ;
}
?>