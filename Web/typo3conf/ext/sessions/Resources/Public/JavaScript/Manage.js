require(['manageConfig', 'jquery','TYPO3/CMS/Sessions/Contrib/uri-templates', 'TYPO3/CMS/Backend/Modal', 'TYPO3/CMS/Backend/Notification'], function(manageConfig, jQuery, UriTemplate, Modal, Notification) {
    jQuery(document).ready(function($) {

        var $tableBody = $('table#tx-sessions-table tbody');

        /*
            Show a modal without buttons when the info button is clicked inside the list
         */
        $tableBody.on('click', 'a.session-info-trigger', function() {
            var url = $(this).data('url');
            if(url) {
                Modal.loadUrl('Session Info', -1, [], url);
            }
        });

        /**
         * Show a Notification indicating success
         * @param {boolean} success
         */
        function showSessionUpdateNotification(success)
        {
             if(success) {
                 Notification.success('Success', 'Session updated', 1);
             } else {
                 Notification.error('Error', 'Session could not be updated', 1);
             }
        }

        /*
            Set the type of the triggered session
         */
        var uriTpl = new UriTemplate(manageConfig.updateUrl.replace(/=%7B(\w+)%7D/g, '={$1}'));
        $tableBody.on('click', 'a.session-change-trigger', function() {
            var id = $(this).data('identity');
            var type = $(this).data('state');
            var url = uriTpl.fill({id:id, type:type});
            $.ajax(url, {context: $(this).parent(''), dataType: 'json'}).done(function(data){
                if(data.success === true) {
                    showSessionUpdateNotification(true);
                    $(this).parents('tr.session-row').remove();
                } else {
                    showSessionUpdateNotification(false);
                }
            }).fail(function(data){
                showSessionUpdateNotification(false);
            });
        });
    });
});
