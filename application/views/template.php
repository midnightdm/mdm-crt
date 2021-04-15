<!DOCTYPE html>
<html>
<head>
  <title>Clinton River Traffic <?php echo $title; ?></title>
  <title>Clinton River Traffic</title>  
  <meta name="description" content= "river fans barge tow spotters riverboats paddlewheelers steamboats Clinton Iowa MMSI transponder data map text alerts" />
<meta name="robots" content= "index, follow">
  <link rel="stylesheet" href="<?php echo getEnv('BASE_URL');?>css/header2.css" type="text/css">
  <link rel="stylesheet" href="<?php echo getEnv('BASE_URL').$main['css'];?>" type="text/css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/base/jquery-ui.css">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <script data-ad-client="ca-pub-6783704513864257" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
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