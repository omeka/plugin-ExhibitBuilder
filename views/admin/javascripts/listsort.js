function makeSortable(list, sortableOptions, orderInputSelector, deleteLinksSelector, deleteConfirmationText, formSelector, callback) {	
	
	list.sortable(sortableOptions);
	
	list.bind('sortupdate', function(event, ui) { event.stopPropagation(); reorderList(jQuery(this), orderInputSelector); });
	
	enableListForm(list, orderInputSelector, false);
	
	//Auto-update the form when someone clicks a delete link
	deleteLinks = jQuery(deleteLinksSelector);
	deleteLinks.bind('click', 
	                 {list: list,
	                  sortableOptions: sortableOptions,
	                  orderInputSelector: orderInputSelector,
	                  deleteLinksSelector: deleteLinksSelector,
	                  deleteConfirmationText: deleteConfirmationText, 
	                  formSelector: formSelector,
	                  callback: callback}, 
	                  ajaxListDelete);
	
	//When we submit the form, then enable the elements in the list so that they submit properly
	jQuery(formSelector).bind('submit', {list: list, orderInputSelector: orderInputSelector}, function() {
		enableListForm(list, orderInputSelector, true);
	});
}

/*
* Enable or disable the section part of the form (depending)
*/
function enableListForm(list, orderInputSelector, enable) {
	var orderInputs = list.find(orderInputSelector);	
	if (enable) {
		orderInputs.trigger('enable');
	} else {
		orderInputs.trigger('disable');
	}
}

/*
* Reorders the hidden input elements of a list
*/
function reorderList(list, orderInputSelector) {
	var elements = list.children('li');
	//Each list element contains one hidden form input that must be re-indexed
    jQuery.each(elements, function(index, element) {
        var listFormInput = jQuery(element).find(orderInputSelector);
        listFormInput.val(index + 1);
    });
}

function ajaxListDelete(event) {
    event.stopPropagation();
	if (confirm(event.data.deleteConfirmationText)) {
	    	
		var uri = jQuery(this).attr('href');
		var list = event.data.list;
		var sortableOptions = event.data.sortableOptions;
		var orderInputSelector = event.data.orderInputSelector;
		var deleteLinksSelector = event.data.deleteLinksSelector;
		var deleteConfirmationText = event.data.deleteConfirmationText;
		var formSelector = event.data.formSelector;
		var callback = event.data.callback;
		
		jQuery.ajax({
           url: uri,
           method: 'GET',

           success: function(data) {
               list.html(data);
               makeSortable(list, sortableOptions, orderInputSelector, deleteLinksSelector, deleteConfirmationText, formSelector, callback);
               if (callback) {
                   callback();
               }
           },

           error: function(xhr, textStatus, errorThrown) {
             alert(textStatus);  
           }
         });
	}
	return false;
}