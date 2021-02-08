<?php
// Report program
    add_shortcode("report-program", 'mbo_report_program'); // show all meta data for cpt
    function mbo_report_program(){
        // if (($s = mbo_permissions()) != "ok") return $s;
        
        // get data from database
        $programCpt = get_posts( array('post_type' => 'digma_families', 'posts_per_page' => -1, 'status' => 'publish') );
        if (count($programCpt) < 1)
            return "No post of type= ". $type ." found";

        // initializate page display
        $str = mbo_init_page_display();
        
        // init once all family fields for histograms
        $programCpt_fieldGroups = array();
        foreach ( $programCpt as $cpt )
            $programCpt_fieldGroups[$cpt->ID] = get_fields($cpt->ID);

        // Display title
        $str .= mbo_report_header('דו"ח כל המשפחות בתכנית ולאחריה<br />נכון לתאריך:', count($programCpt));

        //$str .= '<h2 id="report-title" class="report-title" >דו"ח משפחות בתכנית נכון לתאריך: '.date("j/n/Y").'<br />'.'סה"כ משפחות: ' . 
        //            count($programCpt) . "</h2>";
        // Display sub title 
        $str .= '<p class="report-title">(נכללות משפחות בתכנית, בוגרות ומסיימות)</p>';
        // Display  Menu
        $program_topics = array("סטטוס משפחות" => 'sec-program_status',
                                "תאריך כניסה לתכנית" => 'sec-program_date',
                                "מלווה יהב" => 'sec-intake_yahav_member', // yes. its 'intake_yahav_member'
                                "מספר הילדים במשפחה" => 'sec-kids_number', 
                                "סטטוס זוגיות" => 'sec-mertial_status', 
                                "לקוחות הרווחה" => 'sec-family_in_revaha', 
                                "השתתפות בתכניות אחרות" => 'sec-other_programs', 
                                "התפלגות השתתפות בתכניות אחרות" => 'sec-other_programs', 
                                "צורך ביעוץ משפטי" => 'sec-need_legal', 
                                "התפלגות סוג הדיור" => 'sec-family_house', 
                                "זקוקים לשיפור במצב המגורים" => 'sec-family_house', 
                                "התפלגות עלויות צרכים ומענים למשפחה" => 'sec-needsolution_cost', 
                                "התפלגות מקורות מימון צרכים ומענים למשפחה" => 'sec-needsolution_src', 
                            );

        $str .= mbo_report_menu($program_topics);

        // Display list of all histogram graphs program_status
        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "program_status", 'program_info', "סטטוס משפחות",
        "סטטוס בתכנית ולאחריה", "מספר משפחות");

        $program_date = mbo_date_histogram($programCpt, $programCpt_fieldGroups, "families", "program_date", 'program_info');
        $str .= draw_histogram("תאריך כניסה לתכנית", $program_date, "program_date",
        "תאריך כניסה לפי רבעונים", "מספר משפחות");

        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "intake_yahav_member", 'program_info', "מלווה יהב",
        "שם מלווה התכנית", "מספר משפחות");

        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "kids_number", 'program_info', "מספר הילדים במשפחה",
        "מספר הילדים", "מספר משפחות");
        
        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "mertial_status", 'program_info', "סטטוס זוגיות",
        "סטטוס זוגיות", "מספר משפחות");

        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "family_in_revaha", 'program_info', "לקוחות הרווחה",
        "תשובות כן/לא", "מספר משפחות");
        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "other_programs", 'program_info', "השתתפות בתכניות אחרות",
        "תשובות כן/לא", "מספר משפחות");
        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "other_programs_list", 'program_info', "התפלגות השתתפות בתכניות אחרות",
        "שמות תכניות אחרות", "מספר משפחות");
        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "need_legal", 'family_legal', "צורך ביעוץ משפטי",
        "תשובות כן/לא", "מספר משפחות");
        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "family_house", 'family_housing', "התפלגות סוג הדיור",
        "שמות סוגי הדיור", "מספר משפחות");
        
        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "family_house_status", 'family_housing', "זקוקים לשיפור במצב המגורים",
        "תשובות כן/לא", "מספר משפחות");
        
        $program_ns_sum = mbo_sumsubtable_histogram($programCpt, $programCpt_fieldGroups, "families", 'needsolution_cost', "needsolution_array");
        $str .= draw_histogram("התפלגות עלויות צרכים ומענים למשפחה", $program_ns_sum, "needsolution_cost",
        "סכומי עלויות למשפחה", "מספר משפחות לפי גובה העלות");

        $program_ns_src = mbo_sumsubtable_src_histogram($programCpt, $programCpt_fieldGroups, "families", 
                                    null /* key filed ignored*/, "needsolution_array");
        $str .= draw_histogram("התפלגות מקורות מימון צרכים ומענים בכל השנים", $program_ns_src, "needsolution_src",
        "מקורות מימון", "גובה מימון מצטבר");

        $str .= "<div class='end-report'><h2>סוף הדו''ח</h2></div>";

        return '<div style="margin:-50px auto">' . $str . '</div>';
        //return displayBar($family_referral_status);
    }
 
?>