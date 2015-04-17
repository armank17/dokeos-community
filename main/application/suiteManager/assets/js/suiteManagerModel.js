var SuiteManagerModel = function() {

    function calcPrice() {
        var prices = $(".ui-selected").find(".hdn-prices");
        var total = 0;
        if (prices.length) {
            prices.each(function() {
                var price = parseFloat($(this).val());
                total = total + price;
            });
        }
        $("#total-price").html(total.toFixed(2));
    }
    
    function disableInputs() {
        $("input[type='hidden']").each(function(){
            $(this).attr("disabled", true);
        });
    }
    
    function selectInputs() {
        disableInputs();
        if ($(".ui-selected").length) {
            $(".ui-selected").each(function(){
                $(".ui-selected").find("input[type='hidden']").attr("disabled", false);
            });
        }
    }

    return {
        tableSelectable: function(selector) {
            var _selectRange = false, _deselectQueue = [];
            selector.selectable({
                filter: 'li',
                selecting: function (event, ui) {
                    if (event.detail == 0) {
                        _selectRange = true;
                        return true;
                    }                    
                    if ($(ui.selecting).hasClass('ui-selected')) {
                        _deselectQueue.push(ui.selecting);   
                    }
                    else {
                    }
                    calcPrice();
                },
                unselecting: function (event, ui) {
                    $(ui.unselecting).addClass('ui-selected');            
                    calcPrice();
                },
                stop: function () {
                    if (!_selectRange) {
                        $.each(_deselectQueue, function (ix, de) {
                            $(de)
                                .removeClass('ui-selecting')
                                .removeClass('ui-selected');
                        });
                    }            
                    _selectRange = false;
                    _deselectQueue = [];            
                    calcPrice();
                }
            }); 
        },
        selectPrice: function(selector) {
            var selectorId = selector.attr("id");
            var suite = selectorId.replace("cbo-", "");
            var selectedPrice = selector.val();
            $("#price_"+suite).html(selectedPrice+' &euro;');
            $("#hdn-price-"+suite).val(selectedPrice);            
            var selectedIndex = document.getElementById(selectorId).selectedIndex;
            var selectedText = document.getElementById(selectorId).options[selectedIndex].text;            
            $("#hdn-attribute-"+suite).val(selectedText);
            calcPrice();
        },
        setTotalPrice: function(prices) {
           calcPrice(prices);
        },
        sendPricing: function() {
            selectInputs();
            var data = $("#pricing-form").serialize();
            $.post('/main/index.php?module=suiteManager&cmd=PricingAjax&func=sendPricing', data, function() {
                $.messageBox(getLang('ThanksToUseDokeosSuite'));
            });
        },
        updateAccount: function(myform) {
            var webPath = decodeURIComponent($("#webPath").val()); 
            
            myform.validate();
            
            myform.ajaxForm({
                 dataType:  'json',     
                 beforeSend: function() {
                     $("#install-loading").html("<span><img src='"+webPath+"main/img/mozilla_blu.gif' style='vertical-align:middle;' /></span>");
                 },
                 success: function(data) {
                     $("#install-loading").html("");
                     $.messageBox('<p>'+getLang('YourMessageHasBeenSent')+'<br /><br /><br /><button class="save" onclick="location.href=\''+webPath+'\';" >'+getLang('Accept')+'</button></p>', getLang('Confirmation'), 'confirmation', false, 280);
                 }
             });            
        }
    }    
}();


