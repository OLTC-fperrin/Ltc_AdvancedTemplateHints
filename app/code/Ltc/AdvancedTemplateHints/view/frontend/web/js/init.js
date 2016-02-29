require(['jquery'], function($){
    "use strict";
    $(function(){
        Opentip.styles.slick = {

        };
        $('.tpl-hint').each(function(){
            var id = $(this).attr('id');
            $(this).on('mouseover', function(event){
                event.stopPropagation();
                event.preventDefault();
                new Opentip(
                    this,
                    $('#' + id + '-infobox').html(),
                    $('#' + id + '-title').html(),
                    {
                        style: 'slick',
                        hideOn: 'click',
                        //offset: [-10, -10], TODO: find solution for display
                        fixed: true,
                        group: 'ath',
                        escapeTitle: false
                    }
                )
            })
        })
    });
});