define('TYPO3/CMS/Sessions/Calendar/Analyze',
    ['TYPO3/CMS/Sessions/Calendar/Module',
        'jquery',
        'TYPO3/CMS/Sessions/Contrib/rivets',
        'SessionConfig',
        'moment',
        'TYPO3/CMS/Backend/Notification',
        'TYPO3/CMS/Sessions/Contrib/uri-templates',
        'TYPO3/CMS/Sessions/Contrib/Chart.min',
        'TYPO3/CMS/Backend/Modal'],
    function(Module, $, rivets, SessionConfig, moment, Notification, UriTemplate, Chart, Modal){

    var Analyze = function(utility, calendar) {
        Module.call(this, utility, calendar);

        this.uriTpl = new UriTemplate(SessionConfig.links.analyze.replace(/=%7B(\w+)%7D/g, '={$1}'));

        var _this = this;

        this.modal = null;

        this.charts = [];

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

        this.cleanupModal = function() {
            $.each(this.charts, function(index, chart) {
                chart.destroy();
            }.bind(this));
            Modal.dismiss();
            this.charts = [];
        }.bind(this);

        this.showModal = function(data) {
            var content = '<div class="row">';
            $.each(data, function(index, element) {
                var inner = (element.data.length > 0) ? '<canvas id="modal-analyze-'+element.uid+'" width="200" height="200" style="width:200px;height:200px;" />' : '<p>No topics associated</p>';
                content = content + '<div class="col-sm-4"><h3>' + element.title + '</h3>'  + inner + '</div>';
            });
            content = content + '</div>';
            // show the modal
            this.modal = Modal.confirm('Slot analyze', content, -1, [{
                text: 'OK',
                btnClass: 'btn-info',
                name: 'ok'
            }]);
            // register handler to cleanup. minimize memory consumption by cleaning up instanitiated charts
            this.modal.on('button.clicked', this.cleanupModal);
            // initialize charts when the modal is open. if the canvas elements are not visible Chartjs will fail!
            this.modal.on('shown.bs.modal', function(){
                $.each(data, function(index, element) {
                    if(element.data.length > 0) {
                        var ctx = this.modal.find("canvas#modal-analyze-"+element.uid)[0].getContext('2d');
                        this.charts.push(new Chart(ctx).Pie(element.data, {
                            animation: false
                        }));
                    }
                }.bind(this));
            }.bind(this));
        };

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
            $.ajax({
                dataType: 'json',
                url: this.uriTpl.fill({start:this.rivetData.start.utc().format(), end:this.rivetData.end.utc().format()})
            }).done(function(data){
                if(data.length > 0) {
                    this.showModal(data);
                } else {
                    Notification.error('No data returned', 'The server did not return any data for that selection!', 2);
                }
            }.bind(this)).fail(function(){
                Notification.error('Loading failed', 'Loading of data for analyze failed!', 2);
            });
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

        /**
         * select time range by event click
         */
        this.eventClick = function(event, jsEvent, view) {
            this.rivetData.enabled = true;
            this.rivetData.start = event.start;
            this.rivetData.end = event.end;
        };

    };
    Analyze.prototype = Object.create(Module.prototype);
    Analyze.prototype.constructor = Analyze;

    return Analyze;
});