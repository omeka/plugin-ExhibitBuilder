<?php if ($exhibitSection->Pages): ?>
<?php foreach( $exhibitSection->Pages as $key => $exhibitPage ): ?>
    	<li id="page_<?php echo $exhibitPage->order; ?>">
    		<span class="left">
    			<span class="handle"><img src="<?php echo html_escape(img('arrow_move.gif')); ?>" alt="Move" /></span>
    		    <span class="input"><?php echo text(array('name'=>"Pages[$key][order]",'size'=>2), $exhibitPage->order); ?></span>
    		    <span class="page-title"><?php echo html_escape(snippet($exhibitPage->title, 0, 40, '')); ?></span>
    		</span>
    		<span class="right">
    		    <span class="page-edit"><a href="<?php echo html_escape(uri('exhibits/edit-page-content/'.$exhibitPage->id)); ?>" class="edit">Edit</a></span>
    		    <span class="page-delete"><a href="<?php echo html_escape(uri('exhibits/delete-page/'.$exhibitPage->id)); ?>" class="delete-page">Delete</a></span>
    		</span>
    	</li>
    <?php endforeach; ?>    
<?php endif; ?>