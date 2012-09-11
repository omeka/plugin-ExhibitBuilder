<?php
$title = __('Browse Exhibits by Tag');
head(array('title' => $title, 'bodyid' => 'exhibit', 'bodyclass' => 'tags'));
?>
<div id="primary">
<h1><?php echo $title; ?></h1>
<ul class="navigation exhibit-tags" id="secondary-nav">
    <?php echo nav(array(
        __('Browse All') => url('exhibits/browse'),
        __('Browse by Tag') => url('exhibits/tags')
    )); ?>
</ul>

<?php echo tag_cloud($tags,uri('exhibits/browse')); ?>
</div>
<?php foot(); ?>
