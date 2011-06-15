<?php head(array('title'=>'Browse Exhibits', 'bodyclass'=>'exhibits')); ?>
<h1>Browse Exhibits (<?php echo $total_records; ?> total)</h1>
<?php if (has_permission('ExhibitBuilder_Exhibits','add')): ?>
    <p id="add-exhibit" class="add-button"><a class="add" href="<?php echo html_escape(uri('exhibits/add')); ?>">Add Exhibit</a></p>
<?php endif; ?>
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
        <th>Title</th>
        <th>Tags</th>
        <th>Theme</th>
        <th>Public</th>
        <th>Featured</th>
        <th>Edit?</th>
        <th>Delete?</th>
    </tr>
    </thead>
    <tbody>
        
<?php foreach($exhibits as $key=>$exhibit): ?>
    <tr class="exhibit <?php if($key%2==1) echo ' even'; else echo ' odd'; ?>">
        <td><a href="<?php echo html_escape(exhibit_builder_exhibit_uri($exhibit)); ?>"><?php echo html_escape($exhibit->title); ?></a></td>
        <td><?php echo tag_string($exhibit, uri('exhibits/browse/tag/')); ?></td>
        <?php
        if ($exhibit->theme==null) {
            $themeName = 'Current Public Theme';
        } else {
            $theme = Theme::getAvailable($exhibit->theme);
            $themeName = !empty($theme->title) ? $theme->title : $exhibit->theme;
        }
        ?>
        <td><?php echo html_escape($themeName);?></td>
        <td>
        <?php if($exhibit->public): ?>
            <img src="<?php echo img('silk-icons/tick.png'); ?>" alt="Public"/>
        <?php endif; ?>
        </td>
        <td>
        <?php if($exhibit->featured): ?>
            <img src="<?php echo img('silk-icons/star.png'); ?>" alt="Featured"/>
        <?php endif; ?>
        </td>
        <td>
        <?php if (exhibit_builder_user_can_edit($exhibit)): ?>
        <?php echo link_to($exhibit, 'edit', 'Edit', array('class'=>'edit')); ?>
        <?php endif; ?>
        </td>
        <td>
        <?php if (exhibit_builder_user_can_delete($exhibit)): ?>
        <?php echo delete_button($exhibit, "delete-exhibit-{$exhibit->id}", 'Delete') ?>
        <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="pagination"><?php echo pagination_links(); ?></div>
<?php endif; ?>

</div>
<?php foot(); ?>
