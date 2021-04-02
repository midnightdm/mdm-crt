<div id="post-menu-body">
  <ul class="nav2">
    <li><a class="nav-link" href="../alerts">All</a></li>
    <li><a class="nav-link selected" href="passenger">Passenger</a></li>
    <li><a class="nav-link" href="watchlist">Watch List</a></li>  
  </ul>
  <div id="content-container">
    <h1>Passenger Vessels</h1>
    <p>Waypoint crossing notifications for <a href="watchlist">select passenger vessels</a> passing Clinton, Iowa on the Mississippi river.
    <a href="rsspassenger">
    <?php echo "<img src=\"". getEnv('BASE_URL')."images/rss.jpg\" width=\"50\" alt=\"Link to RSS Feed\"/></a>";?>
    Put the above RSS link in your favorite news reader software to get updates when these vessels are near.</p>
    <ul>
      <?php echo $items; ?>
    </ul>
  </div>
</div>
<script src="<?php echo $main['path'];?>js/jquery-3.5.1.min.js"></script>
<script src="<?php echo $main['path'];?>js/jquery-timeago.js"></script>
<script>
jQuery(document).ready(function() {
  jQuery("time.timeago").timeago();
});
</script>