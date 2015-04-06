<?php

$values = array();
$values['E'] = $values['I'] = 0;
$values['S'] = $values['N'] = 0;
$values['T'] = $values['F'] = 0;
$values['J'] = $values['P'] = 0;

if ($responses['MBTI_q01'] == 'a') $values['E']++; else $values['I']++;
if ($responses['MBTI_q02'] == 'a') $values['S']++; else $values['N']++;
if ($responses['MBTI_q03'] == 'a') $values['T']++; else $values['F']++;
if ($responses['MBTI_q04'] == 'a') $values['J']++; else $values['P']++;
if ($responses['MBTI_q05'] == 'a') $values['E']++; else $values['I']++;
if ($responses['MBTI_q06'] == 'a') $values['S']++; else $values['N']++;
if ($responses['MBTI_q07'] == 'a') $values['T']++; else $values['F']++;
if ($responses['MBTI_q08'] == 'a') $values['J']++; else $values['P']++;
if ($responses['MBTI_q09'] == 'a') $values['E']++; else $values['I']++;
if ($responses['MBTI_q10'] == 'a') $values['S']++; else $values['N']++;
if ($responses['MBTI_q11'] == 'a') $values['T']++; else $values['F']++;
if ($responses['MBTI_q12'] == 'a') $values['J']++; else $values['P']++;
if ($responses['MBTI_q13'] == 'a') $values['E']++; else $values['I']++;
if ($responses['MBTI_q14'] == 'a') $values['S']++; else $values['N']++;
if ($responses['MBTI_q15'] == 'a') $values['T']++; else $values['F']++;
if ($responses['MBTI_q16'] == 'a') $values['J']++; else $values['P']++;
if ($responses['MBTI_q17'] == 'a') $values['E']++; else $values['I']++;
if ($responses['MBTI_q18'] == 'a') $values['S']++; else $values['N']++;
if ($responses['MBTI_q19'] == 'a') $values['T']++; else $values['F']++;
if ($responses['MBTI_q20'] == 'a') $values['J']++; else $values['P']++;

$mbti_result = '';
if ($values['E'] >= $values['I']) $mbti_result .= 'E'; else $mbti_result .= 'I';
if ($values['S'] >= $values['N']) $mbti_result .= 'S'; else $mbti_result .= 'N';
if ($values['T'] >= $values['F']) $mbti_result .= 'T'; else $mbti_result .= 'F';
if ($values['J'] >= $values['P']) $mbti_result .= 'J'; else $mbti_result .= 'P';

$result_descriptions = array();
$results = $xmlDoc->getElementsByTagName('result');
$possible_results = array();
foreach ($results as $result) {
	$children = $result->cloneNode(true);
	$matches = $children->getElementsByTagName('match');
	$match_array = array();
	foreach ($matches as $match) {
		// Match exact value
		$value = $match->getAttribute('value');
		if (!isset($value) || empty($value)) {
			$value = 0;
		}
		// Value description
		$description = $match->getAttribute(get_config('lang'));
		if (empty($description)) {
			$description = $match->getAttribute(self::get_default_lang($filename));
		}
		$possible_results = array_merge($possible_results, array($value => $description));
	}
}

$summary_html = '';
$summary_html .= '<h2>' . $mbti_result . '</h2>';
$summary_html .= '<div>' . $possible_results[$mbti_result] . '</div>';

?>