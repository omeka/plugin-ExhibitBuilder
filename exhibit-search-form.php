<?php if (!$formActionUri): ?>
    <?php $formActionUri = uri('search/results'); ?>
<?php endif; ?>
	
<form <?php echo _tag_attributes($formAttributes); ?> action="<?php echo html_escape($formActionUri); ?>" method="get">
	<div id="search-keywords" class="field">    
		<?php echo label('search','Search for Keywords'); ?>
		<div class="inputs">
		    <?php echo text(array('name'=>'search','size' => '40','id'=>'search','class'=>'textinput'),$_REQUEST['search']); ?>
		</div>
	</div>
	
	<div class="field">
	    <?php echo label('exhibit_id', 'Search By Exhibit'); ?>
	    <div class="inputs">
	        <?php echo exhibit_builder_select_exhibit(array('name'=>'exhibit_id','id'=>'exhibit-search'), $_REQUEST['tags'], null); ?>
	    </div>
	</div>
	
	<div class="field">
	    <?php echo label('tags', 'Search By Tags'); ?>
	    <div class="inputs">
	        <?php echo text(array('name'=>'tags','size' => '40','id'=>'tag-search','class'=>'textinput'),$_REQUEST['tags']); ?>
	    </div>
	</div>
	
	<?php echo hidden(array('name'=>'model', 'id'=>'model'), 'Exhibit,ExhibitSection,ExhibitPage'); ?>
    
	<?php is_admin_theme() ? fire_plugin_hook('admin_append_to_advanced_search') : fire_plugin_hook('public_append_to_advanced_search'); ?>
	<input type="submit" class="submit submit-medium" name="submit_search" id="submit_search" value="Search" />
</form>