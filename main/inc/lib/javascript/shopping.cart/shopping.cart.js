$(document).ready(function() {
    $("#checkout").live('click',function(){
      window.location = "/main/payment/checkout.php";
    });
	$('a.addToCartCourseClick').live('click', function(event) {
		event.preventDefault();
		ShoppingCart.addItemShoppingCart($(this));
	});
        $('a.addToCartCourseFree').live('click', function(event) {
                event.preventDefault();
		ShoppingCart.addItemShoppingCartfree($(this));
        });
	$('div#cart').live('click', function(event) {
		event.preventDefault();
		ShoppingCart.mouseOverShoppingCartContent();
		
		$('div#header div#cart.active table.cart td.remove img').live('click' , function(event){
			event.preventDefault();
			ShoppingCart.removeItemShoppingCart($(this));
		} )
	});
});