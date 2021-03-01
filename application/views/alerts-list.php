<ul class="nav">
  <li><a class="nav-link" href="../alerts">All</a></li>
  <li><a class="nav-link" href="passenger">Passenger</a></li>
  <li><a class="nav-link selected" href="list">Watch List</a></li>  
</ul>
<h1>Watch List</h1>
<p>The following vessels, in likely demand for viewing and scheduled to tour the upper Mississippi river in 2021 or 2022, have been preselected for 
the <a href="passenger">Passenger</a> alert page. To get automatic notifications when the vessels are in the area put this <a href="rsspassenger"><?php echo "<img src=\"". getEnv('BASE_URL')."images/rss.jpg\" width=\"50\" alt=\"(Link to RSS Feed)\"/></a>";?> link in your favorite 
newsreader software and configure it for alert notifications.</p>



<ul>
<?php echo $items; ?>
</ul>