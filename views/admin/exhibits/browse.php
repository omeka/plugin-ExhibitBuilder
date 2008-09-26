<?php head(array('title'=>'Browse Exhibits', 'body_class'=>'exhibits')); ?>
<script type="text/javascript" charset="utf-8">
    Event.observe(window, 'load', function(){
        $$('.delete-exhibit').invoke('observe', 'click', function(){
            return confirm('Are you sure you want to delete this exhibit?');
        });
    });
</script>

<h1>Exhibits</h1>
<div id="primary">
	<a href="<?php echo uri('exhibits/add'); ?>" id="add-exhibit" class="add-exhibit">Add an Exhibit</a>
	
<?php if(!count($exhibits)): ?>	
	<div id="no-exhibits">
	<p>There are no exhibits yet.
	
	<?php if(has_permission('Exhibits','add')): ?>
		  Why don't you <a href="<?php echo uri('exhibits/add'); ?>">add some</a>?</p>
	<?php endif; ?>
	</div>
	
<?php else: //Show the exhibits in a table?>	

<table id="exhibits">
	<col id="col-id" />
	<col id="col-title" />
	<col id="col-tags" />
	<col id="col-edit" />
	<col id="col-delete" />
	<thead>
	<tr>
		<th>ID</th>
		<th>Title</th>
		<th>Tags</th>
		<th>Theme</th>
		<?php if(has_permission('Exhibits','edit')){ ?>
		
		<th>Edit?</th>
		<?php } if(has_permission('Exhibits','delete')){ ?>
		<th>Delete?</th>
		<?php } ?>
	</tr>
	</thead>
	<tbody>
		
<?php foreach( $exhibits as $key=>$exhibit ): ?>
	<tr class="exhibit <?php if($key%2==1) echo ' even'; else echo ' odd'; ?>">
		<td><?php echo h($exhibit->id);?></td>
		<td><?php echo link_to_exhibit($exhibit); ?></td>
		<td><?php echo tag_string($exhibit, uri('exhibits/browse/tag/')); ?></td>
		<td><?php echo h($exhibit->theme); ?></td>
		<td><?php echo link_to($exhibit, 'edit', '[Edit]', array('class'=>'edit-exhibit')); ?></td>
		<td><?php echo link_to($exhibit, 'delete', '[Delete]', array('class'=>'delete-exhibit')) ?></td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php endif; ?>

</div>
<?php foot(); ?>