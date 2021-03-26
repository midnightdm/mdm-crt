<div id="logo-container">
    <h1>clinton<span>river</span>traffic</h1>
    <img id="logo-img" src="images/logo-towboat2.png" alt="The logo image shows a tow boat pushing 9 barges.">
    <div id="mbbg">
        <ul class="nav">
            <li><a class="nav-link <?php echo is_selected($title, 'About');?>" href="<?php echo $main['path'];?>about">ABOUT</a></li>
            <li><a class="nav-link <?php echo is_selected($title, 'Alerts');?>" href="<?php echo $main['path'];?>alerts">ALERTS</a></li>
            <li><a class="nav-link <?php echo is_selected($title, 'Live');?>" href="<?php echo $main['path'];?>livescan">LIVE</a></li>
            <li><a class="nav-link <?php echo is_selected($title, 'Logs');?>" href="<?php echo $main['path'];?>logs">LOGS</a></li>
        </ul>
        <span id="title_slate"><?php echo strtoupper($title);?></span>
    </div>
</div>

