<?php if (!$formActionUri): ?>
    <?php $formActionUri = uri(array('controller'=>'search', 'action'=>'results')); ?>
<?php endif; ?>
	
<form <?php echo _tag_attributes($formAttributes); ?> action="<?php echo $formActionUri; ?>" method="get">
	<div id="search-keywords" class="field">    
		<?php echo label('search','Search for Keywords'); ?>
		<div class="inputs">
		    <?php echo text(array('name'=>'search','size' => '40','id'=>'search','class'=>'textinput'),$_REQUEST['search']); ?>
		</div>
	</div>
	
	<div class="field">
	    <?php echo label('tags', 'Search By Tags'); ?>
	    <div class="inputs">
	        <?php echo text(array('name'=>'tags','size' => '40','id'=>'tag-search','class'=>'textinput'),$_REQUEST['tags']); ?>
	    </div>
	</div>
	
	<?php echo hidden(array('name'=>'model', 'id'=>'model'), 'Exhibit,ExhibitPage'); ?>
    
	<?php is_admin_theme() ? fire_plugin_hook('admin_append_to_advanced_search') : fire_plugin_hook('public_append_to_advanced_search'); ?>
	<input type="submit" class="submit submit-medium" name="submit_search" id="submit_search" value="Search" />
</form>