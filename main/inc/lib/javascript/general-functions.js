var ShoppingCart = function() {

	var isContentEmpty = true;
	var currentItem = 0;
	var clickedAnchors = new Array();

	return {
              addItemShoppingCart : function( clicked )
              {
            $('#shoppingMsgBody').dialog({
                modal: true, 
                //title: 'Panier d\'achats', 
                height: '295', 
                width: '350px', 
                resizable: false,
                closeText : $(".txtclose").html(),
                buttons: [{
                    text: $("#divYes").html(),
                    "id": "btnOk",
                    click: function () {
                                        $('.ui-dialog-titlebar-close').hide();
                        
                        var parentContainer  = $("div.course_catalog_container").length > 0?clicked.closest('div.course_catalog_container'):clicked.closest('span.course_catalog_container');
                        var code = parentContainer.attr('rel');
                        if($('ul#shoppingCartCatalog').length > 0)
                            var type = $('ul#shoppingCartCatalog').attr('rel');
                        else
                            var type = $('#shoppingCartCatalog').attr('rel');
                                        $.ajax({
                                            url : "/main/core/controller/shopping_cart/shopping_cart_controller.php",
                                            data : {
                                                    code: code,
                                                    type : type,
                                                    action: 'addItem'
                                                },
                                            context : document.body,
                                            success : function(msg) {
                                                        $("html, body").animate({ scrollTop: 0 }, 600,function(){
                                                        $('div#header div#cart').replaceWith(msg);
                                                        });
                                                    }
                                              });
                                        $(this).dialog('close');
                                        
                    }

                }, {
                    text: $("#divNo").html(),
                    click: function () {
                        $(this).dialog('close');
                    }
                }]                
//                            buttons: {
//                    'Non': function() {
//                                        $(this).dialog('close');
//                                        },
//                    'Oui': function() {
//                                        $('.ui-dialog-titlebar-close').hide();
//                        
//                        var parentContainer  = $("div.course_catalog_container").length > 0?clicked.closest('div.course_catalog_container'):clicked.closest('span.course_catalog_container');
//                        var code = parentContainer.attr('rel');
//                        if($('ul#shoppingCartCatalog').length > 0)
//                            var type = $('ul#shoppingCartCatalog').attr('rel');
//                        else
//                            var type = $('#shoppingCartCatalog').attr('rel');
//                                        $.ajax({
//                                            url : "/main/core/controller/shopping_cart/shopping_cart_controller.php",
//                                            data : {
//                                                    code: code,
//                                                    type : type,
//                                                    action: 'addItem'
//                                                },
//                                            context : document.body,
//                                            success : function(msg) {
//                                                        $('div#header div#cart').replaceWith(msg);
//                                                    }
//                                              });
//                                        $(this).dialog('close');
//                                        }
//                                     }z
                   });
		},
		removeItemShoppingCart : function( clicked )
		{
			var code = clicked.attr('alt');

			$.ajax({
				url : "/main/core/controller/shopping_cart/shopping_cart_controller.php",
				data : {
					code: code,
					type : '',
					action: 'removeItem'
				},
				context : document.body,
				success : function(msg) {
					$('div#header div#cart').replaceWith(msg);
					$('div#header div#cart').addClass('active');

				}
			});
		},
		mouseOverShoppingCartContent : function()
		{
			$('div#header div#cart').addClass('active');
			$('div#header div#cart *').show();
			$('div#header div#cart').live('mouseleave', function() {
				$(this).removeClass('active');
			});
		},
                addItemShoppingCartfree : function( clicked )
              {
                var parentContainer = $("div.course_catalog_container").length > 0 ? clicked.closest('div.course_catalog_container') : clicked.closest('span.course_catalog_container');
                var code;
                if (parentContainer.length>0)
                    code = parentContainer.attr('rel');
                    else
                        code = clicked.attr('id');
                if ($('ul#shoppingCartCatalog').length > 0)
                    var type = $('ul#shoppingCartCatalog').attr('rel');
                else
                    var type = $('#shoppingCartCatalog').attr('rel');
                $.ajax({
                    url: "/main/core/controller/shopping_cart/shopping_cart_controller.php",
                    data: {
                        code: code,
                        type: type,
                        mode_course: 'free',
                        action: 'addItem'
                    },
                    context: document.body,
                    success: function(msg) {
                        window.location.href = 'main/payment/checkout_2_registration.php?id=&prev=2&chr_type=0';
                    }
                });
                                        
		}
	};
}();
