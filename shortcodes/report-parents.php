<?php
// Report parents
    add_shortcode("report-parents", 'mbo_report_parents'); // show all meta data for cpt
    function mbo_report_parents(){
        // if (($s = mbo_permissions()) != "ok") return $s;
        
        // get data from database
        $programCpt = get_posts( array('post_type' => 'digma_parents', 'posts_per_page' => -1, 'status' => 'publish') );
        if (count($programCpt) < 1)
            return "No post of type= ". $type ." found";

        // initializate page display
        $str = mbo_init_page_display();
        
        // init once all family fields for histograms
        $programCpt_fieldGroups = array();
        foreach ( $programCpt as $cpt )
            $programCpt_fieldGroups[$cpt->ID] = get_fields($cpt->ID);

        // Display title
        $str .= mbo_report_header('דו"ח כלל ההורים נכון לתאריך:', count($programCpt), "הורים");

        //$str .= '<h2 id="report-title" class="report-title" >דו"ח משפחות בתכנית נכון לתאריך: '.date("j/n/Y").'<br />'.'סה"כ משפחות: ' . 
        //            count($programCpt) . "</h2>";
        // Display sub title 
        $str .= '<p class="report-title">(נכללים הורים ממשפחות בתכנית, בוגרות ומסיימות)</p>';
        // Display  Menu
        $program_topics = array("סטטוס מגדר" => 'sec-parent_gender',
                                "אורח חיים" => 'sec-parent_social_section',
                                "סטטוס זוגיות" => 'sec-parent_merital_status',
                                "ארץ מוצא" => 'sec-parent_birth_country',
                                "סטטוס בריאות" => 'sec-parent_health',
                                "קופת חולים" => 'sec-parent_kupat_holim',
                                "התפלגות שנות לימוד" => 'sec-year_of_study',
                                "התפלגות מקצועות" => 'sec-profession',
                                "הכשרה מקצועית" => 'sec-professional',
                                "מצב תעסוקה" => 'sec-employ_status',
                                "בעלי תעודת בגרות" => 'sec-bagroot',
                                "בעלי תעודה אקדמית" => 'sec-academic_education',
                                "בעלי תעודה מהרבנות" => 'sec-secular_education',
                            );

        $str .= mbo_report_menu($program_topics);

        // Display list of all histogram graphs program_status
        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "parents", "parent_gender", 'parent_info', "סטטוס מגדר",
        "מגדר", "מספר הורים");

        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "parents", "parent_social_section", 'parent_info', "אורח חיים",
        "אורח חיים", "מספר הורים");

        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "parents", "parent_merital_status", 'parent_info', "סטטוס זוגיות",
        "סטטוס זוגיות", "מספר הורים");

        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "parents", "parent_birth_country", 'parent_info', "ארץ מוצא",
        "ארץ מוצא", "מספר הורים");

        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "parents", "parent_health", 'parent_hstatus', "סטטוס בריאות",
        "סטטוס בריאות", "מספר הורים");

        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "parents", "parent_kupat_holim", 'parent_hstatus', "קופת חולים",
        "קופת חולים", "מספר הורים");
        
        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "parents", "year_of_study", 'parent_education', "התפלגות שנות לימוד",
        "שנות לימוד", "מספר הורים");

        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "parents", "profession", 'parent_education', "התפלגות מקצועות",
        "סוגי מקצועות", "מספר הורים");

        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "parents", "professional", 'parent_education', "הכשרה מקצועית",
        "סוגי מקצועות", "מספר הורים");

        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "parents", "employ_status", 'parent_employment', "מצב תעסוקה",
        "מצב תעסוקה", "מספר הורים");

        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "parents", "bagroot", 'parent_education', "בעלי תעודת בגרות",
        "סטטוס תעודת בגרות", "מספר הורים");

        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "parents", "academic_education", 'parent_education', "בעלי תעודה אקדמית",
        "סטטוס תעודת אקדמית", "מספר הורים");

        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "parents", "secular_education", 'parent_education', "בעלי תעודה מהרבנות",
        "סטטוס הסמכה מהרבנות", "מספר הורים");
        
/*        
        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "kids_number", 'program_info', "מספר הילדים במשפחה",
        "מספר הילדים", "מספר משפחות");

        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "family_in_revaha", 'program_info', "לקוחות הרווחה",
        "תשובות כן/לא", "מספר משפחות");
        
        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "other_programs_list", 'program_info', "התפלגות השתתפות בתכניות אחרות",
        "שמות תכניות אחרות", "מספר משפחות");
        
        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "family_house", 'family_housing', "התפלגות סוג הדיור",
        "שמות סוגי הדיור", "מספר משפחות");
        
        $program_ns_sum = mbo_sumsubtable_histogram($programCpt, $programCpt_fieldGroups, "families", 'needsolution_cost', "needsolution_array");
        $str .= draw_histogram("התפלגות עלויות צרכים ומענים למשפחה", $program_ns_sum, "needsolution_cost",
        "סכומי עלויות למשפחה", "מספר משפחות לפי גובה העלות");

        $program_ns_src = mbo_sumsubtable_src_histogram($programCpt, $programCpt_fieldGroups, "families", 
                                    null /* key filed ignored* /, "needsolution_array");
        $str .= draw_histogram("התפלגות מקורות מימון צרכים ומענים בכל השנים", $program_ns_src, "needsolution_src",
        "מקורות מימון", "גובה מימון מצטבר");
*/
        $str .= "<div class='end-report'><h2>סוף הדו''ח</h2></div>";

        return $str;
    }
 
?>