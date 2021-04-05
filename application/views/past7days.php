<div id="post-menu-body">
  <ul class="nav2">
    <li><a class="nav-link" href="today">Today</a></li>
    <li><a class="nav-link" href="past24hours">Past 24 Hours</a></li>
    <li><a class="nav-link" href="yesterday">Yesterday</a></li>
    <li><a class="nav-link selected" href="past7days">Past 7 Days</a></li>
  </ul>
  <div id="content-container">
    <h1><?php echo $subtitle; ?></h1>
    <h3><?php echo $range; ?></h3>
    <p>This log shows the time each vessel passed the railroad drawbridge at Clinton, Iowa, Lock and Dam 13 at Fulton, Illinois and whether it was traveling upriver or downriver.</p>
    <table>
    <tr><th>Type</th><th>Name</th><th>Lock 13</th><th>Direction</th><th>RR Bridge</th><th>Thumbnail</th></tr>
      <?php echo $table; ?>
    </table>
  </div>
</div>