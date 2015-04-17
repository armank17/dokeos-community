var FCKTemplateItemManager = 
{
	'isInit' : false,
	'actionsDiv' : null,
	'currentItem' : null,
	'currentAction' : '',
	
	
	'insertActionsDiv' : function()
	{
		
		// create actions div
		FCKTemplateItemManager.actionsDiv = document.createElement('div');
		FCKTemplateItemManager.actionsDiv.setAttribute('id', 'table_actions');
		FCKTemplateItemManager.actionsDiv.setAttribute('_fcktemp', true);
		FCKDomTools.SetElementStyles(FCKTemplateItemManager.actionsDiv, {'position':'absolute'});
		FCKTemplateItemManager.hideActionsDiv();

		// create delete button		
		var buttonDelete = document.createElement('img');
		FCKTools.AddEventListener( buttonDelete, "click", FCKTemplateItemManager.handleDeleteAction);
		buttonDelete.setAttribute('src', FCKPlugins.Items['manageTemplateItems'].Path + 'img/delete.png');
		FCKDomTools.SetElementStyles(buttonDelete, {'cursor':'pointer'});
		FCKTemplateItemManager.actionsDiv.appendChild(buttonDelete);
		
		// create add button
		var buttonAdd = document.createElement('img');
		FCKTools.AddEventListener( buttonAdd, "click", FCKTemplateItemManager.handleAddAction);
		buttonAdd.setAttribute('src', FCKPlugins.Items['manageTemplateItems'].Path + 'img/add.png');
		FCKDomTools.SetElementStyles(buttonAdd, {'cursor':'pointer'});
		FCKTemplateItemManager.actionsDiv.appendChild(buttonAdd);

		document.getElementsByTagName('body')[0].appendChild(FCKTemplateItemManager.actionsDiv);
		
	
	},
	
	'checkActionsDivVisibility' : function()
	{
		if(!document.getElementById('table_actions'))
		{
			FCKTemplateItemManager.insertActionsDiv();
		}
		
		var newElementSelected = FCK.Selection.GetParentBlock();
		if(!newElementSelected)
		{
			FCKTemplateItemManager.hideActionsDiv();
			return false;
		}
		if(newElementSelected == FCKTemplateItemManager.currentItem)
		{
			FCKTemplateItemManager.showActionsDiv();
			return true;
		}
		
		do 
		{
			if(newElementSelected.nodeName == "TD" || newElementSelected.nodeName == "TH")
			{
				// verify that parent table has the class table_actions
				var classAttr = '';
				var parentElement = newElementSelected.parentNode;
				FCKTemplateItemManager.currentItem = newElementSelected;
				do
				{
					if(parentElement.nodeName == "TABLE")
					{
						classAttr = FCKDomTools.GetAttributeValue(parentElement, 'class');
						break;
					}
					
				}while(parentElement = parentElement.parentNode);

				if(classAttr == null || classAttr.indexOf('table_actions') == -1)
				{
					FCKTemplateItemManager.hideActionsDiv();
					return false;
				}
				else if(classAttr.indexOf('table_actions_rows') != -1)
				{
					FCKTemplateItemManager.currentAction = "handleRows";
				}
				else if(classAttr.indexOf('table_actions_columns') != -1)
				{
					FCKTemplateItemManager.currentAction = "handleColumns";
				}
				
				FCKTemplateItemManager.showActionsDiv();
				return true;				
			}
				
		
		}while(newElementSelected = newElementSelected.parentNode);

		setTimeout(FCKTemplateItemManager.hideActionsDiv, 200);
		return false;
		
	},
	
	'handleDeleteAction' : function()
	{
		switch(FCKTemplateItemManager.currentAction)
		{
			case "handleRows":
				FCK.Commands.GetCommand("TableDeleteRows").Execute();
				break;
			case "handleColumns":
				FCK.Commands.GetCommand("TableDeleteColumns").Execute();
				break;
		}
		FCKTemplateItemManager.hideActionsDiv();
	},
	
	'handleAddAction' : function()
	{
		switch(FCKTemplateItemManager.currentAction)
		{
			case "handleRows":
				FCK.Commands.GetCommand("TableInsertRowAfter").Execute();
				break;
			case "handleColumns":
				FCK.Commands.GetCommand("TableInsertColumnAfter").Execute();
				break;
		}
	},
	
	'showActionsDiv' : function()
	{
		if(FCKTemplateItemManager.currentItem != null)
		{
			var itemPos = FCKTools.GetDocumentPosition(window, FCKTemplateItemManager.currentItem);
			var paddingStyles =
			{
				'top' : itemPos.y ,
				'left' : itemPos.x
			}
			FCKDomTools.SetElementStyles( FCKTemplateItemManager.actionsDiv, paddingStyles ) ;
		}
	},
	
	'hideActionsDiv' : function()
	{
		FCKDomTools.SetElementStyles(FCKTemplateItemManager.actionsDiv, {'top':'-100000px', 'left':'100000px'});
		FCKTemplateItemManager.currentItem = null;
	}
	
};

FCK.Events.AttachEvent('OnSelectionChange', FCKTemplateItemManager.checkActionsDivVisibility);