<ul class="nav">
  <li><a class="nav-link" href="../admin">Login</a></li>
  <li><a id="AllLink" class="nav-link selected" href="" 
  data-bind="click: function() { adminVesselsModel.switchFilter('All') }, 
             css: {selected: adminVesselsModel.allLinkIsSelected()==1 }">Vessels</a></li>
  <li><a id="WatchedLink" class="nav-link" href="" 
  data-bind="click: function() { adminVesselsModel.switchFilter('Watched') },
             css: {selected: adminVesselsModel.watchedLinkIsSelected()==1 }">Watchlist</a></li>  
</ul>
<h1>Admin Vessels</h1>

<script src="<?php echo $main['path'];?>js/jquery-3.5.1.min.js"></script>
<script src="<?php echo $main['path'];?>js/knockout-3.5.1.js"></script>
<script type="text/javascript">
    //Parse PHP data into JavaScript array.
    var vesselList = JSON.parse('<?php echo json_encode($vesselList) ?>');     
</script>
<script src="<?php echo $main['path'];?>js/admin.js"></script>
<script type="text/html" id="viewDetail">

<h3>Edit data for <span data-bind="text: vesselName"></span> below.</h3>

<form class="myForm" method="post" enctype="application/x-www-form-urlencoded" action="vessels">
<div class="container">
  <div class="dmodel">
    <div class="col-12">
		<table class="table table-image">		  
		    <tr>
		      <th scope="row">MMSI#</th><td><span data-bind="text: vesselID"></span></td>
        </tr>  
		    <tr>
		      <th scope="row">Type</th><td><input name="vesselType" type="text" value="" data-bind="value: vesselType"/></td>
        </tr>
        <tr>
		      <th scope="row">Name</th><td><input name="vesselName" type="text" value="" data-bind="value: vesselName"/></td>
        </tr>  
		    <tr>
		      <th scope="row">Call Sign</th><td><input name="vesselCallSign" type="text" value="" data-bind="value: vesselCallSign"/></td>
        </tr>
        <tr>
		      <th scope="row">Length</th><td><input name="vesselLength" type="text" value="" data-bind="value: vesselLength"/></td>
        </tr>  
		    <tr>
		      <th scope="row">Width</th><td><input name="vesselWidth" type="text" value="" data-bind="value: vesselWidth"/></td>
        </tr>
        <tr>
		      <th scope="row">Draft</th><td><input name="vesselDraft" type="text" value="" data-bind="value: vesselDraft"/></td>
        </tr>  
        <tr>
		      <th scope="row">Owner</th><td><input name="vesselOwner" type="text" value="" data-bind="value: vesselOwner"/></td>
        </tr>
        <tr>
		      <th scope="row">Built</th><td><input name="vesselBuilt" type="text" value="" data-bind="value: vesselBuilt"/></td>
        </tr>
        <tr>
		      <th scope="row">Has Image?</th><td><input name="vesselHasImage" type="text" value="" data-bind="value: vesselHasImage"/> (1=Yes, 0=No)</td>
        </tr>
        <tr>
		      <th scope="row">Image URL</th><td><span data-bind="text: vesselImageUrl"></span></td>
        </tr>  
        <tr>
          <th scope="row">Image</th>
            <td class="w-25">
			        <img data-bind="attr: { src: vesselImageUrl, alt:'Image of '+vesselName}" class="img-fluid img-thumbnail"  height="240" >
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
</script>

<script type="text/html" id="viewList">
<h3>Showing <span data-bind="text: listStatus"></span> vessels sorted by <span data-bind="text: listSort"></span></h3>

<p>Click a vessel name to see and edit details. Click a heading label to change the sort.</p>
<table>
  <thead>
    <tr>
      <th><button data-bind="click: sortByType">Type</button></th>
      <th><button data-bind="click: sortByName">Name</button></th>
      <th><button  data-bind="click: sortByDate">Date Added</button></th>
      <th><button  data-bind="click: sortByWatch">On Watch List?</button></th>
    </tr>
  </thead>
  <tbody data-bind="foreach: vesselListFiltered">
    <tr>
      <td class="col_r" data-bind="text: vesselType"></td>
      <td><a href="" data-bind="text: vesselName, click: $parent.switchEditView.bind($data, $index())"></td>
      <td data-bind="text: vesselRecordAddedDate"></td>
      
      <td class="col_c" data-bind="text: vesselWatchOnText, css: { 'watchOn': vesselWatchOn()==1}"></td>
      
    </tr>
  </tbody>
</table>
</script>

<!-- Only one div is visible at a time of the next two. -->
<div data-bind="visible: pageView()=='viewList',    template: {name: 'viewList', data: adminVesselsModel }">LOADING...</div>
<div data-bind="visible: pageView()=='viewDetail',  template: {name: 'viewDetail', data: adminVesselsModel.vesselDetail }"></div>

