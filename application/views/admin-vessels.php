<ul class="nav">
  <li><a class="nav-link" href="../admin">Login</a></li>
  <li><a class="nav-link selected" href="../admin/vessels">Vessels</a></li>
  <li><a class="nav-link" href="../admin/list">Watch List</a></li>  
</ul>
<h1>Admin Vessels</h1>
<p>These are all the vessels which have had images and type data scraped from myshiptracking.com. 
They get added automatically when a detected transponder gets added to our live page. 
This page allows you to add a vessel which has not yet passed thorugh while our software was watching. 
You just need to input a known MMSI number into the form below.</p>

<form class="myForm" method="post" enctype="application/x-www-form-urlencoded" action="vessels">
  <fieldset>
    <legend>Enter a new MMSI</legend>
      <p><input type="text" name="mmsi" size="9" maxlength="9" placeholder="Type 9-Digit Number">
	  <input type="hidden" name="mmsi_form" value="1"/>
      <button>Submit</button></p>
  </fieldset>
</form>
<h3>Sorted By Vessel Name</h3>
<div class="container">
  <div class="row">
    <div class="col-12">
		<table class="table table-image">
		  <thead>
		    <tr>
		      <th scope="col">MMSI#</th>
		      <th scope="col">Image</th>
		      <th scope="col">Type</th>
		      <th scope="col">Name</th>
		      <th scope="col">Record Added</th>		      
		    </tr>
		  </thead>
		  <tbody>
<?php foreach($dmodel as $row) {
  		    echo "<tr>";
		      echo "<th scope=\"row\">{$row['vesselID']}</th>";
		      echo "<td class=\"w-25\">";
			    echo "  <img src=\"{$row['vesselImageUrl']}\" class=\"img-fluid img-thumbnail\" alt=\"Image of {$row['vesselName']}\" width=\"200\" height=\"160\" >";
		      echo "</td>";
		      echo "<td>{$row['vesselType']}</td>";
          echo "<td>{$row['vesselName']}</td>";
          echo "<td>".date('M j Y', $row['vesselRecordAddedTS'])."</td>";
		      echo "</tr>";
}?>
		  </tbody>
		</table>   
    </div>
  </div>
</div>