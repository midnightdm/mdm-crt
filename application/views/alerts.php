
<ul class="nav">
  <li><a class="nav-link selected" href="../alerts">All</a></li>
  <li><a class="nav-link" href="alerts/passenger">Passenger</a></li>
  <li><a class="nav-link" href="alerts/watchlist">Watch List</a></li>  
</ul>
<h1>All Vessel Types</h1>
<p>Waypoint crossing notifications for commercial vessels passing Clinton, Iowa on the Mississippi river.
Put this <a href="alerts/rssall"><?php echo "<img src=\"images/rss.jpg\" width=\"50\" alt=\"Link to RSS Feed\"/>";?>
</a> link in your favorite news reader software to get updates when vessels are near or...</p>
<div class="button_cont"><a class="example_c" href="alerts/subscribeAll">Get Notifications!</a></div>
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
<ul>
<?php echo $items; ?>
</ul>
