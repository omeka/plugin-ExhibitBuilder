<?php
$title = __('Browse Exhibits') . ' ' . __('(%s total)', $total_records);
head(array('title'=>$title, 'bodyclass'=>'exhibits'));
?>

<?php if (has_permission('ExhibitBuilder_Exhibits','add')): ?>
    <a class="add-exhibit button small green" href="<?php echo html_escape(uri('exhibits/add')); ?>"><?php echo __('Add Exhibit'); ?></a>
<?php endif; ?>
    
<?php if (!count($exhibits)): ?> 
    <div id="no-exhibits">
    <p><?php echo __('There are no exhibits yet.'); ?></p>
    
    <?php if (has_permission('ExhibitBuilder_Exhibits','add')): ?>
        <a href="<?php echo html_escape( uri('exhibits/add')); ?>"><?php echo __('Add an exhibit.'); ?></a></p>
    <?php endif; ?>
    </div>
    
<?php else: ?>

<div class="pagination"><?php echo pagination_links(); ?></div>

<table id="exhibits" class="full">
    <thead>
    <tr>
        <?php
        $browseHeadings[__('Title')] = 'title';
        $browseHeadings[__('Tags')] = null;
        $browseHeadings[__('Theme')] = null;
        $browseHeadings[__('Date Added')] = 'added';
        echo browse_headings($browseHeadings); ?>
    </tr>
    </thead>
    <tbody>
        
<?php foreach($exhibits as $key=>$exhibit): ?>
    <tr class="exhibit<?php if ($key % 2 == 1) echo ' even'; else echo ' odd'; ?>">
        <td class="exhibit-info<?php if ($exhibit->featured) echo ' featured'; ?>">
            <span>
            <a href="<?php echo html_escape(exhibit_builder_exhibit_uri($exhibit)); ?>"><?php echo metadata($exhibit, 'title'); ?></a>
            <?php if(!$exhibit->public): ?>
                <?php echo __('(Private)'); ?>
            <?php endif; ?>
            </span>
            <ul class="action-links group">
                <?php if (exhibit_builder_user_can_edit($exhibit)): ?>
                <li><?php echo link_to($exhibit, 'edit', __('Edit'), array('class'=>'edit')); ?></li>
                <?php endif; ?>
                <?php if (exhibit_builder_user_can_delete($exhibit)): ?>
                <li><?php echo link_to($exhibit, 'delete-confirm', __('Delete'), array('class' => 'delete-confirm')) ?></li>
                <?php endif; ?>
            </ul>
        </td>
        <td><?php echo tag_string($exhibit, uri('exhibits/browse/tag/')); ?></td>
        <?php
        if ($exhibit->theme==null) {
            $themeName = __('Current Public Theme');
        } else {
            $theme = Theme::getTheme($exhibit->theme);
            $themeName = !empty($theme->title) ? $theme->title : $exhibit->theme;
        }
        ?>
        <td><?php echo html_escape($themeName);?></td>
        <td><?php echo format_date(metadata($exhibit, 'added')); ?></td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="pagination"><?php echo pagination_links(); ?></div>
<?php endif; ?>
<?php foot(); ?>
