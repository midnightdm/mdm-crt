<ul class="nav">
  <li><a class="nav-link" href="../admin">Login</a></li>
  <li><a class="nav-link selected" href="../admin/vessels">Vessels</a></li>
  <li><a class="nav-link" href="../admin/watchlist">Watch List</a></li>  
</ul>
<h1>Admin Vessels</h1>
<?php if(isset($dmodel['error'])):
   echo "<h3 style=\"color:red\">ERROR: ".$dmodel['error']."</h3>";
else: ?>

 

<h3>Data returned by myshiptracking.com</h3>
<div class="container">
  <div class="dmodel">
    <div class="col-12">
		<table class="table table-image">
		  
		    <tr>
		      <th scope="row">MMSI#</th><td><?php echo $dmodel['vesselID'];?></td>
        </tr>  
		    <tr>
		      <th scope="row">Type</th><td><?php echo $dmodel['vesselType'];?></td>
        </tr>
        <tr>
		      <th scope="row">Name</th><td><?php echo $dmodel['vesselName'];?></td>
        </tr>  
		    <tr>
		      <th scope="row">Call Sign</th><td><?php echo $dmodel['vesselCallSign'];?></td>
        </tr>
        <tr>
		      <th scope="row">Length</th><td><?php echo $dmodel['vesselLength'];?></td>
        </tr>  
		    <tr>
		      <th scope="row">Width</th><td><?php echo $dmodel['vesselWidth'];?></td>
        </tr>
        <tr>
		      <th scope="row">Draft</th><td><?php echo $dmodel['vesselDraft'];?></td>
        </tr>  
        <tr>
		      <th scope="row">Owner</th><td><?php echo $dmodel['vesselOwner'];?></td>
        </tr>
        <tr>
		      <th scope="row">Built</th><td><?php echo $dmodel['vesselBuilt'];?></td>
        </tr>
        <tr>
		      <th scope="row">Has Image?</th><td><?php echo $dmodel['vesselHasImage'];?> (1=Yes, 0=No)</td>
        </tr>
        <tr>
		      <th scope="row">Image URL</th><td><?php echo "<a href=\"{$dmodel['vesselImageUrl']}\">{$dmodel['vesselImageUrl']}</a>";?></td>
        </tr>  
        <tr>
          <th scope="row">Image</th>
            <td class="w-25">
			        <img src="<?php echo $dmodel['vesselImageUrl'];?>" class="img-fluid img-thumbnail" alt="Image of <?php echo $dmodel['vesselName'];?>"  height="240" >
		        </td>
        </tr>	  		  
		</table>   
    </div>
  </div>
</div>
<form class="myForm" method="post" enctype="application/x-www-form-urlencoded" action="vessels">
  <fieldset>
    <legend>Save info as is?</legend>
    <p>
	    <input type="hidden" name="save_vessel_form" value="1"/>
      <?php echo form_hidden('dmodel', $dmodel);?>
      <input name="submit" type="submit" value="Yes"/> <input name="submit" type="submit" value="No"/> <input name="submit" type="submit" value="Edit"/>
    </p>
  </fieldset>
</form>
<?php endif; ?>