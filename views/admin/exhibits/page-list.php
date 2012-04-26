<?php if ($exhibitSection->Pages): ?>
<?php foreach( $exhibitSection->Pages as $key => $exhibitPage ): ?>
        <li id="page_<?php echo html_escape($exhibitPage->id); ?>" class="exhibit-page-item">
            <div class="page-info">
                <span class="left">
                    <span class="handle"><img src="<?php echo html_escape(img('silk-icons/page_go.png')); ?>" alt="Move" /></span>
                    <span class="input">
                        <?php
                            if (isset($fromExhibitPage)):
                                $exhibitSectionId = $exhibitSection->id;
                                $exhibitPageId = $exhibitPage->id;
                                echo $this->formText("Pages[$exhibitSectionId][$exhibitPageId][order]", $exhibitPage->order, array('size'=>2, 'id' => 'page-' . $exhibitPage->id . '-order')  );

                            else:
                                echo $this->formText("Pages[$key][order]", $exhibitPage->order, array('size'=>2, 'id' => 'page-' . $exhibitPage->id . '-order')  );
                            endif;
                        ?></span>
                    <span class="page-title"><?php echo html_escape(snippet($exhibitPage->title, 0, 40, '')); ?></span>
                </span>
                <span class="right">
                    <span class="page-edit"><a href="<?php echo html_escape(uri('exhibits/edit-page-content/'.$exhibitPage->id)); ?>" class="edit"><?php echo __('Edit'); ?></a></span>
                    <span class="page-delete"><a href="<?php echo html_escape(uri('exhibits/delete-page/'.$exhibitPage->id)); ?>" class="delete-page"><?php echo __('Delete'); ?></a></span>
                </span>
            </div>
        </li>
    <?php endforeach; ?>
<?php endif; ?>
