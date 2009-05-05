function makeSortable(list) {
	var opt = {tag: listSorter.tag, onUpdate: reorderList,ghosting: false};
	
	if(listSorter.handle) {
		opt.handle = listSorter.handle;
	}	
	if(listSorter.overlap) {
		opt.overlap = listSorter.overlap;
	}
	if(listSorter.constraint) {
		opt.constraint = listSorter.constraint;
	}
//	Sortable.destroy(list);
	
	Sortable.create(list, opt);
	enableListForm(false);
	
	//Auto-update the form when someone clicks a delete link
	var dl = $$(listSorter.deleteLinks);
	dl.each(function(e) {e.onclick = ajaxListDelete; });
	
	//When we submit the form, then enable the elements in the list so that they submit properly
	Event.observe(listSorter.form, 'submit', function() {
		enableListForm(true);
	});
}

//Enable or disable the section part of the form (depending)
function enableListForm(enable) {
	var orderInputs = listSorter.list.select('input');

	if(enable == true) {
		orderInputs.invoke('enable');
	}else {
		orderInputs.invoke('disable');
	}
}

function reorderList(container) {
	var elements = container.select(listSorter.tag);
	//Each list element contains one hidden form input that must be re-indexed
    elements.each(function(el, index){
        var listFormInput = el.select('input').first();
        listFormInput.value = index + 1;
    });
}

function ajaxListDelete(event) {
		var href = this.href;

		if(confirm(listSorter.confirmation)) {
		
			new Ajax.Request(href, {
				method:'get',
				/**
				 * Successful AJAX requests to delete will retrieve XHTML for the list partial.
				 */
				onSuccess: function(t) {
					listSorter.list.updateAppear(t.responseText);
					makeSortable(listSorter.list);
                    if(listSorter.callback) {
                        listSorter.callback();
                    }
                    
				},
				onFailure: function(t) {
					alert(t.status);
				}
			});	
		}
		
		return false;
}