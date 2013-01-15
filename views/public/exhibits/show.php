<?php
echo head(array(
    'title' => metadata('exhibit_page', 'title') . ' &middot; ' . metadata('exhibit', 'title'),
    'bodyid' => 'exhibit',
    'bodyclass' => 'show'));
?>
<h1><?php echo link_to_exhibit(); ?></h1>
<div id="nav-container">
    <?php echo exhibit_builder_page_nav();?>
</div>

<h2><?php echo metadata('exhibit_page', 'title'); ?></h2>

<?php exhibit_builder_render_exhibit_page(); ?>

<div id="exhibit-page-navigation">
    <?php echo exhibit_builder_link_to_previous_page(); ?>
    <?php echo exhibit_builder_link_to_parent_page(); ?>
    <?php echo exhibit_builder_link_to_next_page(); ?>
</div>
<?php echo foot(); ?>
