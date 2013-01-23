<?php
echo head(array(
    'title' => metadata('exhibit_page', 'title') . ' &middot; ' . metadata('exhibit', 'title'),
    'bodyid' => 'exhibit',
    'bodyclass' => 'show'));
?>
<h1><span class="exhibit-name"><?php echo link_to_exhibit(); ?></span>: <span class="exhibit-page"><?php echo metadata('exhibit_page', 'title'); ?></span></h2>


<nav id="exhibit-pages">
    <?php echo exhibit_builder_page_nav();?>
</nav>

<?php exhibit_builder_render_exhibit_page(); ?>

<?php if (exhibit_builder_link_to_previous_page() || exhibit_builder_link_to_parent_page() || exhibit_builder_link_to_next_page()): ?>
<div id="exhibit-page-navigation">
    <?php echo exhibit_builder_link_to_previous_page(); ?>
    <?php echo exhibit_builder_link_to_parent_page(); ?>
    <?php echo exhibit_builder_link_to_next_page(); ?>
</div>
<?php endif; ?>

<?php echo foot(); ?>
