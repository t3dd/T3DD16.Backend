define('TYPO3/CMS/Sessions/Calendar/Analyze', ['TYPO3/CMS/Sessions/Calendar/Module', 'jquery', 'TYPO3/CMS/Sessions/Contrib/rivets', 'SessionConfig', 'moment', 'TYPO3/CMS/Backend/Notification'], function(Module, $, rivets, SessionConfig, moment, Notification){

    var Analyze = function(utility, calendar) {
        Module.call(this, utility, calendar);

        var _this = this;

        this.rivetData = {
            enabled: false,
            start: null,
            end: null,
            analyze: function(){
                _this.analyzeSlot();
            }
        };

        // somehow rivets only recognizes changes to all properties correctly when the data is bound with the . binder on second level...
        this.rivetView = rivets.bind($('div#analyze-module'), {module : this.rivetData});

        /**
         * This method will make an modal ajax call for analysing the previous
         * selected slot.
         */
        this.analyzeSlot = function() {
            if($.type(this.rivetData) === 'undefined') {
                return;
            }
            if(this.rivetData.start === null || this.rivetData.end === null) {
                Notification.info('Warning', 'You must make a selection first!', 1);
                return;
            }
            // first "validate" the selection. analyzation is intended for one slot and shows comparison
            // between sessions which are held simultaneously (topics, vote count and room size etc).
            var duration = moment.duration(moment(this.rivetData.end).diff(this.rivetData.start));
            if(duration.asDays() >= 1 || duration.asHours() > 2) {
                Notification.info('Length exceeded', 'This tool is not intended for large selections', 3);
                return;
            }
            console.log('analyzing from %s until %s', this.rivetData.start.format(), this.rivetData.end.format());
            this.calendar.unselect();
            this.removeSelection();
        };

        /**
         * Callback fired when the user makes a selection. Basically we filter out selections
         * that are only 1 grid element long (represents unselect).
         * @param start
         * @param end
         * @param jsEvent
         * @param view
         * @param resource
         */
        this.select = function(start, end, jsEvent, view, resource) {
            // happend sometimes. don't know why... prevents wrong callbacks fired
            if(typeof view === 'undefined') {
                return;
            }
            // if you draw a selection and then click somewhere there is actually a
            // selection made with the minimum time span possible. don't register this
            // as a real selection... rather use this as unselect (whichs callback is
            // fired before this one)
            var duration = this.calendar.getOption('slotDuration');
            if(moment.isDuration(duration) && moment.isMoment(start) && moment.isMoment(end)) {
                var localStart = start.clone();
                var localEnd = localStart.add(duration);
                if(localEnd.isSame(end)) {
                    return;
                }
            }
            this.rivetData.enabled = true;
            this.rivetData.start = start;
            this.rivetData.end = end;
        };

        /**
         * Callback fired when selection is removed from fullcalendar
         */
        this.unselect = function() {
            //console.log('unselect');
            //this.removeSelection();
        };

        /**
         * Callback fired when the user clicks inside the calendar and does not hit
         * an event
         */
        this.dayClick = function() {
            console.log('dayClick');
            this.removeSelection();
        };

        /**
         * clear the current selection and deactivate button
         */
        this.removeSelection = function() {
            this.rivetData.enabled = false;
            this.rivetData.start = null;
            this.rivetData.end = null;
        };

    };
    Analyze.prototype = Object.create(Module.prototype);
    Analyze.prototype.constructor = Analyze;

    return Analyze;
});