$(function() {
        $('textarea.tinymce').tinymce({
                // Location of TinyMCE script
                script_url : '/js/tiny_mce/tiny_mce.js',

                // General options
                theme : "advanced",
                plugins : "imagemanager,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

                // // Theme options
                // theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
                // theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
                // theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
                // theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
                // theme_advanced_toolbar_location : "top",
                // theme_advanced_toolbar_align : "left",
                // theme_advanced_statusbar_location : "bottom",
                // theme_advanced_resizing : true,

			    relative_urls : false,
    			remove_script_host : true,
			
			
                // Theme options
                theme_advanced_buttons1 : "bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontsizeselect,|,code,cleanup",
                theme_advanced_buttons2 : "bullist,numlist,|,outdent,indent,|,link,unlink,image,|,forecolor,backcolor,|,sub,sup,|,removeformat",
                theme_advanced_buttons3 : "tablecontrols,|,hr,visualaid,|,charmap,nonbreaking",
                theme_advanced_buttons4 : "",
                theme_advanced_toolbar_location : "top",
                theme_advanced_toolbar_align : "left",
                theme_advanced_statusbar_location : "bottom",
                theme_advanced_resizing : false,
                
                // Example content CSS (should be your site CSS)
                // content_css : "css/content.css",
                
                content_css : "/css/tinymce.css",

                // // Drop lists for link/image/media/template dialogs
                // template_external_list_url : "lists/template_list.js",
                // external_link_list_url : "lists/link_list.js",
                // external_image_list_url : "lists/image_list.js",
                // media_external_list_url : "lists/media_list.js",
                
                template_external_list_url : "lists/template_list.js",
                external_link_list_url : "lists/link_list.js",
                external_image_list_url : "lists/image_list.js",
                media_external_list_url : "lists/media_list.js",
                
                // // Replace values for the template plugin
                // template_replace_values : {
                //         username : "Some User",
                //         staffid : "991234"
                // }
                
                template_replace_values : {
                        username : "Some User",
                        staffid : "991234"
                }
        });
});
