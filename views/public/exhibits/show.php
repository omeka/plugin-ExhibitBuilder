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
    <?php if ($prevLink = exhibit_builder_link_to_previous_page()): ?>
    <div id="exhibit-nav-prev">
    <?php echo $prevLink; ?>
    </div>
    <?php endif; ?>
    <?php if ($nextLink = exhibit_builder_link_to_next_page()): ?>
    <div id="exhibit-nav-next">
    <?php echo $nextLink; ?>
    </div>
    <?php endif; ?>
    <?php if ($upLink = exhibit_builder_link_to_parent_page()): ?>
    <div id="exhibit-nav-up">
    <?php echo $upLink; ?>
    </div>
    <?php endif; ?>
</div>
<?php echo foot(); ?>
