require(['samModuleConfig', 'jquery', 'TYPO3/CMS/Backend/Modal', 'TYPO3/CMS/Backend/Notification', 'nprogress'], function(samModuleConfig, jQuery, Modal, Notification, NProgress) {
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
            Set the type of the triggered session
         */
        var updateUrl = samModuleConfig.updateUrl;
        $('table#tx-sessions-table tbody').on('click', 'a.session-change-trigger', function() {
            var id = $(this).data('identity');
            var type = $(this).data('state');
            var url = updateUrl.replace(/%23%23%23id%23%23%23/, id);
            url = url.replace(/%23%23%23type%23%23%23/, type);
            $.ajax(url, {context: $(this).parent('')}).done(function(data){
                Notification.success('Success', 'Session updated', 1);
                $(this).parents('tr.session-row').remove();
            }).fail(function(data){
                Notification.error('Error', 'Session could not be updated', 1);
            });
        });
    });
});
