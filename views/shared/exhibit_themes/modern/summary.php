<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo settings('site_title'); ?></title>

<!-- Meta -->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<!-- Stylesheets -->
<link rel="stylesheet" media="screen" href="<?php echo exhibit_css('screen'); ?>" />
<link rel="stylesheet" media="print" href="<?php echo css('print'); ?>" />

<!-- JavaScripts -->
<?php echo js('prototype'); ?>

<!-- Plugin Stuff -->
<?php plugin_header(); ?>

</head>
<body<?php echo $bodyclass ? ' class="'.$bodyclass.'"' : ''; ?>>
	<div id="wrap">
	<h5><a href="<?php echo uri('exhibits'); ?>">Back to Exhibits</a></h5>
		
		<div id="content">
		<h1><?php echo link_to_exhibit($exhibit); ?></h1>

		<p><?php echo $exhibit->description; ?></p>

		<div id="exhibit-sections">	
			<?php foreach($exhibit->Sections as $section): ?>
			<h3><a href="<?php echo exhibit_uri($exhibit, $section); ?>"><?php echo htmlentities($section->title); ?></a></h3>
			<?php echo $section->description; ?>
			<?php endforeach; ?>
		</div>

		<div id="exhibit-credits">	
			<h3>Credits</h3>
			<p><?php echo htmlentities($exhibit->credits); ?></p>
		</div>

<?php exhibit_foot(); ?>