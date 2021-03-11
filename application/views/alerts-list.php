<ul class="nav">
  <li><a class="nav-link" href="../alerts">All</a></li>
  <li><a class="nav-link" href="passenger">Passenger</a></li>
  <li><a class="nav-link selected" href="watchlist">Watch List</a></li>  
</ul>
<h1>Watch List</h1>
<p>The following vessels, in likely demand for viewing and scheduled to tour the upper Mississippi river in 2021 or 2022, have been preselected for 
the <a href="passenger">Passenger</a> alert page. To get automatic notifications when the vessels are in the area put this <a href="rsspassenger"><?php echo "<img src=\"". getEnv('BASE_URL')."images/rss.jpg\" width=\"50\" alt=\"(Link to RSS Feed)\"/></a>";?> link in your favorite 
newsreader software and configure it for alert notifications.</p>

<div class="container">
  <div class="row">
    <div class="col-12">
		<table class="table table-image">
		  <thead>
		    <tr>
          <th >Image</th>
		      <th >Type</th>
		      <th >Name</th>	
          <th >MMSI#</th>      
		    </tr>
		  </thead>
		  <tbody>
<?php foreach($dmodel as $row) {
echo "<tr>";
echo "<td class=\"w-25\">";
echo '  <img src="' . $row['vesselImageUrl'] . '" class="img-fluid img-thumbnail" alt="Image of ' . $row['vesselName'] . '" width="200" height="160"/>';
echo "</td>";
echo "<td>{$row['vesselType']} Vessel</td>";
echo "<td>{$row['vesselName']}</td>";
echo "<th scope=\"row\">{$row['vesselID']}</th>";
echo "</tr>";
}?>
		  </tbody>
		</table>   
    </div>
  </div>
</div>