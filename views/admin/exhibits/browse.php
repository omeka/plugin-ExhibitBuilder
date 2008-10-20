<?php head(array('title'=>'Browse Exhibits', 'body_class'=>'exhibits')); ?>
<script type="text/javascript" charset="utf-8">
    Event.observe(window, 'load', function(){
        $$('.delete-exhibit').invoke('observe', 'click', function(e){
            if (confirm('Are you sure you want to delete this exhibit?')) {
                return;
            } else {
                e.stop();
            }
        });
    });
</script>

<h1>Exhibits</h1>
<p id="add-exhibit" class="add-button"><a class="add" href="<?php echo uri('exhibits/add'); ?>">Add Exhibit</a></p>
<div id="primary">
	
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
	<col id="col-preview">
	<col id="col-edit" />
	<col id="col-delete" />
	<thead>
	<tr>
		<th>ID</th>
		<th>Title</th>
		<th>Tags</th>
		<th>Theme</th>
		<th>Preview</th>
		<?php if(has_permission('Exhibits','edit')){ ?>
		
		<th>Edit?</th>
		<?php } if(has_permission('Exhibits','delete')){ ?>
		<th>Delete?</th>
		<?php } ?>
	</tr>
	</thead>
	<tbody>
		
<?php foreach($exhibits as $key=>$exhibit): ?>
	<tr class="exhibit <?php if($key%2==1) echo ' even'; else echo ' odd'; ?>">
		<td><?php echo $exhibit->id;?></td>
		<td><?php echo $exhibit->title; ?></td>
		<td><?php echo tag_string($exhibit, uri('exhibits/browse/tag/')); ?></td>
		<td><?php if($exhibit->theme==null) echo 'Current Public Theme'; else echo $exhibit->theme;?></td>
		<td><?php echo '<a href="' .exhibit_uri($exhibit). '">[Preview]</a>'; ?></td>
		<td><?php echo link_to($exhibit, 'edit', '[Edit]', array('class'=>'edit-exhibit')); ?></td>
		<td><?php echo link_to($exhibit, 'delete', '[Delete]', array('class'=>'delete-exhibit')) ?></td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php endif; ?>

</div>
<?php foot(); ?>