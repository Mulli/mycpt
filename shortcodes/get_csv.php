<?php

// read csv file from MS excel save as csv UTF8 with '^' as delimeter
// remove all \" double quotes - somehow it breaks the parsing to too many lines
// OPTION - get also number of columns to overcome this issue
// [get-csv file_name="<under uploads/yahav/..."  entity="<family|parents|kids...>" entity_type="<field group>" ]
define ('DELIMETER_CSV', '^');
// שאלון עמדות: https://digma.me/get-csv2/?csv=2019/04/hayahav-a-d-amadotqP.csv&gender=w&part=amadotq
//  מוצרים ושירותים: https://digma.me/get-csv2/?csv=2019/04/hayahav-a-d-ps.csv&part=products-services
//  דיור: https://digma.me/get-csv2/?csv=2019/04/hayahav-a-d-ps.csv&part=housing
//  משפטי: https://digma.me/get-csv2/?csv=2019/04/hayahav-a-d-ps.csv&part=legal
//  ילדים: https://digma.me/get-csv2/?csv=2019/04/hayahav-a-d-kids.csv&part=kids
//  גבר כללי: https://digma.me/get-csv2/?csv=2019/04/hayahav-a-d-men.csv&gender=m&part=men
//  גבר השכלה: https://digma.me/get-csv2/?csv=2019/04/hayahav-a-d-men.csv&gender=m&part=education
//  גבר תעסוקה: https://digma.me/get-csv2/?csv=2019/04/hayahav-a-d-men.csv&gender=m&part=mwork
//  אורח חיים: https://digma.me/get-csv2/?csv=2019/05/hayahav-a-d-pgeneral.csv&part=pgeneral
//  אשה תעסוקה: https://digma.me/get-csv2/?csv=2019/05/hayahav-a-d-pgeneral.csv&part=wwork
//  אשה לא מעסקת: https://digma.me/get-csv2/?csv=2019/05/hayahav-a-d-pgeneral.csv&part=w_notemployed -- woman only, man is ok.
//  הכנסות: https://digma.me/get-csv2/?csv=2019/05/hayahav-a-d-pgeneral.csv&part=income -- woman only, man is ok.
//  הוצאות: https://digma.me/get-csv2/?csv=2019/05/hayahav-a-d-pgeneral.csv&part=expenses -- woman only, man is ok.
//  החזר חובות: https://digma.me/get-csv2/?csv=2019/05/hayahav-a-d-pgeneral.csv&part=returnloan -- woman only, man is ok.
//  אינטייק: https://digma.me/get-csv2/?csv=2019/05/hayahav-a-d-pgeneral.csv&part=intake -- woman only, man is ok.

add_shortcode('get-csv2','digma_get_csv2');
function digma_get_csv2($atts ) {
	$a = shortcode_atts( array(
		'file_name' => '',
		'entity_type' => 'digma_families',
		'num_cols' => 'unused-now-TBD',
		'print_csv' => 0
	), $atts );
	$gender ="";
	$part = "";
	// get input files name
	if (!isset($_GET['csv']))
		return "file name missing,<br />usage: get-csv2?csv=<upload-dir>/file-name.csv<br />";
	if (isset($_GET['gender']))
		$gender = $_GET['gender'];
	if (isset($_GET['part']))
		$part = $_GET['part'];

	$upload_dir = wp_upload_dir();
	$csvfile = $upload_dir['basedir']. "/". $_GET['csv'];

	// read input files name
	$allRows = array();
	$allRows = read_csvfile($csvfile, $allRows, 59);
	if ($allRows == null)
		return "Fail to open file ". $csvfile . " pleaseverify that its loaded<br />";

	$lines = count($allRows);
	if ($lines < 0)
		return "<p>Fail to read lines " . $csvfile . "<br /></p>\n";
	//error_log("#".__LINE__ .">> lines=". $lines. " table=" .print_r($allRows, true));

	// find start column for man/woman in input csv line #2
	$filters = array("housing" => "דיור ציבורי (כגון, עמידר)", 'products-services' => "מוצרים ושירותים", 'legal' => 'סיוע משפטי',
							'kids' => 'ילדים', 'men' => 'גבר כללי', 'education' => 'גבר השכלה', 'mwork' => 'גבר מקוםעבודה',
							'wwork' => 'אשה מקוםעבודה',	'w_notemployed' => 'אשה מקוםעבודה' , 'pgeneral' => 'הורה מגזר',
							'debts_status' => 'מצבחובות','expenses' => 'הוצאות','savings' => 'חסכונות', 
							'returnloan' => 'החזר חובות', 'income' => 'הכנסות', 'intake' => 'דמי');
				// 'w_notemployed' - dummy value
	if (isset($filters[$part])){
		for ($j=0; $j < count($allRows[1]); $j++)
			if ($allRows[1][$j] == $filters[$part])
				break;
		$qStart = $j;
	} else
		return "Not ready for this: " . $part . ", analysis yet!!!<br />";
	//verify family names
	$str ="";
	switch ($part){
		case 'pgeneral': // update both parents social section
			$str .= update_orach_haim($allRows, $qStart, "parent_info", "parent_social_section"); break;
		case 'wwork': // woman work
			$str .= update_wwork($allRows, 36, "parent_employment", ""); break;
		case 'w_notemployed': // 
			$str .= update_w_notemployed($allRows, 45, "parent_notemployed", ""); break;
		case 'income': // 
			$str .= update_income($allRows, 187, "family_income", ""); break;
		case 'debts_status': // 
			$str .= update_debts_status($allRows); break;
		case 'savings': // 
			$str .= update_savings($allRows); break;
		case 'expenses': // 
			$str .= update_expenses($allRows); break;
		case 'returnloan': // 
			$str .= update_returnloan($allRows); break;
		case 'intake': // 
			$str .= update_intake($allRows); break;
		default:
			break;
	}
/*
	for ($i = 4 ; $i < count($allRows) ; $i++){ // skip csv first 4 lines
		//if ($i>6) break;
		//error_log(print_r($allRows[$i], true));
		switch ($gender){
			case 'w':	$fname = getWomenCPTname($allRows[$i], 1); break;
			case 'm':	$fname = getMenCPTname($allRows[$i], 1); break;
			default:	$fname = getFamilyCPTname($allRows[$i], 1); break;
		}
		// error_log($fname);
		if ($fname == ""){
			$str .= $fname . "חסר שם משפחה או הורה: שם לא תקין. בשורה " . ($i+1) . " בקובץ אקסל<br />";
			continue;
		}
		// 1. find Family/ parent CPT 
		// 2. decide family or if parent find man / woman CPT - GIVEN WOMAN
		if ($gender == "") // family
			$cpt = get_page_by_title($fname, OBJECT, 'digma_families');
		else $cpt = get_page_by_title($fname, OBJECT, 'digma_parents');

		if ($cpt == null){
			$str .= $fname . " קוד =לא נמצא בשורה ". ($i+1) . " בקובץ אקסל<br />";
			continue;
		}
		//else $str .= $fname . "  קוד =". $cpt->ID . "<br>";
		// 3 get all fields
		$x = get_fields($cpt->ID);
		$curline = $allRows[$i];
		//error_log($i. " qstart=". $qStart);
		//if ($curline[$qStart] == "" && $curline[$qStart+1] == "") continue;
		$j = $qStart;
		$pp = array();
		$pp["employ_status"] = $curline[$j];
		$pp["employ_prev_places"] = $curline[$j+1];
		$pp["employ_cap_hours"] = $curline[$j+2];
		$pp["employ_main"] = $curline[$j+3];
		$pp["employ_role"] = $curline[$j+4];  // xl col=108
		//$pp["employ_quality"] = $curline[$j];
		//$pp["employ_current_place"] = $curline[$j];
		$pp["employ_cap_percent"] = $curline[$j+6];
		if ($curline[$j+7] == "קבועה (גם אין אין קביעות)")
			$pp["employ_social"] = "קבועה (גם אם אין קביעות)";
		else		
			$pp["employ_social"] = $curline[$j+7];
		$td = array("6 - 10 שנים" => 96,"עד שנה" => 10, "עד חצי שנה" => 4, "שנתיים עד 5 שנים" => 42, "למעלה מ 10 שנים" => 120);
		if ($curline[$j+5] != "" && isset($td[$curline[$j+5]]))
			$pp["employ_duration"] = $td[$curline[$j+5]];
		else {
			if ($curline[$j+5] != "")
				error_log("work duration - missing definition=".$curline[$j+5]);
			$pp["employ_duration"] = "";
		}
		//$pp["employ_bruto"] = $curline[$j];
		//$pp["employ_neto"] = $curline[$j];
		$pp["employ_comments"] = "חסרים נתוני ברוטו/נטו באקסל"; //$curline[$j];
		if ($curline[$j+50] == ""){
			$pp["employ_interest_inchange"] = "";
			$pp["employ_i1"] = "";
			$pp["employ_i2"] = "";
			$pp["employ_i3"] = "";
			$pp["employ_i4"] = "";
			$pp["employ_i5"] = "";
			$pp["employ_i6"] = "";
		} else {
			$pp["employ_interest_inchange"] = ($curline[$j+50] == "לא מעוניין לעשות שינוי בעבודה" || $curline[$j+50] == "1") ? 
				"לא מעוניין לעשות שינוי בעבודה" : "כן";
			$pp["employ_i1"] = strpos("מעוניין למצוא מקום עבודה", $curline[$j+50]) !== false ? "כן" : "לא";
			$pp["employ_i4"] = strpos("מעוניין לשדרג את העבודה שלי", $curline[$j+50]) !== false ? "כן" : "לא";
			$pp["employ_i3"] = strpos("מעוניין להחליף מקום עבודה", $curline[$j+50]) !== false ? "כן" : "לא";
			$pp["employ_i2"] = strpos("להוסיף עוד מקום עבודה", $curline[$j+50]) !== false ? "כן" : "לא";
			$pp["employ_i5"] = strpos("לא מעוניין לעשות שינוי בעבודה", $curline[$j+50]) !== false ? "כן" : "לא";
			$pp["employ_i6"] = "";
		}
		$pp["employ_new_buisness"] =$curline[$j+51];
		$pp["employ_new_buis_other"] = "";
		if ($curline[$j+8] == "")
			$pp["employ_last3y"]  = $curline[$j+8];
		else if ($curline[$j+8] == "במקום אחד")
			$pp["employ_last3y"]  = 1;
		else if ($curline[$j+8] == "בשני מקומות")
			$pp["employ_last3y"]  = 2;

		$pp["employ_volunteer"] = $curline[$j+52];
		error_log($fname . "mwork =". print_r($pp, true));
		//$str .= $fname . " שנותלימוד: ". $curline[$j]. "<br>";
		update_field("parent_employment", $pp, $cpt->ID);
END EMPLOYMENT */
/* Parent education
		$pe = array ();
		$pe["id"] = "1";
		$pe["year_of_study"] = $curline[$j];
		$pe["bagroot"] = "";
		$pe["professional"] = $curline[$j+1];
		$pe["academic_education"] = $curline[$j+2];
		$pe["secular_education"] = $curline[$j+3];
		$pe["profession"] = $curline[$j+4];
		$pe["comment"] = "";
		error_log($fname . "education =". print_r($pe, true));
		//$str .= $fname . " שנותלימוד: ". $curline[$j]. "<br>";
		update_field("parent_education", $pe, $cpt->ID);
*/
/* Men parent general info & health below		
		$meng = array();
		//$meng["parent_family_name"] =  $curline[$j];
		$meng["parent_first_name"] = $curline[$j];
		$meng["parent_gender"] = $curline[$j+2];
		$meng["parent_idnum"] = $curline[$j+1];
		if ($curline[$j+3] != "" && $curline[$j+3][0] == '5')
			$meng["parent_phone1"] = "0".$curline[$j+3];
		else if ($curline[$j+3] != "" && $curline[$j+3][0] == '\\'){
			$curline[$j+3][0] = ' ';
			$meng["parent_phone1"] = $curline[$j+3];
		} else $meng["parent_phone1"] = $curline[$j+3];
		$meng["parent_email"] = $curline[$j+4];
		$meng["parent_birth_date"] = $curline[$j+5];
		$meng["parent_birth_country"] = $curline[$j+6];
		$meng["parent_immigration_year"] = $curline[$j+7];
		$meng["parent_social_section"] = $curline[$j+8];
		//$meng["comment"] = "";
//"parent_hstatus": 
		$ph = array();
		$ph["id"] = "1";
		$ph["parent_health"] = "";
		$ph["parent_bituachleumi"] = "";
		$ph["parent_bituachleumi_temp"] = "";
		$ph["parent_kupat_holim"] = "";
		$ph["comment"] = "";
		
		if ($curline[$j+9] == "")
			$ph["parent_health"] = $curline[$j+9];
		else {
			$hoptions = array("אין בעיות מיוחדות","יש מחלה כרונית","קיימות מגבלות פיזיות","קיימות בעיות בריאותיות אחרות","אחר");
			if (in_array($curline[$j+9] , $hoptions))
				$ph["parent_health"] = $curline[$j+9];
			else {
				$ph["parent_health"] = "אחר";
				$ph["comment"] .= $curline[$j+9].", ";

				error_log($fname . "add comments =". $curline[$j+9]);
			}
		}
		if ($curline[$j+10] == "")
			$ph["parent_bituachleumi"] = $curline[$j+10];
		else {
			$loptions = array ("לא","כן, בדרגה של 60%","כן, בדרגה של 65%","כן, בדרגה של 74%","כן, בדרגה של 100%","אחר");
			if (in_array($curline[$j+10] , $loptions))
				$ph["parent_bituachleumi"] = $curline[$j+10];
			else {
				if ($curline[$j+10] == "1")
					$ph["parent_bituachleumi"] = "לא";
				else{
					$ph["parent_bituachleumi"] = "אחר";
					$ph["comment"] .= $curline[$j+10].", ";
					error_log($fname . "add comments =". $curline[$j+10]);
				}
			}
		}
		$ph["parent_bituachleumi_temp"] = $curline[$j+11];
		$ph["parent_kupat_holim"] = $curline[$j+12];
		error_log($fname . "info =". print_r($meng, true));
		error_log($fname . "health =". print_r($ph, true));

		update_field("parent_info", $meng, $cpt->ID);
		update_field("parent_hstatus", $ph, $cpt->ID);
*/
/*	kids	
		$j = $qStart;
		$kidParents = array("לשני ההורים","לאמא","לאבא","להורים גרושים","אחר"); 
		$kp["בן/ בת לשני בני הזוג"]  = "לשני ההורים";
		$kp["בן/ בת לשני בני הזוג, הורים בתהליך גירושין"]  = "לשני ההורים"; // + comment ההורים בתהליכי גירושין
		$kp["בת להורים גרושים"] = "להורים גרושים";
		$kp["הורים גרושים"] = "להורים גרושים";
		$kp["ההורים בתהליכי גירושין"] = "לשני ההורים"; // + comment ההורים בתהליכי גירושין
		$kp["בן/ בת לאחד מבני הזוג"] = "אחר"; // + commentבן/ בת לאחד מבני הזוג 
		$kp["בת להורים גרושים, האב לא משתתף בתכנית"] = "להורים גרושים";
		do {

			if ($j == $qStart){
				$karray = array("id" => $j, "kid_family_name" => $allRows[$i][1], 
					"kid_first_name" => $curline[$j], "kid_birthdate" => $curline[$j+1], 
					"kid_gender" => $curline[$j+2], "kid_parents" => $curline[$j+3] == "" ? "" : $kp[$curline[$j+3]] , 
					"kid_misgeret" => $curline[$j+4], 
					"kid_comment" => $curline[$j+3] == "בן/ בת לאחד מבני הזוג" ? "בן/ בת לאחד מבני הזוג" 
						: $curline[$j+3] == "ההורים בתהליכי גירושין" || $curline[$j+3] == "בן/ בת לשני בני הזוג, הורים בתהליך גירושין"
						? "ההורים בתהליכי גירושין" : "" );
					$j += 6;
			} else {
				$j += 1;
				$karray = array("id" => $j, "kid_family_name" =>  $curline[$j], 
					"kid_first_name" => $curline[$j+1], "kid_birthdate" => $curline[$j+2], 
					"kid_gender" => $curline[$j+3], "kid_parents" => $curline[$j+4] == "" ? "" : $kp[$curline[$j+4]] ,  
					"kid_misgeret" => $curline[$j+5], 
					"kid_comment" => $curline[$j+4] == "בן/ בת לאחד מבני הזוג" ? "בן/ בת לאחד מבני הזוג" 
						: $curline[$j+4] == "ההורים בתהליכי גירושין" || $curline[$j+4] == "בן/ בת לשני בני הזוג, הורים בתהליך גירושין"
						? "ההורים בתהליכי גירושין" :  $curline[$j+4] == "בת להורים גרושים, האב לא משתתף בתכנית" ? "האב לא משתתף בתכנית" : "");
					$j += 7;
			}
			$k = add_row( "family_kids", $karray, $cpt->ID );
			error_log($fname . "Add Row result=". $k . "  Kid=". print_r($karray, true));
		} while ($curline[$j] == 2); // index for more kids
		*/
/* legal
		$legal = array("need_legal" => $curline[$qStart] == 2 ? "כן" : "לא",
					"legal_explain" =>  $curline[$qStart+1]);
		//error_log($fname . "  V=". print_r($legal, true));
		update_field("family_legal", $legal, $cpt->ID);
*/
/* housing
		$appValues = array("דירה בבעלותנו","אצל ההורים","דיור ציבורי","בשכירות","דמי מפתח", "סיוע בשכ''ד משרד השיכון","אחר");
		$diurmap = array();
		$diurmap[1] = $appValues[3];
		$diurmap[2] = $appValues[1];
		$diurmap[4] = $appValues[0];
		$diurmap[3] = $appValues[2];
		$diurmap[5] = $appValues[5];
		$j = $qStart;
		$diur = array(	
			"family_house" => $diurmap[$curline[$j+1]],
			//"family_persons_athome": "",
			"family_room_num" => $curline[$j+2],
			"family_house_status" => $curline[$j+3] == 2 ? "כן" : "לא",
			"family_housing_comment" => $curline[$j+4]
		);
		error_log($fname . "  V=". print_r($diur, true));
		update_field("family_housing", $diur, $cpt->ID);
*/
/*
		$psOptions = array( "1" => "קשה מאוד", "2" => "קשה", "3" => "די קל", "4" => "קל", "5" => "קל מאוד");
		// כאמור גרים אצל ההורים
		// מזרן טוב לבעיות גב, אוכל בצמצום גדול מאוד, חסר ציוד לעסק הפרטי הממוקם בסטודיו שמאחורי הבית, היו חסרים פרטי ריהוט וציוד שונים
		$ps = array("q101" => "רכישת תרופות",
			"q102" => "טיפולי שיניים",
			"q103" => "עזרים רפואיים",
			"q104" => "ביקור אצל רופא",
			"q105" => "בדיקות",
			"q106" => "טיפולים רפואיים",
			"q107" => "טיפולים פארה-רפואיים",
			"q108" => "טיפולים התפתחותיים / רגשיים",
			"q109" => "לא ויתרנו על שירות כלשהו בגלל העלות הכספית שלו",
			"q110" => "אחר",
			"q201" => "מקרר",
			"q202" => "כיריים לבישול",
			"q203" => "תנור אפיה",
			"q204" => "מכונת כביסה",
			"q205" => "תנור / אמצעי לחימום",
			"q206" => "דוד חימום / דוד שמש",
			"q207" => "מיטה לכל אחד מבני המשפחה",
			"q208" => "ארונות",
			"q209" => "שמיכות חורף",
			"q210" => "פרטי מטבח מרכזיים לבישול/אפיה/אכילה",
			"q211" => "ביגוד והנעלה",
			"q212" => "ספרי קריאה וצעצועים לילדים",
			"q213" => 'ציוד לבי""ס לכל ילד',
			"q214" => "מוצרי יסוד כגון",
			"q215" => "יש את כל הדברים שצויינו ברשימה",
			"q31" => "מחשב",
			"q32" => "חיבור פעיל לאינטרנט",
			"q33" => "מכונית",
			"q401" => "מקומות העבודה",
			"q402" => "מעונות / משפחתונים / מטפלת",
			"q403" => "גני ילדים",
			"q404" => "בתי ספר",
			"q405" => "שוק / סופרמרקט",
			"q406" => "קופת חולים",
			"q407" => "בית מרקחת",
			"q408" => "שירותי בריאות אחרים",
			"q409" => 'מועדון / מרכז חוגים /מתנ"ס / מינהל קהילתי',
			"q410" => "בני משפחה קרובים",
			"q411" => "חברים",
			"q412" => "מרכזי קניות",
			"q413" => "אחר",
			"comments" => "הערות"
		);
		$product_services = array("q101" => "לא","q102" => "לא",  "q103" => "לא", "q104" => "לא", "q105" => "לא", "q106" => "לא",
					"q107" => "לא",	"q108" => "לא",	"q109" => "לא",	"q110" => "לא",	"q201" => "לא",	"q202" => "לא",	"q203" => "לא",
					"q204" => "לא",	"q205" => "לא",	"q206" => "לא",	"q207" => "לא",	"q208" => "לא",	"q209" => "לא",	"q210" => "לא",
					"q211" => "לא",	"q212" => "לא",	"q213" => "לא",	"q214" => "לא", "q215" => "לא",	"q31" => "לא",	"q32" => "לא",
					"q33" => "לא", "q401" => "", "q402" => "", "q403" => "", "q404" => "", "q405" => "", "q406" => "", "q407" => "",
					"q408" => "", "q409" => "",	"q410" => "", "q411" => "",	"q412" => "", "q413" => "",	"comments" => "");
		$list="";
		for ($j = $qStart; $j < ($qStart+15); $j++){
			//error_log(print_r($allRows[2],true));
			$ii = $allRows[2][$j];
			if ($allRows[0][$j] == "בחירה מרובה"){
				$ms = explode(',',$curline[$j]);
				//error_log("Start MS array =". print_r($ms, true));
				foreach ($ms as $msi){ // forach item in input value
					//error_log("MSI =".$msi. "  ====== start inner loop");
					$valueFound=0;
					if ($msi == "") continue;
					foreach ($ps as $k => $v){ // item in array
						//error_log("msi =".$msi. "  V=". $v);
						if (strpos($msi, $v) !== false){ // set & done
							$product_services[$k] = "כן"; // add $k to array with 'כן'
							//error_log("FOUND>>>NEW K=". $k. " msi =".$msi. "  V=". $v);
							$valueFound=1;
							break;
						}
					}
					if (!$valueFound ) {// filter comma delimeter values, ifstill not found - complain
						if (!(strpos($msi,"משקפיים") !== false || strpos($msi,"אוכל מיוחד") !== false || strpos($msi,"ויטמינים וכדומה)") !== false 
						 || strpos($msi,"מוצרי חלב") !== false || strpos($msi,"פירות") !== false || strpos($msi,"ירקות") !== false))
							$product_services["comments"] .= $msi . ", ";
						else error_log("NOT FOUND>> msi =".$msi. "  V=". $v. "  input line=". $i);
					}
				} 
			} else $product_services[$allRows[2][$j]] = $curline[$j] == "" ? "" : $psOptions[$curline[$j]];
			
			//$amadot[$ii] = $curline[$j] == "" ? "" : $amadotOptions[$curline[$j]];
		}
		error_log(print_r($product_services, true));
		*/
/*
		$amadot = array(
			"id"=> 1, "interviewer"=> "", "date"=> "", "q1"  => "", "q2"  => "", "q3"  => "", "q4"  => "", "q5"  => "", 
			"q6"  => "", "q7"  => "", "q8"  => "", "q9"  => "", "q10"  => "", "q11"  => "", "q12"  => "", "q13"  => "", 
			"q14"  => "", "q15"  => "", "q16"  => "", "q17"  => "", "q18"  => "", "q19"  => "", "q20"  => "", "q21"  => "", 
			"q22"  => "", "q23"  => "", "q24"  => "", "q25"  => "", "q26"  => "", "q27"  => "", "q28"  => "", "s1"  => "", 
			"s2"  => "", "s3"  => "", "s4"  => "", "s5"  => "", "s6"  => "", "s7"  => "", "s8"  => "", "s9"  => "", "s10"  => "", 
			"s11"  => "", "s12"  => "", "r1"  => "", "r2"  => "", "r3"  => "", "r4"  => "", "r5"  => "", "comments"=> "");
		$amadotOptions = array("1"=>"1-כלל לא", "2"=>"2-במידה מעטה מאוד", "3"=>"3- במידה מעטה","4"=>"4-במידה בינונית", "5"=>"5-במידה רבה",
		"6"=>"6-במידה רבה מאוד");
		$amadot["interviewer"]=$curline[4];
		$amadot["date"]=$curline[6];
		for ($j = $qStart; $j < ($qStart+count($amadot)-4); $j++){
			//error_log(print_r($allRows[2],true));
			$ii = $allRows[2][$j];
			$amadot[$ii] = $curline[$j] == "" ? "" : $amadotOptions[$curline[$j]];
		}
		error_log("#".__LINE__ .">> fname=". $fname. " table=" .print_r($amadot, true));

		update_field("questionnaire_pre", $amadot, $cpt->ID);
*/
		//update_field("family_products_services", $product_services, $cpt->ID);
/* EMPLOYMENT
		$str .= " בוצע <b>" . $fname . "  קוד =". $cpt->ID . "</b><br>";

	}
EMPLOYMENT */
	//$getWomenCPTname($family,$col);
	// parse input files name
	
	// generate DB field names
	// start @ line=i,col=j
	// find parent 	1>family 2>woman 3> man
	// $x1 = chr(ord('A')+intval($qStart / 26)) . chr(ord('A')+intval($qStart % 26));
	$ret = "קבץ קלט <b>" . $csvfile . "</b> נקראו <b>" . $lines . "</b> שורות.";
	if ($gender != ""){
		if ($gender == 'w')
			$gen = 'אשה';
		else $gen = 'גבר';
	} else $gen = 'משפחה';
	$ret .= "עמודות  " . $filters[$part] . " (<b>". $gen . "</b>) = " . $qStart . "====<br />";
	return  $ret . "רשימת השמות:<br /><br />" . $str;
}

function read_csvfile($fname, $allRows, $lineLimit){
	if (($handle = fopen( $fname, "r")) === FALSE) 
		return null;

	for ($row = 1; ($line = fgets( $handle)) !== FALSE && $row < $lineLimit; $row++){
		$data = explode( DELIMETER_CSV, $line );
		
		$allRows[$row-1] = $data;
	}
	
	fclose($handle);
	return $allRows; // number of lines read
}
add_shortcode('get-csv','digma_get_csv');
function digma_get_csv($atts ) {
	$a = shortcode_atts( array(
		'file_name' => '',
		'entity_type' => 'digma_families',
		'num_cols' => 'unused-now-TBD',
		'print_csv' => 0
	), $atts );

	if (empty($a['file_name']))
		return "file name missing";
	$str = "";
	$allRows = array();
	$row = 1;
	$upload_dir   = wp_upload_dir();
	$fname = $upload_dir['basedir'] . $a['file_name'];
	if (($handle = fopen( $fname, "r")) !== FALSE) {
		$str ="<h1>Reading file " . $fname . "(". $a['entity_type'] . ") </h1>";
	//    while (($data = fgetcsv($handle, 5000, "^")) !== FALSE) {
		while (($line = fgets( $handle, 5000 )) !== FALSE){
			$data = explode( DELIMETER_CSV, $line );
			// error_log(print_r($line, true));
			$allRows[$row-1] = $data;
			$row += 1;
			/*
			if ($a['print_csv']){
		        $num = count($data);
		        $str .= "<p> $num fields in line $row: <br /></p>\n";
		        $row++;
		        for ($c=0; $c < $num; $c++){
		        	 if (empty($data[$c])) continue;
		             $str .= "(" . $c . "):  " . $data[$c] . "<br />\n";
		        }
		    }else $row++;

            //$str .= add_new_fg($data, $row, $a['entity_type'], ""); add new Field Group - not needed
            if ($a['print_csv'])
				$str .= $line;
			*/
	    }
    } else 
    	return "<p>Fail to open " . $fname . "<br /></p>\n";
	fclose($handle);
	//error_log(print_r($allRows, true));

	$header = array(array('cpt_type', 'table', 'subtable', 'field')) ;
	// read dictionary from 4 lines header
	//error_log(print_r($allRows[0],true));
		
	$cpt_type = array();
	$cpt_type['נתוני משפחה'] = 'digma-families';
	$cpt_type['נתוני אישה'] = 'digma-parents';
	$cpt_type['נתוני גבר'] = 'digma-parents';

	//$fg = array('מידע כללי' => 'program-info');
	// Map header line 1 to fg
	$fg = array();
	$fg['מידע כללי'] = 'program_info';
	$fg['מצב דיור'] = 'family_housing';
	$fg['נתונים כלליים'] = 'parent_info';
	$fg['בריאות'] = 'parent_hstatus';
	$fg['השכלה'] = 'parent_education';
	$fg['תעסוקה'] = 'parent_employment';

	$subtable = array();

	$fields_def = array( // family program_info
					'שם משפחה' => 'intake_family_name',
					'אשה - שם פרטי' => 'intake_family_woman_name',
					'גבר - שם פרטי' => 'intake_family_man_name',
					'מלווה משפחה' => 'intake_yahav_member',
					'תאריך הצטרפות לתכנית' => 'program_date',
					'סטטוס זוגיות' => 'mertial_status',
					'מספר ילדים' => 'kids_number',
					'כתובת מגורים - רחוב' => 'family_address',
					'כתובת מגורים - מספר בית' => '+family_address', // combinewith family address
					'כתובת מגורים - מספר דירה' => '++family_address', // combinewith family address
					'האם לקוחת רווחה' => 'family_in_revaha',
					'השתתפה בתכניות אחרות' => 'other_programs',
					'באילו תוכניות השתתפה?' => 'other_programs_list',
					// family housing
					'מספר נפשות המתגוררות בבית המשפחה' => '**1**',
					// parent info
					'שם פרטי' => 'parent_first_name',
					'תעודת זהות' => 'parent_idnum',
					'מין' => 'parent_gender',
					'מספר טלפון נייד (סלולרי)' => 'parent_phone1',
					'דואר אלקטרוני' => 'parent_email',
					'שנת לידה' => 'parent_birth_date',
					'ארץ לידה' => 'parent_birth_country',
					'שנת עליה' => 'parent_immigration_year',
					// parent health
					'מצב בריאותי' => 'parent_health',
					'האם את/ה מוכר/ת בביטוח הלאומי כבעל אי כושר עבודה או אי כושר השתכרות?' => 'parent_bituachleumi',
					'האם ההכרה בביטוח לאומי זמנית או קבועה?' => 'parent_bituachleumi_temp',
					'קופת חולים' => 'parent_kupat_holim',
					// השכלה
					"מס' שנות לימוד" => 'year_of_study',
					'השכלה מקצועית' => 'professional',
					'השכלה אקדמית' => 'academic_education',
					'השכלה דתית' => 'secular_education',
					'מקצוע' => 'profession',
					// תעסוקה
					'מצב תעסוקתי' => 'employ_status',
					'מספר מקומות העבודה' => 'employ_prev_places',
					'מספר השעות שעובד בשבוע (בכל מקומות העבודה יחד)' => 'MISSING',
					'שם מקום העבודה' => 'employ_main',
					'מהו התפקיד שלך?' => 'employ_role',
					'מה הוותק שלך במקום עבודה זה (כשכיר או כעצמאי)?' => 'employ_duration',
					'היקף אחוזי המשרה במקום העבודה העיקרי בלבד' => 'employ_cap_percent',
					'סוג העבודה' => 'MISSING',
					// בלתי מעסקים
					'בכמה מקומות עבדת ב-3 השנים האחרונות?' => 'places_last3y',
					'האם עבדת בעבר תמורת שכר?' => 'notemp_workbefore',
					'(כשכיר או כעצמאי) - כמה זמן אינך עובד?' => 'notemp_howlong',
					'למה אתה לא עובד?' => 'notemp_why',
					'מה עשית כדי למצוא עבודה? (בדיקת כוחות/ ידע/ מוטיבציה)' => 'notemp_look4work',
					'למבקשי עבודה - האם נתקלת באחד או ביותר מהקשיים הבאים בתהליך חיפוש עבודה?' => 'profession',
					'מקצוע' => 'profession',
					'מקצוע' => 'profession',


				);
	$fields = array();
	foreach ($fields_def as $k => $v)
		$fields[$k] = $v;

	// error_log(print_r($header, true));
	// identify input column header values
	$eol = count($allRows[0]);
	for ($j = 0; $j < $eol; $j++){
		$header[$j]['cpt_type'] = isset($allRows[0][$j]) && $allRows[0][$j] != "" ? $allRows[0][$j] : "*****"; // family or parent man/woman
		$header[$j]['table'] = isset($allRows[1][$j]) && $allRows[1][$j] != "" ? $allRows[1][$j] : "*****";
		$header[$j]['subtable'] = isset($allRows[2][$j]) && $allRows[2][$j] != "" ? $allRows[2][$j] : "*****";
		$header[$j]['field'] = isset($allRows[3][$j]) && $allRows[3][$j] != "" ? $allRows[3][$j] : "*****";
		$str .= $allRows[0][$j] . ">>" . $allRows[1][$j] . ">>" . $allRows[2][$j] . ">>" . $allRows[3][$j] . "<br>";
		//$str .= $cpt_type[$allRows[0][$j]] . ">>" . 
	}

	// db access all women
	for ($i = 4 ; $i < count($allRows) ; $i++){
		if ($i>6) break;
		//error_log(print_r($allRows[$i], true));
		$fname = getFamilyCPTname($allRows[$i], 0); 
		// error_log($fname);
		
		// 1. find Family/ parent CPT 
		// 2. decide family or if parent find man / woman CPT - GIVEN WOMAN
		$cpt = get_page_by_title($fname, OBJECT, 'digma_families');
		if ($cpt == null){
			$str .= $fname . "  קוד =לא נמצא<br>";
			continue;
		}
		//else $str .= $fname . "  קוד =". $pid->ID . "<br>";

		// 3 get all fields
		$x = get_fields($cpt->ID);
		//error_log(print_r($x['program_info'], true));

		// Scan/parse all input line by input header
		$curline = $allRows[$i];
		$listval = "";
		$listh ="";
		$listnew = "";
		for ($j = 0; $j < $eol; $j++){
			//if ($i ==5) error_log("loopstart header=",$header[$j]['cpt_type']);
			if ($header[$j]['cpt_type'] != 'נתוני משפחה') continue; // handle family data only, at this time
			$listh .= "<td> ".  $fields[$header[$j]['field']] . "</td>";

			// get table & field from header
			if (strpos($header[$j]['field'], '*')|| $header[$j]['field'] == "" ) continue; // field definition missing
			if ($header[$j]['cpt_type'] == 'נתוני משפחה'){
				if ($fields[$header[$j]['field']] == "")
				   $str .= "column=". $j . " header=". $header[$j] . "<br />";
				else{
					//$str .= 'CPT='.$cpt_type[$header[$j]['cpt_type']] . "  TABLE=".$fg[$header[$j]['table']] . 
					//			" FIELD=". $fields[$header[$j]['field']] . "<br />";
					$tmp= $x[$header[$j]['table']][$fields[$header[$j]['field']]];
					$listval .= "<td> ".  $tmp . "</td>"; // current value
					$listnew .= "<td> ".  $curline[$j] . "</td>";
					if ($j == 0) {
						$xx = $x[$fg[$header[$j]['table']]];
						foreach ($xx as $k => $v) {//$k . "</td><td> ".
							
							$listval .= "<td> ".  $v . "</td>"; //error_log(print_r($x[$fg[$header[$j]['table']])); // all table once...
							 //error_log(print_r($x[$fg[$header[$j]['table']])); // all table once...
						}
					}
				}
			}
		}
		$str .= "<table><tbody><tr>". $listh . "</tr><tr>" . $listval . "</tr><tr>" . $listnew ."</tr></tbody></table><br />";

		// 4 select group
	// 5. show prev & new
	}
    return $str;
}
function getWomenCPTname($family, $col){
	$w = trim($family[$col+1]) != "" ? " " . trim($family[$col+1]) : "*****";
	return 	trim($family[$col]) . $w ;
}
function getMenCPTname($family, $col){
	$m = trim($family[$col+2]) != "" ? " " . trim($family[$col+2]) : "*****" ;
	return 	trim($family[$col]) . $m;
}
function getFamilyCPTname($family, $col){
	$w = trim($family[$col+1]) != "" ? " " . trim($family[$col+1]) : "" ;
	$m = trim($family[$col+2]) != "" ? " " . trim($family[$col+2]) : "" ;
	//error_log("getTitle=". trim($family[0]) . $w . $m);
	return trim($family[$col]) . $w . $m;
}

// update for men & woman
function update_orach_haim($allRows, $qStart, $dbTable, $field){
	$str ="";
	for ($i = 4 ; $i < count($allRows) ; $i++){ // skip csv first 4 lines
		$fname = getWomenCPTname($allRows[$i], 1); 
		if ($fname == "" || strpos($fname, '*****') !== false){ // woman
			$str .= $fname . "חסר שם משפחה או שם אשה/ שם לא תקין. בשורה " . ($i+1) . " בקובץ אקסל<br />";
		} else $str .= handle_parent($allRows, $qStart, $fname, $allRows[$i], $dbTable, $field); // 25
		$fname = getMenCPTname($allRows[$i], 1); 
		if ($fname == "" || strpos($fname, '*****') !== false){ // man
			$str .= $fname . "חסר שם משפחה או שם גבר/ שם לא תקין. בשורה " . ($i+1) . " בקובץ אקסל<br />";
		} else $str .= handle_parent($allRows, 92, $fname, $allRows[$i], $dbTable, $field, $i); // 93
	}
	return $str;
}
// update single field in parent table
function handle_parent($allRows, $qStart, $fname, $curline, $dbTable, $field, $i){
	if ($curline[$qStart] == "")
		return " בוצע <b>" . $fname . "  קוד =  חסר ערך לא בוצע שינוי:</b><br>";

	$cpt = get_page_by_title($fname, OBJECT, 'digma_parents');

	if ($cpt == null)
		return $fname . " קוד =לא נמצא בשורה ". ($i+1) . " בקובץ אקסל<br />";
	
	$x = get_fields($cpt->ID);

	$x[$dbTable][$field]= $curline[$qStart];
	
	error_log($fname . " ".$dbTable. " =". print_r($x[$dbTable], true));
	update_field($dbTable, $x[$dbTable], $cpt->ID);
	return " בוצע <b>" . $fname . "  קוד =". $cpt->ID . "  ערך:" . $curline[$qStart] ."</b><br>";

}
// update woman work 
function update_wwork($allRows, $qStart, $dbTable, $field){
	$str ="";
	for ($i = 4 ; $i < count($allRows) ; $i++){ // skip csv first 4 lines
		$fname = getWomenCPTname($allRows[$i], 1); 
		if ($fname == "" || strpos($fname, '*****') !== false){ // woman
			$str .= $fname . "חסר שם משפחה או שם אשה/ שם לא תקין. בשורה " . ($i+1) . " בקובץ אקסל<br />";
		} else $str .= handle_wwork($allRows, $qStart, $fname, $allRows[$i], $i, $dbTable); // 37
	}
	return $str;
}

// Update all fields in "parent_employment" group field
function handle_wwork($allRows, $qStart, $fname, $curline, $cline, $dbTable){
	if ($curline[$qStart] == "")
		return " בוצע <b>" . $fname . "  קוד =  חסר ערך לא בוצע שינוי:</b><br>";

	$cpt = get_page_by_title($fname, OBJECT, 'digma_parents');

	if ($cpt == null)
		return $fname . " קוד =לא נמצא בשורה ". ($cline+1) . " בקובץ אקסל<br />";
	
		// Preapre in $pp array
	$j = $qStart;
	$pp = array();

	$pp["employ_comments"] = "חסרים נתוני ברוטו/נטו באקסל"; //$curline[$j];

	$employ_status = array("שכיר","עצמאי","גם שכיר וגם עצמאי","עבודה בשחור","גם שכיר וגם עבודה בשחור","עקר/ת בית",
						"מובטל","פנסיונר","לא עובד עקב נכות מוכרת","בחופשת לידה","מקבל מלגת עבודה","אחר");
	if (empty($curline[$j]))
		$pp["employ_status"] = "";
	else {
		if (in_array($curline[$j], $employ_status))
			$pp["employ_status"] = $curline[$j];
		else {
			$pp["employ_status"] = "אחר";
			$pp["employ_comments"] .= "  " . $curline[$j];
		}
	}

	$pp["employ_prev_places"] = $curline[$j+1];
	$pp["employ_cap_hours"] = $curline[$j+2];
	$pp["employ_main"] = $curline[$j+3];
	$pp["employ_role"] = $curline[$j+4];  // xl col=108
	//$pp["employ_quality"] = $curline[$j];
	$pp["employ_current_place"] = $curline[$j+3];
	$td = array("6 - 10 שנים" => 96,"עד שנה" => 10, "עד חצי שנה" => 4, "שנתיים עד 5 שנים" => 42, "למעלה מ 10 שנים" => 120);
	if ($curline[$j+5] != "" && isset($td[$curline[$j+5]]))
		$pp["employ_duration"] = $td[$curline[$j+5]];
	else {
		if ($curline[$j+5] != "")
			error_log("work duration - missing definition=".$curline[$j+5]);
		$pp["employ_duration"] = "";
	}
	$pp["employ_cap_percent"] = $curline[$j+6];
	if ($curline[$j+7] == "קבועה (גם אין אין קביעות)")
		$pp["employ_social"] = "קבועה (גם אם אין קביעות)";
	else		
		$pp["employ_social"] = $curline[$j+7];

	if ($curline[$j+8] == "")
		$pp["employ_last3y"]  = $curline[$j+8];
	else if ($curline[$j+8] == "במקום אחד")
		$pp["employ_last3y"]  = 1;
	else if ($curline[$j+8] == "בשני מקומות")
		$pp["employ_last3y"]  = 2;
	else if ($curline[$j+8] == "בשלושה מקומות")
		$pp["employ_last3y"]  = 3;
	//$pp["employ_bruto"] = $curline[$j];
	//$pp["employ_neto"] = $curline[$j];
	$j = 78 - 50; // set from excel
	if ($curline[$j+50] == ""){
		$pp["employ_interest_inchange"] = "";
		$pp["employ_i1"] = "";
		$pp["employ_i2"] = "";
		$pp["employ_i3"] = "";
		$pp["employ_i4"] = "";
		$pp["employ_i5"] = "";
		$pp["employ_i6"] = "";
	} else {
		$pp["employ_interest_inchange"] = ($curline[$j+50] == "לא מעוניין לעשות שינוי בעבודה" || $curline[$j+50] == "1") ? 
			"לא מעוניין לעשות שינוי בעבודה" : "כן";
		$pp["employ_i1"] = strpos("מעוניין למצוא מקום עבודה", $curline[$j+50]) !== false ? "כן" : "לא";
		$pp["employ_i4"] = strpos("מעוניין לשדרג את העבודה שלי", $curline[$j+50]) !== false ? "כן" : "לא";
		$pp["employ_i3"] = strpos("מעוניין להחליף מקום עבודה", $curline[$j+50]) !== false ? "כן" : "לא";
		$pp["employ_i2"] = strpos("להוסיף עוד מקום עבודה", $curline[$j+50]) !== false ? "כן" : "לא";
		$pp["employ_i5"] = strpos("לא מעוניין לעשות שינוי בעבודה", $curline[$j+50]) !== false ? "כן" : "לא";
		$pp["employ_i6"] = (strpos("מעוניין למצוא מקום עבודה", $curline[$j+50]) !== false 
							&& strpos("מעוניין לשדרג את העבודה שלי", $curline[$j+50]) !== false
							&& strpos("מעוניין להחליף מקום עבודה", $curline[$j+50]) !== false
							&& strpos("להוסיף עוד מקום עבודה", $curline[$j+50]) !== false
							&& strpos("לא מעוניין לעשות שינוי בעבודה", $curline[$j+50]) !== false
							) ? $curline[$j+50] : "";
	}
	$pp["employ_new_buisness"] =$curline[$j+51];
	$pp["employ_new_buis_other"] = "";
	

	$pp["employ_volunteer"] = $curline[$j+52];
	error_log($fname . "wwork =". print_r($pp, true)); // prove correct parsing
	//$str .= $fname . " שנותלימוד: ". $curline[$j]. "<br>";
	$x = get_fields($cpt->ID);
	foreach ($pp as $k => $v){ // Modify ONLY empty fields
		if (empty($x["parent_employment"][$k]))
			$x["parent_employment"][$k] = $pp[$k];
	}
	error_log($fname . "DB wwork =". print_r($x["parent_employment"], true)); // show values in DB
// display before / after table changes
	$lstr = "<table><tbody><tr><td>שם שדה</td><td>לפני העדכון</td><td>אחרי העדכון</td></tr>";
	foreach ($x["parent_employment"] as $k => $v){ // Modify ONLY empty fields
		$lstr .= "<tr><td>".$k."</td><td>".$v."</td><td>".$pp[$k]."</td></tr>";
	}
	$lstr .= "</tbody></table>";
	update_field("parent_employment", $x["parent_employment"], $cpt->ID);
	return $lstr. "<br />". " בוצע <b>" . $fname . "  קוד =". $cpt->ID . "  ערך:" . $curline[$qStart] ."</b><br>";
}

// exact function as woman work 
// update uneployed women
function update_w_notemployed($allRows, $qStart, $dbTable, $field){
	$str ="";
	for ($i = 4 ; $i < count($allRows) ; $i++){ // skip csv first 4 lines
		$fname = getWomenCPTname($allRows[$i], 1); 
		if ($fname == "" || strpos($fname, '*****') !== false){ // woman
			$str .= $fname . "חסר שם משפחה או שם אשה/ שם לא תקין. בשורה " . ($i+1) . " בקובץ אקסל<br />";
		} else $str .= handle_w_notemployed($allRows, $qStart, $fname, $allRows[$i], $i, $dbTable); // 37
	}
	return $str;
}

function handle_w_notemployed($allRows, $qStart, $fname, $curline, $cline, $dbTable){
	if ($curline[$qStart] == "")
		return " בוצע <b>" . $fname . "  קוד =  חסר ערך לא בוצע שינוי:</b><br>";

	$cpt = get_page_by_title($fname, OBJECT, 'digma_parents');

	if ($cpt == null)
		return $fname . " קוד =לא נמצא בשורה ". ($cline+1) . " בקובץ אקסל<br />";
	
		// Preapre in $pp array
	$j = $qStart;
	$pp = array();
	$pp["notemp_workbefore"]= $curline[$j];
	$pp["notemp_howlong"]= $curline[$j+1];
	$pp["notemp_why"]= $curline[$j+2];
	$pp["notemp_look4work"]= ""; // $curline[$j+3];
	if (empty($curline[$j+4]))
		$pp["notenough"] = $pp["noreview"]= $pp["nopass"]=$pp["nostay"]=$pp["other"]="";
	else{
		if (strpos($curline[$j+4],"אין מספיק הצעות עבודה") !== false)
			$pp["notenough"]= "כן";
		else $pp["notenough"]= "לא";
		if (strpos($curline[$j+4],"מאתר/ת הצעות עבודה") !== false)
			$pp["noreview"]= "כן";
		else $pp["noreview"]= "לא";
		if (strpos($curline[$j+4],"אך לא מגיע/ה לשלב הראיונות") !== false)
			$pp["nopass"]= "כן";
		else $pp["nopass"]= "לא";
		if (strpos($curline[$j+4],"מתקבל/ת לעבודה אך לא נשאר/ת בה מספיק זמן") !== false)
			$pp["nostay"]= "כן";
		else $pp["nostay"]= "לא";
		$pp["other"]= $curline[$j+4];
	}
	$pp["places_last3y"]= convert_text2num($curline[$j+5]); // $curline[$j+5];

	$x = get_fields($cpt->ID);

// display on line before & after
	$lstr = "<table><tbody><tr><td>שם שדה</td><td>לפני העדכון</td><td>אחרי העדכון</td></tr>";
	foreach ($x["parent_notemployed"] as $k => $v){ // Modify ONLY empty fields
		$lstr .= "<tr><td>".$k."</td><td>".$v."</td><td>".$pp[$k]."</td></tr>";
	}
	$lstr .= "</tbody></table>";

// modify non empty fields in DB
	foreach ($pp as $k => $v){ // Modify ONLY empty fields
		if (empty($x["parent_notemployed"][$k]))
			$x["parent_notemployed"][$k] = $pp[$k];
	}
	error_log($fname . "DB parent_notemployed =". print_r($x["parent_notemployed"], true)); // show values in DB

	update_field("parent_notemployed", $x["parent_notemployed"], $cpt->ID);
	return "<br />". " בוצע <b>" . $fname . "  קוד =". $cpt->ID . "  ערך:" . $curline[$qStart] ."</b><br>" . $lstr;
}

function convert_text2num($item){
	if ($item == "") return "";
	if ($item == "במקום אחד") return 1;
	if ($item == "בשני מקומות") return 2;
	if ($item == "בשלושה מקומות") return 3;
	return 0;
}

function update_income($allRows, $qStart, $dbTable, $field){
	$str ="";
	for ($i = 4 ; $i < count($allRows) ; $i++){ // skip csv first 4 lines
		$fname = getFamilyCPTname($allRows[$i], 1); 
		if ($fname == "" || strpos($fname, '*****') !== false){ // woman
			$str .= $fname . "חסר שם משפחה או שם לא תקין. בשורה " . ($i+1) . " בקובץ אקסל<br />";
		} else $str .= handle_income($allRows, $qStart, $fname, $allRows[$i], $i, $dbTable); // 37
	}
	return $str;
}
function handle_income($allRows, $qStart, $fname, $curline, $cline, $dbTable){
	//if ($curline[$qStart] == "")
	//	return " בוצע <b>" . $fname . "  קוד =  חסר ערך לא בוצע שינוי:</b><br>";

	$cpt = get_page_by_title($fname, OBJECT, 'digma_families');

	if ($cpt == null)
		return $fname . " קוד =לא נמצא בשורה ". ($cline+1) . " בקובץ אקסל<br />";
	
		// Preapre in $pp array
	$j = $qStart;
	$id = 1; // id incremented only if successful update
	$id += add_income_line($id, "משכורת", "האשה", "מעסיק", $curline[187], "חודשי", $cpt); // 187
	$id += add_income_line($id, "משכורת", "הגבר", "מעסיק", $curline[189], "חודשי", $cpt); // 189
	$id += add_income_line($id, "הכנסה מעסק", "האשה", "הכנסה מעסק", $curline[191], "חודשי", $cpt); // 191
	$id += add_income_line($id, "הכנסה מעסק", "הגבר", "הכנסה מעסק", $curline[192], "חודשי", $cpt); // 193
	$id += add_income_line($id, "קצבת ילדים", "שני בני הזוג", "ביטוח לאומי", $curline[194], "חודשי", $cpt); // 189
	$id += add_income_line($id, "קצבת ילד נכה", "שני בני הזוג", "ביטוח לאומי", $curline[195], "חודשי", $cpt); // 189
	$id += add_income_line($id, "מזונות", "האשה", "הגרוש", $curline[196], "חודשי", $cpt); // 189
	$id += add_income_line($id, "אבטלה", "האשה", "ביטוח לאומי", $curline[197], "חודשי", $cpt); // 189
	$id += add_income_line($id, "נכות", "האשה", "ביטוח לאומי", $curline[198], "חודשי", $cpt); // 189
	$id += add_income_line($id, "השלמת הכנסה", "האשה", "ביטוח לאומי", $curline[199], "חודשי", $cpt); // 189
	$id += add_income_line($id, "הבטחת הכנסה", "האשה", "ביטוח לאומי", $curline[200], "חודשי", $cpt); // 189
	$id += add_income_line($id, "קצבת שארים", "האשה", "ביטוח לאומי", $curline[201], "חודשי", $cpt); // 189

	$id += add_income_line($id, "אבטלה", "הגבר", "ביטוח לאומי", $curline[203], "חודשי", $cpt); // 189
	$id += add_income_line($id, "נכות", "הגבר", "ביטוח לאומי", $curline[204], "חודשי", $cpt); // 189
	$id += add_income_line($id, "השלמת הכנסה", "הגבר", "ביטוח לאומי", $curline[205], "חודשי", $cpt); // 189
	$id += add_income_line($id, "הבטחת הכנסה", "הגבר", "ביטוח לאומי", $curline[206], "חודשי", $cpt); // 189
	$id += add_income_line($id, "קצבת שארים", "הגבר", "ביטוח לאומי", $curline[207], "חודשי", $cpt); // 189
}
function add_income_line($id, $type, $towhom, $payer, $sum, $freq, $cpt){
	if (empty($sum) || $sum < 10 || strlen($sum) > 10)
		return 0;
	$pp = array();
	$pp["id"]=  $id;
	$pp["income_type"]=  $type;
	$pp["income_goesto"]=  $towhom;
	$pp["income_provider"]=  $payer;
	$pp["income_frequency"]=  $freq;
	$pp["income_sum"]=  $sum;
	$pp["income_comment"]=  "";

	$x = get_field("family_income", $cpt->ID);
//	error_log("FROM DB - family =". $cpt->ID.":".$cpt->post_title . "  " . print_r($x, true));

	if ($id==1)
		$k = update_row( "family_income", 1, $pp, $cpt->ID );
	else
		$k = add_row( "family_income", $pp, $cpt->ID );
	if ($k === false)
		error_log("FAILED UPDAE INCOME on the following");
	error_log("family =". $cpt->post_title . "  " . print_r($pp, true));
	
	// return $k;
	return 1;
}

function update_debts_status($allRows){
// qstartis dead = -1
	$str ="";
	for ($i = 4 ; $i < count($allRows) ; $i++){ // skip csv first 4 lines
		$fname = getFamilyCPTname($allRows[$i], 1); 
		if ($fname == "" || strpos($fname, '*****') !== false){ // woman
			$str .= $fname . "חסר שם משפחה או שם לא תקין. בשורה " . ($i+1) . " בקובץ אקסל<br />";
		} else $str .= handle_debts_status($fname, $allRows[$i], $i); // 37
	}
	return $str;
}

function handle_debts_status($fname, $curline, $cline){
	$cpt = get_page_by_title($fname, OBJECT, 'digma_families');

	if ($cpt == null)
		return $fname . " קוד =לא נמצא בשורה ". ($cline+1) . " בקובץ אקסל<br />";

	$debts_status = array();

	$debts_status["debts_comment"]= ""; // initialize
	if (!empty($curline[218]) && $curline[218] == "אין לנו חובות")
		$debts_status["debts_range"]= "אין לנו חובות";
	else if (!empty($curline[218]) && $curline[218] == "לא יודע"){
		$debts_status["debts_comment"]= "לא יודע אם יש חובות";
		$debts_status["debts_range"]=""; // will be filled later, just good coding
	}
	else $debts_status["debts_range"]=""; // will be filled later, just good coding

	if (!empty($curline[219]) && intval($curline[219])>10)
		$debts_status["debts_total"]= intval($curline[219]);
	else $debts_status["debts_total"]="";

	if (!empty($curline[220])){
		$a = $curline[220];
		if (strpos($a, 'עד 500,000') !== false) $debts_status["debts_range"]= "עד 500,000 ש\"ח";
		if (strpos($a, 'מעל 500,000') !== false) $debts_status["debts_range"]= "מעל 500,000 ש\"ח";
	}
	$debts_status["debts_bankropt"]  = (intval($curline[242])==1? "לא" : (intval($curline[242])==2? "כן" : $curline[242]));
	
	//$x = get_field("debts_status", $cpt->ID);
	//error_log("BEFORE: ". $fname . ":  ". print_r($x, true));
	error_log("AFTER: ". $fname . ":  ". print_r($debts_status, true));
	update_field("debts_status", $debts_status, $cpt->ID);
	return "משפחת ". $fname . " טופל <br />";
}
function update_savings($allRows){
	// qstartis dead = -1
		$str ="";
		for ($i = 4 ; $i < count($allRows) ; $i++){ // skip csv first 4 lines
			$fname = getFamilyCPTname($allRows[$i], 1); 
			if ($fname == "" || strpos($fname, '*****') !== false){ // woman
				$str .= $fname . "חסר שם משפחה או שם לא תקין. בשורה " . ($i+1) . " בקובץ אקסל<br />";
			} else $str .= handle_savings($fname, $allRows[$i], $i); // 37
		}
		return $str;
}
function handle_savings($fname, $curline, $cline){
	$cpt = get_page_by_title($fname, OBJECT, 'digma_families');

	if ($cpt == null)
		return $fname . " קוד =לא נמצא בשורה ". ($cline+1) . " בקובץ אקסל<br />";

	$family_savings = array();
	$family_savings["savings_comment"] = "";
	$family_savings["do_u_save"]= "לא";

	if (!empty($curline[246])){  // האם אתם חוסכים (שמים כסף בצד מההכנסה שלכם)?
		if (strpos($curline[246], "לא") !== false){ // add text to comment
			$family_savings["savings_comment"] .= $curline[246];
			$family_savings["do_u_save"]= "כל חודש בקביעות";
		}
	}

	if (intval($curline[248]) > 10)
		$family_savings["monthly_savings_avg"]= intval($curline[248]);
	else $family_savings["monthly_savings_avg"]= "0";

	if (!empty($curline[250]) && intval($curline[250]) > 10){ // saving for each child
		$family_savings["saving4each_child_sum"]= intval($curline[250]);
		$family_savings["savings_comment"] .= "    ". $curline[249];
		$family_savings["do_u_save"]= "כל חודש בקביעות";
	} else $family_savings["saving4each_child_sum"]= 0;

	$x = get_field("family_savings", $cpt->ID);
	error_log("BEFORE: ". $fname . ":  ". print_r($x, true));
	error_log("AFTER: ". $fname . ":  ". print_r($family_savings, true));
	update_field("family_savings", $family_savings, $cpt->ID);
	return "משפחת ". $fname . " טופל <br />";
}	

function update_expenses($allRows){
	// qstartis dead = -1
		$str ="";
		for ($i = 4 ; $i < count($allRows) ; $i++){ // skip csv first 4 lines
			$fname = getFamilyCPTname($allRows[$i], 1); 
			if ($fname == "" || strpos($fname, '*****') !== false){ // woman
				$str .= $fname . "חסר שם משפחה או שם לא תקין. בשורה " . ($i+1) . " בקובץ אקסל<br />";
			} else $str .= handle_expenses($fname, $allRows[$i], $i); // 37
		}
		return $str;
}	
function handle_expenses($fname, $curline, $cline){
	$cpt = get_page_by_title($fname, OBJECT, 'digma_families');
	if (strpos($fname, "זרד") !== false) 
		return "משפחת ". $fname . " טופל <br />";
	if ($cpt == null)
		return $fname . " קוד =לא נמצא בשורה ". ($cline+1) . " בקובץ אקסל<br />";

	$family_expenses = array();
	$family_expenses["id"] = "";
	$family_expenses["expense_purpose"]= "";
	$family_expenses["expense_category"]= "";
	$family_expenses["expense_sum"]= "";
	$id = 1;
	$newv = $curline[251];
	if (!empty($newv) && intval($newv)> 1){  // האם אתם חוסכים (שמים כסף בצד מההכנסה שלכם)?
		$family_expenses["id"] = $id++;
		$family_expenses["expense_category"]= "דיור";
		$family_expenses["expense_sum"]= intval($newv);
		update_row("family_expenses", 1, $family_expenses, $cpt->ID); // subtract mortagage
		error_log("AFTER: ". $fname . ":  ". print_r($family_expenses, true));
	}

	$newv = $curline[252];
	if (!empty($newv) && intval($newv)> 1){  // האם אתם חוסכים (שמים כסף בצד מההכנסה שלכם)?
		$family_expenses["id"] = $id++;
		$family_expenses["expense_category"]= "חינוך";
		$family_expenses["expense_sum"]= intval($newv);
		add_row("family_expenses", $family_expenses, $cpt->ID); // subtract mortagage
		error_log("AFTER: ". $fname . ":  ". print_r($family_expenses, true));
	}

	$newv = $curline[253];
	if (!empty($newv) && intval($newv)> 1){  // האם אתם חוסכים (שמים כסף בצד מההכנסה שלכם)?
		$family_expenses["id"] = $id++;
		$family_expenses["expense_category"]= "שוטפות";
		$family_expenses["expense_sum"]= intval($newv);
		add_row("family_expenses", $family_expenses, $cpt->ID); // subtract mortagage
		error_log("AFTER: ". $fname . ":  ". print_r($family_expenses, true));
	}

//	$x = get_field("family_expenses", $cpt->ID);
//	error_log("BEFORE: ". $fname . ":  ". print_r($x, true));

	return "משפחת ". $fname . " טופל <br />";
}	

function update_returnloan($allRows){
	// qstartis dead = -1
		$str ="";
		for ($i = 4 ; $i < count($allRows) ; $i++){ // skip csv first 4 lines
			$fname = getFamilyCPTname($allRows[$i], 1); 
			if ($fname == "" || strpos($fname, '*****') !== false){ // woman
				$str .= $fname . "חסר שם משפחה או שם לא תקין. בשורה " . ($i+1) . " בקובץ אקסל<br />";
			} else $str .= handle_returnloan($fname, $allRows[$i], $i); // 37
		}
		return $str;
}
function handle_returnloan($fname, $curline, $cline){
	$newv = $curline[254];
	if (empty($newv)|| intval($newv)<10)
		return $fname . " קוד =לא נמצא ערך בשורה ". ($cline+1) . " בקובץ אקסל<br />";
	$cpt = get_page_by_title($fname, OBJECT, 'digma_families');
	if (strpos($fname, "זרד") !== false) 
		return "משפחת ". $fname . " טופל <br />";
	if ($cpt == null)
		return $fname . " קוד =לא נמצא בשורה ". ($cline+1) . " בקובץ אקסל<br />";

	$family_debts = array();
	$family_debts["id"]= "1";
	$family_debts["family_debt_lawner"]= "אחר";
	$family_debts["family_debt_lawner_other"]= "מספר גורמים";
	$family_debts["family_debt_orig_sum"]= "";
	$family_debts["family_debt_monthlyreturn"]= intval($newv);
	$family_debts["family_debt_returnontime"]= "";
	$family_debts["family_debt_arrangement"]= "";
	$family_debts["family_debt_unify"]= "";
	$family_debts["family_debt_description"]= "סכום ממסמך אקסל";
	$family_debts["family_debt_date"]= "";

	$x = get_field("family_debts", $cpt->ID);
	error_log("BEFORE: ". $fname . ":  ". print_r($x, true));
	
	update_row("family_debts", 1, $family_debts, $cpt->ID); 
	error_log("AFTER: ". $fname . ":  ". print_r($family_debts, true));

	return "משפחת ". $fname . " טופל <br />";
}
function update_intake($allRows){
	// qstartis dead = -1
		$str ="";
		for ($i = 4 ; $i < count($allRows) ; $i++){ // skip csv first 4 lines
			$fname = getFamilyCPTname($allRows[$i], 1); 
			if ($fname == "" || strpos($fname, '*****') !== false){ // woman
				$str .= $fname . "חסר שם משפחה או שם לא תקין. בשורה " . ($i+1) . " בקובץ אקסל<br />";
			} else $str .= handle_intake($fname, $allRows[$i], $i); // 37
		}
		return $str;
}
function handle_intake($fname, $curline, $cline){
	if (empty($curline[282]) && empty($curline[283])) // no data...
		return $fname . " קוד =לא נמצא ערך בשורה ". ($cline+1) . " בקובץ אקסל<br />";
	$cpt = get_page_by_title($fname, OBJECT, 'digma_intakes');
	if ($cpt == null)
		return $fname . " קוד =לא נמצא בשורה ". ($cline+1) . " בקובץ אקסל<br />";

	$newv = $curline[282];

	$intake = array();
	//$intake["intake_date"]= "";
	//$intake["intake_team"]= "מיטל";
	$intake["intake_q1"]= $curline[282];
	$intake["intake_q2"]= $curline[283];
	$intake["intake_q3"]= yesNoOther($curline[284]);
	$intake["intake_q3_other"]="";
	if ($intake["intake_q3"] == "אחר")
		$intake["intake_q3_other"]= $curline[284];
	$intake["intake_q4"]= yesNoOther($curline[285]);
	$intake["intake_q4_other"]= "";
	$intake["intake_q5"]= yesNoOther($curline[286]);
	$intake["intake_q5_other"]= "";
	if ($intake["intake_q5"] == "אחר")
		$intake["intake_q5_other"]= $curline[286];

	//$intake["intake_q6"]= "";
	$v = $curline[287];
	$intake["intake_q61"]= (strpos($v, 'מתנ"ס')!== false)? "כן" : "לא";
	$intake["intake_q62"]= (strpos($v, 'מעון יום')!== false)? "כן" : "לא";
	$intake["intake_q63"]= (strpos($v, 'מרכז לילד ולמשפחה')!== false)? "כן" : "לא";
	$intake["intake_q64"]= (strpos($v, 'תחנה לבריאות')!== false)? "כן" : "לא";
	$intake["intake_q65"]= (strpos($v, 'ארגוני חסד')!== false)? "כן" : "לא";
	$intake["intake_q66"]= (strpos($v, 'עזרה של עמותות')!== false)? "כן" : "לא";
	$intake["intake_q67"]= (strpos($v, 'עזרה בלימודים')!== false)? "כן" : "לא";
	$intake["intake_q68"]= (strpos($v, 'תחנה לבריאות הנפש')!== false)? "כן" : "לא";
	$intake["intake_q69"]= (strpos($v, 'קופת חולים')!== false)? "כן" : "לא";
	$intake["intake_q610"]= (strpos($v, 'פעילות העשרה')!== false)? "כן" : "לא";
	$intake["intake_q611"]= (strpos($v, 'לשכת הרווחה')!== false)? "כן" : "לא";
	$intake["intake_q612"]= $v;

	$v = $curline[288];

	$intake["intake_q71"]= (strpos($v, 'בניית פרופיל')!== false)? "כן" : "לא";
	$intake["intake_q72"]= (strpos($v, 'חיזוק כלים')!== false)? "כן" : "לא";
	$intake["intake_q73"]= (strpos($v, 'הכשרה מקצועית')!== false)? "כן" : "לא";
	$intake["intake_q74"]= (strpos($v, 'לימודים אקדמאים')!== false)? "כן" : "לא";
	$intake["intake_q75"]= (strpos($v, 'כלים למציאת')!== false)? "כן" : "לא";
	$intake["intake_q76"]= (strpos($v, 'מציאת עבודה או עזרה')!== false)? "כן" : "לא";
	$intake["intake_q77"]= (strpos($v, 'עזרה בשדרוג')!== false)? "כן" : "לא";
	$intake["intake_q78"]= (strpos($v, 'יעוץ בפתיחת')!== false)? "כן" : "לא";
	$intake["intake_q79"]= (strpos($v, 'פתיחה מחדש')!== false)? "כן" : "לא";
	$intake["intake_q710"]= (strpos($v, 'הגדלת התפוקה ')!== false)? "כן" : "לא";
	$intake["intake_q711"]= (strpos($v, 'סיוע במיצוי')!== false)? "כן" : "לא";
	$intake["intake_q712"]= (strpos($v, 'התנהלות כלכלית')!== false)? "כן" : "לא";
	$intake["intake_q713"]= (strpos($v, 'העצמה אישית')!== false)? "כן" : "לא";
	$intake["intake_q714"]= (strpos($v, 'יחסים בין בני הזוג')!== false)? "כן" : "לא";
	$intake["intake_q715"]= (strpos($v, 'יחסים עם הילדים')!== false)? "כן" : "לא";
	$intake["intake_q716"]= (strpos($v, 'פיתוח וחיזוק')!== false)? "כן" : "לא";
	$intake["intake_q717"]= (strpos($v, 'מענה לצרכים')!== false)? "כן" : "לא";
	$intake["intake_q718"]= (strpos($v, 'העשרה של הילדים')!== false)? "כן" : "לא";
	$intake["intake_q719"]= $v;
	$intake["intake_q7_other"]= $v;

	$v = $curline[289];

	$intake["intake_q801"]= $curline[289];
	$intake["intake_q802"]= $curline[290];
	$intake["intake_q803"]= yesNoHesitate($curline[291]);
	$intake["intake_q804"]= $curline[292];
	$intake["intake_q805"]= $curline[293];
	$intake["intake_q806"]= yesNoHesitate($curline[294]);
	$intake["intake_q807"]= $curline[295];
	$v = $curline[296];

	$intake["intake_q8"]= ($v == 5 ? "5-רבה מאוד" : ($v == 4 ? "4-רבה" : ""));
	$intake["intake_q9"]= $curline[297];
	$v = $curline[298];

	$intake["intake_q10"]= ($v == 5 ? "5-רבה מאוד" : ($v == 4 ? "4-רבה" : ""));
	$intake["intake_q11"]= $curline[299];
	$intake["intake_q12"]= $curline[300];
	$intake["intake_q13"]= "";
	$intake["intake_q14"]= "";
	$intake["intake_q141"]= "";
	$intake["intake_q142"]= "";
	$intake["intake_q143"]= "";
	$intake["intake_q144"]= "";
	$intake["intake_q145"]= "";
	$intake["intake_q146"]= "";
	$intake["intake_q147"]= "";
	$intake["intake_q151"]= "";
	$intake["intake_q152"]= "";
	$intake["intake_q153"]= "";
	$intake["intake_q154"]= "";
	$intake["intake_q155"]= "";
	$intake["intake_q156"]= "";
	$intake["intake_q157"]= "";
	$intake["intake_q16"]= "";
	$intake["intake_q17"]= "";
	$intake["intake_q18"]= "";
	//$intake["intake_family_code"]= "";

	$x = get_field("intake", $cpt->ID);
	error_log("BEFORE: ". $fname . ":  ". print_r($x, true));
	
	update_field("intake", $intake, $cpt->ID); 
	error_log("AFTER: ". $fname . ":  ". print_r($intake, true));
	return "משפחת ". $fname . " טופל <br />";

}

function yesNoOther($ans){
	if ($ans == 1) return "לא";
	if ($ans == 2) return "כן";
	return "אחר";
}
function yesNoHesitate($ans){
	if ($ans == 1) return "לא";
	if ($ans == 2) return "כן";
	return "מהססים";
}
?>
