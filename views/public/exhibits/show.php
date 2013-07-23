<?php
foreach ($layout_styles as $css_url):
    queue_css_url($css_url);
endforeach;
echo head(array(
    'title' => metadata('exhibit_page', 'title') . ' &middot; ' . metadata('exhibit', 'title'),
    'bodyclass' => 'exhibits show'));
?>

<nav id="exhibit-pages">
    <?php echo exhibit_builder_page_nav(); ?>
</nav>

<h1><span class="exhibit-page"><?php echo metadata('exhibit_page', 'title'); ?></h1>

<nav id="exhibit-child-pages">
    <?php echo exhibit_builder_child_page_nav(); ?>
</nav>

<?php foreach ($blocks as $block): ?>
    <?php $layout = $block->getLayout(); ?>
    <div class="exhibit-block layout-<?php echo html_escape($layout->id); ?>">
    <?php
    $script = $layout->getViewPartial();
    echo $this->partial($script, array(
        'options' => $block->getOptions(),
        'text' => $block->text,
        'attachments' => array_key_exists($block->id, $attachments) ? $attachments[$block->id] : array()
    ));
    ?>
    </div>
<?php endforeach; ?>

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
    <div id="exhibit-nav-up">
    <?php echo exhibit_builder_page_trail(); ?>
    </div>
</div>

<?php echo foot(); ?>
