
var TinyMCE_SimpleButton = {
	getInfo : function() {
		return {
			longname : "Simple Button",
			author : "Movi, LLC",
			authorurl : "http://movillc.com",
			infourl : "",
			version : tinyMCE.majorVersion + "." + tinyMCE.minorVersion
		};
	},

	getControlHTML : function(cn) {
		switch (cn) {
			case 'simplebutton':
				buttons =           tinyMCE.getButtonHTML('simplebutton', 'lang_simplebutton', '{$pluginurl}/../../images/simplebutton.png', 'simplebutton');
				return buttons;
		}

		return '';
	},

	execCommand : function(editor_id, element, command, user_interface, value) {
		switch (command) {
			case 'simplebutton':
				SimpleButtonFunction();
				return true;
		}

		return false;
	}
};

tinyMCE.addPlugin('simplebutton', TinyMCE_SimpleButton);