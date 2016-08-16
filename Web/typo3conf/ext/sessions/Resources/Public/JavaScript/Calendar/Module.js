define('TYPO3/CMS/Sessions/Calendar/Module', [], function(){

    var Module = function(utility, calendar) {
        this.utility = utility;
        this.calendar = calendar;
    };

    /*
     * Callback Prototypes
     */
    Module.prototype.dayClick = function(date, jsEvent, view, resourceObj) {};
    Module.prototype.eventClick = function(event, jsEvent, view) {};
    Module.prototype.select = function(start, end, jsEvent, view, resource) {};
    Module.prototype.unselect = function(view, jsEvent) {};
    Module.prototype.eventDragStart = function(event, jsEvent, ui, view) {};
    Module.prototype.eventDragStop = function(event, jsEvent, ui, view) {};
    Module.prototype.eventDrop = function(event, delta, revertFunc, jsEvent, ui, view) {};
    Module.prototype.eventResize = function(event, delta, revertFunc, jsEvent, ui, view) {};
    Module.prototype.drop = function(date, jsEvent, ui, resourceId) {};

    return Module;
});