/**
 * Survey Contextual Help function (changed Contextual Help function)
 * @source: http://gitorious.org/mahara/mahara
 *
 * @licstart
 * Copyright (C) 2011  Gregor Anzelj, gregor.anzelj@gmail.com
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

/*
 * Survey Contextual Help
 *
 * This is changed contextualHelp function from htdocs/js/mahara.js
 * It allows adding help to questions in survey and changes the location
 * of help files. Help files are located in subfolder with survey name...
 *
 * Example:
 * Let's assume that we have survey, called 'test.survey'
 * The location of the survey itself is:
 * - htdocs/artefact/survey/surveys/test.survey.xml
 *
 * The locations of help files are:
 * - htdocs/artefact/survey/surveys/test.survey/en.utf8/q01.html (English help file for question q01)
 * - htdocs/artefact/survey/surveys/test.survey/sl.utf8/q01.html (Slovenian help file for question q01)
 * - htdocs/artefact/survey/surveys/test.survey/de.utf8/q01.html (German help file for question q01)
 *   etc.
 */
function surveyHelp(pluginType, pluginName, surveyName, question, language, ref) {
    var key;
    //var target = $(surveyName + '_' + question + '_container');
    var url = config.wwwroot + 'artefact/survey/help.json.php';
    var url_params = {
        'plugintype': pluginType,
        'pluginname': pluginName
    };

    var parentElement = 'messages';

    // deduce the key
    key = pluginType + '/' + pluginName + '/surveys/' + surveyName + '/' + language + '/' + question;
    url_params.survey = surveyName;
    url_params.question = question;
    url_params.language = language;

    // close existing contextual help
    if (contextualHelpSelected) {
        removeElement(contextualHelpContainer);

        contextualHelpContainer = null;
        if (key == contextualHelpSelected) {
            // we're closing an already open one by clicking on the ? again
            contextualHelpSelected = null;
            contextualHelpOpened = false;
            return;
        } else {
            // we're closing a DIFFERENT one that's already open (we want to
            // continue and open the new one)
            contextualHelpSelected = null;
            contextualHelpOpened = false;
        }
    }

    // create and display the container
    contextualHelpContainer = DIV({
            'style': 'position: absolute;',
            'class': 'contextualHelp hidden'
        },
        IMG({'src': config.theme['images/loading.gif']})
    );
    appendChildNodes($(parentElement), contextualHelpContainer);

    var position = getElementPosition(ref);
    var dimensions = getElementDimensions(contextualHelpContainer);

    // Adjust the position. The element is moved towards the centre of the
    // screen, based on which quadrant of the screen the help icon is in
    screenDimensions = getViewportDimensions();
    if (position.x + dimensions.w < screenDimensions.w) {
        // Left of the screen - there's enough room for it
        position.x += 15;
    }
    else {
        position.x -= dimensions.w;
    }
    position.y -= 10;

    // Once it has been positioned, make it visible
    setElementPosition(contextualHelpContainer, position);
    removeElementClass(contextualHelpContainer, 'hidden');

    contextualHelpSelected = key;

    // load the content
    if (contextualHelpCache[key]) {
        buildContextualHelpBox(contextualHelpCache[key]);
        callLater(0, function() { contextualHelpOpened = true; });
        ensureHelpIsOnScreen(contextualHelpContainer, position);
    }
    else {
        if (contextualHelpDeferrable && contextualHelpDeferrable.cancel) {
            contextualHelpDeferrable.cancel();
        }

        badIE = true;
        sendjsonrequest(url, url_params, 'GET', function (data) {
            if (data.error) {
                contextualHelpCache[key] = data.message;
                replaceChildNodes(contextualHelpContainer, data.message);
            }
            else {
                contextualHelpCache[key] = data.content;
                buildContextualHelpBox(contextualHelpCache[key]);
            }
            contextualHelpOpened = true;
            ensureHelpIsOnScreen(contextualHelpContainer, position);
            processingStop();
        },
        function (error) {
            contextualHelpCache[key] = get_string('couldnotgethelp');
            contextualHelpContainer.innerHTML = contextualHelpCache[key];
            processingStop();
            contextualHelpOpened = true;
        },
        true);
    }
    contextualHelpContainer.focus();
}
