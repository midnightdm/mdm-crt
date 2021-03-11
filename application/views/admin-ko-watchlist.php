<ul class="nav">
  <li><a class="nav-link" href="../admin/logout">Logout</a></li>
  <li><a id="AddLink" class="nav-link selected" href="" 
  data-bind="click: function() { adminVesselsModel.switchAddView() }, 
             css: {selected: adminVesselsModel.addLinkIsSelected()==1 }">Add</a></li>
  <li><a id="AllLink" class="nav-link" href="" 
  data-bind="click: function() { adminVesselsModel.switchFilter('All') }, 
             css: {selected: adminVesselsModel.allLinkIsSelected()==1 }">All</a></li>
  <li><a id="WatchedLink" class="nav-link" href="" 
  data-bind="click: function() { adminVesselsModel.switchFilter('Watched') },
             css: {selected: adminVesselsModel.watchedLinkIsSelected()==1 }">Watched</a></li>  
</ul>

<script src="<?php echo $main['path'];?>js/jquery-3.5.1.min.js"></script>
<script src="<?php echo $main['path'];?>js/knockout-3.5.1.js"></script>
<script type="text/javascript">
    //Parse PHP data into JavaScript array.
    var vesselList = JSON.parse('<?php echo json_encode($vesselList) ?>');     
</script>
<script src="<?php echo $main['path'];?>js/admin.js"></script>


<script type="text/html" id="viewDetail">
<h3>Edit data for <span data-bind="text: vesselName"></span> below.</h3>

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
		      <th scope="row">Has Image?</th><td><input type="checkbox" data-bind="checked: vesselHasImage"/></td>
        </tr>
        <tr>
		      <th scope="row">Is On Watch List?</th><td><span data-bind="text: vesselWatchOn"></span></td>
        </tr>
        <tr>
		      <th scope="row">Is On Watch List?</th><td><input type="checkbox" data-bind="value: vesselWatchOn, checked: vesselWatchOn"/></td>
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
<button>Return To List</button>
</script>

<script type="text/html" id="viewList">
<h3>Showing <span data-bind="text: listStatus"></span> vessels sorted by <span data-bind="text: listSort"></span></h3>

<p>These are all the vessels which have had images and type data scraped from myshiptracking.com. They get added automatically
   when a detected transponder gets added to our live page. Click a vessel name to see and edit details. The Watchlist view 
   shows only vessels being watched for special notifications.</p>
<table>
  <thead>
    <tr>
      <th>Index</th>  
      <th>Type</th>
      <th>Name</th>
      <th>Date Added</th>
      <th>On Watch List?</th>
    </tr>
  </thead>
  <tbody data-bind="foreach: vesselListFiltered">
    <tr>
      <td class="col_r" data-bind="text: $index"></td>
      <td class="col_r" data-bind="text: vesselType"></td>
      <td><a href="" data-bind="text: vesselName, click: $parent.switchEditView.bind($data, $index())"></td>
      <td data-bind="text: vesselRecordAddedDate"></td>     
      <td  data-bind="text: vesselWatchOnText, css: { 'watchOn': vesselWatchOn()==1}" style="text-align:center"></td>     
    </tr>
  </tbody>
</table>
</script>


<script type="text/html" id="viewAdd">
<h3>Add Vessel</h3>
<p>This page allows you to add a vessel which has not yet passed Clinton while our software was watching. 
You just need to input a known MMSI number into the form below.</p>


  <fieldset>
    <legend>Enter a new MMSI</legend>
      <p><input type="text" name="mmsi" size="9" maxlength="9" placeholder="Type 9-Digit Number">
	  <input type="hidden" name="mmsi_form" value="1"/>
      <button>Submit</button></p>
  </fieldset>

</script>

<!-- Only one div is visible at a time of the next two. -->
<div data-bind="visible: pageView()=='viewList',    template: {name: 'viewList', data: adminVesselsModel }">LOADING...</div>
<div data-bind="visible: pageView()=='viewDetail',  template: {name: 'viewDetail', data: adminVesselsModel.vesselDetail }"></div>
<div data-bind="visible: pageView()=='viewAdd',  template: {name: 'viewAdd', data: adminVesselsModel }"></div>
