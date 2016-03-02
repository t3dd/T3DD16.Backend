define('TYPO3/CMS/Sessions/Calendar/Utility', ['jquery', 'jquery-ui/draggable', 'moment', 'SessionConfig', 'TYPO3/CMS/Backend/Notification'], function($, draggable, moment, SessionConfig, Notification){

    /**
     *  Shows all errors returned from the server as a single error message
     *  with the given title.
     * @param title
     * @param jqxhr
     */
    function showErrorMessages(title, jqxhr)
    {
        if(jqxhr.status = 400) {
            // jquery doesnt parse json onerror ... do it manually
            var json = $.parseJSON(jqxhr.responseText);
            if($.isArray(json.errors)) {
                $.each(json.errors, function(i, obj) {
                    Notification.error(title, obj.title, 3);
                });
            }
        } else {
            // this error is likely a thrown exception or sth that can't be parsed -> parseJSON WILL fail
            Notification.error(title, jqxhr.status + ' ' + jqxhr.statusText, 3);
        }
    }

    /**
     * Displays a success notification
     * @param title
     * @param message
     */
    function showSuccessMessage(title, message)
    {
        Notification.success(title, message, 1);
    }

    /**
     * Parses the plain text response as json (jquery doesnt do this on error!).
     * Concatenates all found error messages and builds the final errrormsg.
     * @param jqxhr
     */
    function buildErrorMsgFromJqxhr(jqxhr)
    {
        // jquery doesnt parse json onerror ... do it manually
        var json = $.parseJSON(jqxhr.responseText);
        var msg = [];
        if($.isArray(json.errors)) {
            $.each(json.errors, function(i, obj) {
                msg.push(obj.title);
            });
        }
        return msg.join('<br><br>');
    }

    /**
     * Takes the given event and extracts all information relevant for persistence.
     * Tests existence and type of exepected properties before adding them to the
     * final Object.
     * @param event
     * @returns {{}}
     */
    function extractBackendDataFromEvent(event)
    {
        var data= {};
        if(event.hasOwnProperty('id') && $.isNumeric(event.id)) {
            data['__identity'] = event.id;
        }
        if(event.hasOwnProperty('start') && moment.isMoment(event.start)) {
            data['begin'] = event.start.utc().format()
        }
        if(event.hasOwnProperty('end') && moment.isMoment(event.end)) {
            data['end'] = event.end.utc().format()
        }
        if(event.hasOwnProperty('resourceId') && $.isNumeric(event.resourceId)) {
            data['room'] = event.resourceId;
        }
        if(event.hasOwnProperty('title') && $.type(event.title) === 'string') {
            data['title'] = event.title;
        }
        return data;
    }

    /**
     * wraps the call to extractBackendDataFromEvent and places the result
     * in wrapper which correctly nests the data for backend mapping
     * @param event
     * @returns object
     */
    function buildJsonDataFromEvent(event)
    {
        return {
            tx_sessions_web_sessionssession: {
                session: extractBackendDataFromEvent(event)
            }
        };
    }

    /**
     * Takes a fullcalendar event and persists it to the backend.
     * Used for Dropping of external events which need special method,
     * since they also have to change their domain model type from
     * accepted to scheduled
     *
     * @param event fullcalendar event
     * @returns Deferred jquery ajax deferred for managing callbacks from the calling function
     */
    function unscheduleSession(event)
    {
        var data = buildJsonDataFromEvent(event);
        return $.ajax({
            type: 'POST',
            data: data,
            url: SessionConfig.links.unschedulesession
        });
    }

    /**
     * Takes a fullcalendar event and persists it to the backend.
     * Used for Dropping of external events which need special method,
     * since they also have to change their domain model type from
     * accepted to scheduled
     *
     * @param event fullcalendar event
     * @returns Deferred jquery ajax deferred for managing callbacks from the calling function
     */
    function scheduleSession(event)
    {
        var data = buildJsonDataFromEvent(event);
        return $.ajax({
            type: 'POST',
            data: data,
            url: SessionConfig.links.schedulesession
        });
    }

    /**
     * Takes a fullcalendar event and persists the changes to the backend.
     * Default function for changes made to already scheduled sessions.
     *
     * @param event fullcalendar event
     * @returns Deferred jquery ajax deferred for managing callbacks from the calling function
     */
    function updateSession(event)
    {
        var data = buildJsonDataFromEvent(event);
        return $.ajax({
            type: 'POST',
            data: data,
            url: SessionConfig.links.updatesession
        });
    }

    /**
     * adds a given event back to the unassigned sessions. makes the new element
     * draggable and sets the necassary data.
     * @param event
     */
    function addUnscheduledEvent(event)
    {
        var data = extractBackendDataFromEvent(event);
        // remove unused properties
        delete data.resourceId;
        delete data.start;
        delete data.end;
        delete data.begin;
        delete data.room;
        // add flattened speakers to data
        data.speakers = event.speakers;

        var identifier = 'unassigned-event-' + data.__identity;
        var element = '<div id="'+identifier+'" class="fc-event unscheduled-event col-xs-3" data-id="'+data.__identity+'" data-title="'+data.title+'" data-speakers="'+data.speakers+'"><strong>'+data.title+'</strong><br><br>'+data.speakers+'</div>';
        $('div#unassigned-sessions').append(element);
        // now make that new event draggable
        $('div#'+identifier).draggable({
            zIndex: 999,
            revert: true,
            revertDuration: 0
        });
    }

    return {
        buildErrorMsgFromJqxhr: buildErrorMsgFromJqxhr,
        buildJsonDataFromEvent: buildJsonDataFromEvent,
        updateSession: updateSession,
        showErrorMessages: showErrorMessages,
        unscheduleSession: unscheduleSession,
        scheduleSession: scheduleSession,
        showSuccessMessage: showSuccessMessage,
        addUnscheduledEvent: addUnscheduledEvent
    };
});