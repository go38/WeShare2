<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage lang/zh_tw.utf8
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @author     Hsin Wen-Yi
 * @copyright  Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License.
 *
 */

defined('INTERNAL') || die();

$string['accessdenied'] = '拒絕存取';
$string['accessdeniedexception'] = '您沒有權限檢視這頁面';
$string['artefactnotfound'] = '找不到id= %s 的作品物件';
$string['artefactnotfoundmaybedeleted'] = '找不到id= %s 的作品物件(也許已經被刪除了？)';
$string['artefactnotinview'] = '作品物件 %s 不在頁面 %s';
$string['artefactonlyviewableinview'] = '這類作品物件只能在頁面中看得到';
$string['artefactpluginmethodmissing'] = '作品的外掛%s 必須實做 %s 但是並沒有';
$string['artefacttypeclassmissing'] = '作品類型必須實施級別。 沒有 %s';
$string['artefacttypenametaken'] = '作品類型 %s 已被另一個外掛(%s) 佔用。';
$string['blockconfigdatacalledfromset'] = '配置資料必須直接設定，使用PluginBlocktype::instance_config_save 作為替代';
$string['blockinstancednotfound'] = '找不到id=%s 的區塊實例';
$string['blocktypelibmissing'] = '區塊%s缺少lib.php於作品外掛%s中';
$string['blocktypemissingconfigform'] = '作品類型 %s 必須實施instance_config_form';
$string['blocktypenametaken'] = '區塊類型 %s 已被另一個外掛(%s) 佔用。';
$string['blocktypeprovidedbyartefactnotinstallable'] = '這將會安裝為作品外掛 %s 安裝的一部分。';
$string['couldnotmakedatadirectories'] = '由於某些原因，某些核心資料目錄無法建立。 這是不可能發生的，因為Mahara在之前已發現資料目錄(data root directory)是可寫入的。 請檢查資料目錄的權限。';
$string['curllibrarynotinstalled'] = '您的伺服器沒有包含curl延伸套件。 Mahara需使用此程式跟Moodle整合及擷取外部消息來源。 請確認php.ini已載入，或如果未安裝則安裝它。';
$string['datarootinsidedocroot'] = '你已將你的資料目錄設定在文件目錄中。 這是很大的安全問題，這樣任何人也可以直接要求會議(session)資料 (以搶奪別人的會議)，或其他人上傳而他們不能存取的檔案。 請將資料目錄配置於文件目錄之外。';
$string['datarootnotwritable'] = '你的已定義資料目錄 (data root directory)，%s，無法被寫入. 這表示需要上傳的會議(session)資料，用戶檔案或其他資料均不能儲存在你的伺服器。 如目錄並不存在，請建立目錄，或將目錄的擁有權交給網站伺服器用戶。';
$string['dbconnfailed'] = 'Mahara不能連結至應用程式資料庫。

 * 如果您正使用Mahara，請稍候再重試
 * 如果您是管理員，請檢查您的資料庫設定及確認資料庫是可以使用

收到的錯誤為:';
$string['dbversioncheckfailed'] = '您的資料庫伺服器版本太舊，因此Mahara無法成功運作。 您的伺服器是 %s %s，但Mahara所需的版本至少是 %s。';
$string['domextensionnotloaded'] = '您的伺服器環境沒有包含dom 延伸套件。 Mahara需使用此程式分析來自不同來源的XML資料。';
$string['gdextensionnotloaded'] = '您的伺服器沒有包含 gd 延伸套件。 Mahara需使用此程式調整上傳圖片的大小或其他有關的操作。 請確認php.ini已載入，或如果未安裝則安裝它。';
$string['gdfreetypenotloaded'] = '您的gd 延伸套件沒有包含Freetype的支援。 Mahara需使用此程式產生CAPTCHA圖形驗證。 請確認gd已經設置好它。';
$string['interactioninstancenotfound'] = '找不到id= %s 的活動實例';
$string['invaliddirection'] = '無效的指向 %s';
$string['invalidviewaction'] = '無效的頁面控制動作： %s';
$string['jsonextensionnotloaded'] = '您的伺服器沒有包含JSON延伸套件。 Mahara需使用此程式從瀏覽器傳送資料或傳送資料至瀏覽器 。 請確認php.ini已載入，或如果未安裝則安裝它。';
$string['magicquotesgpc'] = '您有危險的PHP設定，magic_quotes_gpc已啟動。 Mahara正嘗試處理它，但你應該解決它';
$string['magicquotesruntime'] = '您有危險的PHP設定，magic_quotes_runtime已啟動。 Mahara正嘗試處理它，但你應該解決它';
$string['magicquotessybase'] = '您有危險的PHP設定，magic_quotes_sybase已啟動. Mahara正嘗試處理它，但你應該解決它';
$string['missingparamblocktype'] = '先嘗試選擇要新增的區塊類型';
$string['missingparamcolumn'] = '沒有指明欄位';
$string['missingparamid'] = '沒有帳號';
$string['missingparamorder'] = '沒有指明次序';
$string['mysqldbextensionnotloaded'] = '您的伺服器沒有包含mysql延伸套件。 Mahara需使用此程式儲存資料至資料庫。 請確認php.ini已載入，或如果未安裝則安裝它。';
$string['notartefactowner'] = '您並不擁有此元件';
$string['notfound'] = '找不到';
$string['notfoundexception'] = '找不到您要尋找的頁面';
$string['onlyoneblocktypeperview'] = '不能放超過一個 %s 區塊在一個頁面上。';
$string['onlyoneprofileviewallowed'] = '您只能有一個個人資料頁面。';
$string['parameterexception'] = '一個必要的參數遺失了';
$string['pgsqldbextensionnotloaded'] = '您的伺服器環境沒有包含pgsql延伸套件。 Mahara需使用此程式儲存資料至資料庫。  請確認php.ini已載入，或如果未安裝則安裝它。';
$string['phpversion'] = 'Mahara無法在 PHP小於 %s 的版本運作。 請升級您的 PHP 版本，或將Mahara移至另一個主機。';
$string['postmaxlessthanuploadmax'] = '您的PHP的post_max_size 參數設定值(%s) 小於upload_max_filesize 的參數值(%s)，上傳超過%s時會錯誤，也不會顯示錯誤訊息。通常，post_max_size值應該大過於upload_max_filesize。';
$string['registerglobals'] = '您有危險的PHP設定，register_globals已啟動。 Mahara正試圖解決這個問題，但您真的應該修復它。如果您使用的共享主機和您的主機，它允許你應該包括下行在htaccess檔案：php_flag register_globals off';
$string['safemodeon'] = '您的伺服器正以安全模式運作。 Mahara並不支援安全模式。 您必須於php.ini檔案或此網站的apache設定中關掉它。如果您使用共享主機，除了詢問您的主機提供者，可能無法將安全模式關掉。 也許您可以考慮移至別的主機。';
$string['sessionextensionnotloaded'] = '您的伺服器環境沒有包含連線延伸套件(session extension)。 Mahara需使用此程式支援用戶登入。 請確認php.ini已載入，或如果未安裝則安裝它。';
$string['smallpostmaxsize'] = '您的PHP的post_max_size 參數設定值(%s) 太小了，上傳超過%s時會錯誤，也不會顯示錯誤訊息。';
$string['themenameinvalid'] = '佈景 \'%s\' 的名稱包含無效的字元。';
$string['timezoneidentifierunusable'] = '在您的網站主機上的PHP不會返回有用的值 - 一個時區識別(%%z)- 的特定日期格式，如LEAP2A匯出時將會中斷。 %%z是一個PHP的日期格式代碼。這個問題通常是因為在Windows上運行PHP的限制。';
$string['unknowndbtype'] = '你的伺服器配置推薦不明的資料庫類型。 有效數值為"postgres8" 及 "mysql5"。 請於config.php更改資料庫類型設定。';
$string['unrecoverableerror'] = '發生無法復原的錯誤。 這表示您遇到系統的故障。';
$string['unrecoverableerrortitle'] = '%s - 網站不能使用';
$string['versionphpmissing'] = '外掛元件 %s %s 沒有 version.php 檔案！';
$string['viewnotfound'] = '找不到id= %s 的頁面';
$string['viewnotfoundexceptionmessage'] = '您嘗試存取的頁面並不存在！';
$string['viewnotfoundexceptiontitle'] = '找不到頁面';
$string['xmlextensionnotloaded'] = '您的伺服器環境沒有包含 %s 延伸套件。 Mahara需使用此程式分析來自不同來源的XML資料。 請確認php.ini已載入，或如果未安裝則安裝它。';
$string['youcannotviewthisusersprofile'] = '您不能檢視這用戶的個人資料頁';
