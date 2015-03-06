/**
 * Javascript routines for patch-adminlang
 * @source: http://gitorious.org/mahara-contrib/patch-adminlang
 *
 * @licstart
 * Copyright (C) 2006-2010 David Mudrak <david.mudrak@gmail.com>
 *
 * The JavaScript code in this page is free software: you can
 * redistribute it and/or modify it under the terms of the GNU
 * General Public License (GNU GPL) as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option)
 * any later version.  The code is distributed WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU GPL for more details.
 *
 * As additional permission under GNU GPL version 3 section 7, you
 * may distribute non-source (e.g., minimized or compacted) forms of
 * that code without the copy of the GNU GPL normally required by
 * section 4, provided you include this license notice and a URL
 * through which recipients can access the Corresponding Source.
 * @licend
 */

var strings_in_queue = new Array();
var strings_in_help_queue = new Array();
var running = false;

/**
 * Adds a string to the process queue
 *
 * @uses strings_in_queue
 */
function queue_string(id) {
    var msgbox = $('msg_' + id);

    replaceChildNodes(msgbox, IMG({'src': get_themeurl('images/loading.gif'), 'alt': get_string('stringsaving')}));
    strings_in_queue.push(id);
    if (!running) {
        process_next_in_queue();
    }
}

/**
 * Adds a help string to the process queue
 *
 * @uses strings_in_help_queue
 */
function queue_help_string(id) {
    var msgbox = $('msg_' + id);

    replaceChildNodes(msgbox, IMG({'src': get_themeurl('images/loading.gif'), 'alt': get_string('stringsaving')}));
    strings_in_help_queue.push(id);
    if (!running) {
        process_next_in_help_queue();
    }
}

/**
 * If there is no other string being saved, try and save another one from the queue
 *
 * @uses strings_in_queue
 * @uses running
 */
function process_next_in_queue() {
    if (!running) {
        var nextstring = strings_in_queue.shift();
        if (nextstring) {
            save_string(nextstring);
        }
    }
}

/**
 * If there is no other help string being saved, try and save another one from the queue
 *
 * @uses strings_in_help_queue
 * @uses running
 */
function process_next_in_help_queue() {
    if (!running) {
        var nextstring = strings_in_help_queue.shift();
        if (nextstring) {
            save_help_string(nextstring);
        }
    }
}

/**
 * When the user starts to type something into the field
 */
function string_focus(id) {
    var msgbox = $('msg_' + id);

    if (!running) {
        msgbox.innerHTML = '';
    }
}

/**
 * The input field loose focus
 */
function string_blur(id) {
    var textarea = $('text_' + id);           // textarea
    var text = textarea.value;       // the value of textarea

    if (text !== '') {
        toggle_edit(id, false);
    }
}

/**
 * Replace a token in a string
 *
 * @param string t Token to be found and removed
 * @param string u Token to be inserted
 * @param string s String to be processed
 * @returns string New string
 */
function str_replace(t, u, s) {
    i = s.indexOf(t);
    r = "";
    if (i == -1) {
        return s;
    }
    r += s.substring(0,i) + u;
    if ( i + t.length < s.length) {
        r += str_replace(s.substring(i + t.length, s.length), t, u);
    }
    return r;
}

/**
 * Add class "hidden" to the given element
 *
 * @param string id Element ID
 */
function hide_element(id) {
    var e = $(id);  // element

    e.className = e.className + ' hidden';
}

/**
 * Remove class "hidden" from the given element
 *
 * @param string id Element ID
 */
function show_element(id) {
    var e = $(id);  // element

    e.className = str_replace('hidden', '', e.className);
}

function toggle_edit(id, edit) {
    var textarea = $('text_' + id);

    if (edit) {
        show_element('text_' + id);     // show <textarea>
        textarea.focus();
        hide_element('static_' + id);   // hide static text
    } else {
        hide_element('text_' + id);     // ... and vice-versa
        show_element('static_' + id);
    }
}

/**
 * Save a string via JSON request
 *
 * @param str id The string identifier
 * @uses running
 */
function save_string(id) {
    var msgbox = $('msg_' + id);            // status table cell
    var transbox = $('translated_' + id);   // table cell with textarea
    var textarea = $('text_' + id);           // textarea
    var text = textarea.value;       // the value of textarea
    var stext = $('static_' + id);          // static text containg the saved translation
    var current_file = $('current_file').value;

    if (!current_file) {
        replaceChildNodes(msgbox, IMG({'src': get_themeurl('images/failure.gif'), 'alt': ':('}));
        return;
    }
    running = true;
    sendjsonrequest(
    'lang.json.php',
    {'action': 'save', 'file': current_file, 'stringid': id, 'text': text },
    'POST',
    function (data) {
        var message;
        if ( !data.error ) {
            replaceChildNodes(msgbox, IMG({'src': get_themeurl('images/success.gif'), 'alt': ':)'}), data.message);
            if (data.savedtext == "") {
                transbox.className = transbox.className + ' empty';
            } else {
                transbox.className = str_replace('empty', '', transbox.className);
                stext.innerHTML = data.savedtext;
                textarea.value = stext.firstChild.nodeValue;
                toggle_edit(id, false);
            }
        }
        else {
            if (data.errormessage) {
                message = data.errormessage;
            }
            else {
                message = get_string('stringunsaved');
            }
            replaceChildNodes(msgbox, IMG({'src': get_themeurl('images/failure.gif'), 'alt': ':('}), message);
        }
        running = false;
        process_next_in_queue();
    },
    '',
    true);
}

/**
 * Save a help string via JSON request
 *
 * @param str id The string identifier
 * @uses running
 */
function save_help_string(id) {
    var msgbox = $('msg_' + id);            // status table cell
    var transbox = $('translated_' + id);   // table cell with textarea
    var textarea = $('text_' + id);           // textarea
    var text = textarea.value;       // the value of textarea
    var stext = $('static_' + id);          // static text containg the saved translation
    var current_help_file = $('current_help_file').value;

    if (!current_help_file) {
        msgbox.innerHTML =  IMG({'src': get_themeurl('images/success.gif'), 'alt': ':)'});
        return;
    }
    running = true;
    sendjsonrequest(
    'lang.json.php',
    {'action': 'savehelp', 'helpfile': current_help_file, 'stringid': id, 'text': text },
    'POST',
    function (data) {
        var message;
        if ( !data.error ) {
            replaceChildNodes(msgbox, IMG({'src': get_themeurl('images/success.gif'), 'alt': ':)'}), data.message);
            if (data.savedtext == "") {
                transbox.className = transbox.className + ' empty';
            } else {
                transbox.className = str_replace('empty', '', transbox.className);
                stext.innerHTML = data.savedtext;
                textarea.value = stext.firstChild.nodeValue;
                toggle_edit(id, false);
            }
        }
        else {
            if (data.errormessage) {
                message = data.errormessage;
            }
            else {
                message = get_string('stringunsaved');
            }
            replaceChildNodes(msgbox, IMG({'src': get_themeurl('images/failure.gif'), 'alt': ':('}), message);
        }
        running = false;
        process_next_in_help_queue();
    },
    '',
    true);
}
