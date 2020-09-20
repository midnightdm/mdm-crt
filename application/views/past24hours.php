<ul class="nav">
  <li><a class="nav-link" href="../logs">Logs</a></li>
  <li><a class="nav-link" href="today">Today</a></li>
  <li><a class="nav-link selected" href="past24hours">Past 24 Hours</a></li>
  <li><a class="nav-link" href="yesterday">Yesterday</a></li>
  <li><a class="nav-link" href="past7days">Past 7 Days</a></li>
</ul>
<h1><?php echo $subtitle; ?></h1>
<h3><?php echo $range; ?></h3>
<p>This log shows the time each vessel passed the railroad drawbridge at Clinton, Iowa, Lock and Dam 13 at Fulton, Illinois and whether it was traveling upriver or downriver.</p>
<table>
  <tr><th>Name</th><th>Type</th><th>Direction</th><th>Lock 13</th><th>RR Bridge</th><th></th></tr>
  <?php echo $table; ?>
</table>
<div class="img-container">
<img src="../images/drawbridge.jpg" height="200" alt="Clinton railroad drawbridge" />
<img src="../images/lock13.jpg" height="200" alt="Lock & Dam 13" />