<?php
queue_js_file('exhibits-browse');
$title = __('Browse Exhibits') . ' ' . __('(%s total)', $total_results);
echo head(
	array(
		'title'=>$title, 
		'bodyclass'=>'exhibits'
	)
);
echo flash();
echo item_search_filters();
?>
    
<?php if ($total_results): ?> 
	<?php echo pagination_links(); ?>

	<?php if (is_allowed('ExhibitBuilder_Exhibits', 'add')): ?>
	<div class="table-actions">
		<a href="<?php echo html_escape(url('exhibits/add')); ?>" class="add button small green"><?php echo __('Add an Exhibit'); ?></a>
	</div>
	<?php endif; ?>
	<?php echo common('quick-filters', array(), 'exhibits'); ?>

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
				<?php $exhibitImage = record_image($exhibit, 'square_thumbnail');
				if ($exhibitImage):
					echo exhibit_builder_link_to_exhibit($exhibit, $exhibitImage, array('class' => 'image'));
				endif; ?>
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

				<div class="details">
					<?php echo snippet_by_word_count($exhibit->description, 40); ?>
					<p>
						<strong><?php echo __('Use summary page'); ?>:</strong>
						<?php echo ($exhibit->use_summary_page ? __('True') : __('False')); ?>
					</p>
					<p>
						<strong><?php echo __('Pages'); ?>:</strong>
						<?php echo count($exhibit->TopPages); ?>
					</p>
				</div>
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
	<script type="text/javascript">
		Omeka.addReadyCallback(Omeka.ExhibitsBrowse.setupDetails, [
			<?php echo js_escape(__('Details')); ?>,
			<?php echo js_escape(__('Show Details')); ?>,
			<?php echo js_escape(__('Hide Details')); ?>
		]);
	</script>
    
<?php else: ?>
    <?php $total_exhibits = total_records('Exhibit'); ?>
    <?php if ($total_exhibits === 0): ?>
		<div id="no-exhibits">
		<h2><?php echo __('There are no exhibits yet.'); ?></h2>
		
		<?php if (is_allowed('ExhibitBuilder_Exhibits', 'add')): ?>
			<p><?php echo __('Get started by adding your first exhibit.'); ?></p>
			<a href="<?php echo html_escape(url('exhibits/add')); ?>" class="add button big green"><?php echo __('Add an Exhibit'); ?></a>
		<?php endif; ?>
		</div>
    <?php else: ?>
        <p>
            <?php echo __(plural('The query searched 1 exhibit and returned no results.', 'The query searched %s exhibits and returned no results.', $total_exhibits), $total_exhibits); ?>
            <?php echo __('Would you like to') . ' <a href="' . url('exhibits/browse') .'">' . __('reset your search') . '</a>?'; ?>
        </p>
    <?php endif; ?>
<?php endif; ?>
<?php echo foot(); ?>
