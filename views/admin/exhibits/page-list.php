<?php if ($section->Pages): ?>
<?php foreach( $section->Pages as $key => $page ): ?>
    	<li id="page_<?php echo $page->order; ?>">
    		<span class="left">
    			<span class="handle"><img src="<?php echo html_escape(img('arrow_move.gif')); ?>" alt="Move" /></span>
    		    <span class="input"><?php echo text(array('name'=>"Pages[$key][order]",'size'=>2), $page->order); ?></span>
    		    <span class="page-title"><?php echo html_escape(snippet($page->title, 0, 40, '')); ?></span>
    		</span>
    		<span class="right">
    		    <span class="page-edit"><a href="<?php echo html_escape(uri('exhibits/edit-page-content/'.$page->id)); ?>" class="edit">Edit</a></span>
    		    <span class="page-delete"><a href="<?php echo html_escape(uri('exhibits/delete-page/'.$page->id)); ?>" class="delete">Delete</a></span>
    		</span>
    	</li>
    <?php endforeach; ?>    
<?php endif; ?>