/**
 * AJAX Nette Framework plugin for jQuery
 *
 * @copyright   Copyright (c) 2009 Jan Marek
 * @license     MIT
 * @link        http://nettephp.com/cs/extras/jquery-ajax
 * @version     0.2
 */

jQuery.extend({
    nette: {
        updateSnippet:  function (id, html) {
            $("#" + id).fadeTo("fast", 0.01, function () {
                $(this).height($(this).height);
                $(this).html(html).fadeTo("fast", 1, function(){
                    if ($.browser.msie){
                        this.style.removeAttribute('filter');
                    }
                });	    
            });
    },
    /*updateSnippet: function (id, html) {
	 $("#" + id).html(html);
      },*/

    success: function (payload) {
        // redirect
        if (payload.redirect) {
            window.location.href = payload.redirect;
            return;
        }

        // snippets
        if (payload.snippets) {
            for (var i in payload.snippets) {
                jQuery.nette.updateSnippet(i, payload.snippets[i]);
                jQuery("#" + i + ' form').each(function(i, val) {
                    Nette.initForm(val);		  
                });
            }
        }
    }
}
});

jQuery.ajaxSetup({
    success: jQuery.nette.success,
    dataType: "json"
});

// nette ajax init
$("a.ajax").live("click", function (event) {
    event.preventDefault();
    $.get(this.href);
    var offset = $(this).parent().offset();
    var w = $(this).parent().width();
    var h = $(this).parent().height();
    $("#ajax-spinner").show().css({
        position: "absolute",
        left: offset.left + w /2,// + 20,
        top: offset.top + h/2// + 40
    });
});

$("form.ajax :submit").live("click", function (event) {
    event.preventDefault();
    $(this).ajaxSubmit();
    return false;
});

$(function () {
    $('<div id="ajax-spinner"></div>').appendTo("body").ajaxStop(function () {
        $(this).hide().css({
            position: "fixed",
            left: "50%",
            top: "50%"
        });
    }).hide();
});

// nette flash hiding
$("div.flash").livequery(function () {
    var el = $(this);
    if(el.hasClass("ui-state-error"))
        return;
   
    setTimeout(function () {
        el.animate({
            "opacity": 0
        }, 2000);
        el.slideUp();
    }, 7000);
});
