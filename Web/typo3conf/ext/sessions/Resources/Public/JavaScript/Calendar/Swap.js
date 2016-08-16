define('TYPO3/CMS/Sessions/Calendar/Swap', ['TYPO3/CMS/Sessions/Calendar/Module', 'jquery', 'TYPO3/CMS/Sessions/Contrib/rivets', 'SessionConfig', 'TYPO3/CMS/Backend/Notification'], function(Module, $, rivets, SessionConfig, Notification){


    var Swap = function(utility, calendar) {
        Module.call(this, utility, calendar);

        var _this = this;

        this.rivetData = {
            enabled: false,
            sessions: [],
            swap: function(){
                _this.swap();
            }
        };

        rivets.bind($('div#swap-module'), this.rivetData);

        this.swap = function() {
            var data = {
                tx_sessions_web_sessionssession: {}
            };
            var first = this.utility.buildJsonDataFromEvent(this.rivetData.sessions[0]);
            var second = this.utility.buildJsonDataFromEvent(this.rivetData.sessions[1]);
            data.tx_sessions_web_sessionssession['first'] = first.tx_sessions_web_sessionssession.session;
            data.tx_sessions_web_sessionssession['second'] = second.tx_sessions_web_sessionssession.session;
            $.ajax({
                type: 'POST',
                data: data,
                url: SessionConfig.links.swapsessions
            }).done(function(){
                this.clearSwapEvents();
                Notification.success('Success', 'Swapped Events', 1);
                this.calendar.reload();
            }.bind(this)).fail(function(jqxhr){
                this.utility.showErrorMessages('Swapping failed', jqxhr);
            }.bind(this));
        };

        /**
         * Clears the currently stored events for swapping.
         * Fired from different callbacks that are fired when
         * the user does NOT click on an event.
         * @return void
         */
        this.clearSwapEvents = function() {
            this.rivetData.enabled = false;
            // rivets does not detect changes to arrays if done by assigning a new empty array, since it
            // can't observe that sort of events.
            // little hack using array mutators which is recognized by rivets (loop should run max twice)
            if(this.rivetData.sessions.length > 0) {
                while(this.rivetData.sessions.length > 0) {
                    this.rivetData.sessions.pop();
                }
            }
        };


        this.dayClick = function() {
            this.clearSwapEvents();
        };

        this.select = function() {
            this.clearSwapEvents();
        };

        /**
         * Called when user clicked on an event. Handles related to the swapping tool.
         * Adds the clicked event to the swap selection. Catches case when user clicks
         * on the same event more than once. Limits the selections to 2 if there are
         * more than 2 events saved for swapping.
         * @param event
         * @param jsEvent
         * @param view
         */
        this.eventClick = function(event, jsEvent, view) {
            // catch user clicking on the same event over and over again. swapping an event with itself doesn't make sense
            if(this.rivetData.sessions.length >= 1) {
                var cancel = false;
                $.each(this.rivetData.sessions, function(index, el) {
                    if(el.id == event.id) {
                        cancel = true;
                    }
                });
                if(cancel === true) {
                    return;
                }
            }
            var data = {
                title: event.title,
                speakers: event.speakers,
                id: event.id,
                start: event.start,
                end: event.end
            };
            this.rivetData.sessions.push(data);
            if(this.rivetData.sessions.length > 2) {
                // more than two is more too many for swapping :)
                // simple slice should work since this will fire once there are 3 sessions present
                // rivet does not track slice calls! use shift workaround
                while(this.rivetData.sessions.length > 2) {
                    this.rivetData.sessions.shift();
                }
            }
            this.rivetData.enabled = this.rivetData.sessions.length >= 2;
        };

    };
    Swap.prototype = Object.create(Module.prototype);
    Swap.prototype.constructor = Swap;
    /*
    Swap.prototype.dayClick = function(date, jsEvent, view, resourceObj) {};
    Swap.prototype.eventClick = function(event, jsEvent, view) { console.log('Swap.eventClick'); };
    Swap.prototype.select = function(start, end, jsEvent, view, resource) { console.log('Swap.select'); };
    Swap.prototype.unselect = function(view, jsEvent) { console.log('Swap.unselect'); };
    Swap.prototype.eventDrop = function(event, delta, revertFunc, jsEvent, ui, view) { console.log('Swap.eventDrop'); };
    Swap.prototype.eventResize = function(event, delta, revertFunc, jsEvent, ui, view) { console.log('Swap.eventResize'); };
    Swap.prototype.drop = function(date, jsEvent, ui, resourceId) { console.log('Swap.drop'); };
    */
    return Swap;
});