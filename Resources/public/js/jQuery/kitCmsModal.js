/**
.kit-cms-modal-overlay {
    position: fixed;
    z-index:100;
    top: 0px;
    left: 0px;
    height:100%;
    width:100%;
    background: #000;
    display: none;
}
 */
(function($){

    var Widget = (function() {
        // constructor
        function Widget(boundingBox, options) {
            this._settings = {
                // custom events callback
                render: null,
                close: null,
                // overlay
                overlay: $('<div class="kit-cms-modal-overlay"><div class="kit-cms-modal-overlay-progress"></div></div>'),
                // closable ?
                closable: true
            };
            // settings
            if (options) {
                $.extend(this._settings, options);
            }

            this._boundingBox = boundingBox;
            this._boundingBox.data( "kitCmsModal", this );
            this.init();
        };
        // methods
        Widget.prototype = {
            init: function() {
                //console.debug("autocomplete init");
                var self = this;
                var eventList = ['render', 'close'];
                // init custom events according to settings callback values
                for (var i = 0 ; i < eventList.length ; i++ ) {
                    if (this._settings[eventList[i]]) {
                        this._boundingBox.bind(eventList[i]+"_kitCmsModal", this._settings[eventList[i]]);
                    }
                }
                // init default events callback
                for (var i = 0 ; i < eventList.length ; i++ ) {
                    var callbackName = "_"+eventList[i]+"Callback";
                    this._boundingBox.bind(eventList[i]+"_kitCmsModal", {self:self}, this[callbackName]);
                }
                // register events
                this._boundingBox.bind("click", this._boundingBoxClickCallback);
            },
            ////
            // callbacks
            ////
            _renderCallback: function(event) {
                if (event.isDefaultPrevented()) {
                    return;
                }
                var self = $(this).data("kitCmsModal");
                self._render();
            },
            _closeCallback: function(event) {
                if (event.isDefaultPrevented()) {
                    return;
                }
                var self = $(this).data("kitCmsModal");
                self._close();
            },

            ////
            // methods
            ////
            _render: function() {
                var self = this;
                if (self._settings.closable) {
                    $(document).keydown(self._escapeCallback);
                    self._settings.overlay.click(function() {
                        self.close();
                    });
                }
                $("body").append(self._settings.overlay);
                self._settings.overlay.css("opacity", 0.8);
                self._settings.overlay.fadeIn(200);

                self._escapeCallback = function(e) {
                    if (e.keyCode == 27) {
                      self.close();
                    }
                }
            },
            _close: function() {
                var self = this;
                if (self._settings.closable) {
                    $(document).unbind("keydown", self._escapeCallback)
                }
                self._settings.overlay.fadeOut(function() {
                    $(this).remove();
                });
            },

            _boundingBoxClickCallback: function(e) {
                var self = $(this).data("kitCmsModal");
                self.render();
            },

            // functions
            close: function() {
                var self = this;
                self._boundingBox.trigger("close_kitCmsModal");
            },
            render: function() {
                var self = this;
                self._boundingBox.trigger("render_kitCmsModal");
            }
        };
        return Widget;
    })();


    var methods = {
        /**
         * add events to a dl instance
         * @this the dl instance (jquery object)
         */
        init : function ( options ) {
            var self = $(this);
            // chainability => foreach
            return this.each(function() {
                var widget = new Widget($(this), options);
            });
        },
        /**
         * unbind all events
         */
        destroy : function( ) {
        }
    };

    $.fn.kitCmsModal = function(method) {
        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.kitCmsModal' );
        }
    };

})(jQuery);

