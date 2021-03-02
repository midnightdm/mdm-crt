<ul class="nav">
  <li><a class="nav-link" href="../admin">Login</a></li>
  <li><a class="nav-link selected" href="../admin/vessels">Vessels</a></li>
  <li><a class="nav-link" href="../admin/list">Watch List</a></li>  
</ul>
<h1>Admin Vessels</h1> 

<form class="myForm" method="post" enctype="application/x-www-form-urlencoded" action="vessels">
<h3>Edit Data</h3>
<div class="container">
  <div class="dmodel">
    <div class="col-12">
		<table class="table table-image">
		  
		    <tr>
		      <th scope="row">MMSI#</th><td><input name="vesselID" type="text" value="<?php echo $dmodel['vesselID'];?>"/></td>
        </tr>  
		    <tr>
		      <th scope="row">Type</th><td><input name="vesselType" type="text" value="<?php echo $dmodel['vesselType'];?>"/></td>
        </tr>
        <tr>
		      <th scope="row">Name</th><td><input name="vesselName" type="text" value="<?php echo $dmodel['vesselName'];?>"/></td>
        </tr>  
		    <tr>
		      <th scope="row">Call Sign</th><td><input name="vesselCallSign" type="text" value="<?php echo $dmodel['vesselCallSign'];?>"/></td>
        </tr>
        <tr>
		      <th scope="row">Length</th><td><input name="vesselLength" type="text" value="<?php echo $dmodel['vesselLength'];?>"/></td>
        </tr>  
		    <tr>
		      <th scope="row">Width</th><td><input name="vesselWidth" type="text" value="<?php echo $dmodel['vesselWidth'];?>"/></td>
        </tr>
        <tr>
		      <th scope="row">Draft</th><td><input name="vesselDraft" type="text" value="<?php echo $dmodel['vesselDraft'];?>"/></td>
        </tr>  
        <tr>
		      <th scope="row">Owner</th><td><input name="vesselOwner" type="text" value="<?php echo $dmodel['vesselOwner'];?>"/></td>
        </tr>
        <tr>
		      <th scope="row">Built</th><td><input name="vesselBuilt" type="text" value="<?php echo $dmodel['vesselBuilt'];?>"/></td>
        </tr>
        <tr>
		      <th scope="row">Has Image?</th><td><input name="vesselHasImage" type="text" value="<?php echo $dmodel['vesselHasImage'];?>"/> (1=Yes, 0=No)</td>
        </tr>
        <tr>
		      <th scope="row">Image URL</th><td><?php echo $dmodel['vesselImageUrl'];?><input name="vesselImageUrl" type="hidden" value="<?php echo $dmodel['vesselImageUrl'];?>"/></td>
        </tr>  
        <tr>
          <th scope="row">Image</th>
            <td class="w-25">"
			        <img src="<?php echo $dmodel['vesselImageUrl'];?>" class="img-fluid img-thumbnail" alt="Image of <?php echo $dmodel['vesselName'];?>"  height="240" >
		        </td>
        </tr>	  		  
		</table>   
    </div>
  </div>
</div>
  <fieldset>
    <legend>Save Data?</legend>
    <p>
	    <input type="hidden" name="edit_vessel_form" value="1"/>
      <input name="submit" type="submit" value="Yes"/> <input name="submit" type="submit" value="No"/> 
    </p>
  </fieldset>
</form>