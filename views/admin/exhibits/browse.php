<?php
$title = __('Browse Exhibits') . ' ' . __('(%s total)', $total_results);
echo head(array('title'=>$title, 'bodyclass'=>'exhibits'));
?>
    
<?php if (!count($exhibits)): ?> 
    <div id="no-exhibits">
    <h2><?php echo __('There are no exhibits yet.'); ?></h2>
    
    <?php if (is_allowed('ExhibitBuilder_Exhibits','add')): ?>
        <a href="<?php echo html_escape(url('exhibits/add')); ?>" class="big green add button"><?php echo __('Add an Exhibit'); ?></a></p>
    <?php endif; ?>
    </div>
    
<?php else: ?>

<?php if (is_allowed('ExhibitBuilder_Exhibits', 'add')): ?>
<div class="table-actions">
    <a href="<?php echo html_escape(url('exhibits/add')); ?>" class="small green add button"><?php echo __('Add an Exhibit'); ?></a>
</div>
<?php endif; ?>

<?php echo pagination_links(); ?>

<table id="exhibits" class="full">
    <thead>
    <tr>
        <?php
        $browseHeadings[__('Title')] = 'title';
        $browseHeadings[__('Tags')] = null;
        $browseHeadings[__('Theme')] = null;
        $browseHeadings[__('Date Added')] = 'added';
        echo browse_sort_links($browseHeadings, array('link_tag' => 'th scope="col"', 'list_tag' => '')); ?>
    </tr>
    </thead>
    <tbody>
        
<?php foreach($exhibits as $key=>$exhibit): ?>
    <tr class="exhibit<?php if ($key % 2 == 1) echo ' even'; else echo ' odd'; ?>">
        <td class="exhibit-info<?php if ($exhibit->featured) echo ' featured'; ?>">
            <?php if ($exhibitImage = record_image($exhibit, 'square_thumbnail')): ?>
                <?php echo exhibit_builder_link_to_exhibit($exhibit, $exhibitImage, array('class' => 'image')); ?>
            <?php endif; ?>
            <span>
            <a href="<?php echo html_escape(exhibit_builder_exhibit_uri($exhibit)); ?>"><?php echo metadata($exhibit, 'title'); ?></a>
            <?php if(!$exhibit->public): ?>
                <?php echo __('(Private)'); ?>
            <?php endif; ?>
            </span>
            <ul class="action-links group">
                <?php if (is_allowed($exhibit, 'edit')): ?>
                <li><?php echo link_to($exhibit, 'edit', __('Edit'), array('class'=>'edit')); ?></li>
                <?php endif; ?>
                <?php if (is_allowed($exhibit, 'delete')): ?>
                <li><?php echo link_to($exhibit, 'delete-confirm', __('Delete'), array('class' => 'delete-confirm')) ?></li>
                <?php endif; ?>
            </ul>
        </td>
        <td><?php echo tag_string($exhibit, 'exhibits'); ?></td>
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
<?php echo pagination_links(); ?>
<?php endif; ?>
<?php echo foot(); ?>
