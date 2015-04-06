<?php

$result_descriptions = array();
$results = $xmlDoc->getElementsByTagName('result');
foreach ($results as $result) {
	$section = $result->getAttribute('section');
	$description = $result->getAttribute(get_config('lang'));
	if (empty($description)) {
		$description = $result->getAttribute(self::get_default_lang($filename));
	}
	$result_descriptions = array_merge($result_descriptions, array(
		$section => $description
	));
}

$summary_html = '';

list($valuesV, $percentV) = explode('|', $results_summary['V'], 2);
list($valuesA, $percentA) = explode('|', $results_summary['A'], 2);
list($valuesR, $percentR) = explode('|', $results_summary['R'], 2);
list($valuesK, $percentK) = explode('|', $results_summary['K'], 2);

// calculate the total ticks and corresponding limit
$total = $valuesV + $valuesA + $valuesR + $valuesK;

$limit = 0;
if ($total > 32)  { $limit = 4; }
if ($total <= 32) { $limit = 3; }
if ($total <= 27) { $limit = 2; }
if ($total <= 21) { $limit = 1; }

$vark = array('V' => $valuesV, 'A' => $valuesA, 'R' => $valuesR, 'K' => $valuesK);

// sort scores from highest to lowest score
$vark_sorted = $vark;
arsort($vark_sorted);

// store the highest preference
$keys_sorted = array_keys($vark_sorted);
$highest = $keys_sorted[0];

// now see which of the scores are to be included as preferences
$values_sorted = array_values($vark_sorted);
$preferences[] = $keys_sorted[0];
$bigStep = false;
$n = 1;
while ($n < 4 && $bigStep == false) {
	if (($values_sorted[$n-1] - $values_sorted[$n]) <= $limit) {
		$preferences[] = $keys_sorted[$n];
	} else {
		$bigStep = true;
	}
	$n++;
}

// find out if there is a single preference (i.e. only the highest (first) score is 
// included) and if there is find out how strong the preference is.
$strength = '';
if (!in_array($keys_sorted[1], $preferences)) {
	$diff = $values_sorted[0] - $values_sorted[1] - $limit;
	if ($diff <= 2) {
		$strength = get_string('strengthmild', 'artefact.survey');
	} else if ($diff <= 4) {
		$strength = get_string('strengthstrong', 'artefact.survey');
	} else {
		$strength = get_string('strengthverystrong', 'artefact.survey');
	}
}

// Output highest value...
$summary_html .= '<h4>' . get_string('highestvalue', 'artefact.survey') . '</h4>';
$summary_html .= '<div>' . $items_array[$highest] . '</div>';

$summary_html .= '<h4>' . get_string('preferences', 'artefact.survey') . '</h4>';
// Output multimodal description if more than one preference...
if (count($preferences) > 1) {
	$summary_html .= '<h5>' . $items_array['M'] . '</h5>';
	$summary_html .= '<div>' . $result_descriptions['M'] . '</div>';
}
// Output preference description(s)...
foreach ($results_summary as $key => $value) {
	if (in_array($key, $preferences)) {
		$summary_html .= '<h5>' . $items_array[$key] . '</h5>';
		$summary_html .= '<div>' . $result_descriptions[$key] . '</div>';
	}
}

// Output strength if necessary...
if ($strength != '') {
	$summary_html .= '<h4>' . get_string('strengths', 'artefact.survey') . '</h4>';
	$summary_html .= '<div>' . $strength . '</div>';
}

?>