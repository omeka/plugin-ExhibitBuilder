<?php
if ($section->title) {
    $sectionTitle = $actionName . ' Section: "' . $section->title . '"';
} else {
    $sectionTitle = $actionName . ' Section';
}
?>
<?php head(array('title'=> html_escape($sectionTitle), 'bodyclass'=>'exhibits')); ?>
<?php echo js('listsort'); ?>
<script type="text/javascript" charset="utf-8">
//<![CDATA[

	var listSorter = {};
	
	Event.observe(window, 'load', Omeka.ExhibitBuilder.wysiwyg);
	
	Event.observe(window, 'load', function() {	
		if(!$('page-list')) return;	
		listSorter.list = $('page-list');
		listSorter.form = $('section-metadata-form');
		listSorter.editUri = <?php echo Zend_Json::encode($_SERVER['REQUEST_URI']); ?>;
		listSorter.partialUri = <?php echo Zend_Json::encode(uri('exhibits/page-list')); ?>;
		listSorter.recordId = <?php echo Zend_Json::encode($section->id); ?>;
		listSorter.tag = 'li';
		listSorter.handle = 'handle';
		listSorter.overlap = 'vertical';
		listSorter.constraint = 'vertical';
		listSorter.confirmation = 'Are you sure you want to delete this page?';
		listSorter.deleteLinks = 'a.delete-page';
								
		if(listSorter.list) {
			//Create the sortable list
			makeSortable(listSorter.list);
		}
	});

//]]>
</script>

<h1><?php echo html_escape($sectionTitle); ?></h1>

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
		
	<div class="field"><?php echo text(array('name'=>'title', 'id'=>'title', 'class'=>'textinput'), $section->title, 'Title for the Section'); ?></div>
		<div class="field"><?php echo text(array('name'=>'slug','id'=>'slug','class'=>'textinput'), $section->slug, 'URL Slug (optional)'); ?></div>
	<div class="field"><?php echo textarea(array('name'=>'description', 'id'=>'description', 'class'=>'textinput','rows'=>'10','cols'=>'40'), $section->description, 'Section Description'); ?></div>	

	</fieldset>
		<fieldset id="section-pages">
			<legend>Pages in this Section</legend>	
	<?php if (exhibit_builder_section_has_pages($section) ): ?>
		<p>To reorder pages, click and drag the page thumbnail to the left or right.</p>
			<ul id="page-list">
			<?php common('page-list', compact('section'), 'exhibits'); ?>

			</ul>
	<?php else: ?>
		<p>There are no pages in this section.</p>
	<?php endif; ?>
	</fieldset>

	<fieldset>
		<p><input type="submit" name="section_form" value="Save Changes" /> or 
		    <input type="submit" name="page_form" id="page_form" value="Add Page" /> or <a href="<?php echo html_escape(uri(array('module'=>'exhibit-builder', 'controller'=>'exhibits', 'action'=>'edit', 'id'=>$section->exhibit_id))); ?>">Cancel</a></p>
	</fieldset>
</form>
</div>
<?php foot(); ?>