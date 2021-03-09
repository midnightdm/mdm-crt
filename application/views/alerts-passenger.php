<ul class="nav">
  <li><a class="nav-link" href="../alerts">All</a></li>
  <li><a class="nav-link selected" href="passenger">Passenger</a></li>
  <li><a class="nav-link" href="watchlist">Watch List</a></li>  
</ul>
<h1>Passenger Vessels</h1>
<p>Waypoint crossing notifications for <a href="list">select passenger vessels</a> passing Clinton, Iowa on the Mississippi river.
<a href="rsspassenger">
<?php echo "<img src=\"". getEnv('BASE_URL')."images/rss.jpg\" width=\"50\" alt=\"Link to RSS Feed\"/></a>";?>
 Put the above RSS link in your favorite news reader software to get automatic notifications when these vessels are near.</p>
<ul>
<?php echo $items; ?>
</ul>
