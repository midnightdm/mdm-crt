<ul class="nav">
  <li><a class="nav-link selected" href="../alerts">All</a></li>
  <li><a class="nav-link" href="alerts/passenger">Passenger</a></li>
  <li><a class="nav-link" href="alerts/list">Watch List</a></li>  
</ul>
<h1>All Vessel Types</h1>
<p>Waypoint crossing notifications for commercial vessels passing Clinton, Iowa on the Mississippi river.
Put this <a href="alerts/rssall"><?php echo "<img src=\"". getEnv('BASE_URL')."images/rss.jpg\" width=\"50\" alt=\"Link to RSS Feed\"/>";?>
</a> link in your favorite news reader software to get automatic notifications when vessels are near.</p>
<ul>
<?php echo $items; ?>
</ul>