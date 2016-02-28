define('TYPO3/CMS/Sessions/Calendar/Base', ['TYPO3/CMS/Sessions/Calendar/Module'], function(Module){


    var Base = function(utility, calendar) {
        Module.call(this, utility, calendar);

        this.rivetData = {

        };

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
        this.eventChanged = function(event, delta, revertFunc, jsEvent, ui, view) {
            this.utility.updateSession(event).done(
                /**
                 * Session was successfully updated
                 * -> show notification
                 */
                function(){
                    this.utility.showSuccessMessage('Session updated!', event.title);
                }.bind(this))
                .fail(
                    /**
                     * Sth went wrong with the update
                     * -> show notification with error messages
                     * -> call the revertFunc to position the event back
                     */
                    function(jqxhr){
                        revertFunc();
                        this.utility.showErrorMessages('Update failed!', jqxhr);
                    }.bind(this));
        };

        this.eventDrop = function() {
            this.eventChanged.apply(this, arguments);
        };

        this.eventResize = function() {
            this.eventChanged.apply(this, arguments);
        };

    };
    Base.prototype = Object.create(Module.prototype);
    Base.prototype.constructor = Base;

    return Base;
});