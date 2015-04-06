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

$string['pluginname'] = 'Vprašalniki'; // For tabs and menus...

$string['survey'] = 'vprašalnik';
$string['surveys'] = 'vprašalniki';
$string['Survey'] = 'Vprašalnik';
$string['Surveys'] = 'Vprašalniki';

$string['nosurveysaddone'] = 'Še brez vprašalnikov. %sDodajte in izpolnite enega%s!';

$string['typefeedback'] = 'Nov dostop do rezultatov vprašalnika';
$string['surveyaccesssubject'] = 'Novi rezultati vprašalnika osebe %s';
$string['surveyaccessmessage'] = '%s vam je dodelil/a dostop do rezulatov nedavno izpolnjenega vprašalnika.';
$string['surveyaccessurltext'] = 'Ogled rezultatov vprašalnika';

$string['artefactnotsurvey'] = 'Ni izdelek vrste vprašalnik';

$string['addsurvey'] = 'Dodaj vprašalnik';
$string['surveysettings'] = 'Nastavitve vprašalnika';
$string['completesurvey'] = 'Izpolni vprašalnik';
$string['backtosurveys'] = 'Nazaj na vprašalnike';
$string['instructions'] = 'Navodila';
$string['settings'] = 'Nastavitve';
$string['results'] = 'Rezultati';
$string['noresults'] = 'Ta vprašalnik ne vrne nobenih rezultatov.';
$string['responses'] = 'Odgovori';
$string['noresponses'] = 'Ta vprašalnik ne vsebuje nobenih odgovorov ali pa nimate dovoljenja za ogled teh odgovorov.';
$string['chart'] = 'Graf';
$string['comment'] = 'Komentar...';

$string['selectsurvey'] = 'Izberite vprašalnik...';
$string['categorylanguage'] = 'Jezikovni vprašalniki';
$string['categorypersonal'] = 'Osebnostni vprašalniki';
$string['categorycareer'] = 'Karierni vprašalniki';
$string['categoryother'] = 'Drugi vprašalniki';
$string['categorystaff'] = 'Vprašalniki za osebje';

$string['recipients'] = 'Prejemniki';
$string['allusers'] = 'Vsi uporabniki';
$string['surveyrecipients'] = 'Prejemnik(i) rezultatov vprašalnika';

$string['confirmcancelcreatingsurvey'] = 'Tega vprašalnika niste dokončali. Ali ga zares želite preklicati?';
$string['surveysaved'] = 'Vprašalnik uspešno shranjen';
$string['surveysavefailed'] = 'Neuspešno shranjevanje vprašalnika';
$string['deletesurvey'] = 'Ali zares želite izbrisati ta vprašalnik?';
$string['surveydeleted'] = 'Vprašalnik uspešno izbrisan';
$string['surveyerror'] = 'Ne morem odpreti vprašalnika "%s". Prosim, preverite ali vprašalnik obstaja in ali vsebuje napake.';
$string['surveynameerror'] = 'The name attribute and the filename (without .xml extension) of survey "%s" do not match.';

$string['surveysettings'] = 'Nastavitve vprašalnika';
$string['surveytitle'] = 'Naslov vprašalnika';
$string['surveytitledesc'] = 'Izberite vprašalnik, ki ga želite dodati v vaš listovnik.';
$string['foreignlanguage'] = 'Tuji jezik';
$string['foreignlanguagedesc'] = 'Če je vprašalnik povezan s tujimi jeziki, potem izberite ustrezen tuji jezik.';
$string['surveydesc'] = 'Opis vprašalnika';
$string['surveycreated'] = 'Ustvarjen';
$string['surveymodified'] = 'Spremenjen';

$string['addresponsecomments'] = 'Dodajte komentar k vašemu odgovoru:';

$string['highestvalue'] = 'Najvišja vrednost';
$string['preferences'] = 'Izbire'; // Preference
$string['strengths'] = 'Prednosti';
$string['strengthmild'] = 'Blaga prednost';
$string['strengthstrong'] = 'Močna prednost';
$string['strengthverystrong'] = 'Zelo močna prednost';

// Chart options element strings, used in blocktype instance config forms
$string['chartoptions'] = 'Možnosti grafa';
$string['palette'] = 'Paleta';
$string['palettedescription'] = 'Izberite paleto barv, ki bo uporabljena v grafu vprašalnika.';
$string['legend'] = 'Legenda';
$string['legendkey'] = 'Za legendo uporabi ključe';
$string['legendlabel'] = 'Za legendo uporabi oznake';
$string['fonttype'] = 'Vrsta pisave';
$string['seriffonttype'] = 'Pisava s serifi';
$string['sansseriffonttype'] = 'Pisava brez serifov';
$string['fonttypedescription'] = 'Izberite vrsto pisave, ki bo uporabljena za oznake v grafu vprašalnika.';
$string['fontsize'] = 'Velikost pisave';
$string['fontsizedescription'] = 'Izberite vrsto pisave, ki bo uporabljena za oznake v grafu vprašalnika (v pikah).';
$string['height'] = 'Višina';
$string['heightdescription'] = 'Določite višino grafa vprašalnika (v pikslih).';
$string['width'] = 'Širina';
$string['widthdescription'] = 'Določite širino grafa vprašalnika (v pikslih).';

// Survey analysis strins
$string['surveyanalysis'] = 'Analiza vprašalnika';
$string['surveyanalysisdesc'] = 'Tukaj lahko izberete vprašalnik, ki so ga izpolnili drugi uporabniki (ali vi sami) ter so vam dali dovoljenje za ogled odgovorov na ta vprašalnik. Nato lahko odgovore uporabnikov izvozite v CSV datoteko, ali pa si ogledate analizo odgovorov.';
$string['noaccessiblesurveys'] = 'Ne morete analizirati vprašalnikov, saj nimate dostopa do nobenega vprašalnika.';

$string['generate'] = 'Ustvari';
$string['analysistype'] = 'Vrsta analize';
$string['analysisonline'] = 'Prikaži analizo odgovorov vprašalnika';
$string['analysisexportcsv'] = 'Izvozi odgovore vprašalnika v CSV datoteko';

$string['emptysurveyname'] = 'Niste izbrali vprašalnika';
$string['surveyanalysisgenerated'] = 'Analiza vprašalnika uspešno ustvarjena';
$string['surveyresponsesexported'] = 'Odgovori vprašalnika izvoženi v CSV datoteko';
$string['csvfiledescription'] = 'CSV datoteko z odgovori na vprašalnik je ustvaril vtičnik Mahara Vprašalniki.';

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
