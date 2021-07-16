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
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo getEnv('GOOGLE_ANALYTICS_ID') ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '<?php echo getEnv('GOOGLE_ANALYTICS_ID')?>');
</script>
</head>
<body>
    <div id="wrapper">
        <div id="header">
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
                <button class="omb" data-bind="click: liveScanModel.toggleMileLabels">Toggle Mile Labels</button>  
                <div id="title_slate"><?php echo strtoupper($title);?></div>
            </div>
        </div>
        <div id="main">
            <script src="<?php echo $main['path'];?>js/jquery-3.5.1.min.js"></script>
            <script src="<?php echo $main['path'];?>js/knockout-3.5.1.js"></script>
            <script defer src="<?php echo $main['path'];?>js/livescan.copy.js"></script>
            <script defer async
            src="https://maps.googleapis.com/maps/api/js?key=<?php echo getEnv('MDM_CRT_MAP_KEY');?>&callback=initMap">
            </script> 
            <div id="post-menu-body">
                <div id="content-container">
                    <div class="left-pane">
                        <div id="map"></div>
                    </div>        
    
                <div id="scans">
                <ul data-bind="foreach: livescans">
                    <li data-bind="class: dataAge">
                    <div class="timer" ></div>
                    <div class="label-wrap" data-bind="click: toggleExpanded">
                        <h4 class="map-label" data-bind="text: mapLabel"></h4>
                        <h4 class="tile-title" data-bind="text: name"></h4> 
                        <img class="dir-img" data-bind="attr: {src: dirImg }"/>              
                    </div>
                    <div class="location" data-bind="text: liveLocation"></div>
                    <div data-bind="visible: expandedViewOn, template: {name: 'viewDetail', data: $data}"></div>
                    </li>
                </ul>
                    <!-- ko if: livescans().length<1 -->
                    <h1 class="announcement">NO VESSELS IN RANGE CURRENTLY</h1>
                <!-- /ko   -->
                <div id="compass">
      <p><button class="pill">American Duchess in Clinton 6:11pm July 15, 2021.</button></p>
    <video controls="controls" width="320">
      <source src="../images/vessels/AmericanDutchess.mp4" type="video/mp4">
      Your browser does not support the HTML5 Video element.
    </video>
    </div>      
                </div>
            </div>
        </div>  


<script type="text/html" id="viewDetail">
        
        <div class="tile-body" data-bind="css:{ on: expandedViewOn()}">

          <h3>Vessel Data:</h3>

          <div id="vessel-data-group">
            <div class="block">              
              <span class="tlabel">Direction: </span> 
              <span></span>             
              <span class="ttext" data-bind="text: dir"></span>              
            </div>
            <div class="block">
              <span class="tlabel">Speed:</span>
              <span></span>
              <span class="ttext" data-bind="text: speed"></span>
            </div>
            <div class="block">
              <span class="tlabel">Course:</span>
              <span></span>
              <span class="ttext" data-bind="text: course"></span>
            </div>
            <div class="block" data-bind="css: {zoomed: isZoomed}">
              <span class="tlabel">Location:</span><a href="#" data-bind="click: zoomMap">
              <span class="ttext" data-bind="text: lat"></span>
              <span class="ttext" data-bind="text: lng"></span></a>
            </div>            
            <div class="block">
              <span class="tlabel">Type:</span>
              <span class="ttext" data-bind="text: type"></span>
            </div>
            <div class="block">
              <span class="tlabel">MMSI:</span>
              <span class="ttext" data-bind="text: id"></span>
            </div>
            <img class="vessel-img" data-bind="visible: hasImage, attr:{ src:imageUrl}"/>                           
          </div>
            
          <h3>Waypoints:</h3>         
            
          <div id="waypoint-data-group" data-bind="text: localVesselText"></div>
            <div data-bind="if: liveIsLocal()==0">
              <div class="block chk">
                <span class="tlabel">3 North:</span>
                <span class="bullet" data-bind="css: {reached: liveMarkerAlphaWasReached}"></span>
                <span class="ttext" data-bind="text:alphaTime">Not Yet Reached</span>
              </div>
              <div class="block chk">
                <span class="tlabel">Lock 13:</span>
                <span class="bullet" data-bind="css: {reached: liveMarkerBravoWasReached}"></span>
                <span class="ttext" data-bind="text: bravoTime">Not Yet Reached</span>
              </div>
              <div class="block chk">
                <span class="tlabel">RR Bridge:</span>
                <span class="bullet" data-bind="css: {reached: liveMarkerCharlieWasReached}"></span>
                <span class="ttext" data-bind="text: charlieTime">Not Yet Reached</span>
              </div>          
              <div class="block chk">
                <span class="tlabel">3 South:</span>
                <span class="bullet" data-bind="css: {reached: liveMarkerDeltaWasReached}"></span>
                <span class="ttext" data-bind="text: deltaTime">Not Yet Reached</span>
              </div>  
            </div>
             
          </div>  
          <div><a id="history" href="" data-bind="attr: { href: url }">History</a></div>       
</script>
    </div>

  </div>  
</body>
</html>