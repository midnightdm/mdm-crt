<!DOCTYPE html>
<html>
<head>
  <title>Clinton River Traffic <?php echo $title; ?></title>
  <title>Clinton River Traffic</title>  
  <meta name="description" content= "river fans barge tow spotters riverboats paddlewheelers steamboats Clinton Iowa MMSI transponder data map text alerts" />
<meta name="robots" content= "index, follow">
  <link rel="stylesheet" href="<?php echo getEnv('BASE_URL');?>css/header2.css" type="text/css">
  <link rel="stylesheet" href="<?php echo getEnv('BASE_URL').$main['css'];?>" type="text/css">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:wght@800&family=Rubik:wght@700&display=swap" rel="stylesheet">     
</head>
<body>
  <div id="wrapper">
    <div id="header">
      <?php $this->load->view('header2'); ?>
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