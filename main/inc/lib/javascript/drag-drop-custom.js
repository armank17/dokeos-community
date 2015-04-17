/************************************************************************************************************
	(C) www.dhtmlgoodies.com, November 2005
	
	This is a script from www.dhtmlgoodies.com. You will find this and a lot of other scripts at our website.	
	
	Terms of use:
	You are free to use this script as long as the copyright message is kept intact. However, you may not
	redistribute, sell or repost it without our permission.
	
	Thank you!
	
	www.dhtmlgoodies.com
	Alf Magne Kalleland
	
	************************************************************************************************************/
		
	var shuffleQuestions = false;	/* Shuffle questions ? */
	var shuffleAnswers = false;	/* Shuffle answers ? */
	var lockedAfterDrag = false;	/* Lock items after they have been dragged, i.e. the user get's only one shot for the correct answer */
	
	function quizIsFinished()
	{
		// This function is called when everything is solved		
		
	}
	
	
	/* Don't change anything below here */
	var dragContentDiv = false;
	var dragContent = false;
	
	var dragSource = false;
	var dragDropTimer = -1;
	var destinationObjArray = new Array();
	var destination = false;
	var dragSourceParent = false;
	var dragSourceNextSibling = false;
	var answerDiv;
	var questionDiv;	
	var sourceObjectArray = new Array();
	var arrayOfEmptyBoxes = new Array();
	var arrayOfAnswers = new Array();
	
	function getTopPos(inputObj)
	{		
	  if(!inputObj || !inputObj.offsetTop)return 0;		
	  var returnValue = inputObj.offsetTop;
	  while((inputObj = inputObj.offsetParent) != null)returnValue += inputObj.offsetTop;
	  return returnValue;
	}
	
	function getLeftPos(inputObj)
	{
	  if(!inputObj || !inputObj.offsetLeft)return 0;	
	  var returnValue = inputObj.offsetLeft;
	  while((inputObj = inputObj.offsetParent) != null)returnValue += inputObj.offsetLeft;
	  return returnValue;
	}
		
	function cancelEvent()
	{
		return false;
	}
	
	function initDragDrop(e)
	{
		if(document.all)e = event;
		if(lockedAfterDrag && this.parentNode.parentNode.id=='questionDiv')return;
		dragContentDiv.style.left = e.clientX  + Math.max(document.documentElement.scrollLeft,document.body.scrollLeft) + 'px';
		dragContentDiv.style.top = e.clientY  + Math.max(document.documentElement.scrollTop,document.body.scrollTop) + 'px';
		dragSource = this;
		dragSourceParent = this.parentNode;
		dragSourceNextSibling = false;
		if(this.nextSibling)dragSourceNextSibling = this.nextSibling;
		if(!dragSourceNextSibling.tagName)dragSourceNextSibling = dragSourceNextSibling.nextSibling;
		
		dragDropTimer=0;
		timeoutBeforeDrag();
		
		return false;
	}
	
	function timeoutBeforeDrag(){
		if(dragDropTimer>=0 && dragDropTimer<10){
			dragDropTimer = dragDropTimer +1;
			setTimeout('timeoutBeforeDrag()',10);
			return;
		}
		if(dragDropTimer>=10){
			dragContentDiv.style.display='block';
			dragContentDiv.innerHTML = '';
			dragContentDiv.appendChild(dragSource);
		
			
		}		
	}
	
	function dragDropMove(e)
	{
		if(dragDropTimer<10){
			return;
		}
		
		if(document.all)e = event;
		
		var scrollTop = Math.max(document.documentElement.scrollTop,document.body.scrollTop);
		var scrollLeft = Math.max(document.documentElement.scrollLeft,document.body.scrollLeft);
		
		dragContentDiv.style.left = e.clientX + scrollLeft + 'px';
		dragContentDiv.style.top = e.clientY + scrollTop + 'px';
		
		var dragWidth = dragSource.offsetWidth;
		var dragHeight = dragSource.offsetHeight;
		

		var objFound = false;
		
		var mouseX = e.clientX + scrollLeft;
		var mouseY = e.clientY + scrollTop;
		
		destination = false;
		for(var no=0;no<destinationObjArray.length;no++){
			var left = destinationObjArray[no]['left'];
			var top = destinationObjArray[no]['top'];
			var width = destinationObjArray[no]['width'];
			var height = destinationObjArray[no]['height'];
			
			destinationObjArray[no]['obj'].className = 'destinationBox';
			var subs = destinationObjArray[no]['obj'].getElementsByTagName('DIV');
			if(!objFound && subs.length==0){
				if(mouseX < (left/1 + width/1) && (mouseX + dragWidth/1) >left && mouseY < (top/1 + height/1) && (mouseY + dragHeight/1) >top){
					destinationObjArray[no]['obj'].className='dragContentOver';
					destination = destinationObjArray[no]['obj'];					
					objFound = true;
				}		
			}	
		}
		
		sourceObjectArray['obj'].className='';
		
		if(!objFound){
			var left = sourceObjectArray['left'];
			var top = sourceObjectArray['top'];
			var width = sourceObjectArray['width'];
			var height = sourceObjectArray['height'];
						
			if(mouseX < (left/1 + width/1) && (mouseX + dragWidth/1) >left && mouseY < (top/1 + height/1) && (mouseY + dragHeight/1) >top){
				destination = sourceObjectArray['obj'];
				sourceObjectArray['obj'].className='dragContentOver';
			}
		}
		return false;
	}
	
	
	function dragDropEnd()
	{		
	//	alert('Dragdropend='+document.frm_exercise.questionid.value);
	//	alert('cntOption='+document.frm_exercise.cntOption.value);
		var qnid = document.frm_exercise.questionid.value;
		var cntOption = document.frm_exercise.cntOption.value;
		if(dragDropTimer<10){
			dragDropTimer = -1;
			return;
		}
		dragContentDiv.style.display='none';
		sourceObjectArray['obj'].style.backgroundColor = '#FFF';
		if(destination){
			destination.appendChild(dragSource);
			destination.className='destinationBox';
			
			// Check if position is correct, i.e. correct answer to the question
			
			if(!destination.id || destination.id!='answerDiv'){
				var previousEl = dragSource.parentNode.previousSibling;
				if(!previousEl.tagName)previousEl = previousEl.previousSibling;
				var numericId = previousEl.id.replace(/[^0-9]/g,'');
				var numericIdSource = dragSource.id.replace(/[^0-9]/g,'');
				var ansOption = (numericId*1) + (cntOption*1);
	//			alert('numericId=='+numericId);
	//			alert('numericIdsource=='+numericIdSource);				
	//			alert('ansOption=='+ansOption);				
	//			alert('value='+document.getElementById('choice['+qnid+']['+ansOption+']').value);
				document.getElementById('choice['+qnid+']['+ansOption+']').value = numericIdSource;
	//			alert('value='+document.getElementById('choice['+qnid+']['+ansOption+']').value);
				if(numericId==numericIdSource){
					dragSource.className='correctAnswer';
					checkAllAnswers();	
				}
				else
					dragSource.className='correctAnswer';				
			}
			
			if(destination.id && destination.id=='answerDiv'){
				dragSource.className='dragDropSmallBox';
			}
			
		}else{
			if(dragSourceNextSibling)
				dragSourceNextSibling.parentNode.insertBefore(dragSource,dragSourceNextSibling);
			else
				dragSourceParent.appendChild(dragSource);
		}
		dragDropTimer = -1;
		dragSourceNextSibling = false;
		dragSourceParent = false;
		destination = false;
	}
	
	function checkAllAnswers()
	{	
		for(var no=0;no<arrayOfEmptyBoxes.length;no++){
			var sub = arrayOfEmptyBoxes[no].getElementsByTagName('DIV');
			if(sub.length==0)return;
			
			if(sub[0].className!='correctAnswer'){
				return;
			}	
			
		}	
		
		quizIsFinished();	
	}
	

	
	function resetPositions()
	{
		if(dragDropTimer>=10)return;
		
		for(var no=0;no<destinationObjArray.length;no++){
			if(destinationObjArray[no]['obj']){
				destinationObjArray[no]['left'] = getLeftPos(destinationObjArray[no]['obj'])
				destinationObjArray[no]['top'] = getTopPos(destinationObjArray[no]['obj'])	
			}		
			
		}
		sourceObjectArray['left'] = getLeftPos(answerDiv);
		sourceObjectArray['top'] = getTopPos(answerDiv);		
	}
	
	
	function initDragDropScript()
	{		
		dragContentDiv = document.getElementById('dragContent');
		answerDiv = document.getElementById('answerDiv');
		
		if(!answerDiv || !dragContentDiv)		return;
		
		answerDiv.onselectstart = cancelEvent;
		var divs = answerDiv.getElementsByTagName('DIV');
		var answers = new Array();
		
		for(var no=0;no<divs.length;no++){
			if(divs[no].className=='dragDropSmallBox'){
				divs[no].onmousedown = initDragDrop;
				answers[answers.length] = divs[no];
				arrayOfAnswers[arrayOfAnswers.length] = divs[no];
			}
			
		}	
		
		if(shuffleAnswers){
			for(var no=0;no<(answers.length*10);no++){
				var randomIndex = Math.floor(Math.random() * answers.length);
				answerDiv.appendChild(answers[randomIndex]);
			}		
		}
		
		sourceObjectArray['obj'] = answerDiv;
		sourceObjectArray['left'] = getLeftPos(answerDiv);
		sourceObjectArray['top'] = getTopPos(answerDiv);
		sourceObjectArray['width'] = answerDiv.offsetWidth;
		sourceObjectArray['height'] = answerDiv.offsetHeight;
		
		
		questionDiv = document.getElementById('questionDiv');
		
		questionDiv.onselectstart = cancelEvent;
		var divs = questionDiv.getElementsByTagName('DIV');
		
		var questions = new Array();
		var questionsOpenBoxes = new Array();
		

		for(var no=0;no<divs.length;no++){
			if(divs[no].className=='destinationBox'){
				var index = destinationObjArray.length;
				destinationObjArray[index] = new Array();
				destinationObjArray[index]['obj'] = divs[no];
				destinationObjArray[index]['left'] = getLeftPos(divs[no])
				destinationObjArray[index]['top'] = getTopPos(divs[no])
				destinationObjArray[index]['width'] = divs[no].offsetWidth;
				destinationObjArray[index]['height'] = divs[no].offsetHeight;
				questionsOpenBoxes[questionsOpenBoxes.length] = divs[no];
				arrayOfEmptyBoxes[arrayOfEmptyBoxes.length] = divs[no];
			}
			if(divs[no].className=='dragDropSmallBox'){
				questions[questions.length] = divs[no];
			}
				
		}
		
		if(shuffleQuestions){
			for(var no=0;no<(questions.length*10);no++){
				var randomIndex = Math.floor(Math.random() * questions.length);

				questionDiv.appendChild(questions[randomIndex]);			
				questionDiv.appendChild(questionsOpenBoxes[randomIndex]);		
				
				destinationObjArray[destinationObjArray.length] = destinationObjArray[randomIndex];
				destinationObjArray.splice(randomIndex,1);	
				
				questionsOpenBoxes[questionsOpenBoxes.length] = questionsOpenBoxes[randomIndex];
				questionsOpenBoxes.splice(randomIndex,1);
				questions[questions.length] = questions[randomIndex];
				questions.splice(randomIndex,1);	
				
				
			}		
		}
		
		questionDiv.style.visibility = 'visible';
		answerDiv.style.visibility = 'visible';
		
		document.documentElement.onmouseup = dragDropEnd;	
		document.documentElement.onmousemove = dragDropMove;	
		setTimeout('resetPositions()',150);
		window.onresize = resetPositions;
	}

	/* Reset the form */
	function dragDropResetForm()
	{
		for(var no=0;no<arrayOfAnswers.length;no++){
			arrayOfAnswers[no].className='dragDropSmallBox'
			answerDiv.appendChild(arrayOfAnswers[no]);			
		}	
	}