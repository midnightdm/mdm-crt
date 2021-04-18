<ul class="nav">
  <li><a class="nav-link" href="../admin">Login</a></li>
  <li><a class="nav-link" href="../admin/vessels">Vessels</a></li>
  <li><a class="nav-link selected" href="../admin/watchlist">Watch List</a></li>  
</ul>
<h1>Admin Watchlist</h1> 

<table class="table table-image">
<div class="container">
  <div class="row">
    <div class="col-12">
		
        <form class="myForm" method="post" action="watchlist">
		  <thead>
		    <tr>
                <th>Image</th>
		        <th>Type</th>
		        <th>Name</th>	
                <th>MMSI#</th> 
                <th>Status</th>
                <th>Change</th>     
		    </tr>
		  </thead>
		  <tbody>
<?php foreach($dmodel as $row) {
  		    echo "<tr>";
            echo "<td class=\"w-25\">";
		    echo "  <img src=\"{$row['vesselImageUrl']}\" class=\"img-fluid img-thumbnail\" alt=\"Image of {$row['vesselName']}\" width=\"200\" height=\"160\" >";
		    echo "</td>";
		    echo "<td>".$row['vesselType']." Vessel</td>";
            echo "<td>{$row['vesselName']}</td>";
            echo "<td>{$row['vesselID']}</td>";
            echo "<td>{$row['watchOnString']}</td>";
            echo "<td><input type=\"checkbox\"";
            if($row['watchOn']) {
                echo " checked ";
            }
            echo "/></td>";
		    echo "</tr>";
}?>
		  </tbody>
          
		</table>   
    </div>
  </div>
</div>
<p>Press "Save" to submit any status changes you may have made above.</p>
</form>