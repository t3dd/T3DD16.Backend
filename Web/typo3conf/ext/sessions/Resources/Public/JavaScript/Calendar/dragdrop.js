define('TYPO3/CMS/Sessions/Calendar/Dragdrop', ['TYPO3/CMS/Sessions/Calendar/Module', 'jquery', 'jquery-ui/draggable', 'TYPO3/CMS/Backend/Notification'], function(Module, $, draggable, Notification){


    var Dragdrop = function(utility, calendar) {
        Module.call(this, utility, calendar);

        // make all initial external events droppable
        $('#unassigned-sessions .unscheduled-event').each(function() {
            $(this).draggable({
                zIndex: 999,
                revert: true,
                revertDuration: 0
            });

        });

        var _this = this;

        this.eventDragStop = function(event, jsEvent, ui, view) {
            this.unscheduleIfWanted(event, jsEvent, ui, view);
        };

        /**
         * Callback when external events have been dropped
         * !!! this is bound to the dropped event in this callback
         * use _this if you want the object context.
         * @param date
         * @param jsEvent
         * @param ui
         * @param resourceId
         */
        this.drop = function(date, jsEvent, ui, resourceId) {
            // event fires too often... catch wrong calls
            if($.type(date) === 'undefined') {
                return;
            }
            // we have to save this event manually...
            var data = {
                id: $(this).data('id'),
                title: $(this).data('title'),
                speakers: $(this).data('speakers')
            };
            data['start'] = $.fullCalendar.moment(date);
            // set end datetime based on the default event length
            var duration = _this.calendar.getOption('defaultTimedEventDuration');
            data['end'] = $.fullCalendar.moment(date).add(duration);
            if($.isNumeric(resourceId)) {
                data.resourceId = resourceId;
            }
            // hide the dragged event until we know if it can be saved or not
            $(this).hide();
            // schedule the event
            _this.utility.scheduleSession(data).done(
                /**
                 * Session was successfully scheduled (start and end set + type changed)
                 * -> remove the hidden droppable event
                 * -> show a notification
                 */
                function(){
                    $(this).remove();
                    Notification.success('Success', 'Session scheduled', 1);
                    // reload events since fullcalendar seems a bit buggy
                    _this.calendar.reload();
                }.bind(this)).fail(
                /**
                 * The session could not be scheduled
                 * -> show the hidden event again, since it can be dropped again
                 * -> remove the event from the calendar (gets added automatically)
                 * -> show a notification with the first error message
                 */
                function(jqxhr){
                    $(this).show();
                    _this.calendar.removeEvent(data.id);
                    _this.utility.showErrorMessages('Scheduling failed!', jqxhr);
                }.bind(this));
        };

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
        this.unscheduleIfWanted = function(event, jsEvent, ui, view) {
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
                this.utility.unscheduleSession(event).done(function(){
                    // successfully unscheduled
                    Notification.success('Event unscheduled!', event.title, 1);
                    // remove it from the calendar
                    this.calendar.removeEvent(event.id);
                    // add back to externals
                    this.utility.addUnscheduledEvent(event);
                }.bind(this)).fail(function(jqxhr){
                    this.utility.showErrorMessages('Unscheduling failed!', jqxhr);
                }.bind(this));
            }
        }


    };
    Dragdrop.prototype = Object.create(Module.prototype);
    Dragdrop.prototype.constructor = Dragdrop;

    return Dragdrop;
});