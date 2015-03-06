<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
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
 * @subpackage blocktype-externalfeeds
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

$string['title'] = '外部彙集';
$string['description'] = '嵌入一個外面的RSS或ATOM的彙集';
$string['feedlocation'] = '彙集的位置';
$string['feedlocationdesc'] = '一個有效的RSS或ATOM彙集的網址';
$string['itemstoshow'] = '要顯示的訊息則數';
$string['itemstoshowdescription'] = '介於1到20之間';
$string['showfeeditemsinfull'] = '查看所有訊息？';
$string['showfeeditemsinfulldesc'] = '除了訊息的標題外，也顯示完整文字內容';
$string['invalidurl'] = '該網址是無效的。您只能查看HTTP和HTTPS的訊息來源網址。';
$string['invalidfeed'] = '訊息來源似乎無效。回報的錯誤是: %s';
$string['lastupdatedon'] = '最後更新的日期 %s';
$string['defaulttitledescription'] = '自行定義標題(如果留空，則使用彙集所提供的標題)';
