<!DOCTYPE html>
<html>
<head>
  <title>Clinton River Traffic <?php echo $title; ?></title>
  <title>Clinton River Traffic</title>  
  <meta name="description" content= "river fans barge tow spotters riverboats paddlewheelers steamboats Clinton Iowa MMSI transponder data map text alerts" />
   <meta name="robots" content= "index, follow">
  <!-- <link rel="stylesheet" href="<?php echo getEnv('BASE_URL');?>css/header2.css" type="text/css">--->
  <link rel="stylesheet" href="<?php echo getEnv('BASE_URL').$main['css'];?>" type="text/css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/base/jquery-ui.css">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo getEnv('GOOGLE_ANALYTICS_ID');?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '<?php echo getEnv('GOOGLE_ANALYTICS_ID')?>');
</script>
</head>
<body>
  <div class="parent">
      <div class="div1">
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
                        <li><a class="nav-link <?php echo is_selected($title, 'About');?>" href="<?php echo $main['path'];?>about">ABOUT</a></li>
                        <li><a class="nav-link <?php echo is_selected($title, 'Alerts');?>" href="<?php echo $main['path'];?>alerts">ALERTS</a></li>
                        <li><a class="nav-link <?php echo is_selected($title, 'Live');?>" href="<?php echo $main['path'];?>livescan/live">LIVE</a></li>
                        <li><a class="nav-link <?php echo is_selected($title, 'Logs');?>" href="<?php echo $main['path'];?>logs">LOGS</a></li>
                    </ul>
                </div>
                <div id="title_slate"><?php echo strtoupper($title);?></div>
            </div>
            <ul class="nav2">
              <li><a class="nav-link selected" href="alerts">All</a></li>
              <li><a class="nav-link" href="alerts/passenger">Passenger</a></li>
              <li><a class="nav-link" href="alerts/watchlist">Watch List</a></li>  
            </ul>
        </div>
        <div class="div2">
            <h1>All Vessel Types</h1>
            <ul>
            <?php echo $items; ?>
            </ul>
        </div>
        <div class="div3">
            <p>Waypoint crossing notifications for commercial vessels passing Clinton, Iowa on the Mississippi river.
            Put this <a href="alerts/rssall"><?php echo "<img src=\"../images/rss.jpg\" width=\"50\" alt=\"Link to RSS Feed\"/>";?>
            </a> link in your favorite news reader software to get updates when vessels are near or...</p>
            <div class="button_cont"><a class="example_c" href="alerts/subscribeAll">Get Notifications For ALL Vessels!</a></div>
            <p>The button above will trigger a request from your web browser to approve notifications from the CRT All Vessels stream. Accepting will 
            join your device to get notification events for each of the listed vessels.</p>
            <ol>
            <li>When the vessel's radio transponder is first detected</li>
            <li>When it reaches a 3 mile waypoint</li>
            </ol>
            <p>The waypoint is 3 miles south of the Clinton drawbridge for 
            vessels traveling upriver or 3 miles north of Lock and Dam 13 for vessels traveling downriver. This is fewer than for the Passenger Vessel 
            notification stream becasue there are so many more towing vessels. Vessels flagged as being local use vessels will not trigger notifications.  
            They just go back and forth or sit parked for long periods and don't traverse the four waypoints.</p>
        </div>
        <div class="div4">
            <h4></h4>
            <h4></h4>
        </div>
    </div>
  <script src="<?php echo $main['path'];?>js/jquery-3.5.1.min.js"></script>
  <script src="<?php echo $main['path'];?>js/jquery-timeago.js"></script>
  <script>
    jQuery(document).ready(function() {
      jQuery("time.timeago").timeago();
    });
  </script>
</body>
</html>


