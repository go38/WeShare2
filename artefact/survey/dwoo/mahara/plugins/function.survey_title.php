<?php

/**
 * Dwoo {survey_name} function plugin
 *
 * Type:     function<br>
 * Name:     survey_title<br>
 * Date:     June 22, 2006<br>
 * Purpose:  Get the survey title from XML
 * @author   Gregor Anzelj
 * @version  1.0
 * @return Survey title instead of survey filename
 */
function Dwoo_Plugin_survey_title(Dwoo $dwoo, $survey, $lang=null) {
	safe_require('artefact', 'survey');

	$return = ArtefactTypeSurvey::get_survey_title_from_xml($survey);
	if ($lang != null) {
		$return .= ' (' . substr($lang, 0, 2) . ')';
	}

	return $return;
}

?>
