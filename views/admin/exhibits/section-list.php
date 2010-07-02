<?php if ($exhibit->Sections): ?>
    <?php foreach($exhibit->Sections as $key => $exhibitSection ): ?>
    	<li id="section_<?php echo html_escape($exhibitSection->id); ?>" class="exhibit-section-item">
    		<div class="section-info">
        		<span class="left">
        			<span class="handle"><img src="<?php echo html_escape(img('arrow_move.gif')); ?>" alt="Move" /></span>
        		    <span class="input"><?php echo text(array('name'=>"Sections[$key][order]",'size'=>2,'class'=>'order-input', 'id' => 'section-' . $exhibitSection->id . '-order'), $exhibitSection->order); ?></span>
        		    <span class="section-title"><?php echo html_escape(snippet($exhibitSection->title, 0, 40, '')); ?></span>
        		</span>
        		<span class="right">
        		    <span class="section-edit"><a href="<?php echo html_escape(uri('exhibits/edit-section/'.$exhibitSection->id)); ?>" class="edit">Edit</a></span>
        		    <span class="section-delete"><a href="<?php echo html_escape(uri('exhibits/delete-section/'.$exhibitSection->id)); ?>" class="delete-section">Delete</a></span>
        		</span>
    		</div>
  			<ul class="page-list">
			<?php if (exhibit_builder_section_has_pages($exhibitSection)): ?>
			    <?php 
			        $fromExhibitPage = true;
			        common('page-list', compact('exhibitSection', 'fromExhibitPage'), 'exhibits'); 
			    ?>
        	<?php endif; ?>
			</ul>
    	</li>
    <?php endforeach; ?>    
<?php endif; ?>