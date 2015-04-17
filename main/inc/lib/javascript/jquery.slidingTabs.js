// 
//  jquery.slidingTabs.js
//  A jQuery plugin for create animated tabs style carousel that loads dinamic content via ajax
//  
//  Created by Javier Sánchez - Marín (vieron) 
//  http://www.angryloop.net/blog/2010/05/16/jquery-slidingtabs-animated-tabs-that-loads-dinamic-content-via-ajax/
//  http://github.com/vieron/jquery.slidingTabs/
//  Free distribution.
// 


$.fn.slidingTabs = function(options) {

  // build main options before element iteration
  var opts = $.extend({}, $.fn.slidingTabs.defaults, options);
  $.fn.slidingTabs.options = opts;

  // iterate and reformat each matched element
  return this.each(function() {   
      // declaring vars
      var $wrap = $(this) ,
          $tabs_wiewport = $(opts.tabs, $wrap),
          $tabs_wrap = $('ul', $tabs_wiewport),
          $tabs = $('li', $tabs_wiewport),
          $tabs_links = $('a', $tabs),
          $content = $(opts.content , $wrap),
          $viewport_w = $tabs_wiewport[0].clientWidth,
          $tabs_total_w = 0,
          $last_offset = 0;
          
          
          //si las pestañas tienen anchos diferentes, las recorremos y guardamos los anchos en un array
          if (opts.diff_widths == true)  {
            $tabs.each(function(i){ $tabs_total_w = parseInt($tabs_total_w+$(this).outerWidth()); }); $tabs_total_w = parseInt($tabs_total_w+opts.offset_x);
          }else{
            $tabs_total_w = ($tabs.outerWidth()*$tabs.length+1)+opts.offset_x;
          }
          
          
          if ($tabs_total_w >= $viewport_w) {
            //ajustamos el wrapper de las pestañas al ancho oportuno
            $tabs_wrap.css('width', $tabs_total_w+'px');
            
            //creamos las flechas de navegacion
            var $next = $('<a href="#" class="next"><span>'+opts.next_txt+'</span></a>').appendTo($tabs_wiewport);
            var $prev = $('<a href="#" class="prev"><span>'+opts.prev_txt+'</span></a>').prependTo($tabs_wiewport).addClass(opts.hiddenClass);
            
            // eventos de click para navegacion
            $prev.bind('click', function(e){
              e.preventDefault();
              if ($last_offset < 0  && isAnimated() != true) {
                if (($last_offset+opts.displacement) < 0 ){
                  var offset = ($last_offset+opts.displacement);
                  $next.removeClass(opts.hiddenClass);
                }else{ 
                  var offset = 0;
                  if (offset > -opts.displacement)  $next.removeClass(opts.hiddenClass);
                  $prev.addClass(opts.hiddenClass);
                }
                $tabs_wrap.animate({ left : offset+'px'}, opts.animationSpeed);
                $last_offset = offset;
              }
            })

            $next.bind('click', function(e){
              e.preventDefault();
              if(-$tabs_total_w < ($last_offset - $viewport_w) && isAnimated() != true) {
                if(($last_offset-opts.displacement) <= (-$tabs_total_w+$viewport_w)){
                  var offset = (-$tabs_total_w+$viewport_w);
                  $next.addClass(opts.hiddenClass);
                  if (offset > -opts.displacement )  $prev.removeClass(opts.hiddenClass);
                }else{
                  var offset = ($last_offset-opts.displacement);
                  $prev.removeClass(opts.hiddenClass);
                }
                $tabs_wrap.animate({ left : offset+'px'}, opts.animationSpeed );
                $last_offset = offset;
              }
            })
            
          };
          
          
          //comprobamos si está animandose
          var isAnimated = function(){
            return $tabs_wrap.is(':animated');
          }

          //eventos de carga de contenido
          $tabs_links.bind('click', function(e){
            e.preventDefault();
            var $a = $(this);
            //active tabs
            $tabs.removeClass('active');
            $a.closest('li').addClass('active');
            //cargamos el contenido via ajax
            $.ajax({
                 type: opts.requestType ,
                 url: $a.attr('href'),
                 success: function(content){
                   opts.onSuccessCallback($wrap, $tabs_links, content);
                   $('> div:first', $content).fadeOut(opts.fadeOut_duration, function(){
                     opts.onFadeOutCallback($wrap, $tabs_links, content);
                     $(this).html(content).fadeIn(opts.fadeIn_duration, function(){
                       opts.onFadeInCallback($wrap, $tabs_links, content);
                      });
                   })
                 }
               });
          })

  });
}; 


// plugin defaults
$.fn.slidingTabs.defaults = {
  tabs: '.menu',
  content : '.cont_tabs',
  diff_widths : true,
  offset_x : 3,
  displacement : 200,
  requestType : 'GET',
  loader : true,
  hiddenClass : 'accessible',
  next_txt : 'Next',
  prev_txt : 'Previous',
  fadeIn_duration : 'slow',
  fadeOut_duration : 'normal',
  animationSpeed : 500,
  onSuccessCallback : function($wrap, $tabs_links, content){},
  onFadeInCallback : function($wrap, $tabs_links, content){},
  onFadeOutCallback : function($wrap, $tabs_links, content){}
  
};
