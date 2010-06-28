function makeSortable(list) {	
	var options = {};
	if (listSorter.axis) {
	    options.axis = listSorter.axis;
	}
	list.sortable(options);
	list.bind('sortupdate', function(event, ui) { reorderList(jQuery(this)); });
	
	enableListForm(false);
	
	//Auto-update the form when someone clicks a delete link
	var deleteLinks = jQuery(listSorter.deleteLinks);
	deleteLinks.bind('click', ajaxListDelete);
	
	//When we submit the form, then enable the elements in the list so that they submit properly
	listSorter.form.bind('submit', function() {
		enableListForm(true);
	});
}

/*
* Enable or disable the section part of the form (depending)
*/
function enableListForm(enable) {
	var orderInputs = listSorter.list.find('input');
	if (enable) {
		orderInputs.trigger('enable');
	} else {
		orderInputs.trigger('disable');
	}
}

function reorderList(container) {
	var elements = container.find(listSorter.tag);
	//Each list element contains one hidden form input that must be re-indexed
    jQuery.each(elements, function(index, element){       
        // I'm not sure why the .find('input :first') and .find('input').first() does not work. 
        var listFormInput = jQuery(element).find('input');
        listFormInput.val(index + 1);
    });
}

function ajaxListDelete(event) {
    event.stopPropagation();
	if (confirm(listSorter.confirmation)) {	
		var uri = jQuery(this).attr('href');
		jQuery.ajax({
           url: uri,
           method: 'GET',

           success: function(data) {
               listSorter.list.html(data);
			   makeSortable(listSorter.list);
               if (listSorter.callback) {
                   listSorter.callback();
               }
           },

           error: function(xhr, textStatus, errorThrown) {
             alert(textStatus);  
           },
         });
	}
	return false;
}