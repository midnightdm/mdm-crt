<!DOCTYPE html>
<html>
<head>
  <title>Clinton River Traffic <?php echo $title; ?></title>
  <title>Clinton River Traffic</title>  
  <meta name="description" content= "river fans barge tow spotters Clinton, Iowa Displays MMSI transponder data on a map Get text alert for approaching boats" />
<meta name="robots" content= "index, follow">
  <link rel="stylesheet" href="<?php echo $main['path'];?>css/header.css" type="text/css">
  <link rel="stylesheet" href="<?php echo $main['path'].$main['css'];?>" type="text/css">
</head>
<body>
  <div id="wrapper">
    <div id="header">
      <?php $this->load->view('header'); ?>
    </div>
    <div id="main">
      <?php $this->load->view($main['view']); ?>
    </div>
    <div id="footer">
      <?php $this->load->view('footer'); ?>
    </div>
  </div>  
</body>
</html>