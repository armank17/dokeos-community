
if(!(window.chrome == undefined))

{

	$( window ).load(function() {
	console.log( "window loaded" );

		window.setTimeout(function(){
			console.log(frames.length);
			for (i=0;i<frames.length;i++)
			{
				var cssLink = document.createElement("link") 
				cssLink.href = "/main/application/evaluation/assets/css/fix.css"; 
				cssLink .rel = "stylesheet"; 
				cssLink .type = "text/css"; 
				frames[i].document.head.appendChild(cssLink);
				console.log(" "+ i +" ");
				
				
			}

		},1000);


	});
            
                
}
