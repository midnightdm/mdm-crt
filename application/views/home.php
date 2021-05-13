<!DOCTYPE html>
<html>
<head>
  <title>Clinton River Traffic <?php echo $title; ?></title> 
  <meta name="description" content= "river fans barge tow spotters riverboats paddlewheelers steamboats Clinton Iowa MMSI transponder data map text alerts" />
<meta name="robots" content= "index, follow">
  
  <link rel="stylesheet" href="<?php echo getEnv('BASE_URL').$css;?>" type="text/css">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
  <div id="wrapper">

    <div id="logo-container">
      <h1>clinton<span>river</span>traffic</h1>

      <img id="logo-img" src="<?php echo getEnv('BASE_URL');?>images/logo-towboat2.png" alt="The logo image shows a tow boat pushing 9 barges.">
      <div id="mbbg" class="hasNav">
      <!-- hidden checkbox is used as click reciever -->
      <input type="checkbox" />    
      <!--    Some spans to act as a hamburger. -->
      <span></span>
      <span></span>
      <span></span>
      <ul id="menu" class="nav">
              <li><a class="nav-link <?php echo is_selected($title, 'About');?>" href="<?php echo $path;?>about">ABOUT</a></li>
              <li><a class="nav-link <?php echo is_selected($title, 'Alerts');?>" href="<?php echo $path;?>alerts">ALERTS</a></li>
              <li><a class="nav-link <?php echo is_selected($title, 'Live');?>" href="<?php echo $path;?>livescan/live">LIVE</a></li>
              <li><a class="nav-link <?php echo is_selected($title, 'Logs');?>" href="<?php echo $path;?>logs">LOGS</a></li>
          </ul>
      </div>
    </div>
    <video id="videoBG" autoplay muted loop>
      <source src="<?php getEnv('BASE_URL');?>images/crt-background-vid.mp4" type="video/mp4">
    </video>
    
    
    <h1 class="huge">
      <div>
        <ul>
          <li>Riverboat Viewing...</li>
          <li>...Begins With Tracking</li>
        </ul>
      </div>
     </h1>
  

  </div>  
  


</body>
</html>