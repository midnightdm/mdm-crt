<script type="application/javascript" src="https://js.pusher.com/beams/1.0/push-notifications-cdn.js"></script>
<script type="application/javascript" src="<?php echo getEnv('BASE_URL');?>js/notifications.js"></script>
<h4>Waypoint crossing notifications for all commercial vessels passing Clinton, Iowa on the Mississippi river</h4>
<div class="button_cont"><a class="example_c" href="#" onclick="requestAllNotifications">All Vessels</a></div>
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

<h4>Waypoint crossing notifications for select passenger vessels only</h4>
<div class="button_cont"><a class="example_c" href="#" onclick="requestPassengerNotifications">Passenger Vessels</a></div>
<p>The button above will trigger a request from your web browser to approve notifications from the CRT Passenger stream. Accepting will 
join your device to get notification events for each of the listed vessels when traveling upriver:</p>
<ol>
<li>When the vessel's radio transponder is first detected</li>
<li>When it reaches 3 miles South of the Clinton drawbridge</li>
<li>When it arrives at the drawbridge</li>
<li>At Lock and Dam 13</li>
<li>At the point 3 miles North of Lock 13</li>
</ol>
<p>(The order is reversed for vessels traveling downriver.)</p>

<p><a href="<?php echo getEnv('BASE_URL');?>alerts"><< Back</a></p>
