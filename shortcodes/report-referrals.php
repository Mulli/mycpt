<?php
// Report referrals
    add_shortcode("report-referrals", 'mbo_report_referrals'); // show all meta data for cpt
    function mbo_report_referrals(){
        // if (($s = mbo_permissions()) != "ok") return $s;

            // get data from database
        $referralsCpt = get_posts( array('post_type' => 'digma_referrals', 'posts_per_page' => -1, 'status' => 'publish') );
        if (count($referralsCpt) < 1)
            return "No post of type= ". $type ." found";
        // initialization
        $str = mbo_init_page_display();
        
        // init once all family fields for histograms
        $family_referral_fileds = array();
        foreach ( $referralsCpt as $cpt )
            $family_referral_fileds[$cpt->ID] = get_fields($cpt->ID);

        // Display header
        $str .= mbo_report_header('דו"ח הפניות נכון לתאריך:', count($referralsCpt));

        // $str .= '<h2 id="report-title" class="report-title">דו"ח הפניות נכון לתאריך: '.date("j/n/Y").'<br />'.'סה"כ הפניות: ' . count($referralsCpt) . "</h2>";
        $referral_topics = array("סטטוס הפניות" => 'sec-family_referral_status', 
                        "תאריך פתיחת הפניה" => 'sec-family_referral_date', 
                        "גורם מפנה" => 'sec-family_referral_initiator', 
                        "חד הוריות או שני בני זוג" => 'sec-mbo_single_parent_histogram', 
                        "סטטוס זוגיות" => 'sec-family_referral_merita_status', 
                        "מספר ילדים" => 'sec-family_referral_num_kids', 
                        "גיל האשה" => 'sec-family_referral_age_woman', 
                        "גיל הגבר" => 'sec-family_referral_age_man',
                        "התפלגות כמות הקשיים במשפחה (מכסימום 6)" => 'sec-family_referral_r', 
                        "התפלגות סוגי הקשיים במשפחה" => 'sec-family_referral_r_yesno', 
                        "החלטת מנהלת" => 'sec-family_referral_mgr_decision', 
                        "החלטת משפחה" => 'sec-family_referral_family_decision',
                        "חובות משפחה" => 'sec-family_referral_debts', 
                        "לקוחות הרווחה" => 'sec-family_referral_os_client',
                        "השתתפות בתוכניות אחרות" => 'sec-family_referral_prev_plan',
                        "התפלגות תוכניות אחרות" => 'sec-family_referral_prev_plan_name');

        $str .= mbo_report_menu($referral_topics);

        // Display list of all histogram graphs
        $family_referral_status = mbo_optimized_histogram($referralsCpt, $family_referral_fileds, "referrals", "family_referral_status");
        $str .= draw_histogram("סטטוס הפניות", $family_referral_status, "family_referral_status",
        "סטטוס הפניה", "מספר משפחות");

        $family_referral_date = mbo_date_histogram($referralsCpt, $family_referral_fileds, "referrals", "family_referral_date", null);
        $str .= draw_histogram("תאריך פתיחת הפניה", $family_referral_date, "family_referral_date",
        "תאריך הפניה לפי רבעונים", "מספר משפחות");
        
        $family_referral_initiator = mbo_optimized_histogram($referralsCpt, $family_referral_fileds, "referrals", "family_referral_initiator");
        $str .= draw_histogram("גורם מפנה", $family_referral_initiator, "family_referral_initiator", "", "" );
        
        $mbo_single_parent_histogram = mbo_single_parent_histogram($referralsCpt, $family_referral_fileds, "referrals", "not used");
        $str .= draw_histogram("חד הוריות או שני בני זוג", $mbo_single_parent_histogram, "mbo_single_parent_histogram",
        "חד הורית או זוג הורים", "מספר משפחות");

        $family_referral_merita_status = mbo_optimized_histogram($referralsCpt, $family_referral_fileds, "referrals", "family_referral_merita_status");
        $str .= draw_histogram("סטטוס זוגיות", $family_referral_merita_status, "family_referral_merita_status",
        "סטטוס זוגיות", "מספר משפחות");
        
        $family_referral_num_kids = mbo_optimized_histogram($referralsCpt, $family_referral_fileds, "referrals", "family_referral_num_kids");
        $str .= draw_histogram("מספר ילדים", $family_referral_num_kids, "family_referral_num_kids",
        "מספר הילדים", "מספר משפחות");
        $str .= mbo_avg_line($family_referral_num_kids);

        error_log(print_r($family_referral_num_kids, true));
        
        //$family_referral_age_woman = mbo_optimized_histogram($referralsCpt, $family_referral_fileds, "referrals", "family_referral_age_woman");
        $family_referral_age_woman = mbo_age_histogram($referralsCpt, $family_referral_fileds, "referrals", "family_referral_age_woman");
        $str .= draw_histogram("התפלגות גילאי הנשים", $family_referral_age_woman, "family_referral_age_woman",
        "טווחי גיל בקבוצות של 5 שנים", "מספר הנשים");
        $str .= mbo_avg_line($family_referral_age_woman);
        //$str .= '<p style="text-align:center"> avg = ' . $family_referral_age_woman['avg'] . '  median = ' . $family_referral_age_woman['median'] .
        //            '  std dev = ' . $family_referral_age_woman['stddev'] . '</p>';
        
        $family_referral_age_man = mbo_age_histogram($referralsCpt, $family_referral_fileds, "referrals", "family_referral_age_man");
        $str .= draw_histogram("התפלגות גילאי הגברים", $family_referral_age_man, "family_referral_age_man",
        "טווחי גיל בקבוצות של 5 שנים", "מספר הגברים");
        $str .= mbo_avg_line($family_referral_age_man);

        //$str .= '<p style="text-align:center"> avg = ' . $family_referral_age_man['avg'] . '  median = ' . $family_referral_age_man['median'] .
        //            '  std dev = ' . $family_referral_age_man['stddev'] . '</p>';
        
        $family_referral_r = mbo_count_histogram($referralsCpt, $family_referral_fileds, "referrals", "not used");
        $str .= draw_histogram("התפלגות כמות הקשיים במשפחה (מכסימום 6)", $family_referral_r, "family_referral_r",
        "מספר הקשיים", "מספר משפחות");
        
        $family_referral_r_yesno = mbo_count_yes_histogram($referralsCpt, $family_referral_fileds, "referrals", "not used");
        $str .= draw_histogram("התפלגות סוגי הקשיים במשפחה", $family_referral_r_yesno, "family_referral_r_yesno",
        "סוגי הקשיים", "מספר משפחות");
        
        $family_referral_mgr_decision = mbo_optimized_histogram($referralsCpt, $family_referral_fileds, "referrals", "family_referral_mgr_decision");
        $str .= draw_histogram("החלטת מנהלת", $family_referral_mgr_decision, "family_referral_mgr_decision",
        "תמצית החלטת מנהלת", "מספר משפחות");
        
        $family_referral_family_decision = mbo_optimized_histogram($referralsCpt, $family_referral_fileds, "referrals", "family_referral_family_decision");
        $str .= draw_histogram("החלטת משפחה", $family_referral_family_decision, "family_referral_family_decision",
        "תמצית החלטת משפחה", "מספר משפחות");
        
        $family_referral_debts = mbo_optimized_histogram($referralsCpt, $family_referral_fileds, "referrals", "family_referral_debts");
        $str .= draw_histogram("חובות משפחה", $family_referral_debts, "family_referral_debts",
        "סכומי חובות למשפחה", "מספר משפחות לפי גובה החוב");
        $str .= mbo_avg_line($family_referral_debts);

        
        $family_referral_os_client = mbo_optimized_histogram($referralsCpt,  $family_referral_fileds, "referrals", "family_referral_os_client");
        $str .= draw_histogram("לקוחות הרווחה", $family_referral_os_client, "family_referral_os_client",
        "תשובות כן/לא", "מספר משפחות");
        
        $family_referral_prev_plan = mbo_optimized_histogram($referralsCpt,  $family_referral_fileds, "referrals", "family_referral_prev_plan");
        $str .= draw_histogram("השתתפות בתוכניות אחרות", $family_referral_prev_plan, "family_referral_prev_plan",
        "תשובות כן/לא", "מספר משפחות");
        
        $family_referral_prev_plan_name = mbo_optimized_histogram($referralsCpt,  $family_referral_fileds, "referrals", "family_referral_prev_plan_name");
        $str .= draw_histogram("התפלגות תוכניות אחרות", $family_referral_prev_plan_name, "family_referral_prev_plan_name",
        "שמות תכניות אחרות", "מספר משפחות");
        
        $str .= "<div class='end-report'><h2>סוף הדו''ח</h2></div>";

        return $str;
        //return displayBar($family_referral_status);
    }
    function mbo_avg_line($arg){
        $s = '<p style="text-align:center"> avg = ' . $arg['avg'] . '  median = ' . $arg['median'];
        $s .= '  std dev = ' . $arg['stddev'] . '  N= '. $arg['N']  .'</p>';
        return $s;
    }
    function draw_histogram($title, $arg, $canvasId, $xAxesTitle, $yAxesTitle){
        $lables = array(); 
        $data = array();
        $tlen = 0;
        foreach ($arg['table'] as $key => $value) $tlen+=1;// count table size/length
        //error_log(print_r($arg['table'], true));
        if ($tlen>24){
            $res = drawColumns($title, $arg, $canvasId, $tlen);
            $strtest = "";
        } else {
            foreach ($arg['table'] as $key => $value){
                $labels[] = $key; // strlen($key) > 1 ? $key : "לא מוגדר";
                $data[] = $value;
            }
            $res = drawCanvas('bar', $title, $labels, $data, $canvasId, $xAxesTitle, $yAxesTitle);
            //$strtest = drawSroiCanvas($title, $histogram, $field, $xAxesTitle, $yAxesTitle);
        }
        
        //error_log(print_r($labels, true));
        //error_log(print_r($data, true));
    
        $out =  '<section id="sec-'.$canvasId.'" >'. $res
                    . '<div class="gotoTop"><a href="#report-title">&uarr;</a></div>'
                    . '</section>';
        return $out; // . '<div'. $strtest . '</div>';
    }

    function drawColumns($title, $arg, $canvasId, $tlen){
        $columnLen = round($tlen / 4 - 0.5);
        // error_log("columnLen=". $columnLen);
        $arr = array();
        for ($i = 0; $i <= $columnLen; $i++) $arr[$i]="";

        $i = 0;
        foreach ($arg['table'] as $key => $value){
            //error_log("loop key=".$key."  val=". $value);
            if (!isset($value) || $value=="") $value="***";
            if (!isset($key) || $key=="") $key="לא מוגדר";
            $arr[$i] .= '<td class="mbo-key">'.$key.'</td><td class="mbo-value">'.$value.'</td>';
            $i =  $i >= $columnLen ? $i=0 : $i+1;
        }
        // fill the last columns empty cells
        for (; $i < $columnLen; $i++)
            $arr[$i] .= '<td class="mbo-key"></td><td class="mbo-value"></td>';

        // error_log(print_r($arr, true));
        $str = '<div class="mbo-table" style="height:auto;max-width:600px;margin: 0 auto;">';
        $str .='<h3 style="text-align:center">'.$title.'</h3>';
        $str .='<table style="width:100%;border-collapse: collapse;"><tbody>';
        for ($i = 0; $i < $columnLen; $i++) 
            $str .= '<tr>'.$arr[$i].'</tr>';

        $str .= '</tbody></table></div>';
        return $str;
    }


    function mbo_age_histogram($postList, $allFields, $type, $keyField){ // $keyField not used
        $i = 0; $age_arr = array(); $index =0;
        $histogram = array('20-24' => 0, '25-29' => 0,'30-34' => 0, '35-39' => 0, 
                            '40-44' => 0, '45-49' => 0, '50-54' => 0, '55-59' => 0, 'לא מוגדר' => 0);
        foreach ( $postList as $cpt ){
            //$x = get_fields($cpt->ID);
            $x = $allFields[$cpt->ID];
            if (!isset($x[$keyField]) || empty($x[$keyField]))
                $histogram['לא מוגדר'] += 1; 
            else {
                $age = intval($x[$keyField]);
                $age_arr[$index++] = $age;
                for ($i = 20 ; $i < 60; $i +=5) {
                    if ($age >= $i && $age <= $i+4 ) { 
                        $s= $i.'-'.($i+4); 
                        $histogram[$s] += 1;
                        break;
                    }
                }
            }
            $i++; 
        }
        asort($age_arr, SORT_NUMERIC); //, SORT_NUMERIC
        $average = number_format(array_sum($age_arr) / count($age_arr), 2);

        $midval = floor(count($age_arr)/2);
        if (count($age_arr) % 2) // odd
            $median = $age_arr[$midval];
        else $median = ($age_arr[$midval] + $age_arr[$midval+1]) / 2;

        $arg = array ('type' => $type, 'total' => $i, 'table' => $histogram, 
            'avg' => $average, 'median' => $median, 'stddev' => number_format(mbo_stand_deviation($age_arr), 2), 'N' => count($age_arr));
        //error_log($type. " historgram, total=". $i);
        ////error_log(print_r( $histogram, true ));
        //return "Result generated"; // $json['body']['data'];
        return $arg;
    }
    function mbo_stand_deviation($arr) 
    { 
        $num_of_elements = count($arr); 
          
        $variance = 0.0; 
          
                // calculating mean using array_sum() method 
        $average = array_sum($arr)/$num_of_elements; 
          
        foreach($arr as $i) { 
            // sum of squares of differences between  
                        // all numbers and means. 
            $variance += pow(($i - $average), 2); 
        } 
          
        return (float)sqrt($variance/$num_of_elements); 
    } 

    function mbo_single_parent_histogram($postList, $allFields, $type, $keyField){ // $keyField not used
        $i = 0;
        $histogram = array('זוג הורים' => 0, 'חד הורית' => 0, 'לא מוגדר' => 0);
        foreach ( $postList as $cpt ){
            //$x = get_fields($cpt->ID);
            $x = $allFields[$cpt->ID];
            if (!isset($x["family_referral_woman_firstname"]) && !isset($x["family_referral_man_firstname"]))
                $histogram['לא מוגדר'] += 1; 
            else if (isset($x["family_referral_woman_firstname"]) && strlen($x["family_referral_woman_firstname"])>1
                    && isset($x["family_referral_man_firstname"]) && strlen($x["family_referral_man_firstname"])>1)
                $histogram['זוג הורים'] += 1;
            else $histogram['חד הורית'] += 1;
            
            $i++; 
        }
        arsort($histogram, SORT_NUMERIC); //, SORT_NUMERIC

        $arg = array ('type' => $type, 'total' => $i, 'table' => $histogram	);
        //error_log(print_r( $histogram, true ));
        return $arg;
    }

    // count the number of yes in family difficulties
    function mbo_count_histogram($postList, $allFields, $type, $keyField){ // $keyField not used
        $i = 0;
        $histogram = array();
        for ($j = 0; $j <= 6; $j++){
            //$k = "family_referral_r".($j+1);
            $histogram[$j] = 0;
        }
        $histogram['ללא תשובה'] = 0;

        foreach ( $postList as $cpt ){
            //$x = get_fields($cpt->ID);
            $x = $allFields[$cpt->ID];
            $noValueEntered = 0; // check if no value entered to any of 6 fiedls
            
            for ($j = 0; $j < 6; $j++){
                $k = "family_referral_r".($j+1);
                // error_log("index =".$k);
                if (!isset($x[$k]) || empty($x[$k]) || $x[$k] == "נא לבחור")
                    $noValueEntered += 1;
            }
            if ($noValueEntered == 6) // all fields are not defined
                $histogram['ללא תשובה'] += 1;
            else {
                $yesvalue = 0;
                for ($j = 0; $j < 6; $j++){
                    $k = "family_referral_r".($j+1);
                    if (isset($x[$k]) && $x[$k] == "כן")
                        $yesvalue += 1;
                }
                $histogram[$yesvalue] += 1;
            }
            $i++; 
        }
        arsort($histogram, SORT_NUMERIC); //, SORT_NUMERIC

        $arg = array ('type' => $type, 'total' => $i, 'table' => $histogram	);
        //error_log($type. " historgram, total=". $i);
        //error_log(print_r( $histogram, true ));
        //return "Result generated"; // $json['body']['data'];
        return $arg;
    }
    // Histogram of yes answers of difficulties
    function mbo_count_yes_histogram($postList, $allFields, $type, $keyField){ // $keyField not used
        $i = 0;
        $histogram = array();
        for ($j = 0; $j < 6; $j++){
            $k = "family_referral_r".($j+1);
            $histogram[$k] = 0;
        }
        
        foreach ( $postList as $cpt ){
            //$x = get_fields($cpt->ID);
            $x = $allFields[$cpt->ID];
            
            for ($j = 0; $j < 6; $j++){
                $k = "family_referral_r".($j+1);
                // error_log("index =".$k);
                if (isset($x[$k]) && $x[$k] == "כן")
                    $histogram[$k] += 1;
                $i++; // total number of yes
            }
        }
        arsort($histogram, SORT_NUMERIC); //, SORT_NUMERIC
        $translate = array(
            "family_referral_r1" => "התנהלות כלכלית",
            "family_referral_r3" => "חובות כבדים",
            "family_referral_r4" => "הכנסות נמוכות",
            "family_referral_r2" => "אבטלה או תעסוקה לא איכותית",
            "family_referral_r5" => "מצב משפחתי רעוע",
            "family_referral_r6" => "קשיים של הילדים"
        );
        $tarray = array();
        foreach ($histogram as $k => $v)
            $tarray[$translate[$k]] = $v;

        $arg = array ('type' => $type, 'total' => $i, 'table' => $tarray	);
        //error_log($type. " historgram, total=". $i);
        ////error_log(print_r( $histogram, true ));
        ////error_log(print_r( $tarray, true ));
        //return "Result generated"; // $json['body']['data'];
        return $arg;
    }

?>