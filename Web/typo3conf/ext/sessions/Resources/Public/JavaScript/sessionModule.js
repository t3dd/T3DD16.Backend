/**
 * Created by aschmid on 03.02.2016.
 */

// Start the main app logic.

define(['jquery', 'TYPO3/CMS/Sessions/fullcalendar', 'TYPO3/CMS/Sessions/scheduler'], function($, fullcalendar, scheduler) {
        console.log('TEST');
        var sessions = {};
        //$(function () { // document ready
            var overlapcounter = 0;

            sessions = $('#calendar').fullCalendar({
                schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
                now: '2016-01-07',
                editable: true,
                aspectRatio: 2.0,
                eventOverlap: function (stillEvent, movingEvent) {
                    var copiedObject = extend({}, stillEvent)
                    if (stillEvent.resourceId !== movingEvent.resourceId) {
                        stillEvent.resourceId = movingEvent.resourceId;
                        movingEvent.resourceId = copiedObject.resourceId;
                        overlapcounter = 0;
                        return true;
                    }
                    if (overlapcounter == 2) {
                        stillEvent.start = movingEvent.start;
                        stillEvent.end = movingEvent.end;

                        movingEvent.start = copiedObject.start;
                        movingEvent.end = copiedObject.end;
                        console.log("stillEvent: " + new Date(stillEvent.start));
                        console.log("movingEvent: " + new Date(movingEvent.start));
                        overlapcounter = 0;
                        return true;
                    } else {
                        overlapcounter++;
                    }
                },
                scrollTime: '00:00',
                header: {
                    left: 'today prev,next',
                    center: 'title',
                    right: 'agendaDay,timelineDay'
                },
                defaultView: 'agendaDay',
                views: {
                    agendaDay: {
                        buttonText: 'Agenda',
                        type: 'agendaDay'
                    },
                    timelineDay: {
                        buttonText: 'Timeline',
                        slotDuration: '01:00',
                        snapDuration: '00:30'
                    }
                },
                resourceAreaWidth: '15%',
                resourceLabelText: 'Rooms',
                resources: [
                    {id: 'a', title: 'Room A'},
                    {id: 'b', title: 'Room B', eventColor: 'green'},
                    {id: 'c', title: 'Room C', eventColor: 'orange'},
                    {id: 'd', title: 'Room D', eventColor: 'blue'}
                ],
                events: [
                    {
                        id: '1',
                        resourceId: 'a',
                        start: '2016-01-07T00:00:00',
                        end: '2016-01-07T01:30:00',
                        title: 'event 1',
                        firstSpeaker: 'Max Mustermann'
                    },
                    {
                        id: '2',
                        resourceId: 'b',
                        start: '2016-01-07T01:30:00',
                        end: '2016-01-07T03:00:00',
                        title: 'event 2',
                        firstSpeaker: 'Peter Meier'
                    },
                    {
                        id: '3',
                        resourceId: 'c',
                        start: '2016-01-07T03:00:00',
                        end: '2016-01-07T04:30:00',
                        title: 'event 3',
                        firstSpeaker: 'Dick und Doof'
                    },
                    {
                        id: '4',
                        resourceId: 'd',
                        start: '2016-01-07T04:30:00',
                        end: '2016-01-07T06:00:00',
                        title: 'event 4',
                        firstSpeaker: 'Martin Luther'
                    },
                    {
                        id: '5',
                        resourceId: 'd',
                        start: '2016-01-07T06:00:00',
                        end: '2016-01-07T07:30:00',
                        title: 'event 5',
                        firstSpeaker: 'Bruce Wayne'
                    }
                ],
                eventDurationEditable: false,
                eventRender: function (event, element) {
                    element.find('.fc-title').append("<br/>" + event.firstSpeaker);
                }
            });
    return sessions;
});