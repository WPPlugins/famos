(function($) {
    
    var baseurl = null;
    var menuButton = null;
    tinymce.create('tinymce.plugins.Famos', {

       /**
        * Returns information about the plugin as a name/value array.
        * The current keys are longname, author, authorurl, infourl and version.
        *
        * @return {Object} Name/value array containing information about the plugin.
        */
        getInfo : function() {
                return {
                    longname : "FAMOS",
                        author : 'FAMOS, LLC',
                        authorurl : 'http://www.famos.com',
                        infourl : '',
                        version : $.famos.version
                        };
            },

        /**
         * Initializes the plugin, this will be executed after the plugin has
         * been created. 
         * This call is done before the editor instance has finished it's
         * initialization so use the onInit event of the editor instance to
         * intercept that event. 
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
         init : function(ed, url) {

                baseurl = url;
                ed.onPostRender.add(function(ed, cm) {
                    // TODO: make the menu disappear on hover out
                    // or find the option that evokes said behavior
                    $('#content_famos_author_menu').hoverIntent(function() {
                            menuButton.showMenu();
                        }, function() { });
                });

                ed.onExecCommand.add(function(ed, cmd, ui, val) {
                    if(cmd=='mceRepaint') {
                        //This command is called after an image is deleted
                        try {
                            $.famos.cleanCornerImages();
                        } catch(e) { }
                    }
                });
                ed.onPostProcess.add(function(ed, o) {
                    // FIXME only want to do this on submit;
                    // this clears hints when switching between rich/non-rich
                    try {
                        o.content = $.famos.removeHints(o.content);
                    } catch(e) { }
                });

                $.famos.tinymceeditor = ed;
            },

            /**
             * Creates control instances based in the incoming name. This
             * method is normally not needed since the addButton method of the
             * tinymce.Editor class is a more easy way of adding buttons 
             * but you sometimes need to create more complex controls like
             * listboxes, split buttons etc then this method can be used to
             * create those. 
             *
             * @param {String} n Name of the control to create.
             * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
             * @return {tinymce.ui.Control} New control instance or null if no control was created.
             */
            createControl : function(n, cm) {

                switch(n) {
                case 'famos_author_menu':
                    var c = cm.createMenuButton('famos_author_menu', {
                            title : 'Famos Authoring',
                            image : baseurl + '/images/imgann.png',
                            icons : false
                        });

                    c.onRenderMenu.add(function(c, m) {
                            m.add({title:'Annotate Text', onclick: function() {
                                        $.famos.manualAnnotation();
                                    }})});
                    c.onRenderMenu.add(function(c, m) {
                            m.add({title:'Annotate Image', onclick: function() {
                                        $.famos.manualAnnotation();
                                    }})});
                    c.onRenderMenu.add(function(c, m) {
                            m.add({title:'Remove Annotation', onclick: function() {
                                        $.famos.manualUnannotation();
                                    }})});
                    c.onRenderMenu.add(function(c, m) {
                            var title = function() {
                                return $.cookie('famos.nohints')
                                ? 'Enable Hints' : 'Disable Hints';
                            };
                            m.add({title:title(), onclick: function() {
                                        var old = title();
                                        $.famos.toggleHints();
                                        var text = title();
                                        // kinda hacky, but it works...
                                        $('span.mceText[title='+old+']').attr('title', text).text(text);
                                        
                                    }})});
                    menuButton = c;
                    return c;
                }
                return null;
            }
        });

    // Register plugin
    tinymce.PluginManager.add('famos', tinymce.plugins.Famos);

})(window.famos.jQuery);