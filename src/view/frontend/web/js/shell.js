define([
    "jquery",
    "mage/apply/main"
], function ($, mage) {
    'use strict';

    var target = $('#shell-target');

    $.ajax({
            url: location.href,
            dataType: 'JSON',
            data: {
                service_worker: 'true'
            },
            cache: true,
            beforeSend: function() {
                target.addClass('is-fetching');
            }
        })
        .done(function( data ) {
            target.html(data.content);
            target.removeClass('is-fetching');
            $('body').trigger('contentUpdated');
        })
        .fail(function( error ) {
            console.log('There has been a problem with your ajax operation: ' + error.message);
        });
});
