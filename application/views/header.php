<div>
  <div class="logo">
    <span class="logo-end">clinton</span><span class="logo-mid">river</span><span class="logo-end">traffic</span>
    <span class="logo-title"><?php echo strtoupper($title);?></span>
  </div>
  <ul class="nav">
    <li><a class="nav-link <?php echo is_selected($title, 'About');?>" href="<?php echo $main['path'];?>about">ABOUT</a></li>
    <li><a class="nav-link <?php echo is_selected($title, 'Alerts');?>" href="<?php echo $main['path'];?>alerts">ALERTS</a></li>
    <li><a class="nav-link <?php echo is_selected($title, 'Live');?>" href="<?php echo $main['path'];?>livescan">LIVE</a></li>
    <li><a class="nav-link <?php echo is_selected($title, 'Logs');?>" href="<?php echo $main['path'];?>logs">LOGS</a></li>
  </ul>
</div>