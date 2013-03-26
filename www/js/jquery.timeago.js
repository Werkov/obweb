/**
 * Timeago is a jQuery plugin that makes it easy to support automatically
 * updating fuzzy timestamps (e.g. "4 minutes ago" or "about 1 day ago").
 *
 * @name timeago
 * @version 0.10.0
 * @requires jQuery v1.2.3+
 * @author Ryan McGeary
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 *
 * For usage and examples, visit:
 * http://timeago.yarp.com/
 *
 * Copyright (c) 2008-2011, Ryan McGeary (ryanonjavascript -[at]- mcgeary [*dot*] org)
 */
(function($) {
    $.timeago = function(timestamp) {
        if (timestamp instanceof Date) {
            return inWords(timestamp, this);
        } else if (typeof timestamp === "string") {
            return inWords($.timeago.parse(timestamp), this);
        } else {
            return inWords($.timeago.datetime(timestamp), this);
        }
    };
    var $t = $.timeago;

    $.extend($.timeago, {
        settings: {
            refreshMillis: 60000,
            allowFuture: true,
            strings: {
                prefixAgo: null,
                prefixFromNow: null,
                suffixAgo: "ago",
                suffixFromNow: "from now",
                seconds: "less than a minute",
                minute: "about a minute",
                minutes: "%d minutes",
                hour: "about an hour",
                hours: "about %d hours",
                day: "a day",
                days: "%d days",
                month: "about a month",
                months: "%d months",
                year: "about a year",
                years: "%d years",
                numbers: []
            }
        },
        inWords: function(distanceMillis, element) {
            var $l = this.settings.strings;
            
            var seconds = Math.abs(distanceMillis) / 1000;
            var minutes = seconds / 60;
            var hours = minutes / 60;
            var days = hours / 24;
            var years = days / 365;
            
            var prefix = $l.prefixAgo;
            var suffix = $l.suffixAgo;
            if (this.settings.allowFuture) {
                if (distanceMillis < 0) {
                    prefix = $l.prefixFromNow;
                    suffix = $l.suffixFromNow;
                    $l = this.settings.futureStrings;
                }
            }            

            function substitute(stringOrFunction, number) {
                var string = $.isFunction(stringOrFunction) ? stringOrFunction(number, distanceMillis) : stringOrFunction;
                var value = ($l.numbers && $l.numbers[number]) || number;
                return string.replace(/%d/i, value);
            }

            var words = seconds < 45 && substitute($l.seconds, Math.round(seconds)) ||
            seconds < 90 && substitute($l.minute, 1) ||
            minutes < 45 && substitute($l.minutes, Math.round(minutes)) ||
            minutes < 90 && substitute($l.hour, 1) ||
            hours < 24 && substitute($l.hours, Math.round(hours)) ||
            hours < 48 && substitute($l.day, 1) ||
            false;
            days < 30 && substitute($l.days, Math.floor(days)) ||
            days < 60 && substitute($l.month, 1) ||
            days < 365 && substitute($l.months, Math.floor(days / 30)) ||
            years < 2 && substitute($l.year, 1) ||
            substitute($l.years, Math.floor(years));
            if(!words){
                return $(element).text();
            }else{
                return $.trim([prefix, words, suffix].join(" "));
            }
    
        },
        parse: function(iso8601) {
            var s = $.trim(iso8601);
            s = s.replace(/\.\d\d\d+/,""); // remove milliseconds
            s = s.replace(/-/,"/").replace(/-/,"/");
            s = s.replace(/T/," ").replace(/Z/," UTC");
            s = s.replace(/([\+\-]\d\d)\:?(\d\d)/," $1$2"); // -04:00 -> -0400
            return new Date(s);
        },
        datetime: function(elem) {
            // jQuery's `is()` doesn't play well with HTML5 in IE
            var isTime = $(elem).get(0).tagName.toLowerCase() === "time"; // $(elem).is("time");
            var iso8601 = isTime ? $(elem).attr("datetime") : $(elem).attr("title");
            return $t.parse(iso8601);
        }
    });

    $.fn.timeago = function() {
        var self = this;
        self.each(refresh);

        var $s = $t.settings;
        if ($s.refreshMillis > 0) {
            setInterval(function() {
                self.each(refresh);
            }, $s.refreshMillis);
        }
        return self;
    };

    function refresh() {
        var data = prepareData(this);
        if (!isNaN(data.datetime)) {
            $(this).text(inWords(data.datetime, this));
        }
        return this;
    }

    function prepareData(element) {
        element = $(element);
        if (!element.data("timeago")) {
            element.data("timeago", {
                datetime: $t.datetime(element)
            });
            var text = $.trim(element.text());
            if (text.length > 0) {
        //element.attr("title", text);
        }
        }
        return element.data("timeago");
    }

    function inWords(date, element) {
        return $t.inWords(distance(date), element);
    }

    function distance(date) {
        return (new Date().getTime() - date.getTime());
    }

    // fix for IE6 suckage
    document.createElement("abbr");
    document.createElement("time");
}(jQuery));

// Czech
(function() {    
    jQuery.timeago.settings.strings = {
        prefixAgo: "před",
        prefixFromNow: "za",
        suffixAgo: null,
        suffixFromNow: null,
        seconds: "méně než minutou",
        minute: "minutou",
        minutes: "%d minutami",
        hour: "hodinou",
        hours: "%d hodinami",
        day: "1 dnem",
        days: "%d dny",
        month: "1 měsícem",
        months: "%d měsíci",
        year: "1 rokem",
        years: "%d roky"
    };
    jQuery.timeago.settings.futureStrings = {
        prefixAgo: null,
        prefixFromNow: "za",
        suffixAgo: null,
        suffixFromNow: null,
        seconds: "méně než minutu",
        minute: "minutu",
        minutes: "%d minut",
        hour: "hodinu",
        hours: "%d hodin",
        day: "1 den",
        days: "%d dny",
        month: "1 měsíc",
        months: "%d měsíce",
        year: "rok",
        years: "%d roky"
    };
})();