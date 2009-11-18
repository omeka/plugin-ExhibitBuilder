<?php head(array('title'=> 'Browse Exhibits by Tag','bodyid'=>'exhibit','bodyclass'=>'tags')); ?>
<div id="primary">
<h1>Browse by Tag</h1>
<ul class="navigation exhibit-tags" id="secondary-nav">
	<?php echo nav(array('Browse All' => uri('exhibits/browse'), 'Browse by Tag' => uri('exhibits/tags'))); ?>
</ul>

<?php echo tag_cloud($tags,uri('exhibits/browse')); ?>
</div>
<?php foot(); ?>