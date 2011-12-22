<?php
if ($exhibitSection->title) {
    $exhibitSectionTitle = __('Edit Section: "%s"', $exhibitSection->title);
} else {
    $exhibitSectionTitle = ($actionName == 'Add') ? __('Add Section') : __('Edit Section');
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
        var pageListDeleteConfirmationText = <?php echo js_escape(__('Are you sure you want to delete this page?')); ?>;
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
        <a href="<?php echo html_escape(uri('exhibits')); ?>"><?php echo __('Exhibits'); ?></a> &gt;
        <a href="<?php echo html_escape(uri('exhibits/edit/' . $exhibit['id']));?>"><?php echo html_escape($exhibit['title']); ?></a> &gt;
        <?php echo html_escape($exhibitSectionTitle); ?>
	</div>

<?php 
	echo flash();
?>

<form method="post" accept-charset="utf-8" action="" id="section-metadata-form" class="exhibit-builder">
			
	<fieldset>
		<legend><?php echo __('Section Metadata'); ?></legend>
	    <div class="field"><?php echo text(array('name'=>'title', 'id'=>'title', 'class'=>'textinput'), $exhibitSection->title, __('Title')); ?></div>
        <div class="field">
            <?php echo text(array('name'=>'slug','id'=>'slug','class'=>'textinput'), $exhibitSection->slug, __('Slug')); ?>
            <p class="explanation"><?php echo __('No spaces or special characters allowed.'); ?></p>
        </div>
    	<div class="field"><?php echo textarea(array('name'=>'description', 'id'=>'description', 'class'=>'textinput','rows'=>'10','cols'=>'40'), $exhibitSection->description, __('Description')); ?></div>
	</fieldset>
	<fieldset id="section-pages">
		<legend><?php echo __('Pages in this Section'); ?></legend>
        <?php if (exhibit_builder_section_has_pages($exhibitSection) ): ?>
        <p><?php echo __('To reorder pages, click and drag the page thumbnail up or down to the preferred location.'); ?></p>
        <?php else: ?>
	    <p><?php echo __('There are no pages in this section.'); ?></p>
        <?php endif; ?>
		<ul class="page-list">
		<?php common('page-list', compact('exhibitSection'), 'exhibits'); ?>
		</ul>
	</fieldset>

	<fieldset>
		<p id="exhibit-builder-save-changes"><input type="submit" name="section_form" value="<?php echo __('Save Changes'); ?>" /> <?php echo __('or'); ?> 
		    <input type="submit" name="page_form" id="page_form" value="<?php echo __('Add Page'); ?>" /> <?php echo __('or'); ?> <a href="<?php echo html_escape(uri(array('module'=>'exhibit-builder', 'controller'=>'exhibits', 'action'=>'edit', 'id'=>$exhibitSection->exhibit_id))); ?>"><?php echo __('Cancel'); ?></a></p>
	</fieldset>
</form>
</div>
<?php foot(); ?>
