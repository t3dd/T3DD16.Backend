require(['samModuleConfig', 'jquery', 'TYPO3/CMS/Backend/Modal', 'TYPO3/CMS/Backend/Notification'], function(samModuleConfig, jQuery, Modal, Notification) {
    jQuery(document).ready(function($) {
        /*
            Show a modal without buttons when the info button is cliked inside the list
         */
        $('table#tx-sessions-table tbody').on('click', 'a.session-info-trigger', function() {
            var url = $(this).data('url');
            if(url) {
                Modal.loadUrl('Session Info', -1, [], url);
            }
        });

        /*
            Show a Notification indicating success
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
        var updateUrl = samModuleConfig.updateUrl;
        $('table#tx-sessions-table tbody').on('click', 'a.session-change-trigger', function() {
            var id = $(this).data('identity');
            var type = $(this).data('state');
            var url = updateUrl.replace(/%23%23%23id%23%23%23/, id);
            url = url.replace(/%23%23%23type%23%23%23/, type);
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
