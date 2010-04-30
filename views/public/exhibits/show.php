<?php head(array('title' => html_escape($exhibit->title),'bodyid'=>'exhibit','bodyclass'=>'show')); ?>
<div id="primary">
	<h2><?php echo exhibit_builder_link_to_exhibit($exhibit); ?></h2>

    <div id="nav-container">
    	<?php echo exhibit_builder_section_nav();?>
    	<?php echo exhibit_builder_page_nav();?>
    </div>

	<?php echo flash(); ?>
	<?php if($pageTitle = $page->title): ?>
	<h3><?php echo $pageTitle; ?></h3>
	<?php endif; ?>
	
	<?php exhibit_builder_render_exhibit_page(); ?>
	
	<div id="exhibit-page-navigation">
	   	<?php echo exhibit_builder_link_to_previous_exhibit_page(); ?>
    	<?php echo exhibit_builder_link_to_next_exhibit_page(); ?>
	</div>
</div>	
<?php foot(); ?>