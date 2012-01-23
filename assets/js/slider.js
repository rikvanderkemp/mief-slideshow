/*  Copyright 2011  Rik van der Kemp  (email : rik@mief.nl)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License, version 2, as
 published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
(function ($) {
    "use strict";

    var app = {
        // Current image pointer
        pointer:0,
        // Html parent object
        object:null,
        // What are the correct slides within the dom
        slides:'ul > li',
        controls:'div.controls',
        // Will hold final extended settings
        settings:{
            height:350,
            width:1000,
            speed:3000,
            transitions:{
                fade:'_transitionfade'
            },
            transition:'fade'
        },

        cycle:true,
        timer:null,

        /**
         * Initialize plugin
         * Extend all given settings and startup the slider cycle
         *
         * @param object options
         */
        init:function (options) {
            this.settings = $.extend(app.settings, options);
            app._reset();
            app.startCycle();
        },

        /**
         * Start the animation cycle
         */
        startCycle:function () {
            if (app.cycle === true) {
                app.timer = setTimeout(function () {
                    app.nextImage();
                }, app.settings.speed);
            }
        },


        /**
         * Pause the current cycle
         */
        pause:function () {
            if (app.cycle === true) {
                app.cycle = false;
                clearTimeout(app.timer);
            } else {
                app.cycle = true;
                app.startCycle();
            }
        },

        /**
         * Calculate next image and pass allong to transition
         */
        nextImage:function () {
            var next = 0;

            if (app.pointer >= ($(app.object).find('ul').children().length - 1)) {
                next = 0;
            } else {
                next = app.pointer + 1;
            }
            var from = $(app.object).find('ul').children()[app.pointer];
            var to = $(app.object).find('ul').children()[next];

            app.doTransition(from, to, 'startCycle');

            app.pointer++;

            // Reset to first image if necessary
            if (next === 0) {
                app.pointer = 0;
            }
        },

        /**
         * Calculate previous image and pass allong to transition
         */
        prevImage:function () {
            var prev = 0;

            if (app.pointer === 0) {
                prev = $(app.object).find('ul').children().length - 1;
            } else {
                prev = app.pointer - 1;
            }
            var from = $(app.object).find('ul').children()[app.pointer];
            var to = $(app.object).find('ul').children()[prev];

            app.doTransition(from, to, 'startCycle');

            app.pointer--;

            if (prev === $(app.object).find('ul').children().length - 1) {
                app.pointer = $(app.object).find('ul').children().length - 1;
            }
        },

        /**
         * Do the current selected transition
         *
         * @param object from
         * @param object to
         * @param string callback
         */
        doTransition:function (from, to, callback) {
            if (app.settings.transitions[app.settings.transition]) {
                if (typeof app[app.settings.transitions[app.settings.transition]] === 'function') {
                    app[app.settings.transitions[app.settings.transition]](from, to, callback);
                }
            }
        },

        /**
         * Basic fade transitions
         *
         * @param object from
         * @param object to
         * @param callback
         */
        _transitionfade:function (from, to, callback) {
            $(from).fadeOut(2000);
            $(to).fadeIn(1500, function () {
                app[callback]();
            });
        },

        /**
         * Reset plugin
         */
        _reset:function () {
            app._applyCSSClasses();

            // Set dimensions
            $(app.object).css({
                'width':app.settings.width,
                'height':app.settings.height
            });

            // Hide all except first
            $(app.object).find(app.slides).each(function () {
                if (!$(this).hasClass('first')) {
                    $(this).hide();
                }
            });

            $(app.controls)
                .find('div.next, div.prev')
                .css({ 'cursor':'pointer'});


            app.pointer = 0;
            app._applyBindings();
        },

        /**
         * Apply all sorts of bindings to all elements here
         */
        _applyBindings:function () {
            $(app.object).mouseover(
                function () {
                    app._showHideControls(1);
                    app.pause();
                }).mouseout(function () {
                    app._showHideControls(0);
                    app.pause();
                });

            if ($(app.object).find(app.controls).length) {
                $(app.object).find(app.controls).show();
                $(app.object).find(app.controls).find('.next').click(function () {
                    app.nextImage();
                });
            }

            if ($(app.object).find(app.controls).length) {
                $(app.object).find(app.controls).find('.prev').click(function () {
                    app.prevImage();
                });
            }

            $(app.controls).mouseover(
                function () {
                    app._showHideControls(1);
                });
        },

        _showHideControls:function (action) {
            if (action === 0) {
                $(app.controls).hide();

            } else if (action === 1) {
                $(app.controls).show();
            }
        },

        /**
         * Apply all necessary css classes*
         */
        _applyCSSClasses:function () {
            var idx = 0;
            $(app.object).find(app.slides).each(function () {
                $(this).addClass('slide');
                if (idx === 0) {
                    $(this).addClass('first');
                } else if (idx === ($(app.object).find(app.slides).length - 1)) {
                    $(this).addClass('last');
                }
                idx++;
            });
        }
    };

    /**
     * Plugin constructor as suggested from the jQuery documentation
     *
     * @param string method
     */
    $.fn.mief_slider = function (method) {
        app.object = this;
        // Method calling logic
        if (app[method]) {
            return app[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return app.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist');
        }
    };

})(jQuery);

jQuery('div.mief_slider').mief_slider();