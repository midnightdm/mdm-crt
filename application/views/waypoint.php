<!DOCTYPE html>
<html>
<head>
  <title>Clinton River Traffic <?php echo $title; ?></title>
  <title>Clinton River Traffic</title>  
  <meta name="description" content= "river fans barge tow spotters riverboats paddlewheelers steamboats Clinton Iowa MMSI transponder data map text alerts" />
<meta name="robots" content= "index, follow">
    <link rel="stylesheet" href="<?php echo getEnv('BASE_URL');?>css/header2.css" type="text/css">
    <link rel="stylesheet" href="<?php echo getEnv('BASE_URL');?>css/waypoint.css" type="text/css">

</head>
<body>

  <div id="wrapper">
    <div id="header">
      <?php $this->load->view('header2'); ?>
    </div>
    <div id="main">
    <?php echo $items;?>
    <?php 
        if($mapOn) {
          echo "<div id=\"map\"></div>"; 
        } else {
          echo "<div id=\"shadow1\"><img id=\"mapImg\" src=\"$bgmap\" alt=\"A Google Earth map shows a representation of a tow vessel at the noted waypoint location along the river.\"></div>";
        }  
    ?>
    <div id="shadow2"><?php echo "<img id=\"vesselImg\" src=\"$vesselImg\" alt=\"Image of the vessel $vesselName\">";
     ?>
    </div>
    <div id="shadow3"><?php echo "<img id=\"supImg\" src=\"$supimg\" alt=\"$supalt\">";?></div>
    <div id="shadow4"><h1 id="overlay1"><?php echo $text;?></h1><a id="history" href="<?php echo getenv('BASE_URL')."logs/vessel/".$vesselID;?>">History</a></div>
    </div>
    <div id="footer">
      <?php $this->load->view('footer'); ?>
    </div>
  </div>  
  <script src="<?php echo getEnv('BASE_URL');?>js/waypoint.js"></script>
  <script defer async
src="https://maps.googleapis.com/maps/api/js?key=<?php echo getEnv('MDM_CRT_MAP_KEY');?>&callback=initMap">
</script>
<script type="text/javascript">
  var vesselPos  = JSON.parse('<?php echo $position ?>');
  var vesselName = "<?php echo $vesselName ?>";
</script>

</body>
</html>