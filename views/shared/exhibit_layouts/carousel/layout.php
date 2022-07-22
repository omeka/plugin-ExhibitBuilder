<?php
$configs = array('carousel' => array());

$configs['file-size'] = isset($options['file-size'])
    ? html_escape($options['file-size'])
    : 'thumbnail';
$configs['show-title'] = isset($options['show-title'])
    ? html_escape($options['show-title'])
    : 0;
$fade = isset($options['fade'])
    ? html_escape($options['fade'])
    : 0;
$overlay = (isset($options['float-caption']) && ($options['float-caption'] == 1))
    ? 'text-overlay'
    : 'text-float';
$perSlide = isset($options['per-slide'])
    ? html_escape($options['per-slide'])
    : 1;
$stretchImage = isset($options['stretch-image'])
    ? html_escape($options['stretch-image'])
    : 'none';
$captionPosition = isset($options['caption-position'])
    ? html_escape($options['caption-position'])
    : 'center';
$scrollingSpeed = isset($options['speed'])
    ? html_escape($options['speed'])
    : 400;
$autoSlide = isset($options['auto-slide'])
    ? (int) html_escape($options['auto-slide'])
    : 0;
$loop = isset($options['loop'])
    ? html_escape($options['loop'])
    : 0;
$carouselTitle = isset($options['carousel-title'])
    ? html_escape($options['carousel-title'])
    : '';

// jCarousel configs
if (is_numeric($scrollingSpeed)) {
    $configs['carousel']['animation'] = (int) $scrollingSpeed;
} else {
    // Fade only works with numeric scrolling speed values
    $configs['carousel']['animation'] = ($fade == true) ? 400 : $scrollingSpeed;
}

if ($autoSlide > 0) {
    $configs['autoscroll'] = array();
    $configs['autoscroll']['interval'] = $autoSlide;
    if ($fade == true) { 
        $configs['autoscroll']['method'] = 'fade';
    }
}
if ($loop == true) {
    $configs['carousel']['wrap'] = 'circular';    
}
$configs['carousel']['transitions'] = 1;
?>

<?php if ($carouselTitle): ?>
<h2><?php echo $carouselTitle; ?></h2>
<?php endif; ?>
<div class="jcarousel-wrapper captions-<?php echo $captionPosition; ?> <?php echo $overlay; ?>"
     data-jcarousel-perslide="<?php echo $perSlide ?>"
     data-jcarousel-stretch="<?php echo $stretchImage ?>"
     data-jcarousel-fade="<?php echo $fade ?>">
    <?php echo $this->exhibitAttachmentCarousel($attachments, $configs); ?>
</div>

<script type='text/javascript'>
jQuery(function() {
    var carouselConfig = <?php echo json_encode($configs['carousel']);?>;
    var carousel = jQuery('.jcarousel').jcarousel(carouselConfig);
    
    <?php if(isset($configs['autoscroll'])): ?>
    var autoscrollConfig = <?php echo json_encode($configs['autoscroll']);?>;
    carousel.jcarouselAutoscroll(autoscrollConfig);
    <?php endif; ?>
});
</script>
