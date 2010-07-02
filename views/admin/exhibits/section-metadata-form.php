<?php
if ($exhibitSection->title) {
    $exhibitSectionTitle = $actionName . ' Section: "' . $exhibitSection->title . '"';
} else {
    $exhibitSectionTitle = $actionName . ' Section';
}
?>
<?php head(array('title'=> html_escape($exhibitSectionTitle), 'bodyclass'=>'exhibits')); ?>
<?php echo js('listsort'); ?>
<script type="text/javascript" charset="utf-8">
//<![CDATA[
    
    function makePageListDraggable()
    {
        var pageListSortableOptions = {axis:'y'};
        var pageListOrderInputSelector = '.page-info input';
        var pageListDeleteLinksSelector = '.page-delete a';
        var pageListDeleteConfirmationText = 'Are you sure you want to delete this page?';
        var pageListFormSelector = '#section-metadata-form';
        var pageListCallback = Omeka.ExhibitBuilder.addStyling;
        
        var pageList = jQuery('.page-list');
        makeSortable(jQuery(pageList), 
                     pageListSortableOptions,
                     pageListOrderInputSelector, 
                     pageListDeleteLinksSelector, 
                     pageListDeleteConfirmationText, 
                     pageListFormSelector, 
                     pageListCallback);
    }
    
    jQuery(window).load(function() {
        Omeka.ExhibitBuilder.wysiwyg();
        makePageListDraggable();
    });
//]]>
</script>

<h1><?php echo html_escape($exhibitSectionTitle); ?></h1>

<div id="primary">
	<div id="exhibits-breadcrumb">
		<a href="<?php echo html_escape(uri('exhibits')); ?>">Exhibits</a> &gt; <a href="<?php echo html_escape(uri('exhibits/edit/' . $exhibit['id']));?>"><?php echo html_escape($exhibit['title']); ?></a>  &gt; <?php echo html_escape($actionName . ' Section'); ?>
	</div>

<?php 
	echo flash();
?>

<form method="post" accept-charset="utf-8" action="" id="section-metadata-form" class="exhibit-builder">
			
	<fieldset>
		<legend>Section Metadata</legend>	
	    <div class="field"><?php echo text(array('name'=>'title', 'id'=>'title', 'class'=>'textinput'), $exhibitSection->title, 'Section Title'); ?></div>
    		<div class="field"><?php echo text(array('name'=>'slug','id'=>'slug','class'=>'textinput'), $exhibitSection->slug, 'Section Slug (no spaces or special characters)'); ?></div>
    	<div class="field"><?php echo textarea(array('name'=>'description', 'id'=>'description', 'class'=>'textinput','rows'=>'10','cols'=>'40'), $exhibitSection->description, 'Section Description'); ?></div>
	</fieldset>
	<fieldset id="section-pages">
		<legend>Pages in this Section</legend>	
        <?php if (exhibit_builder_section_has_pages($exhibitSection) ): ?>
	    <p>To reorder pages, click and drag the page thumbnail to the up or down to the preferred location.</p>
        <?php else: ?>
	    <p>There are no pages in this section.</p>
        <?php endif; ?>
		<ul class="page-list">
		<?php common('page-list', compact('exhibitSection'), 'exhibits'); ?>
		</ul>
	</fieldset>

	<fieldset>
		<p id="exhibit-builder-save-changes"><input type="submit" name="section_form" value="Save Changes" /> or 
		    <input type="submit" name="page_form" id="page_form" value="Add Page" /> or <a href="<?php echo html_escape(uri(array('module'=>'exhibit-builder', 'controller'=>'exhibits', 'action'=>'edit', 'id'=>$exhibitSection->exhibit_id))); ?>">Cancel</a></p>
	</fieldset>
</form>
</div>
<?php foot(); ?>