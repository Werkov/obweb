(function ($, undefined) {

$.widget("ui.gridito", {

	options: {
		
	},


	_create: function () {
		var _this = this;
		
		this.table = this.element.find("table.gridito-table");
		//this.table.addClass("ui-widget ui-widget-content");
		//this.table.find("th").addClass("ui-widget-header");
		this.table.find("tbody tr").hover(function () {
			$(this).addClass("ui-state-hover");
		}, function () {
			$(this).removeClass("ui-state-hover");
		});
		
		// buttons
		this.element.find("a.gridito-button").each(function () {
			var el = $(this);
			
			// window button
			if (el.hasClass("gridito-window-button")) {
				el.click(function (e) {
					e.stopImmediatePropagation();
					e.preventDefault();
			
					var win = $('<div></div>').appendTo('body');
					win.attr("title", $(this).attr("data-gridito-window-title"));
					win.load(this.href, function () {
						win.dialog({
							modal: true
						});
						win.find("input:first").focus();
					});
				});
			}
			
			if (el.attr("data-gridito-question")) {
				el.click(function (e) {					
					if (!confirm($(this).attr("data-gridito-question"))) {
						e.stopImmediatePropagation();
						e.preventDefault();
					}
				});
			}
		});
	}
	
});

})(jQuery);