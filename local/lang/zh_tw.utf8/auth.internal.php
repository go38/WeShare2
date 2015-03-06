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
 * @subpackage auth-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();  

$string['internal'] = '內部';
$string['title'] = '內部';
$string['description'] = '驗證mahara數據庫';

$string['completeregistration'] = '完成註冊';
$string['emailalreadytaken'] = '這個電子郵件地址已經在這裡註冊';
$string['iagreetothetermsandconditions'] = '我同意條款及細則';
$string['passwordformdescription'] = '您的密碼必須至少6個字符，並至少包含一個數字和兩個字母';
$string['passwordinvalidform'] = '您的密碼必須至少6個字符，並至少包含一個數字和兩個字母';
$string['registeredemailsubject'] = '您已註冊為 %s';
$string['registeredemailmessagetext'] = '你好 %s,

感謝您在 %s 註冊帳戶。請按照此連結完成註冊過程：

%sregister.php?key=%s

	
這個連結將在24小時內過期。

--
%s 團隊';
$string['registeredemailmessagehtml'] = '<p>你好 %s,</p>
<p>感謝您在 %s 註冊帳戶。請按照此連結完成註冊過程:</p>
<p><a href="%sregister.php?key=%s">%sregister.php?key=%s</a></p>
<p>這個連結將在24小時內過期.</p>

<pre>--
%s 團隊</pre>';
$string['registeredok'] = '<p>您已經成功註冊。請檢查您的電子郵件帳戶的說明，看看如何啟動您的帳戶</p>';
$string['registrationnosuchkey'] = '很抱歉，您的註冊失敗。也許你沒有在24小時之內完成您的註冊。否則，這可能是我們的過錯。';
$string['registrationunsuccessful'] = '很抱歉，您的註冊失敗。這是我們的過錯，請稍後再試。';
$string['usernamealreadytaken'] = '很抱歉，此用戶名已被使用';
$string['usernameinvalidform'] = '用戶名可以包含字母，數字和最常見的符號，而且必須由3至30個字符的長度。不允許使用空格。';
$string['youmaynotregisterwithouttandc'] = '您可能無法註冊，除非您同意遵守<a href="terms.php">條款及細則</a>';
$string['youmustagreetothetermsandconditions'] = '您必須同意<a href="terms.php">條款及細則</a>';