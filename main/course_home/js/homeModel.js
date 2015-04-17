var HomeModel = function() {
	
	var courseCode, webPath;
	
	
	function openDialog(myurl, mytitle, mywidth, myheight, refresh, func) {
        courseCode = decodeURIComponent($("#courseCode").val());
        webPath = decodeURIComponent($("#webPath").val());
        iframe = $('<iframe id="idialog" src="'+myurl+'" frameborder="0"></iframe>');
        
           
        // UI Dialog
        iframe.dialog({
            autoOpen: false,
            closeText: getLang('Close'),
            modal: true,
            title: mytitle,
            resizable: false,
            width: mywidth,
            height: myheight,
            open: function(event, ui) {
                if (func == 'sorter') {
                    $("input#item-func").val("sorter");
                    $("#authoring-form").submit();
                }
            },
            close: function(event, ui) {                
                if (func == 'sorter') {
                    var courseCode = decodeURIComponent(window.parent.$("#courseCode").val());
                    var webPath  = decodeURIComponent(window.parent.$("#webPath").val());
                    var itemType = window.parent.$("input#item-type").val();
                    var itemId   = window.parent.$("input#item-id").val();
                    var lpId     = window.parent.$("input#item-lp-id").val();
                    var itemQuery = itemId != ''?'&lpItemId='+itemId:'';
                    var href = webPath+'main/index.php?module=author&cmd=Authoring&func=index&cidReq='+courseCode+'&itemType='+itemType+'&lpId='+lpId+itemQuery;
                    window.parent.location.href = href;
                    return false;
                }
                else {
                    if (refresh == 'true') {   
                        window.parent.location.reload();
                        return false;
                    }
                }
            }
        });
        
        $(".ui-dialog").find(".ui-dialog-titlebar-close").css("right","1em");
        $(".ui-dialog").find(".ui-icon-closethick").css("padding-right","5px");
        
        iframe.dialog('open');
        iframe.css({"display":"block", "width": (mywidth - 15)+'px', "height": (myheight - 15)+'px'});
        iframe.contents().find("body").css("font-size", "15px");        
        if (func == 'sorter') {
            iframe.dialog({
                    buttons: [{
                                id:"btn-close-sort",
                                text: getLang('Validate'),
                                click: function() {
                                        $(this).dialog("close");
                                }
                            }]
            });
            //iframe.attr("width", mywidth+'px');
            iframe.css("width", (mywidth - 15)+'px');            
        }                        
    }
	
	function getQueryParams(url) {
        var qparams = {},
            parts = (url||'').split('?'),
            qparts, qpart,
            i=0;
        if (parts.length <= 1 ){
            return qparams;
        } else{
            qparts = parts[1].split('&');
            for(i in qparts){
                if(typeof(qparts[i])=='string')
                {
                qpart = qparts[i].split('=');
                qparams[decodeURIComponent(qpart[0])] = 
                decodeURIComponent(qpart[1] || '');
                }
            }
        }
        return qparams;
    };
	
	
	return {
	
	
		showActionDialog: function(e, selector) {
				e.preventDefault();           
				var w = 500;
				var h = 170;
				var refresh = true;
				var url = selector.attr("href");
				var title = selector.attr("title");           
				var params = getQueryParams(url);
				if (params['width']) {
					w = parseInt(params['width']);
				}
				if (params['height']) {
					h = parseInt(params['height']);
				}
				if (params['refresh']) {
					refresh = params['refresh'];
				}    
				// Validations
				switch (params['func']) {
					case 'sorter':
						var itemTitle = $("input#txt-item-title").val();
						if ($.trim(itemTitle) == '') {
							$.alert(getLang('TitleRequiredField'), getLang('Error'), 'error');
							$("input#txt-item-title").addClass( "ui-state-error" );    
							return false;
						}
						break;
					case 'image':
					case 'video':
						if ($("#cke_authoring_editor").length == 0) {
							$.alert(getLang('ChooseLayoutToInsertRessource'), getLang('Error'), 'error');
							return false;
						}                  
						break;
				}
				openDialog(url, title, w, h, refresh, params['func']);
			}
	}		
}();
