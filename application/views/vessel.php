<div id="post-menu-body">
  <ul class="nav2">
    <li><a class="nav-link" href="../../logs">Logs</a></li>
    <li><a class="nav-link" href="../today">Today</a></li>
    <li><a class="nav-link" href="../past24hours">Past 24 Hours</a></li>
    <li><a class="nav-link" href="../yesterday">Yesterday</a></li>
    <li><a class="nav-link" href="../past7days">Past 7 Days</a></li>
  </ul>
  <div id="content-container">
    <h1><?php echo $vesselName; ?> Passages</h1>

    <p>This log shows the direction of travel and the time this vessel passed each of the waypoints tracked by the Clinton River Traffic website. They include the railroad drawbridge at Clinton, Iowa; Lock and Dam 13 at Fulton, Illinois; and points 3 miles up and downriver from those.</p>
    <table>
      <tr><th>Direction</th><th>3 Mi North of Dam</th><th>Lock & Dam 13</th><th>RR Bridge</th><th>3 Mi South of Bridge</th></tr>
      <?php echo $table; ?>
    </table>
    <div class="img-container-2">
    <img src="<?php echo $vesselImageUrl;?>" height="200" alt="<?php echo $vesselName;?>" />
    <img src="../../images/lock13.jpg" height="200" alt="Lock & Dam 13" />
    <img src="../../images/drawbridge.jpg" height="200" alt="Clinton railroad drawbridge" />
    </div>
  </div>
</div>