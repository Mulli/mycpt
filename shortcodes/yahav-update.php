<?php

// db update - for Cron
// Validate or Modify in the future
add_shortcode("yahav-update-daily", 'yahav_update_daily');

function yahav_update_daily(){
	// check permission
//	if (!isset($_GET['user']) || !$_GET['user'] == 'yahav-office')
//		return 'aborted- not authorized'; 

	$programCpt = get_posts( array('post_type' => 'digma_families', 'posts_per_page' => -1, 'status' => 'publish') );
	if (count($programCpt) < 1)
		return "No post of type= ". $type ." found";
	
	// init once all family fields for histograms
	foreach ( $programCpt as $cpt ){
        $family_fg = get_fields($cpt->ID); // family field groups
        $fincome = $family_fg['family_income'];
        error_log(print_r($fincome, true));
        $totalsum=0;
        for ($i=0; $i < count($fincome); $i++){
            $val = intval($fincome[$i]['income_sum']);
            switch ($fincome[$i]['income_frequency']) {
                case 'חודשי':
                    $totalsum += $val; break;
                case 'רבעוני':
                    $totalsum += round($val/4); break;
                case 'שנתי':
                    $totalsum += round($val/12); break;
                default:
                    //error_log("SWITH fail cpt id=".$cpt->ID. "   switch value=". $fincome[$i]['income_frequency']);
                    break;
            }
        }
        $shotefexp= calc_total($family_fg['family_expenses'], 'expense_sum'); // shotef expenses
        $debts = calc_total($family_fg['family_debts'], 'family_debt_monthlyreturn'); // family_debt_monthly return
        $mort = calc_total($family_fg['family_mortgage'], 'mortgage_monthly'); // mortgage_monthly
        $savings = intval($family_fg['family_savings']['monthly_savings_avg']);
        $totalexp = $shotefexp + $debts + $mort + $savings;
        $diff = $totalsum - $totalexp;
        error_log("family: ". $family_fg['program_info']['intake_family_name']. "   ". 
            $family_fg['program_info']['intake_family_woman_name']. "   ". $family_fg['program_info']['intake_family_man_name']);
        error_log("income=". $totalsum . " exp=". $shotefexp . "  debts=". $debts . " mort=". $mort . " saving=". $savings . "  diff=".$diff);

        $family_balance_computed = 'field_5cac7d4ab4322';
        $total_family_balance = 'field_5cac7d4bb4328';
        $total_income = 'field_5cac7d4bb4324';
        $total_family_expenses = 'field_5cac7d4bb4327';
        $total_shotef = 'field_5cac7d4bb4325';
        $total_debts = 'field_5cac7d4bb4326';
        $total_mort_return = 'field_5cac7d4bb4323';
        $kids_savings = 'field_5cac7d4bb4329';

        update_field($family_balance_computed, array(
            $total_family_balance => $diff, $total_income => $totalsum, $total_family_expenses => $totalexp, 
            $total_shotef => $shotefexp, $total_debts => $debts, $total_mort_return => $mort, $kids_savings => $savings),
            $cpt->ID);
            /*
        if (!isset($family_fg['family_balance_computed']) 
            || $family_fg['family_balance_computed'] == false){
            // generate new balance
            // $key = field_5cac7d4ab4322;
            if (!update_field($family_balance_computed, array(
                $total_family_balance => $diff, $total_income => $totalsum, $total_family_expenses => $totalexp, 
                $total_shotef => $shotefexp, $total_debts => $debts, $total_mort_return => $mort, $kids_savings => $savings),
				$cpt->ID)){
                    error_log("Fail1 to update cptid=". $cpt->ID);
                    //break;
            }
        }
        else {
            if (!update_field($family_balance_computed,array(
                $total_family_balance => $diff, $total_income => $totalsum, $total_family_expenses => $totalexp, 
                $total_shotef => $shotefexp , $total_debts => $debts, $total_mort_return => $mort, $kids_savings => $savings),
				$cpt->ID)){
                    error_log("Fail2 to update cptid=". $cpt->ID);
            }
        } */
    }
    
	/*
	$post_id = $acfServer -> mbo_new_postat($d);
	$res = "Cron job, date:".$d. "  post id=".$post_id. " created";
	error_log($res);*/
	return "update complete";
}

// Example: calc_total($fshotefexp, 'expense_sum');
function calc_total($table, $field){
    $total=0;
    for ($i=0; $i < count($table); $i++){
        $val = empty($table[$i][$field]) ? 0 : intval($table[$i][$field]);
        $total += $val;
    } 
    return $total;
}
?>