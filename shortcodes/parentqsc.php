<?php
/* shortcodes/parentqsc.php - display all compare options of שאלון עמדות
 * Personal: Women or Man : start vs. end
 * Compare Woman vs. Man : at start & at end
 * */
//  https://digma.me/parentqsc/?family=2019&parent=1984&gender=m
//       start  end
// questions:
add_shortcode('parentqsc','digma_parentq');

// questions classified into groups
$stat_code = array( "רשת תמיכה" => array('q1', 'q24'), "כלכלית" => array('q2', 'q3', 'q5', 'q10'),"מימוש עצמי" => array('q26', 'q27'), 
                 "מסוגלות עצמית" => array('q4','q8', 'q14', 'q17', 'q23') , "תעסוקה" => array('q6', 'q9', 'q13', 'q20'),
                 "משפחה" => array('q12', 'q15', 'q16', 'q19', 'q22'), "מוטיבציה" => array('q7', 'q18', 'q25', 'q28' ));
$reverse_code = array('q4', 'q5', 'q6', 'q7', 'q11', 'q12','q13', 'q14', 'q16', 'q18', 'q23', 'q24', 'q26');
$reverse_str = "q4, q5, q6, q7, q11, q12,q13, q14, q16, q18, q23, q24, q26";

 // translate to integer values
$dict = array('' => 0, '1-כלל לא' => 1, '2-במידה מעטה מאוד' => 2, '3- במידה מעטה'=> 3, 
				 '4-במידה בינונית' => 4, '5-במידה רבה' => 5, '6-במידה רבה מאוד' => 6);
// usage stats($answer_table) table is $x['questionnaire_pre'] or post or $y...
// Calc stats / avg. for all groups in a fiven list of answers
function qstats($answer_table, $stat_res){
	global $stat_code, $dict, $reverse_code;
	foreach ($stat_code as $group => $qarray ){ // go over groups & calc avg
		$num_ans = 0; // count answers for avg.
		$sum_ans = 0; // sum of answers
		foreach ($qarray as $ans){
			if (empty($answer_table[$ans])) continue; // skip empty
			$sum_ans += in_array($ans, $reverse_code) ?  7 - $dict[$answer_table[$ans]] : $dict[$answer_table[$ans]];
			//error_log("qstat v=". $dict[$answer_table[$ans]] . " converted=".(7 - $dict[$answer_table[$ans]]));
			// $sum_ans += $dict[$answer_table[$ans]];
			$num_ans += 1;
		}
		if ($num_ans > 0) 
			$stat_res[$group] = $sum_ans / $num_ans;
		else $stat_res[$group] = 0;
	}
	// error_log(print_r($stat_res, true));
	return $stat_res;
}
function invers_dict($value){
       global $dict;
       if ($value == "" || "נא לבחור" ) return "";
       // find index of $value
       $index = $dict[$value];

       // invert 7-$value
       $new_index = 7-$index;
       // return the dict value of inverted
       foreach ($dict as $k => $v){
           if ($v == $new_index){
               // error_log("convert value=".$value . "  to  " . $k);
               return $k;
           }
       }
       error_log("NOT FOUND convert value=".$value. " table=" . print_r($dict, true));
       return 'not found';
}			 
function digma_parentq($atts ) {
	global $stat_code, $reverse_code;
	$a = shortcode_atts( array(
		'family' => '',
		'parent' => ''
	), $atts ); // not used
	$style = "<style>.line{text-align:right;}.line:nth-child(even){background:#ededed;}body{font-family:'Assistant';}</style>";
	$qdict = array(
		"interviewer" => "מראיין",
		"date" => "תאריך מילוי השאלון",
		"q1" => "במקרה הצורך יש לי במי להיעזר מחוץ למשפחה הגרעינית" . " [רשת תמיכה]",
		"q24" => "אני מרגיש/ה בודד/ה" . "  [רשת תמיכה]",
		"q2" => "אנחנו נערכים ביחד (מדברים ומתכננים) לקראת הוצאות גדולות הצפויות בטווח הארוך (אירועים ושמחות, החלפת רכב, שיפוץ בית וכד')" . "[כלכלית]",
		"q3" => "קשה לי להגיד ''לא'' לילדים כשהם מבקשים לקנות משהו" . " [כלכלית]",
		"q5" => "צריך ליהנות מהחיים כמה שאפשר כי חיים רק פעם אחת" . "  [כלכלית]",
		"q10" => "כל אחד במשפחה יכול לתרום לשיפור המצב הכלכלי בדרך שלו" . "  [כלכלית]",
		"q4" => "לעיתים קרובות אני מרגיש/ה חסר שליטה בחיי ושאחרים או ''המצב'' מנהלים אותי" . "  [מסוגלות עצמית]",
		"q8" => "העתיד ומה שיקרה לנו תלוי בעיקר בי ובבת/בן זוגי" . "  [מסוגלות עצמית]",
		"q14" => "לפעמים אני מעדיף/ה לוותר מאשר להתעקש על מה שמגיע לי" . "  [מסוגלות עצמית]",
		"q17" => "אני סומך/ת על הכוחות שלי בהתמודדות עם מצבים בחיים" . "  [מסוגלות עצמית]",
		"q23" => "קשה לי להאמין שאצליח לשנות דברים שחשובים לי בחיים" . "  [מסוגלות עצמית]",
		"q6" => "למרות המצב הכלכלי שלי, יש עבודות שזו בושה בשבילי לעבוד בהן" . "  [תעסוקה]",
		"q9" => "הכישורים ו/או הניסיון שלי מאפשרים לי למצוא עבודה טובה" . "  [תעסוקה]",
		"q13" => "אני אעדיף לחתום אבטלה מאשר לעבוד תמורת אותו סכום של דמי אבטלה" . "  [תעסוקה]",
		"q20" => "אוכל למצוא עבודה המתאימה ליכולותיי ולנטיותיי" . "  [תעסוקה]",
		"q11" => "אנחנו מתקשים להסתדר טוב ביחד במשפחה הגרעינית" . "  [יחסים במשפחה]",
		"q12" => "יש הרבה רגשות קשים במשפחה שלנו" . "  [משפחה]",
		"q15" => "לילדים שההורים שלהם מפנקים אותם קשה לתפקד היטב בתור מבוגרים" . "  [משפחה]",
		"q16" => "במשפחה שלנו מתקשים לקבל החלטות ביחד" .  "  [משפחה]",
		"q19" => "בתקופות של קושי אנחנו עוזרים אחד לשני במשפחה הגרעינית" . "  [משפחה]",
		"q22" => "אנחנו פותרים בעיות ביחד במשפחה שלנו" . "  [משפחה]",
		"q26" => "כיום, אני רחוק/ה מהמצב בו דמיינתי שאהיה כאשר חלמתי על העתיד שלי" . "  [מימוש עצמי]",
		"q27" => "אני מרוצה מהמצב שלי בחיים" . "  [מימוש עצמי]",
		"q7" => "אני טורח/ת להתאמץ רק אם אני בטוח/ה מה ייצא מזה" . "  [מוטיבציה]",
		"q18" => "אם אעשה שינוי בחיים שלי אני עלול/ה לאבד את מה שיש לי עכשיו" . "  [מוטיבציה]",
		"q25" => "אני משקיע/ה מאמצים רבים כדי להשיג את המטרות שלי בחיים" . "  [מוטיבציה]",
		"q28" => "אני מרגיש/ה אופטימי לגבי העתיד" . "  [מוטיבציה]",
		"q21" => "כאשר אני מגיע/ה לאחד המוסדות והרשויות, אני דורש/ת לקבל את מה שמגיע לי" . " [מיצוי זכויות]",
		"s1" => "מצב הבריאות שלך, כולל בריאות השיניים",
		"s2" => "המידה שיש לך מענה לצרכים הבסיסיים: מזון, הלבשה, קורת גג",
		"s3" => "רמת החינוך שמקבלים ילדייך",
		"s4" => "דרג את מידת ההערכה העצמית שלך",
		"s5" => "המידה שבה אתה מממש את הפוטנציאל שלך עד היום",
		"s6" => "מערכת היחסים שלך עם ילדייך",
		"s7" => "מערכת היחסים שלך עם בן/ בת זוגך",
		"s8" => "מערכת היחסים עם הקהילה שלך",
		"s9" => "פעילות הפנאי שלך",
		"s10" => "המצב הכלכלי שלך",
		"s11" => "העבודה שלך",
		"s12" => "מערכת התמיכה שלך (משפחה מורחבת, חברים, שכנים, אנשי מקצוע)",
		"r1" => "רמת ההערכה העצמית שלך",
		"r2" => "רמת האופטימיות שלך לעתידך האישי, המשפחתי המקצועי והכלכלי",
		"r3" => "רמת הנכונות שלך להתאמץ כדי שנות דברים במצב המשפחתי שלך",
		"r4" => "רמת הנכונות שלך להתאמץ כדי לשנות דברים במצב התעסוקתי שלך",
		"r5" => "רמת נכונות שלך להתאמץ כדי לשפר את המצב הכלכלי שלך",
		"comments" => "הערות נוספות"
		);
	//$dict = array('' => 0, '1-כלל לא' => 1, '2-במידה מעטה מאוד' => 2, '3- במידה מעטה'=> 3, 
	//					'4-במידה בינונית' => 4, '5-במידה רבה' => 5, '6-במידה רבה מאוד' => 6);

	// get input files name
	if (!isset($_GET['family']) || !isset($_GET['parent']))
		return "file name missing,<br />usage: parentqcs?family=<id>&parent=<id><br />";
	
	$family = $_GET['family'];
	$parent = $_GET['parent'];
	$ppids = explode(',',$parent);
	$str = "";
	if (count($ppids)>1){
		//$str = "found ". $ppids[0] . " and ". $ppids[1]. "<br>";
		$x = get_fields($ppids[0]);
		$y = get_fields($ppids[1]);
	} else {
		//$str = "found ". $ppids[0] . "<br>";
		$x = get_fields($ppids[0]);
	}
	$hdr = "<h2>משפחת ".$x['parent_info']['parent_family_name']. "</h2>";
	if (count($ppids)>1)
		$hdr .= '<table style="width:95%"><tbody>';
	else $hdr .= '<table style="width:55%"><tbody>';

	$colspan = count($ppids)>1 ? 5 : 3;
	$colspan2 = count($ppids)>1 ? 4 : 1;
	$wname = $x['parent_info']['parent_first_name'];
	if (count($ppids)>1)
		$mname = $y['parent_info']['parent_first_name'];

	$hdr .= '<tr><td colspan="'.$colspan.'"><h3 style="text-align:center">מידע גולמי</h3></td><td colspan="'.$colspan2.'"><h3 style="text-align:center">השוואות</h3></td></tr>';
	$hdr .= '<tr class="line"><td class="quest">שאלה</td><td class="ans">'.$wname.' התחלה</td><td class="ans">'.$wname.' בסיום</td>';
	if (count($ppids)>1) // add man results
		$hdr .= '<td class="ans">'.$mname.' התחלה</td><td class="ans">'.$mname.' בסיום</td>';

	$hdr .= '<td class="ans">'.$wname.' סיום-התחלה</td>';
	if (count($ppids)>1){
		$hdr .= '<td class="ans">'.$mname.' סיום-התחלה</td>';
		$hdr .= '<td class="ans">התחלה '.$mname.'-'.$wname.'</td>';
		$hdr .= '<td class="ans">סיום '.$mname.'-'.$wname.'</td>';
	}
	$hdr .= '</tr>';

//	foreach ($x['questionnaire_pre'] as $k => $v){
	foreach ($qdict as $k => $l){
		if ($k == "id") continue; // skip id
		//$v = $x['questionnaire_pre'][$k];
		$v = in_array($k, $reverse_code)?  invers_dict($x['questionnaire_pre'][$k]) : $x['questionnaire_pre'][$k];
		$str .= '<tr class="line">';
		//$v1 = $x['questionnaire_post'][$k];
		$v1 = in_array($k, $reverse_code)? invers_dict($x['questionnaire_post'][$k]): $x['questionnaire_post'][$k];
                $rvrs = in_array($k, $reverse_code)? "**" : "";
		$str .= '<td class="quest">'.$k. $rvrs . ":" . $qdict[$k].'</td>'; // the question
                $str .= '<td class="ans">'.$v.'</td><td class="ans">'.$v1.'</td>'; // woman both answers
		if (count($ppids)>1){ // add man results
			//$y0 = $y['questionnaire_pre'][$k]; 
			//$y1 = $y['questionnaire_post'][$k];
			$y0 = in_array($k, $reverse_code)? invers_dict($y['questionnaire_pre'][$k]) : $y['questionnaire_pre'][$k];
			$y1 = in_array($k, $reverse_code)? invers_dict($y['questionnaire_post'][$k]) : $y['questionnaire_post'][$k];
			$str .= '<td class="ans">'.$y0.'</td><td class="ans">'.$y1.'</td>';
		}

		$str .= '<td class="wdiff">'. digma_diff($v1, $v) . '</td>'; // woman diff end- start
		if (count($ppids)>1){ // add man start vs. end if exists
			$str .= '<td class="ans">'. digma_diff($y1, $y0) .'</td>'; // woman diff end- start
		// compare woman:man at start and at at end

			$str .= '<td class="ans">'. digma_diff($y0, $v) .'</td>'; // man:woman diff at start
			$str .= '<td class="ans">'. digma_diff($y1, $v1) .'</td>'; // man:woman diff at end
		}
		$str .= "</tr>";
	}
	// statistics - only if paid
	$all_stat_res = array(array(), array(), array(), array()); 
	$all_stat_res[0] = qstats($x['questionnaire_pre'], $all_stat_res[0]);
	$all_stat_res[1] = qstats($x['questionnaire_post'], $all_stat_res[1]);
	if (count($ppids)>1){
		$all_stat_res[2] = qstats($y['questionnaire_pre'],$all_stat_res[2]);
		$all_stat_res[3] = qstats($y['questionnaire_post'], $all_stat_res[3]);
	}
	// error_log(print_r($all_stat_res, true));

	$str .= "<tr ><td colspan='9'><h3 style='text-align:center'>ממוצעים לפי תחומים</h3></td></tr>";
	$str .= '<tr><td class="quest">תחום</td><td class="ans">'.$wname.' בהתחלה</td><td class="ans">'.$wname.' בסיום</td>';
	if (count($ppids)>1) // add man results
		$str .= '<td class="ans">'.$mname.' בהתחלה</td><td class="ans">'.$mname.' בסיום</td>';
	$str .= "<td class='ans'>".$wname.' סיום-התחלה</td>';
	if (count($ppids)>1){
		$str .= '<td class="ans">'.$mname.' סיום-התחלה</td>';
		$str .= '<td class="ans">התחלה '.$mname.'-'.$wname.'</td>';
		$str .= '<td class="ans">סיום '.$mname.'-'.$wname.'</td>';
	}
	$str .= "<tr>";

	foreach ($stat_code as $group => $qarray ){ // display all results
		$lstr = "<tr class='line'><td>".$group."  (". count($qarray) . " שאלות)</td><td>".$all_stat_res[0][$group].
							"</td><td>".$all_stat_res[1][$group]."</td>";
		if (count($ppids)>1)
			$lstr .= "<td>".$all_stat_res[2][$group]."</td><td>".$all_stat_res[3][$group]."</td>";
		$lstr .= "<td>". ($all_stat_res[1][$group]-$all_stat_res[0][$group]) ."</td>";
		if (count($ppids)>1){
			$lstr .= "<td>".($all_stat_res[3][$group] - $all_stat_res[2][$group])."</td>";
			$lstr .= "<td>".($all_stat_res[2][$group] - $all_stat_res[0][$group])."</td>";	
			$lstr .= "<td>".($all_stat_res[3][$group] - $all_stat_res[1][$group])."</td>";
		}
		$str .= $lstr . "</tr>";
	}
	$str .= "</tbody></table>";

	$final_notes = "<h3>הבהרות</h3>";
	$final_notes .= "<ul>
						<li>פעולת השוואה: חיסור בין הערכים.</li>
                                                <ul><li>סיום פחות התחלה</li><li>ערכי גבר פחות ערכי אשה</li></ul>
						<li>מתבצעת השוואה רק אם ניתנו תשובות, אחרת השדה ריק.</li>
                                                <li>שאלות מסומנות ** ערכי התשובות הפוכים: q4, q5, q6, q7, q11, q12,q13, q14, q16, q18, q23, q24, q26</li>
						<li> להדפסה הקלידי CTRL-P.</li>
						</ul>";
	//error_log(print_r($x['questionnaire_post'], true));
	return $style . $hdr . $str . $final_notes;

/* handle amadot form
	$form_data = array();
	$str = get_input_form($family, $parent, $form_data);
	return $str;
*/
}
function digma_diff($x, $y){
	global $dict; 

	if (!array_key_exists($x, $dict) || !array_key_exists($y, $dict))
		return "";

	if ($dict[$x] == 0 || $dict[$y] == 0)
		return "";

	return $dict[$x] - $dict[$y];
}
function get_input_form($family, $parent, $form_data){
	$args = array(
	    'post_type'  => 'elementor_lead',
	    'posts_per_page' => -1,
	);
	$postslist = get_posts( $args );
	
	$str = "Found ". count($postslist). " forms";
	
	foreach ( $postslist as $p ) {
	   $str .= '<p>' . $p->post_title . '</p>';
	   // $key_value = get_post_meta( $p->ID, 'meta_input', true );
	   $ld = get_post_meta($p->ID,'lead_data',true);
	   $ld_json = json_decode($ld,true);
	   foreach ($ld_json as $k => $v){
		   $str .= $v['title'] . '=' . $v['value'] . "<br />";
		   $form_data[$v['title']] = $v['value'];
	   }
//	   error_log(print_r( $ld_json , true));
	// map form fields to field-group subfields
	// Move to mycpt plugin & attach to NEW import button
	}
	return $str;
}

?>