<?php
echo $this->exhibitPageEditTree($exhibit);
echo $this->formHidden('pages-hidden');
echo $this->formHidden('pages-delete-hidden');
?>

<script type="text/javascript">
Omeka.addReadyCallback(Omeka.ExhibitBuilder.enableSorting);
Omeka.addReadyCallback(Omeka.ExhibitBuilder.activateDeleteLinks);
Omeka.addReadyCallback(Omeka.ExhibitBuilder.setUpFormSubmission);
</script>
