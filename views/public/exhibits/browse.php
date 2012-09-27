<?php
$title = __('Browse Exhibits');
echo head(array('title'=>$title, 'bodyid' => 'exhibit', 'bodyclass'=>'browse'));
?>
<div id="primary">
    <h1><?php echo $title; ?> <?php echo __('(%s total)', $total_records); ?></h1>
	<?php if (count($exhibits) > 0): ?>
	
	<ul class="navigation" id="secondary-nav">
	    <?php echo nav(array(__('Browse All') => url('exhibits'), __('Browse by Tag') => url('exhibits/tags'))); ?>
    </ul>	
	
    <div class="pagination"><?php echo pagination_links(); ?></div>
	
    <div id="exhibits">	
    <?php $exhibitCount = 0; ?>
    <?php foreach (loop('exhibit') as $exhibit): ?>
    	<?php $exhibitCount++; ?>
    	<div class="exhibit <?php if ($exhibitCount%2==1) echo ' even'; else echo ' odd'; ?>">
    		<h2><?php echo link_to_exhibit(); ?></h2>
    		<div class="description"><?php echo metadata('exhibit', 'description', array('no_escape' => true)); ?></div>
    		<p class="tags"><?php echo tag_string('exhibit', 'exhibits/browse/tag/'); ?></p>
    	</div>
    <?php endforeach; ?>
    </div>
    
    <div class="pagination"><?php echo pagination_links(); ?></div>

    <?php else: ?>
	<p><?php echo __('There are no exhibits available yet.'); ?></p>
	<?php endif; ?>
</div>
<?php echo foot(); ?>
