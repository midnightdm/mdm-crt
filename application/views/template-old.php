<!DOCTYPE html>
<html>
<head>
  <title>Clinton River Traffic <?php echo $title; ?></title>
  <link rel="stylesheet" href="https://<?php echo base_url();?>css/header.css" type="text/css">
  <link rel="stylesheet" href="https://<?php echo base_url();echo $main['css'];?>" type="text/css">
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