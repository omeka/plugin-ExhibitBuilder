Omeka = Omeka || new Object;
Omeka.ExhibitBuilder = Omeka.ExhibitBuilder || new Object;

Omeka.ExhibitBuilder = Class.create({
    
    initialize: function() {		
        Event.observe(document, 'omeka:loaditems', this.onLoadPagination.bind(this));
        //When you're done, make all the items drag/droppable
    	Event.observe(document, 'omeka:loaditems', this.enableDragAndDrop.bind(this));
    	
        Event.observe(document, 'omeka:dropitem', function(e){
            this.moveItem(e.memo.item, e.memo.container);
            this.postDropStyling(e.memo.item, e.memo.container);
        }.bind(this));
    },
    
    postDropStyling: function(item, container)
    {
        item.select('.handle').first().hide();
		new Effect.Highlight(container);
    },
    
    /**
     * AJAX request to retrieve the list of items that can be used in the exhibit.
     */
     getItems: function(uri, parameters)
	 {	
	     var fireEvents = false;
	     
	     this.paginationUri = uri;    
		new Ajax.Updater('item-select', uri, {
			parameters: parameters,
			evalScripts: true,
			method: 'get',
			onSuccess: function() {
			    fireEvents = true;
			},
			onFailure: function(t) {
			    alert(t.status);
			},
			onComplete: function() {
			    if(fireEvents) {
			        document.fire("omeka:loaditems");
			    }
			}
		});
    },
    
    searchItems: function(searchForm)
    {
        // The advanced search form automatically puts 'items/browse' as the 
        // action URI.  We need to hack that with Javascript to make this work.
        // This will give it the URI exhibits/items or whatever is set in
        // page-form.php.
        searchForm.action = this.paginationUri;
        searchForm.request({
            onComplete: function(t) {
                $('item-select').update(t.responseText);
                document.fire("omeka:loaditems");
            }
        });
    },
    
    getItemId: function(element) {
    	var id = null;
    	if(!element) {
    		throw 'Invalid element passed to getItemId()';
    	} 

        function getItemIdFromSubDiv(element) {
        	var idDiv = element.select('.item_id').first();
        	if(Object.isUndefined(idDiv)) {
        	    return false;
        	}
        	return parseInt(idDiv.innerHTML.strip());
        }

    	//item is a form element so return the value
    	if(element.hasClassName('item-drag')) {

    		return getItemIdFromSubDiv(element);		
    	}
    	else if( element.hasClassName('item-drop') ) {

    		//try to get itemId from the input in the div
    		id = this.getItemIdFromInput(element);
    		if(isNaN(parseInt(id))) {
    			return getItemIdFromSubDiv(element);
    		} else {
    			return id;
    		}

    	}else {
    		throw 'Invalid element with class = ' + element.className + ' has been passed to getItemId()';
    	}

    	return false;
    },

    getItemIdFromInput: function(element) {
    	var input = element.getElementsByTagName('input')[0];

    	if(!input) {
    		return false;
    	}
    	return input.value;
    },

    setItemId: function(element, id) {
    	if(element.hasClassName('item-drop')) {
    		var input = element.getElementsByTagName('input')[0];
    		if(input) {
    			input.value = id;
    		}
    	}else if(element.hasClassName('item-drag')) {
    		var idDiv = element.getElementsByClassName('item_id')[0];
    		idDiv.innerHTML = id;
    	}else {
    		throw 'Element passed to setItemId() has className of '+element.className;
    	}
    },

    getItemFromContainer: function(container) {
    	var item = container.select('div.item-drag').last();
    	return item ? item : false;
    },

    sendItemHome: function(draggable) {
    		var draggableId = this.getItemId(draggable);
            
            this.addHandles([draggable]);
            
    		//Loop through a list of the containers in the pagination
    		//Check the itemId of each for a match
    		var containers = $$('#item-select .item-drop');
    		var reAttached = false;

    		for (var i=0; i < containers.length; i++) {
    			var containerItemId = this.getItemId(containers[i]);
    			var container = containers[i];

    			if(containerItemId == draggableId) {
    				//Check if there is already an item in this spot
    				if(!this.getItemFromContainer(container)) {
    					reAttached = true;
    					container.appendChild(draggable);
    				}
    			}
    		};

    		if(!reAttached) {
    			draggable.destroy();
    		}

    },

    moveItem: function(item, container) {
    	/* 	Step 1: Get the itemId for the newly dropped item
    		Step 2: Get the existing item
    			Step 3: If there is an existing item, move it back to its original spot
    				Step 4: If there is already a droppable item in its original spot, destroy it
    		Step 5: Move the newly dropped item inside the droppable container
    		Step 6: Change the value of the container's form element to reflect the new itemId */
    	var oldItem = this.getItemFromContainer(container);
        
    	if(oldItem) {
    		this.sendItemHome(oldItem);
    	}

    	//append and set the itemId of the droppable element
    	container.appendChild(item);
    	this.setItemId(container, this.getItemId(item));
    },
    
    /**
     * Get a list of the draggables on the form. Fade the image and unregister
     * the ones that are on the pagination.
     */
    disablePaginationDraggables: function() {
    	var formItemId, selectItemId, itemToRemove;
    	var layoutContainers = $$('#layout-form div.item-drop');
    	var selectItemContainers = $$('#item-select div.item-drop');
        
        layoutContainers.each(function(formContainer) {
            if (formItemId = this.getItemId(formContainer)) {
                selectItemContainers.each(function(itemContainer) {
                    if (formItemId == this.getItemId(itemContainer)) {
                        //Stop the item from being dragged.
                        if (itemToRemove = this.getItemFromContainer(itemContainer)) {
                            itemToRemove.remove();
                        };
                    };
                }.bind(this));
            }
        }.bind(this));
    },

    makeDraggable: function(containers) {
    	var item, options = {
    		revert:true,
    		handle: 'handle',
    		snap: [20, 20],
    		scroll: window,
    		scrollSensitivity: 50,
    		scrollSpeed: 40,
    		onStart: function(item, event) {			
    			if(item.element.descendantOf('layout-form')) {
    				var oldContainer = item.element.parentNode;
    				this.setItemId(oldContainer, false);
    			}
    		}.bind(this),
    		onEnd: function(item) {
    			var container = item.element.parentNode;
    			if(container.descendantOf('layout-form')) {
    				this.setItemId(container, this.getItemId(item.element));
    			}
    		}.bind(this)
    	};

    	for (var i=0; i < containers.length; i++) {

    		var item = this.getItemFromContainer(containers[i]);
    		if(item) {
    			var drag = new Draggable(item, options);
    		}
    	};
    },

	onLoadPagination: function() 
	{        
		new Effect.Highlight('item-list');
		
		//Make each of the pagination links fire an additional ajax request
		$$('#pagination a').invoke('observe', 'click', function(e){
		    e.stop();
		    this.getItems(e.element().href);
		}.bind(this));
		
		//Make the correct elements on the pagination draggable
		this.makeDraggable($$('#item-select div.item-drop'));
		
		//Disable the items in the pagination that are already used
		this.disablePaginationDraggables();
		
		//Hide all the numbers that tell us the Item ID
		$$('.item_id').invoke('hide');
		
		//Make the search form respond with ajax power
		Event.observe('search', 'submit', function(e){
		    e.stop();
		    this.searchItems($('search'));
		}.bind(this));
	},

    makeDroppable: function(containers) {
    	containers.each(function(container){
    	    Droppables.add(container, {
    			snap: true,
    			onDrop: function(draggable, droppable) {
    			    document.fire('omeka:dropitem', {item: draggable, container: droppable});
    			}
    		});
    	});
    },
    
    setUrlForHandleGif: function(url) {
        this.handleUrl = url;
    },
    
    addHandles: function(items) {
        items.invoke('insert', {top: '<div class="handle"><img src="' + this.handleUrl + '"></div>'});
    },
        
    deleteItemFromContainer: function(container) {
        var handle, item = this.getItemFromContainer(container);
        this.setItemId(container, false);
        if(item) {
        	this.sendItemHome(item);
        	// Show the handle for moving the item.
        	item.select('.handle').first().show();
        }
    },
    
    enableDragAndDrop: function() {
    	var formContainers = $$('#layout-form div.item-drop');
        
    	// All items are draggable but only items on the form will reset the form inputs when dragged
    	this.makeDraggable(formContainers);

    	// Dropping the items on the form should only work when dropping them elsewhere on the form
    	this.makeDroppable(formContainers);

    	this.styleDeleteButton(formContainers);
    	
    	// Hide the item_id divs, labels for the item boxes, text fields for the layout form
    	$$('.item_id, .item-drop input, .item-drop label').invoke('hide');
    },
    
    styleDeleteButton: function(containers) {
        containers.each(function(container){
            var clear = $(document.createElement('a'));
    		clear.innerHTML = "Remove This Item";
    		clear.className = 'remove_item';
    		clear.style.cursor = "pointer";
    		clear.observe('click', this.deleteItemFromContainer.bind(this, container));
    		container.appendChild(clear);            
        }.bind(this));
    },
    
    /**
     * Style the handles for the section/page lists in the exhibit builder.
     */
    addStyling: function() {
        $$('.handle').invoke('setStyle', {display:'inline',cursor: 'move'});

    	$$('.order-input').invoke('setStyle', {border: 'none',background:'#fff',color: '#333'});
    }
});

Omeka.ExhibitBuilder.wysiwyg = function() {
    
    if (!this.isInitialized) {
        this.isInitialized = true;
		tinyMCE.init({
			mode : "textareas", // All textareas
			theme: "advanced",
			theme_advanced_toolbar_location : "top",
			force_br_newlines : false,
			forced_root_block : '', // Needed for 3.x
			remove_linebreaks : true,
			fix_content_duplication : false,
			fix_list_elements : true,
			valid_child_elements:"ul[li],ol[li]",
			theme_advanced_buttons1 : "bold,italic,underline,justifyleft,justifycenter,justifyright,bullist,numlist,link,formatselect,code",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_toolbar_align : "left"
		});
    };
}