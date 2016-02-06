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
        calendar.fullCalendar({
            schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
            timezoneParam: 'UTC',
            allDaySlot: false,
            slotEventOverlap: false,
            now: '2016-09-01',
            editable: true,
            aspectRatio: 2.7,
            //scrollTime: '06:00',
            minTime: '05:00',
            maxTime: '22:00',
            businessHours: {
                start: '08:00',
                end: '20:00',
                dow: [0, 1, 2, 3, 4, 5, 6, 7]
            },
            header: {
                left: 'today prev,next',
                center: 'title',
                right: 'agendaDay,timelineDay,agendaFourDay,timelineFourDay'
            },
            defaultView: 'agendaFourDay',
            views: {
                agendaDay: {
                    buttonText: 'One Day Vertical',
                    type: 'agendaDay',
                    snapDuration: '00:30'
                },
                timelineDay: {
                    buttonText: 'One Day Horizontal',
                    type: 'agendaDay',
                    snapDuration: '00:30'
                },
                agendaFourDay: {
                    buttonText: 'Four Day Vertical',
                    type: 'agenda',
                    duration: {days: 4}
                },
                timelineFourDay: {
                    buttonText: 'Four Day Horizontal',
                    type: 'timeline',
                    duration: {days: 4}
                }
            },
            eventRender: function (event, element) {
                eventRender(event, element);
            },
            eventOverlap: function (stillEvent, movingEvent) {
                return overlapEvent(stillEvent, movingEvent);
            },
            eventDragStart: function (event, jsEvent, ui, view) {
                eventDragStart(event, jsEvent, ui, view);
            },
            eventDrop: function (event, delta, revertFunc, jsEvent, ui, view) {
                eventDrop(event, delta, revertFunc, jsEvent, ui, view);
            },
            resourceAreaWidth: '15%',
            resourceLabelText: 'Rooms',
            resources: calendar.data('listRooms'),
            events: calendar.data('listSessions'),
            eventDurationEditable: false
        });
    });

    var counter = 0;

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
                if(view.type != 'agendaFourDay') {
                    console.log('start/end equals')
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

        var updateSession = calendar.data('updateSession');
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
        var linkRooms = calendar.data('listRooms');
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
        var linkSession = calendar.data('listSessions');
        $.ajax({
            url: linkSession,
            contentType: 'application/json; chaset=UTF-8'
        }).done(function (data) {
            console.log('Test: ' + data);
            return data;
        });
    }
});