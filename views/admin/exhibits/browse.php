<?php head(array('title'=>'Browse Exhibits', 'bodyclass'=>'exhibits')); ?>
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

<h1>Browse Exhibits (<?php echo $total_records; ?> total)</h1>
<p id="add-exhibit" class="add-button"><a class="add" href="<?php echo html_escape(uri('exhibits/add')); ?>">Add Exhibit</a></p>
<div id="primary">
    
<?php if (!count($exhibits)): ?> 
    <div id="no-exhibits">
    <p>There are no exhibits yet.
    
    <?php if (has_permission('ExhibitBuilder_Exhibits','add')): ?>
          Why don't you <a href="<?php echo html_escape( uri('exhibits/add')); ?>">add an exhibit</a>?</p>
    <?php endif; ?>
    </div>
    
<?php else: //Show the exhibits in a table?>

<div class="pagination"><?php echo pagination_links(); ?></div>

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
        <?php if (has_permission('ExhibitBuilder_Exhibits','browse')): ?>
        <th>Edit?</th>
        <?php endif; ?>
        <?php if (has_permission('ExhibitBuilder_Exhibits','deleteAll')): ?>
        <th>Delete?</th>
        <?php endif; ?>
    </tr>
    </thead>
    <tbody>
        
<?php foreach($exhibits as $key=>$exhibit): ?>
    <tr class="exhibit <?php if($key%2==1) echo ' even'; else echo ' odd'; ?>">
        <td><?php echo html_escape($exhibit->id);?></td>
        <td><?php echo html_escape( $exhibit->title); ?></td>
        <td><?php echo tag_string($exhibit, uri('exhibits/browse/tag/')); ?></td>
        <td><?php if ($exhibit->theme==null) echo 'Current Public Theme'; else echo html_escape($exhibit->theme);?></td>
        <td><?php echo '<a href="' . html_escape(exhibit_builder_exhibit_uri($exhibit)). '">[Preview]</a>'; ?></td>
        <td>
        <?php if (exhibit_builder_user_can_edit($exhibit)): ?>
        <?php echo link_to($exhibit, 'edit', 'Edit', array('class'=>'edit')); ?>
        <?php endif; ?>
        </td>
        <?php if (has_permission('ExhibitBuilder_Exhibits','deleteAll')): ?>
        <td><?php echo link_to($exhibit, 'delete', 'Delete', array('class'=>'delete')) ?></td>
        <?php endif; ?>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="pagination"><?php echo pagination_links(); ?></div>
<?php endif; ?>

</div>
<?php foot(); ?>