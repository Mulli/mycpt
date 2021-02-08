<?php
if (!defined('MBO_DEBUG'))
    define('MBO_DEBUG', false); // get from national social site
// from $poverty_table https://www.btl.gov.il/Publications/oni_report/Documents/oni2017.pdf
// 0- Number of families, 1- TEKEN (נפשות תקניות) Number , 2- NIS per month, 3- תוספת שולית
$poverty_table = array(
    array(1, 1.25,   3423, 0),
    array(2, 2,      5477, 2054),
    array(3, 2.65,   7257, 1780),
    array(4, 3.2,    8764, 1506),
    array(5, 3.75,   10270, 1506),
    array(6, 4.25,   11639, 1369),
    array(7, 4.75,   13008, 1369),
    array(8, 5.2,    14241, 1232),
    array(9, 5.6,    15336, 1095)
);
$povert_level_nefesh = 2739; // קו העוני לנפש תקנית

// Report program
add_shortcode("report-bros2", 'mbo_report_bros2'); // show all meta data for cpt
function mbo_report_bros2(){
    // if (($s = mbo_permissions()) != "ok") return $s;
    //error_log("hello");
    // get data from database
    $ddata = gen_ddata_array(); 
    if ($ddata < 0)
        return "No posts of found";

    //error_log('total number programCpt='. count($programCpt));
    $res0 = sroi_total_income($ddata, 'f0');
    $res2 = sroi_total_income($ddata, 'f2');
    if (MBO_DEBUG)
        error_log("FAMILY LIST: summary= ". print_r($res2[1], true) . " FAMILY LIST=" . print_r($res2[2], true));
    //error_log(print_r($res2[1], true));

    $str0 = $res0[0];
    $str2 = $res2[0];
    $ns_cost = 0; //needsolution_yahav
    foreach ($ddata as $d)
        $ns_cost += sroi_total_column($d['f2']['family']['needsolution_array'], 'needsolution_cost'); 

    foreach ($ddata as $d)
        $ns_yahav_cost += sroi_total_column($d['f2']['family']['needsolution_array'], 'needsolution_yahav'); 

    // error_log("res0 is ". print_r($res0[1], true) . print_r($res2[1], true));
    $start = $res0[1];
    $end = $res2[1];
    $team_salary = intval(9000*24*1.75); // for 2 years
    $sroi = round ( (($end['total_income'] - $start['total_income']) 
            + ($start['total_expenses'] - $end['total_expenses'])
            + ($start['total_debt_monthly'] - $end['total_debt_monthly']))*24/($ns_cost+$team_salary) *100);
    $sroi_yahav = round ( (($end['total_income'] - $start['total_income']) 
            + ($start['total_expenses'] - $end['total_expenses'])
            + ($start['total_debt_monthly'] - $end['total_debt_monthly']))*24/($ns_yahav_cost+$team_salary) *100);
    $str_sroi = "<h2>SROI = ".$sroi."% </h2>" ;
    $str_sroi .= "<h2>SROI YAHAV= ".$sroi_yahav."% </h2>" ;
    $str_sroi .= "<h3> הפרש הכנסות לחודש + הפרש הוצאות לחודש + הפרש החזר חובות לחודש מוכפל ב 24</h3>";
    $str_sroi .= "<h3> מחולק בעלות מענים כוללת + עלות צוות ב 24 חודשים</h3>";
    $str_sroi .= "<p>החישוב: סך הגדלת ההכנסה הפנויה לחודש (בממוצע) כפול 24 חודשים מחולק בעלות מענים ועבודת צוות. מוכפל ב100%</p>" ;
    $str_sroi .= "<li>הפרש הכנסות = ".($end['total_income'] - $start['total_income'])." של כלל המשפחות לחודש</li>";
    $str_sroi .= "<li>הפרש הוצאות = ".($start['total_expenses'] - $end['total_expenses'])." של כלל המשפחות לחודש</li>";
    $str_sroi .= "<li>הפרש החזר חובות לחודש = ". ($start['total_debt_monthly'] - $end['total_debt_monthly']) ." של כלל המשפחות</li>";
    $str_sroi .= "<li>עלות צרכים ומענים כוללת = ".  $ns_cost ." עבור כלל המשפחות</li>";
    $str_sroi .= "<li>עלות צרכים ומענים יהב בלבד = ".  $ns_yahav_cost ." עבור כלל המשפחות</li>";
    $str_sroi .= "<li>עלות צוות 1.75 תקנים = 9000*24*1.75 = 294000</li>";
    $str_sroi .= "<li>חסר כרגע תוספת להכנסה ממיצוי זכויות</li>";


    $str3 = sprintf('<h2>'.'ממוצע עלות מענים למשפחה: %d סה"כ: %d</h2>', round($ns_cost/35), $ns_cost). "<hr />";
    $str4 = '<h2>'.sprintf(' הבדל בהוצאות: %d', $res2[1]['total_expenses']-$res0[1]['total_expenses']). '</h2>';
    $str4 .= '<h2>'.sprintf(' הבדל בהכנסות: %d', $res2[1]['total_income']-$res0[1]['total_income']). '</h2>'. "<hr />";
    
    $pov = calc_income_teken($ddata, $res0, $res2);
    //error_log("RESULT " . print_r($pov['single'], true));
    //error_log("RESULT " . print_r($pov['couple'], true));
    $singles = count($pov['single']['f0']);
    $couples = count($pov['couple']['f0']);
    // count number of single and two parents families
    $str5  = sprintf("<h2> single = %d couples = %d </h2>", $singles, $couples). "<hr />";
    // calculate under/above povery level see above: $povert_level_nefesh = 2739; // קו העוני לנפש תקנית
    $pov1_start = family_in_poverty($pov['single']['f0']); // count number
    $pov1_end   = family_in_poverty($pov['single']['f2']);
    $pov2_start = family_in_poverty($pov['couple']['f0']);
    $pov2_end   = family_in_poverty($pov['couple']['f2']);
    $wtf = array(round($pov1_start/$singles*100), round($pov1_end/$singles*100),
                    round($pov2_start/$couples*100), round($pov2_end/$couples*100));
    $str6  = sprintf("<h2>Under Poverty line: Single families before %d (%d%%) after %d (%d%%)</h2>", 
                $pov1_start, $wtf[0], $pov1_end, $wtf[1]);
    $str6 .= sprintf("<h2>Under Poverty line: Couple families before %d (%d%%) after %d (%d%%)</h2>", 
                $pov2_start, $wtf[2], $pov2_end, $wtf[3]). "<hr />";
    
    // count families that increased income % of families & average
    $str7 = calc_family_income_increase($pov). "<hr />";

    // count families that increased allowence % of families & average
    $str8 = calc_family_allowence_increase($pov). "<hr />";

    // decrease diff between income & expended
    $str9 = calc_family_in_out($res0[2], $res2[2]) . "<hr />";

    // count families that are even
    $str9 = calc_family_even_in_out($res0[2], $res2[2]) . "<hr />";

    $str10 = "<h2>אין מידע מדויק לגבי כניסה להסדר חובות</h2>". "<hr />";

    $str11 = calc_family_saving($ddata). "<hr />";

    return $str_sroi . $str0 . $str2 . $str3 . $str4. $str5. $str6 . $str7 . $str8 . $str9. $str10 . $str11;
}

// Count families that started saving
function calc_family_saving($ddata){
    $count0 = 0;
    $count2 = 0;
    foreach ($ddata as $d){
        if ($d['f0']['family']['family_savings']['do_u_save'] != 'לא')
            $count0 += 1;
        if ($d['f2']['family']['family_savings']['do_u_save'] != 'לא')
            $count2 += 1;
    }
    $s  = "<h2>". "Families saving monthly at start number =". $count0. ", ". round($count0/count($ddata)*100) . "%</h2>";
    $s .= "<h2>". "Families saving monthly at end number =".   $count2. ", ". round($count2/count($ddata)*100) . "%</h2>";
    
    return $s;
}

// Count families that become EVEN in expenses vs income
function calc_family_even_in_out($r0, $r2){
    $count_even_start = 0;
    $count_even_end = 0;
    //error_log('calc_family_allowence_increase = '. print_r($r0, true));

    for ($i=1; $i < count($r0); $i++){
        $dif0 = intval($r0[$i]['total_income']) + intval($r0[$i]['total_monthly_rights']) 
                        - (intval($r0[$i]['total_expenses']) + intval($r0[$i]['total_debt_monthly']));
        $dif2 = intval($r2[$i]['total_income']) + intval($r2[$i]['total_monthly_rights']) 
                        - (intval($r2[$i]['total_expenses']) + intval($r2[$i]['total_debt_monthly']));
        if ($dif0 > 0)
           $count_even_start += 1; 
        
        if ($dif2 > 0)
            $count_even_end += 1; 
        
        if (MBO_DEBUG)
            error_log("starting index= $i dif2= $dif2 dif0= $dif0 r0=". print_r($r0[$i], true) . " r2= ".print_r($r2[$i], true));
    }
    
    $s  = "<h2>". "Families even at start number =". $count_even_start. ", ". round($count_even_start/(count($r0)-1)*100) . "%</h2>";
    $s .= "<h2>". "Families even at end number =". $count_even_end. ", ". round($count_even_end/(count($r0)-1)*100) . "%</h2>";
    
    return $s;
}

function calc_family_in_out($r0, $r2){
    $count = 0;
    $total_decrease = 0;
    //error_log('calc_family_allowence_increase = '. print_r($r0, true));

    for ($i=1; $i < count($r0); $i++){
        $dif0 = intval($r0[$i]['total_income']) + intval($r0[$i]['total_monthly_rights']) 
                        - (intval($r0[$i]['total_expenses']) + intval($r0[$i]['total_debt_monthly']));
        $dif2 = intval($r2[$i]['total_income']) + intval($r2[$i]['total_monthly_rights']) 
                        - (intval($r2[$i]['total_expenses']) + intval($r2[$i]['total_debt_monthly']));
        if ($dif2 > $dif0){ // diff decreased
            $count += 1;
            $total_decrease += $dif2 - $dif0; // amount of decrease
        }
        if (MBO_DEBUG)
            error_log("starting index= $i dif2= $dif2 dif0= $dif0 r0=". print_r($r0[$i], true) . " r2= ".print_r($r2[$i], true));

    }
    
    if ($count>0)
        $s1 = "<h2>". "Decrease income - expenses : number =". $count. " ". round($count/(count($r0)-1)*100) . "%  ".
              " avg decrease= " . round($total_decrease/$count)."</h2>";
    else
        $s1 = "<h2>". "Decrease income - expenses : number = 0</h2>";

    return $s1;
}
// MISSING DATA from rights_q
function calc_family_allowence_increase($pov){
    $count = array('single' => 0, 'couple' => 0);
    $total_increase = array('single' => 0, 'couple' => 0);
    // error_log('calc_family_income_increase = '. print_r($pov, true));

    // calc singles
    $p = $pov['single'];
    for ($i=0; $i < count($p['f0']); $i++){
        $alwnc0 = $p['f0'][$i]['income'] - $p['f0'][$i]['work-only'];
        $alwnc2 = $p['f2'][$i]['income'] - $p['f2'][$i]['work-only'];
        if ($alwnc2 > $alwnc0){
            $count['single'] += 1;
            $total_increase['single'] += $alwnc2 - $alwnc0;
        }
    }
    $p = $pov['couple'];
    for ($i=0; $i < count($p['f0']); $i++){
        $alwnc0 = $p['f0'][$i]['income'] - $p['f0'][$i]['work-only'];
        $alwnc2 = $p['f2'][$i]['income'] - $p['f2'][$i]['work-only'];
        if ($alwnc2 > $alwnc0){
            $count['couple'] += 1;
            $total_increase['couple'] += $alwnc2 - $alwnc0;
        }
    }
    if ($count['single']>0)
        $s1 = "<h2>". "singles increase allowence: number =". $count['single']. 
            " (". round($count['single']/count($pov['single']['f0'])*100) . "%)  ".
              " avg increase= " . round($total_increase['single']/$count['single'])."</h2>";
    else
        $s1 = "<h2>". "singles increase allowence: 0</h2>";
    
    if ($count['couple']>0)
        $s2 = "<h2>". "couples increase allowence: number =". $count['couple'].  
            " (". round($count['couple']/count($pov['couple']['f0'])*100) . "%)  ".
              " avg increase= " . round($total_increase['couple']/$count['couple'])."</h2>";
    else
        $s2 = "<h2>". "couples increase allowence: 0</h2>";

    return $s1 . $s2;
}

function calc_family_income_increase($pov){
    /*'income'=> $family_income,
    'work-only' => $sal_work, 
    'tikni' => round($family_income / $teken ), 
    'poverty-line' => $povert_level_nefesh,
    'family-in-poverty' => $family_in_poverty);*/
    $count = array('single' => 0, 'couple' => 0);
    $total_increase = array('single' => 0, 'couple' => 0);
    // error_log('calc_family_income_increase = '. print_r($pov, true));

    // calc singles
    $p = $pov['single'];
    for ($i=0; $i < count($p['f0']); $i++){
        if ($p['f2'][$i]['work-only'] > $p['f0'][$i]['work-only']){
            $count['single'] += 1;
            $total_increase['single'] += $p['f2'][$i]['work-only'] - $p['f0'][$i]['work-only'];
        }
    }
    $p = $pov['couple'];
    for ($i=0; $i < count($p['f0']); $i++){
        if ($p['f2'][$i]['work-only'] > $p['f0'][$i]['work-only']){
            $count['couple'] += 1;
            $total_increase['couple'] += $p['f2'][$i]['work-only'] - $p['f0'][$i]['work-only'];
        }
    }
    if ($count['single']>0)
        $s1 = "<h2>". "singles increase salary: number =". $count['single']. 
            " (". round($count['single']/count($pov['single']['f0'])*100) . "%)  ".
              " avg increase= " . round($total_increase['single']/$count['single'])."</h2>";
    else
        $s1 = "<h2>". "singles increase salary: 0</h2>";
    
    if ($count['couple']>0)
        $s2 = "<h2>". "couples increase salary: number =". $count['couple'].  
            " (". round($count['couple']/count($pov['couple']['f0'])*100) . "%)  ".
              " avg increase= " . round($total_increase['couple']/$count['couple'])."</h2>";
    else
        $s2 = "<h2>". "couples increase salary: 0</h2>";

    return $s1 . $s2;
}


// positive count family-in-poverty = 1 if below level
function family_in_poverty($arr){
    $cnt = 0;
    foreach ($arr as $a)
        $cnt += $a['family-in-poverty'];
    return $cnt;
}
function calc_income_teken($ddata, $res0, $res2){
    //	אחוז % המשפחות עם הכנסה לנפש תקנית גבוהה מקו העוני -מהבוגרות ומסיימות בהסכמה בלבד
    global $poverty_table, $povert_level_nefesh;
   
    $single = 0;
    $couple = 0;
    $validated = true;
    $parentsal = array('novalue' => 0, 'netonly' => 0, 'brutuonly' => 0, 'both' => 0);
    $phases = array('f0', 'f2');
    // calc per phase 'f0' and 'f2'
    $single_teken_per_person = array(); // each array with its index
    $couple_teken_per_person = array(); // each array with its index
    foreach ($phases as $phase){
        foreach ($ddata as $d){
            if ($d[$phase]['single'] == 1){
                $single += 1;
                $p = $d[$phase]['mlink'] == 0 ? $d[$phase]['plink'] : $d[$phase]['mlink']; 
                $parentsal = addbrutoneto($p, $parentsal);
            } else if ($d[$phase]['single'] == 2) {
                $couple += 1;
                $parentsal = addbrutoneto($d[$phase]['mlink'], $parentsal);
                $parentsal = addbrutoneto($d[$phase]['plink'], $parentsal);
            } else {
                $validated = false;
                error_log("MISSING INFO single or double Family  ". print_r($d[$phase]['family'], true));
            }
            // Now check income table...
        }
        if ($validated){
            // calc tikni for single families, couples & for all
            // @todo - chakeck salaries
            $i = 0; $j = 0; // indexes to main result arrays
            foreach ($ddata as $d){

                $a = sroi_total_column_by_class($d[$phase]['family']['family_income'], 'income_sum', 'income_type', 
                        array('הכנסה מעסק', 'הכנסות מהון', 'משכורת'), 'income_frequency', 'חודשי');
                $name = $d[$phase]['family']['program_info']['intake_family_name'] . "  " .
                        $d[$phase]['family']['program_info']['intake_family_woman_name'] . '  '.
                        $d[$phase]['family']['program_info']['intake_family_man_name'] ;
                $sal_work = 0;
                foreach ($a['sum'] as $k => $v)
                    $sal_work += intval($v);
                //error_log("Family ". $name . " Total sal work ". $sal_work . " array = ". print_r($a, true));

                $fi = $d[$phase]['family']['family_income'];
                $family_income =  sroi_total_income_column($fi, 'income_sum', 'income_frequency', 'חודשי');
                // kids
                $kids = count($d[$phase]['family']['family_kids']);
                $tk = $d[$phase]['family']['program_info']['kids_number'];
                if ($kids != $tk && MBO_DEBUG) // debug validate purposes only
                    error_log("Missing kids ". $name . " number is =".$tk. "  count is= ".$kids."  kids table is  ".print_r($d[$phase]['family']['family_kids'],true));
                $kids = $kids < $tk ? $tk : $kids; // take the maximum, assume all under 18
                // נפשות תקניות עד 9
                $family_size = $kids + $d[$phase]['single'];
                if ( $family_size < 10)
                    $teken = (float) $poverty_table[$family_size-1][1];
                else {
                    $teken = (float) $poverty_table[9-1][1]; // table starts at 0
                    error_log('TEKEN family size 9 <  ' . $family_size);
                }
                
                $family_in_poverty = round($family_income / $teken) < $povert_level_nefesh ? 1 : 0;

                if ($d[$phase]['single'] == 1)
                    $single_teken_per_person[$phase][$i++] = array('name'=> $name, 'size' => $family_size, 
                                'income'=> $family_income,
                                'work-only' => $sal_work, 
                                'tikni' => round($family_income / $teken), 
                                // 'f-line' => round($poverty_table[$family_size-1][1] * ($family_income) / $teken),
                                'poverty-line' => $povert_level_nefesh,
                                'family-in-poverty' => $family_in_poverty);
                                //'p-level' => $poverty_table[$family_size-1][2]); 
                else
                    $couple_teken_per_person[$phase][$j++] = array('name'=> $name, 'size' => $family_size, 
                                'income'=> $family_income,
                                'work-only' => $sal_work,
                                'tikni' => round($family_income / $teken ), 
                                'poverty-line' => $povert_level_nefesh,
                                'family-in-poverty' => $family_in_poverty);
                                // 'f-line' => round($poverty_table[$family_size-1][1] * $family_income) / $teken),
                                //'p-level' => $poverty_table[$family_size-1][2]); 
                
            }
            if (MBO_DEBUG){
                error_log("single_teken_per_person ".$phase." = ". print_r($single_teken_per_person[$phase], true));
                error_log("couple_teken_per_person ".$phase." = ". print_r($couple_teken_per_person[$phase], true));
            }
        }
    }
    if (MBO_DEBUG)
        error_log("parent salary ".print_r($parentsal, true));
    return(array('single'=> $single_teken_per_person, 'couple' => $couple_teken_per_person));
//    return sprintf("<h2> single = %d couples = %d   </h2>", $single, $couple);
}
function addbrutoneto($p, $parentsal){
    $bruto = $p['parent_employment']['employ_bruto'];
    $neto = $p['parent_employment']['employ_neto'];
    $pinfo = $p['parent_info'];
    $s = sprintf ("%s %s ", $pinfo['parent_family_name'], $pinfo['parent_first_name']);

    if (!empty($bruto) && !empty($neto)){
        $parentsal['both'] += 1;
        $s .= sprintf ("Bruto= %d Neto= %d ", $bruto, $neto);
    }else if (empty($bruto) && empty($neto)){
        $parentsal['novalue'] += 1;
        $s .= " NO VALUE";
        if (mbo_if_unemployed($p['parent_employment']['employ_status']))
            $s .= ">> Unemployed OK";
        else $s .= ">> CONFLICT with employ status - NOT OK";
    }else if (empty($neto)){
        $parentsal['brutuonly'] += 1;
        $s .= sprintf ("Only Bruto= %d",$bruto);
    }else {
        $parentsal['netonly'] += 1;
        $s .= sprintf ("Only Bruto= %d",$neto);
    }
    if (MBO_DEBUG) error_log("Validation Bruto/Neto/Unemployed: " . $s);
    return ($parentsal);
}
// status as phase 0
function sroi_total_income($ddata, $index){
    $family_data = array('legal' => 0, 'housing' => 0, 'total_expenses' => 0, 'total_income' => 0, 
                        'total_monthly_rights' => 0, 'total_debt_monthly' => 0,
                        'total_debts' => 0, 'total_mort_monthly' =>0, 'total_mort' => 0, 'total_kiz'=>0);
    $summary = array('legal' => 0, 'housing' => 0, 'total_expenses' => 0, 'total_income' => 0, 
                        'total_monthly_rights' => 0, 'total_debt_monthly' => 0,
                        'total_debts' => 0, 'total_mort_monthly' =>0, 'total_mort' => 0, 'total_kiz'=>0);
    $family_list = array(); // list results per family for additional calculations
    $cnt = 0;
    foreach ($ddata as $d){
        $cnt += 1;
        $l = $d[$index]['family']['family_legal']['need_legal'];
        if (isset($l) && $l == 'כן'){
            $summary['legal'] += 1;
            $family_data['legal'] = 1;
        }

        $h = $d[$index]['family']['family_housing']['family_house_status'];
        if (isset($h) && $h == 'כן'){
            $summary['housing'] += 1;
            $family_data['housing'] = 1;
        }

        $e = $d[$index]['family']['family_expenses'];
        if (isset($e)){
            $family_data['total_expenses'] = sroi_total_column($e, 'expense_sum');
            $summary['total_expenses'] += $family_data['total_expenses'];
        }

        //@TODO sroi_total_column_by_class($e, 'expense_sum', 'expense_category', array('דיור','חינוך', 'שוטפות', 'כבלים'))
        $i = $d[$index]['family']['family_income'];
        if (isset($i)){
            $family_data['total_income'] = sroi_total_income_column($i, 'income_sum', 'income_frequency', 'חודשי');
            $summary['total_income'] += $family_data['total_income'];
            $a = sroi_total_column_by_class($i, 'income_sum', 'income_type', 
                        array('הכנסה מעסק', 'הכנסות מהון', 'משכורת'), 'income_frequency', 'חודשי');
            $summary['total_kiz'] += $a['rest'] ; // קצבאות
            $family_data['total_kiz'] = $a['rest'] ;
        }
        $rights = isset($d[$index]['family']['family_rights'])? $d[$index]['family']['family_rights'] : false;
        if (isset($rights)){
            $family_data['total_monthly_rights'] = sroi_total_income_column($rights, 'rights_sum', 'rights_frequency', 'חודשי');
            $summary['total_monthly_rights'] += $family_data['total_monthly_rights'];
        }
        $debts = isset($d[$index]['family']['family_debts']) ? $d[$index]['family']['family_debts'] : false;
        if ($debts){
            $family_data['total_debt_monthly'] = sroi_total_column($debts, 'family_debt_monthlyreturn');
            $summary['total_debt_monthly'] +=  $family_data['total_debt_monthly'];

            $family_data['total_debts'] = sroi_total_column($debts, 'family_debt_orig_sum');
            $summary['total_debts'] += $family_data['total_debts'];
        }
        $mort = isset($d[$index]['family']['family_mortgage']) ? $d[$index]['family']['family_mortgage'] : false;
        if ($mort){
            $family_data['total_mort_monthly'] = sroi_total_column($mort, 'mortgage_monthly');
            $summary['total_mort_monthly'] += $family_data['total_mort_monthly'];

            $family_data['total_mort'] = sroi_total_column($mort, 'mortgage_sum');
            $summary['total_mort'] += $family_data['total_mort'];
        } // else do nothing is like adding zero to sum

        $t = $d[$index]['family']['program_info']['intake_family_name'] . "  "
                                .  $d[$index]['family']['program_info']['intake_family_woman_name'] . "  "
                                .  $d[$index]['family']['program_info']['intake_family_man_name'] . "  "
                                .  $d[$index]['family']['program_info']['program_code']  ;
        //error_log('#' . $cnt. " Family: ". $t);
        //error_log(' סטטוס משפחה' . $index . "  " . print_r($family_data, true));
        $family_list[$cnt] = $family_data;
        //error_log(' סטטוס מצטבר' . $index . "  " . print_r($summary, true));
    }
    $phase = $index == 'f0' ? 'התחלה' : 'סיום';
    $str = '<h2>מספר המשפחות ב'. $phase. " : "  . $cnt . '</h2>';
    $str .= '<table><tbody>';
    foreach ($summary as $k => $v){
        $str .= '<tr><td>'.$k.'</td><td>'.$v.'</td><td>'.round($v/$cnt, 2).'</td></tr>';
    }
    $str .= '</tbody></table>';
    
    return array($str, $summary, $family_list, $cnt);
}
function sroi_total_column($table, $col){
    $sum = 0;
    if (empty($table) || $table == false)
        return -1;
    foreach ($table as $t){
        if (isset($t[$col]))
            $sum += intval($t[$col]);
    }
    return $sum;
}
// Only monthly income
function sroi_total_income_column($table, $col, $freq, $value){
    $sum = 0;
    if (empty($table) || $table == false)
        return -1;
    foreach ($table as $t){
        if (isset($t[$col]) && !empty($t[$col]) && isset($t[$freq]) && $t[$freq] == $value) // verify income is monthly
            $sum += intval($t[$col]);
        else if (!empty($t[$col]) && MBO_DEBUG) // ignore empty lines
            error_log("Found non monthly income = ". print_r($t, true));
    }
    return intval($sum);
}
function sroi_total_column_by_class($table, $col, $class, $vlist, $freq, $value){
    $sum_array = array();
    $rest = 0;
    if (!is_array($vlist))
        return -1;
    foreach ($vlist as $v) $sum_array[$v] = 0;

    foreach ($table as $t){
        if (isset($t[$freq]) && $t[$freq] == $value){ // only montly
            if (in_array($t[$class], $vlist) )
                $sum_array[$t[$class]] += !isset($t[$col]) ? 0 : intval($t[$col]);
            else $rest += !isset($t[$col]) ? 0 : intval($t[$col]);
        }
    }
//error_log('sroi_total_column_by_class ' . print_r(array('arr' => $sum_array, 'rest' => $rest), true));
    return array('sum' => $sum_array, 'rest' => $rest);
}

///// Interface to mbo_server
// res0, res2 has familiy list data in res0[2], res2[2] item
// Start End ROI
function calc_se_roi(){

    $ddata = gen_ddata_array(); 
    if ($ddata < 0)
        return -1;

    //error_log('total number programCpt='. count($programCpt));
    $res0 = sroi_total_income($ddata, 'f0');
    $res2 = sroi_total_income($ddata, 'f2');
    if (MBO_DEBUG)
        error_log("FAMILY LIST: summary= ". print_r($res2[1], true) . " FAMILY LIST=" . print_r($res2[2], true));
    //error_log(print_r($res2[1], true));

    $ns_cost = 0;
    foreach ($ddata as $d)
        $ns_cost += sroi_total_column($d['f2']['family']['needsolution_array'], 'needsolution_cost'); 

    // error_log("res0 is ". print_r($res0[1], true) . print_r($res2[1], true));
    $start = $res0[1];
    $end = $res2[1];
    $team_salary = intval(7000*24*3.5); // for 2 years
    $sroi = round ( (($end['total_income'] - $start['total_income']) 
            + ($start['total_expenses'] - $end['total_expenses'])
            + ($start['total_debt_monthly'] - $end['total_debt_monthly']))*24/($ns_cost+$team_salary) *100);
    return array($sroi, $res0, $res2);
    /* Use the following as documentation
    $str_sroi = "<h2>SROI = ".$sroi."% </h2>" ;
    $str_sroi .= "<p>החישוב: סך הגדלת ההכנסה הפנויה החודשית למשפחות כפול 24 חודשים מחולק בעלות מענים ומוכפל ב100%</p>" ;
    $str_sroi .= "<li>הפרש הכנסות = ".($end['total_income'] - $start['total_income'])."</li>";
    $str_sroi .= "<li>הפרש הוצאות = ".($start['total_expenses'] - $end['total_expenses'])."</li>";
    $str_sroi .= "<li>הפרש החזר חובות חודשי = ". ($start['total_debt_monthly'] - $end['total_debt_monthly']) ."</li>";
    $str_sroi .= "<li>עלות צרכים ומענים = ".  $ns_cost ."</li>";
    $str_sroi .= "<li>עלות צוות 3.5תקנים = 7000*24*3.5 = 294000</li>";
    $str_sroi .= "<li>חסר כרגע תוספת להכנסה ממיצוי זכויות</li>";


    $str3 = sprintf('<h2>'.'ממוצע עלות מענים למשפחה: %d סה"כ: %d</h2>', round($ns_cost/35), $ns_cost). "<hr />";
    $str4 = '<h2>'.sprintf(' הבדל בהוצאות: %d', $res2[1]['total_expenses']-$res0[1]['total_expenses']). '</h2>';
    $str4 .= '<h2>'.sprintf(' הבדל בהכנסות: %d', $res2[1]['total_income']-$res0[1]['total_income']). '</h2>'. "<hr />";
    */
}
?>