<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2011 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage artefact-survey
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2010-2011 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

defined('INTERNAL') || die();

$string['pluginname'] = 'Surveys'; // For tabs and menus...

$string['survey'] = 'survey';
$string['surveys'] = 'surveys';
$string['Survey'] = 'Survey';
$string['Surveys'] = 'Surveys';

$string['nosurveysaddone'] = 'No surveys yet. %sAdd and complete one%s!';

$string['typefeedback'] = 'New survey results access';
$string['surveyaccesssubject'] = 'New survey results by %s';
$string['surveyaccessmessage'] = '%s granted you the access to view survey results of recently completed survey.';
$string['surveyaccessurltext'] = 'View survey results';

$string['artefactnotsurvey'] = 'Not a survey artefact';

$string['addsurvey'] = 'Add Survey';
$string['surveysettings'] = 'Survey Settings';
$string['completesurvey'] = 'Complete Survey';
$string['backtosurveys'] = 'Back to Surveys';
$string['instructions'] = 'Instructions';
$string['settings'] = 'Settings';
$string['results'] = 'Results';
$string['noresults'] = 'This Survey doesn\'t return any results.';
$string['responses'] = 'Responses';
$string['noresponses'] = 'This Survey doesn\'t contain any responses or you are not allowed to see these responses.';
$string['responsesnotfound'] = 'The survey "%s" doesn\'t contain any responses. Please check, if you\'ve correctlly filled it.';
$string['chart'] = 'Chart';
$string['comment'] = 'Comment...';

$string['selectsurvey'] = 'Select survey...';
$string['categorylanguage'] = 'Language surveys';
$string['categorypersonal'] = 'Personality surveys';
$string['categorycareer'] = 'Career surveys';
$string['categoryother'] = 'Other surveys';
$string['categorystaff'] = 'Staff surveys';

$string['recipients'] = 'Recipients';
$string['allusers'] = 'All users';
$string['surveyrecipients'] = 'Survey results recipient(s)';

$string['confirmcancelcreatingsurvey'] = 'This Survey has not been completed. Do you really want to cancel?';
$string['surveysaved'] = 'Survey saved successfully';
$string['surveysavefailed'] = 'Survey save failed';
$string['deletesurvey'] = 'Do you really want to delete this Survey?';
$string['surveydeleted'] = 'Survey deleted successfully';
$string['surveyerror'] = 'Cannot open survey "%s". Please check if it exists or if it contains errors.';
$string['surveynameerror'] = 'The name attribute and the filename (without .xml extension) of survey "%s" do not match.';

$string['surveysettings'] = 'Survey Settings';
$string['surveytitle'] = 'Survey Title';
$string['surveytitledesc'] = 'Select the survey that you wish to add to your portfolio.';
$string['foreignlanguage'] = 'Foreign Language';
$string['foreignlanguagedesc'] = 'If the survey is foreign language related, than select that foreign language.';
$string['surveydesc'] = 'Survey Description';
$string['surveycreated'] = 'Created';
$string['surveymodified'] = 'Modified';

$string['addresponsecomments'] = 'Add comment to your response:';

$string['highestvalue'] = 'Highest value';
$string['preferences'] = 'Preferences';
$string['strengths'] = 'Stengths';
$string['strengthmild'] = 'Mild strength';
$string['strengthstrong'] = 'Strong strength';
$string['strengthverystrong'] = 'Very strong strength';

// Chart options element strings, used in blocktype instance config forms
$string['chartoptions'] = 'Chart options';
$string['palette'] = 'Palette';
$string['palettedescription'] = 'Specify which set of colors will be used for survey chart.';
$string['legend'] = 'Legend';
$string['legendkey'] = 'Use legend keys';
$string['legendlabel'] = 'Use legend labels';
$string['fonttype'] = 'Font Type';
$string['seriffonttype'] = 'Serif font';
$string['sansseriffonttype'] = 'Sans-serif font';
$string['fonttypedescription'] = 'Specify the font type used for labels in survey chart.';
$string['fontsize'] = 'Font Size';
$string['fontsizedescription'] = 'Specify the font size for labels in survey chart (in points).';
$string['height'] = 'Height';
$string['heightdescription'] = 'Specify the height for survey chart (in pixels).';
$string['width'] = 'Width';
$string['widthdescription'] = 'Specify the width for survey chart (in pixels).';

// Survey analysis strins
$string['surveyanalysis'] = 'Survey analysis';
$string['surveyanalysisdesc'] = 'Here you can select the survey that other users (or yoursef) have completed and gave you the access to the responses of that survey. Than you can export that responses to the CSV file or view the analysis of the responses online.';
$string['noaccessiblesurveys'] = 'You cannot analyse surveys, because you don\'t have the access to any survey.';

$string['generate'] = 'Generate';
$string['analysistype'] = 'Analysis type';
$string['analysisonline'] = 'Show survey responses analysis online';
$string['analysisexportcsv'] = 'Export survey responses to CSV file';

$string['emptysurveyname'] = 'You didn\'t select a survey';
$string['surveyanalysisgenerated'] = 'Survey analysis successfully generated';
$string['surveyresponsesexported'] = 'Survey responses exported to CSV file';
$string['csvfiledescription'] = 'CSV file with survey responses generated by Mahara Survey plugin.';

//$string[''] = '';

// The names of languages are localized - there's no need for translation!
// Official languages of the European Union
$string['language.bg_BG'] = 'Български – (bg)'; 	// Bulgarian
$string['language.cs_CZ'] = 'čeština – (cs)'; 		// Czech
$string['language.da_DK'] = 'Dansk – (da)'; 		// Danish
$string['language.de_DE'] = 'Deutsch – (de)'; 		// German
$string['language.el_GR'] = 'Ελληνικά – (el)'; 		// Greek
$string['language.en_GB'] = 'English – (en)';
$string['language.es_ES'] = 'Español – (es)'; 		// Spanish
$string['language.et_EE'] = 'eesti keel – (et)'; 	// Estonian
$string['language.fi_FI'] = 'suomi – (fi)'; 		// Finnish
$string['language.fr_FR'] = 'Français – (fr)'; 		// French
$string['language.hr_HR'] = 'Hrvatski – (hr)'; 		// Croatian
$string['language.hu_HU'] = 'magyar – (hu)'; 		// Hungarian
$string['language.is_IS'] = 'Íslenska – (is)'; 		// Icelandic
$string['language.it_IT'] = 'Italiano – (it)'; 		// Italian
$string['language.lt_LT'] = 'Lietuvių – (lt)'; 		// Lithuanian
$string['language.lv_LV'] = 'Latviešu – (lv)'; 		// Latvian
$string['language.mt_MT'] = 'Malti – (mt)'; 		// Maltese
$string['language.nl_NL'] = 'Nederlands – (nl)'; 	// Dutch
$string['language.no_NO'] = 'Norsk – (no)'; 		// Norwegian
$string['language.pl_PL'] = 'polski – (pl)'; 		// Polish
$string['language.pt_PT'] = 'Português – (pt)'; 	// Portuguese
$string['language.ro_RO'] = 'Română – (ro)'; 		// Romanian
$string['language.sk_SK'] = 'slovenčina – (sk)'; 	// Slovak
$string['language.sl_SI'] = 'slovenščina – (sl)'; 	// Slovenian
$string['language.sv_SE'] = 'Svenska – (sv)'; 		// Swedish
$string['language.tr_TR'] = 'Türkçe – (tr)'; 		// Turkish
// Other European languages (not official in European Union)
$string['language.by_BE'] = 'Беларуская - (by)'; 	// Belarussian
$string['language.ba_BS'] = 'bosanski - (ba)'; 		// Bosnian
$string['language.fr_BR'] = 'brezhoneg - (fr)'; 	// Breton
$string['language.es_CA'] = 'Català - (es)'; 		// Catalan, Valenican
$string['language.gb_CY'] = 'Cymraeg - (gb)'; 		// Welsh
$string['language.es_EU'] = 'euskara - (es)'; 		// Basque
$string['language.fo_FO'] = 'føroyskt - (fo)'; 		// Faroese
$string['language.ie_GA'] = 'Gaeilge - (ie)'; 		// Irish
$string['language.gb_GD'] = 'Gàidhlig - (gb)'; 		// Scots Gaelic
$string['language.am_HY'] = 'Հայերեն - (am)'; 		// Armenian
$string['language.ge_KA'] = 'ქართული - (ge)'; 		// Georgian
$string['language.la_LA'] = 'lingua latina - (la)'; // Latin
$string['language.mk_MK'] = 'македонски - (mk)'; 	// Macedonian
$string['language.ru_RU'] = 'русский - (ru)'; 		// Russian
$string['language.al_SQ'] = 'Shqip - (al)'; 		// Albanian
$string['language.rs_SR'] = 'српски - (rs)'; 		// Serbian
$string['language.ua_UK'] = 'українська - (ua)'; 	// Ukrainian

?>
