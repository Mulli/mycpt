<?php 
/* Global shortcode definitions */
define("MBO_XL_DELIM", "^");

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


?>