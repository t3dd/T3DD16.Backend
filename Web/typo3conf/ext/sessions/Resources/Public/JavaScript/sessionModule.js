/**
 * Created by aschmid on 03.02.2016.
 */
/*
 TODO: [TASK] Set date to first t3dd16-day (1. september 2016)
 TODO: [TASK] Import icons for Rooms and Sessions
 TODO: [BUG] Switch events with same resourceId doesn't work
 */


define(['jquery', 'TYPO3/CMS/Sessions/fullcalendar', 'TYPO3/CMS/Sessions/scheduler', 'SessionConfig'], function ($, fullcalendar, scheduler, SessionConfig) {
    var calendar = $('#calendar');
    return $(this.document).ready(function () { // document ready
        /**
         * Configuration
         * @see {@link http://fullcalendar.io/docs/|Fullcalendar Documentation}
         * @see {@link http://fullcalendar.io/scheduler/|Scheduler Plugin}
         */
        calendar.fullCalendar({
            schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
            /**
             * General Display
             * @see {@link http://fullcalendar.io/docs/display/}
             */
            header: {
                left: 'today prev,next analyzeBtn',
                center: 'title',
                right: 'agendaDay,timelineDay,agendaAllDays,timelineAllDays'
            },
            customButtons: {
                analyzeBtn: {
                    text: 'Analyze',
                    click: function() {
                        analyzeSlot();
                    }
                }
            },
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
            defaultView: 'agendaAllDays',
            views: {
                agendaDay: {
                    buttonText: 'Day Calendar',
                    type: 'agendaDay',
                    snapDuration: '00:30'
                },
                timelineDay: {
                    buttonText: 'Day Schedule',
                    type: 'agendaDay',
                    snapDuration: '00:30'
                },
                agendaAllDays: {
                    buttonText: 'Full Calendar',
                    type: 'agenda',
                    duration: {days: SessionConfig.days.length}
                },
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
            dayClick: function( date, jsEvent, view, resourceObj) { },
            eventClick: function( event, jsEvent, view ) { },
            /**
             * Selection
             * @see {@link http://fullcalendar.io/docs/selection/}
             */
            selectable: true,
            select: function( start, end, jsEvent, view, resource) {},
            /**
             * Event Data
             * @see {@link http://fullcalendar.io/docs/event_data/}
             */
            events: SessionConfig.links.getsessions,
            startParam: 'tx_sessions_web_sessionssession%5Bstart%5D',
            endParam: 'tx_sessions_web_sessionssession%5Bend%5D',
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
            eventDurationEditable: false,
            eventDragStart: function (event, jsEvent, ui, view) {
                eventDragStart(event, jsEvent, ui, view);
            },
            eventDragStop: function( event, jsEvent, ui, view ) { },
            eventDrop: function (event, delta, revertFunc, jsEvent, ui, view) {
                eventDrop(event, delta, revertFunc, jsEvent, ui, view);
            },
            eventResize: function( event, delta, revertFunc, jsEvent, ui, view ) { },
            eventOverlap: function (stillEvent, movingEvent) {
                return overlapEvent(stillEvent, movingEvent);
            },
            /**
             * Dropping External Elements
             * @see {@link http://fullcalendar.io/docs/dropping/}
             */
            droppable: true,
            dropAccept: '.unscheduled-event',
            drop: function( date, jsEvent, ui, resourceId ) { },
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
    });

    var counter = 0;

    var analyzeSelection = {
        start: null,
        end: null
    };

    function onSelect(start, end, jsEvent, view, resource) {
        if(view.type === 'agendaAllDays' || view.type === 'timelineAllDays') {

        } else {
            calendar.fullCalendar( 'unselect' );
        }
    }

    function analyzeSlot()
    {

    }

    /**
     * Switch two events when they're overlapping.
     *
     * @param stillEvent
     * @param movingEvent
     * @param overlapCounter
     * @returns {boolean} if false, overlap isn't allowed.
     */
    function overlapEvent(stillEvent, movingEvent) {
        console.log('overlapEvent');
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
     *
     * @param event
     * @param jsEvent Holds the native JavaScript event with low-level information such as mouse coordinates.
     * @param ui
     * @param view Holds the current View Object.
     */
    function eventDragStart(event, jsEvent, ui, view) {
        console.log('eventDragStart');
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
     *
     * @param event
     * @param delta Represents the amount of time the event was moved.
     * @param reverFunc Reverts the event's start/end datea to the values before the drag.
     * @param jsEvent Holds the native JavaScript event with low-level information such as mouse coordinates.
     * @param ui
     * @param view Holds the current View Object.
     */
    function eventDrop(event, delta, revertFunc, jsEvent, ui, view) {
        console.log('eventDrop');
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

    /**
     * Render more information in an event than time and title.
     * @param event
     * @param element
     */
    function eventRender(event, element) {
        "use strict";
        element.find('.fc-title').append("<br/>" + event.description);
    }

    /**
     * Get all resources (like rooms, etc.) from database.
     */
    function getResources() {
        var linkRooms = SessionConfig.links.getrooms;
        $.ajax({
            url: linkRooms,
            contentType: 'application/json; chaset=UTF-8'
        }).done(function (data) {
            return data;
        });
    }

    /**
     * Get all events from database.
     */
    function getEvents() {
        var linkSession = SessionConfig.links.getsessions;
        $.ajax({
            url: linkSession,
            contentType: 'application/json; chaset=UTF-8'
        }).done(function (data) {
            console.log('Test: ' + data);
            return data;
        });
    }
});