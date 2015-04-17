$(document).ready(function(){
  $("#content-slider").slider({
    animate: true,
    change: handleSliderChange,
    slide: handleSliderSlide
  });
	

});

function handleSliderChange(e, ui)
{
  //var maxScroll = $("#content-scroll").attr("scrollWidth") - $("#content-scroll").width();
  var maxScroll = $("#content-scroll")[0].scrollWidth - $("#content-scroll").width();
  $("#content-scroll").animate({scrollLeft: ui.value * (maxScroll / 100) }, 200);
}

function handleSliderSlide(e, ui)
{
  var maxScroll = $("#content-scroll").attr("scrollWidth") - $("#content-scroll").width();
  $("#content-scroll").attr({scrollLeft: ui.value * (maxScroll / 100) });	
}
