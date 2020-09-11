<div>
  <div class="logo">
    <span class="logo-end">clinton</span><span class="logo-mid">river</span><span class="logo-end">traffic</span>
    <span class="logo-title"><?php echo strtoupper($title);?></span>
  </div>
  <ul class="nav">
    <li><a class="nav-link <?php echo is_selected($title, 'About');?>" href="about">ABOUT</a></li>
    <li><a class="nav-link <?php echo is_selected($title, 'Alerts');?>" href="alerts">ALERTS</a></li>
    <li><a class="nav-link <?php echo is_selected($title, 'Live');?>" href="livescan">LIVE</a></li>
    <li><a class="nav-link <?php echo is_selected($title, 'Logs');?>" href="logs">LOGS</a></li>
  </ul>
</div>