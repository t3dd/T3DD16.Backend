/**
 * Created by aschmid on 03.02.2016.
 */
/*
 TODO: [TASK] Import icons for Rooms and Sessions
 */


define(['jquery', 'TYPO3/CMS/Sessions/fullcalendar', 'TYPO3/CMS/Sessions/scheduler', 'SessionConfig', 'TYPO3/CMS/Backend/Notification', 'moment', 'TYPO3/CMS/Sessions/rivets'],
    function ($, fullcalendar, scheduler, SessionConfig, Notification, moment, rivets) {

    var rivetData = {
        selection: {
            enabled : false,
            start : null,
            end : null
        },
        actions: {
            analyze: analyzeSlot
        }
    };

    var rivetViews;

    var calendar = {
        instance: $('#calendar'),
        initialize: function () {
            // Generate Buttons dynamically (one for each day of DD based on TS Config)
            var btnTextConfig = [];
            var btnDynConfig = {};
            $.each(SessionConfig.days, function (i, day) {
                var identifier = 'dynDaySel' + i;
                var dayOfEvent = i + 1;
                btnDynConfig[identifier] = {
                    text: 'Day ' + dayOfEvent,
                    click: function () {
                        calendar.instance.fullCalendar('gotoDate', day);
                    }
                };
                btnTextConfig[i] = identifier;
            });
            btnTextConfig = btnTextConfig.join(',');
            /**
             * Configuration
             * @see {@link http://fullcalendar.io/docs/|Fullcalendar Documentation}
             * @see {@link http://fullcalendar.io/scheduler/|Scheduler Plugin}
             */
            this.instance.fullCalendar({
                schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
                /**
                 * General Display
                 * @see {@link http://fullcalendar.io/docs/display/}
                 */
                header: {
                    left: btnTextConfig + ' prev,next reloadBtn',
                    center: 'title',
                    right: 'agendaDay,timelineDay,agendaAllDays,timelineAllDays'
                },
                customButtons: $.extend({}, btnDynConfig, {
                    reloadBtn: {
                        text: 'Reload',
                        click: function () {
                            calendar.instance.fullCalendar('refetchEvents');
                        }
                    }
                }),
                businessHours: {
                    start: '08:00',
                    end: '20:00',
                    dow: [0, 1, 2, 3, 4, 5, 6, 7]
                },
                aspectRatio: 2.7,
                /**
                 * Timezone
                 * @see {@link http://fullcalendar.io/docs/timezone/}
                 */
                timezoneParam: 'UTC',
                /**
                 * Views
                 * @see {@link http://fullcalendar.io/docs/views/}
                 */
                defaultView: 'agendaDay',
                views: {
                    agendaDay: {
                        buttonText: 'Day Calendar',
                        type: 'agendaDay',
                        snapDuration: '00:15'
                    },
                    timelineDay: {
                        buttonText: 'Day Schedule',
                        type: 'agendaDay',
                        snapDuration: '00:15'
                    },
                    /*agendaAllDays: {
                        buttonText: 'Full Calendar',
                        type: 'agenda',
                        duration: {days: SessionConfig.days.length}
                    },*/
                    timelineAllDays: {
                        buttonText: 'Full Schedule',
                        type: 'timeline',
                        duration: {days: SessionConfig.days.length}
                    }
                },
                /**
                 * Agenda Options
                 * @see {@link http://fullcalendar.io/docs/agenda/}
                 */
                allDaySlot: false,
                minTime: '05:00',
                maxTime: '22:00',
                slotEventOverlap: false,
                slotDuration: moment.duration(15, 'minutes'),
                /**
                 * Current Date
                 * @see {@link http://fullcalendar.io/docs/current_date/}
                 */
                defaultDate: SessionConfig.days[0],
                nowIndicator: true,
                /**
                 * Clicking & Hovering
                 * @see {@link http://fullcalendar.io/docs/mouse/}
                 */
                dayClick: function (date, jsEvent, view, resourceObj) {
                    // clicked on a day -> basically not an event
                    console.log('dayClick');
                },
                eventClick: function (event, jsEvent, view) {
                    // clicked on an event
                    console.log('eventClick');
                },
                /**
                 * Selection
                 * @see {@link http://fullcalendar.io/docs/selection/}
                 */
                selectable: true,
                select: function (start, end, jsEvent, view, resource) {
                    onSelectionMade(start, end, jsEvent, view, resource);
                },
                unselect: function(view, jsEvent) {
                    onSelectionRemoved(view, jsEvent)
                },
                /**
                 * Event Data
                 * @see {@link http://fullcalendar.io/docs/event_data/}
                 */
                events: SessionConfig.links.getsessions,
                startParam: 'tx_sessions_web_sessionssession[start]',
                endParam: 'tx_sessions_web_sessionssession[end]',
                // changed made here have to be done in externalDrop function as well... for now
                defaultTimedEventDuration: moment.duration({ hours:1, minutes:30 }),
                /**
                 * Event Rendering
                 * @see {@link http://fullcalendar.io/docs/event_rendering/}
                 */
                eventRender: function (event, element) {
                    eventRender(event, element);
                },
                /**
                 * Event Dragging & Resizing
                 * @see {@link http://fullcalendar.io/docs/event_ui/}
                 */
                editable: true,
                eventDurationEditable: true,
                eventOverlap: false,
                eventDragStart: function(event, jsEvent, ui, view) {
                    //console.log('eventDragStart'); irrelevant
                },
                eventDragStop: function(event, jsEvent, ui, view) {
                    //console.log('eventDragStop');
                    unscheduleIfWanted(event, jsEvent, ui, view);
                },
                eventDrop: function(event, delta, revertFunc, jsEvent, ui, view) {
                    // session was dragged to another position -> start and end changed
                    //console.log('eventDrop');
                    eventChanged(event, delta, revertFunc, jsEvent, ui, view);
                },
                eventResize: function(event, delta, revertFunc, jsEvent, ui, view) {
                    // session was resized --> start or end changed
                    //console.log('eventResize');
                    eventChanged(event, delta, revertFunc, jsEvent, ui, view);
                },
                /**
                 * Dropping External Elements
                 * @see {@link http://fullcalendar.io/docs/dropping/}
                 */
                droppable: true,
                dropAccept: '.unscheduled-event',
                drop: externalDrop,
                /**
                 * Timeline View
                 * @see {@link http://fullcalendar.io/docs/timeline/}
                 */
                resourceAreaWidth: '15%',
                resourceLabelText: 'Rooms',
                /**
                 * Resource Data
                 * @see {@link http://fullcalendar.io/docs/resource_data/}
                 */
                resources: SessionConfig.links.getrooms
            });

            /**
             * Initialize rivets {@link http://rivetsjs.com/}
             * Used as mini MVC :)
             */
            rivetView = rivets.bind($('div#rivet-container'), rivetData);
        }
    };



    function onSelectionMade(start, end, jsEvent, view, resource)
    {
        // happend sometimes. don't know why... prevents wrong callbacks fired
        if(typeof(view) === 'undefined') {
            return;
        }
        // if you draw a selection and then click somewhere there is actually a
        // selection made with the minimum time span possible. don't register this
        // as a real selection... rather use this as unselect (whichs callbach is
        // fired before this one)
        var duration = calendar.instance.fullCalendar('option', 'slotDuration');
        if(moment.isDuration(duration) && moment.isMoment(start) && moment.isMoment(end)) {
            var localStart = start.clone();
            var localEnd = localStart.add(duration);
            if(localEnd.isSame(end)) {
                return;
            }
        }
        rivetData.selection.enabled = true;
        rivetData.selection.start = start;
        rivetData.selection.end = end;
    }

    function onSelectionRemoved()
    {
        rivetData.selection.enabled = false;
        rivetData.selection.start = null;
        rivetData.selection.end = null;
    }

    function analyzeSlot()
    {
        if($.type(rivetData) === 'undefined') {
            return;
        }
        if(rivetData.selection.start === null || rivetData.selection.end === null) {
            Notification.info('Warning', 'You must make a selection first!', 1);
            return;
        }
        console.log('analyzing from %s until %s', rivetData.selection.start, rivetData.selection.end);
        // TODO: implement logic
    }

    /**
     * Helperfunction which removes the event with the given id from the current calendar.
     * This happens locally in memory and does not persist.
     * ATM used for removing dropped events that can not be scheduled.
     * @param id
     */
    function removeEvent(id)
    {
        if($.isNumeric(id)) {
            calendar.instance.fullCalendar( 'removeEvents', id);
        }
    }

    /**
     * Callback triggered when an exisiting event was modified. This happens when the
     * start or end is dragged or the whole event was dragged to a new position.
     * Function currently used for resize and drop callback.
     *
     * @param event
     * @param delta
     * @param revertFunc
     * @param jsEvent
     * @param ui
     * @param view
     */
    function eventChanged(event, delta, revertFunc, jsEvent, ui, view)
    {
        updateSession(event).done(
            /**
             * Session was successfully updated
             * -> show notification
             */
            function(){
                Notification.success('Session updated!', event.title, 1);
            }.bind(this))
            .fail(
            /**
             * Sth went wrong with the update
             * -> show notification with error messages
             * -> call the revertFunc to position the event back
             */
            function(jqxhr){
                revertFunc();
                showErrorMessages('Update failed!', jqxhr);
            }.bind(this));
    }

    /**
     * Callback when external events have been dropped
     * @param date
     * @param jsEvent
     * @param ui
     * @param resourceId
     */
    function externalDrop( date, jsEvent, ui, resourceId )
    {

        // event fires too often... catch wrong calls
        if($.type(date) === 'undefined') {
            return;
        }
        // we have to save this event manually...
        var data = {
            title: $(this).data('title'),
            id: $(this).data('id')
        };
        data['start'] = $.fullCalendar.moment(date);
        // set end datetime based on the default event length
        var duration = calendar.instance.fullCalendar('option', 'defaultTimedEventDuration');
        data['end'] = $.fullCalendar.moment(date).add(duration);
        if($.isNumeric(resourceId)) {
            data.resourceId = resourceId;
        }
        // hide the dragged event until we know if it can be saved or not
        $(this).hide();
        // schedule the event
        scheduleSession(data).done(
            /**
             * Session was successfully scheduled (start and end set + type changed)
             * -> remove the hidden droppable event
             * -> show a notification
             */
            function(){
            $(this).remove();
            Notification.success('Success', 'Session scheduled', 1);
        }.bind(this)).fail(
            /**
             * The session could not be scheduled
             * -> show the hidden event again, since it can be dropped again
             * -> remove the event from the calendar (gets added automatically)
             * -> show a notification with the first error message
             */
            function(jqxhr){
            $(this).show();
            removeEvent(data.id);
            showErrorMessages('Scheduling failed!', jqxhr);
        }.bind(this));
    }

    /**
     *  Shows all errors returned from the server as a single error message
     *  with the given title.
     * @param title
     * @param jqxhr
     */
    function showErrorMessages(title, jqxhr)
    {
        // jquery doesnt parse json onerror ... do it manually
        var json = $.parseJSON(jqxhr.responseText);
        if($.isArray(json.errors)) {
            $.each(json.errors, function(i, obj) {
                Notification.error(title, obj.title, 3);
            });
        }
    }

    /**
     * This callback is fired on eventDragStop.
     * We check if user dropped the scheduled event on the unschedule area
     * and do so if positions match.
     * -> unschedule with backend
     * -> remove event from fullcalendar
     * -> add event to the external list for rescheduling
     * @param event
     * @param jsEvent
     * @param ui
     * @param view
     */
    function unscheduleIfWanted(event, jsEvent, ui, view)
    {
        var x = jsEvent.clientX, y = jsEvent.clientY;
        var external_events = $('#unschedule-area');
        var offset = external_events.offset();
        offset.right = external_events.width() + offset.left;
        offset.bottom = external_events.height() + offset.top;

        // Compare
        if (x >= offset.left
            && y >= offset.top
            && x <= offset.right
            && y <= offset .bottom) {

            // the fullcalendar event was dropped on our unschedule area
            unscheduleSession(event).done(function(){
                // successfully unscheduled
                Notification.success('Event unscheduled!', event.title, 1);
                // remove it from the calendar
                removeEvent(event.id);
                // add back to externals
                $('div#unassigned-sessions').append('<div class="fc-event unscheduled-event col-xs-3" data-id="'+event.id+'" data-title="'+event.title+'">'+event.title+'</div>');
            }.bind(this)).fail(function(jqxhr){
                showErrorMessages('Unscheduling failed!', jqxhr);
            }.bind(this));
        }
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
     * @returns object
     */
    function buildJsonDataFromEvent(event)
    {
        var data = {
            tx_sessions_web_sessionssession: {
                session: {

                }
            }
        };
        if(event.hasOwnProperty('id') && $.isNumeric(event.id)) {
            data.tx_sessions_web_sessionssession.session['__identity'] = event.id;
        }
        if(event.hasOwnProperty('start') && moment.isMoment(event.start)) {
            data.tx_sessions_web_sessionssession.session['begin'] = event.start.utc().format()
        }
        if(event.hasOwnProperty('end') && moment.isMoment(event.end)) {
            data.tx_sessions_web_sessionssession.session['end'] = event.end.utc().format()
        }
        if(event.hasOwnProperty('resourceId') && $.isNumeric(event.resourceId)) {
            data.tx_sessions_web_sessionssession.session['room'] = event.resourceId;
        }
        if(event.hasOwnProperty('title') && $.type(event.title) === 'string') {
            data.tx_sessions_web_sessionssession.session['title'] = event.title;
        }
        return data;
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
     * Takes a fullcalendar event and persists the changed to the backend.
     * Default function for changed made to already scheduled sessions.
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
     * Render more information in an event than time and title.
     * @param event
     * @param element
     */
    function eventRender(event, element)
    {
        "use strict";
        if($.type(element) !== 'undefined') {
            element.find('.fc-title').append("<br/><br/>" + event.speakers);
        }
    }

    /*
     * GRAVEYARD BEGIN
     */
    var counter = 0;

    /**
     * @deprecated
     * Switch two events when they're overlapping.
     *
     * @param stillEvent
     * @param movingEvent
     * @param overlapCounter
     * @returns {boolean} if false, overlap isn't allowed.
     */
    function overlapEvent(stillEvent, movingEvent) {
        console.log('overlapEvent'); return;
        movingEvent.stillEvent = {
            id: stillEvent.id,
            resourceId: stillEvent.resourceId,
            start: $.extend({}, stillEvent.start),
            end: $.extend({}, stillEvent.end)
        };
        counter++;
        if (stillEvent.resourceId != movingEvent.resourceId) {
            return true;
        } else {
            if (counter == 3) {
                return true;
            } else if (counter > 3 || counter < 3) {
                //delete(movingEvent['stillEvent']);
                //delete(movingEvent['movingEvent']);
                return false;
            }
        }
    }

    /**
     * @deprecated
     *
     * @param event
     * @param jsEvent Holds the native JavaScript event with low-level information such as mouse coordinates.
     * @param ui
     * @param view Holds the current View Object.
     */
    function eventDragStart(event, jsEvent, ui, view) {
        console.log('eventDragStart'); return;
        if (typeof event.movingEvent != 'undefined') {
            delete(event['movingEvent']);
        }
        if (typeof event.stillEvent !== 'undefined') {
            delete(event['stillEvent']);
        }
        event.movingEvent = {
            id: event.id,
            resourceId: event.resourceId,
            start: $.extend({}, event.start),
            end: $.extend({}, event.end)
        };
        counter = 0;
    }

    /**
     * @deprecated
     *
     * @param event
     * @param delta Represents the amount of time the event was moved.
     * @param reverFunc Reverts the event's start/end datea to the values before the drag.
     * @param jsEvent Holds the native JavaScript event with low-level information such as mouse coordinates.
     * @param ui
     * @param view Holds the current View Object.
     */
    function eventDrop(event, delta, revertFunc, jsEvent, ui, view) {
        console.log('eventDrop'); return;
        if (typeof event.stillEvent !== 'undefined') {
            console.log('stillevent defined');
            var tempStillEvent = calendar.fullCalendar('clientEvents', event.stillEvent.id)[0];
            if (event.end.toString() == tempStillEvent.end.toString() && event.start.toString() == tempStillEvent.start.toString() && event.resourceId == event.stillEvent.resourceId) {
                if(view.type != 'agendaAllDays') {
                    console.log('start/end equals');
                    var tempStillStart = $.extend({}, tempStillEvent.start);
                    var tempStillEnd = $.extend({}, tempStillEvent.end);
                    var tempStillResourceId = $.extend({}, tempStillEvent.resourceId);

                    tempStillEvent.start = event.movingEvent.start;
                    tempStillEvent.end = event.movingEvent.end;
                    tempStillEvent.resourceId = event.movingEvent.resourceId;
                }
                saveEvents(event, tempStillEvent, delta);

            } else {
                if (event.stillEvent.resourceId != event.movingEvent.resourceId && event.resourceId == event.stillEvent.resourceId) {
                    revertFunc();
                } else {
                    saveEvents(event, null, delta);
                }
                if (event.end <= tempStillEvent.start || event.start >= tempStillEvent.end) {
                    saveEvents(event, null, delta);
                }
            }
        } else {
            console.log('stillevent undefined');
            saveEvents(event, null, delta);
        }
        if (typeof event.movingEvent != 'undefined') {
            delete(event['movingEvent']);
        }
        if (typeof event.stillEvent !== 'undefined') {
            delete(event['stillEvent']);
        }
    }

    /**
     * @deprecated
     *
     * @param firstEvent
     * @param secondEvent
     * @param delta
     */
    function saveEvents(firstEvent, secondEvent, delta) {

        var updateSession = SessionConfig.links.updatesession;
        var savedFirstEventData = {
            __identity: firstEvent.id,
            begin: firstEvent.start.utc().format(),
            end: firstEvent.end.utc().format(),
            room: firstEvent.resourceId,
            title: firstEvent.title
        };
        var savedFirstEvent = {session: savedFirstEventData};

        var savedSecondEventData = null;
        if (secondEvent != null) {

            savedSecondEventData = {
                __identity: secondEvent.id,
                begin: secondEvent.start.utc().format(),
                end: secondEvent.end.utc().format(),
                room: secondEvent.resourceId,
                title: secondEvent.title
            };
            var savedSecondEvent = {session: savedSecondEventData};

        }
        var eventArr = {
            tx_sessions_web_sessionssession: {
                session: savedFirstEventData,
                secondSession: savedSecondEventData
            }
        };
        console.log(eventArr);
        //if (delta != null) {
        $.ajax({
            type: 'POST',
            data: eventArr,
            url: updateSession,
            success: function (data) {
                //Get all Rooms
                //$('#calendar').fullCalendar('refetchResources');
                //Get all Events
                //$('#calendar').fullCalendar('refetchEvents');
            }
        });
        //}
    }
    /*
     * GRAVEYARD END
     */

    return {initialize: function(){calendar.initialize();}};
});