<ul class="nav">
  <li><a class="nav-link" href="../admin/logout">Logout</a></li>
  <li><a id="AddLink" class="nav-link selected" href="" 
  data-bind="click: function() { adminVesselsModel.goToPage(null, 'add') }, 
             css: {selected: adminVesselsModel.selectedLink().add==1 }">Add</a></li>
  <li><a id="AllLink" class="nav-link" href="" 
  data-bind="click: function() { adminVesselsModel.goToPage(null, 'all') }, 
             css: {selected: adminVesselsModel.selectedLink().all==1 }">All</a></li>
  <li><a id="PassengerLink" class="nav-link" href="" 
  data-bind="click: function() { adminVesselsModel.goToPage(null, 'passenger') },
             css: {selected: adminVesselsModel.selectedLink().passenger==1 }">Passenger</a></li> 
  <li><a id="WatchedLink" class="nav-link" href="" 
  data-bind="click: function() { adminVesselsModel.goToPage(null, 'watched') },
             css: {selected: adminVesselsModel.selectedLink().watched==1 }">Watched</a></li>
             <li><a id="WatchedLink" class="nav-link" href="" 
  data-bind="click: function() { adminVesselsModel.goToPage(null, 'messages') },
             css: {selected: adminVesselsModel.selectedLink().messages==1 }">Notifications</a></li>   
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
		      <th scope="row">Type</th><td><input class="wfc" name="vesselType" type="text" value="" data-bind="value: vesselType, event: { change: changeDetected}" /></td>
        </tr>
        <tr>
		      <th scope="row">Name</th><td><input class="wfc" name="vesselName" type="text" value="" data-bind="value: vesselName, event: { change: changeDetected}" /></td>
        </tr>  
		    <tr>
		      <th scope="row">Call Sign</th><td><input class="wfc" name="vesselCallSign" type="text" value="" data-bind="value: vesselCallSign, event: { change: changeDetected}" /></td>
        </tr>
        <tr>
		      <th scope="row">Length</th><td><input class="wfc" name="vesselLength" type="text" value="" data-bind="value: vesselLength, event: { change: changeDetected}" /></td>
        </tr>  
		    <tr>
		      <th scope="row">Width</th><td><input class="wfc" name="vesselWidth" type="text" value="" data-bind="value: vesselWidth, event: { change: changeDetected}" /></td>
        </tr>
        <tr>
		      <th scope="row">Draft</th><td><input class="wfc" name="vesselDraft" type="text" value="" data-bind="value: vesselDraft, event: { change: changeDetected}" /></td>
        </tr>  
        <tr>
		      <th scope="row">Owner</th><td><input class="wfc" name="vesselOwner" type="text" value="" data-bind="value: vesselOwner, event: { change: changeDetected}" /></td>
        </tr>
        <tr>
		      <th scope="row">Built</th><td><input class="wfc" name="vesselBuilt" type="text" value="" data-bind="value: vesselBuilt, event: { change: changeDetected}" /></td>
        </tr>
        <tr>
		      <th scope="row">Has Image?</th><td><input class="wfc" type="checkbox" data-bind="checked: vesselHasImage, event: { change: changeDetected}" /><span data-bind="text: vesselHasImageText, css: { 'watchOn': vesselHasImage()==1}"></span></td>
        </tr>
        <tr>
		      <th scope="row">Is On Watch List?</th><td><input class="wfc" type="checkbox" data-bind="checked: vesselWatchOn, event: { change: changeDetected}" /> <span data-bind="text: vesselWatchOnText, css: { 'watchOn': vesselWatchOn()==1}"></span></td>
        </tr>
        <tr>
		      <th scope="row">Image URL</th><td><input class="wfc" name="vesselImageUrl" type="text" value="" data-bind="value: vesselImageUrl, event: { change: changeDetected}"></td>
        </tr>  
        <tr>      
            <td colspan="2" class="w-25">
			        <img data-bind="attr: { src: vesselImageUrl, alt:'Image of '+ vesselName}" class="img-fluid img-thumbnail"  height="240" ><button data-bind="click: tryImageUrl">URL Refresh</button>
		        </td>
        </tr>	  		  
		</table>   
    </div>
  </div>
  <!-- ko if: adminVesselsModel.nowPage()=='add' && !adminVesselsModel.formSaved() -->
  <h1>You're not done yet!</h1>
  <p>You may change what the scaper found, but even if you don't the data won't be saved to the database until you accept it below.</p>
  <div class="button_cont"><a class="example_a" href="" data-bind="click: function() { apiInsertNewVessel() }">Accept Vessel</a></div>
  <!-- /ko -->
  <!-- ko if: adminVesselsModel.formSaved() -->
  <h1>Record Saved</h1>
  <!-- /ko -->
  <h1 style="color:red" data-bind="visible: adminVesselsModel.errorMsg()!=null">ERROR: <span data-bind="text: adminVesselsModel.errorMsg"></span></h1>
  <!-- ko if: adminVesselsModel.nowPage()=='detail' && adminVesselsModel.formChanged() -->
  <div class="button_cont"><a class="example_a" href="" data-bind="click: function() { apiUpdateVessel() }">Save Changes</a></div>
  <!-- /ko -->
  <!-- ko if: adminVesselsModel.lastPage()=='watched' -->
  <div class="button_cont"><a class="example_b" data-bind="click: function() { adminVesselsModel.goToPage(null, 'watched') }" href="">Return To List</a></div>
  <!-- /ko -->
  <!-- ko if: adminVesselsModel.lastPage()=='all' -->
  <div class="button_cont"><a class="example_b" data-bind="click: function() { adminVesselsModel.goToPage(null, 'all') }" href="">Return To List</a></div>
  <!-- /ko -->
  <!-- ko if: adminVesselsModel.lastPage()=='passenger' --> 
  <div class="button_cont"><a class="example_b" data-bind="click: function() { adminVesselsModel.goToPage(null, 'passenger') }" href="">Return To List</a></div>
  <!-- /ko -->
</div>

</script>

<script type="text/html" id="viewList">
<h2>Showing <span data-bind="text: nowPage"></span> vessels</h2>

<p>These are vessels for which images and data have been scraped from myshiptracking.com. They are added automatically
   when a detected transponder activates our live page. Click a vessel name to see and edit its details. Correct anything 
   the source website didn't have right. The Watched page shows only those vessels specially flagged for alert notifications.
   You can add or remove a flagged vessel's status with a checkbox on the edit page.</p>
<table>
  <thead>
    <tr>
      <th>Index</th>  
      <th>Type</th>
      <th>Name</th>
      <th>MMSI</th>
      <th>Date Added</th>
      <th>On Watch List?</th>
    </tr>
  </thead>
  <tbody data-bind="foreach: vesselListFiltered">
    <tr>
      <td class="col_r" data-bind="text: $index"></td>
      <td class="col_r" data-bind="text: vesselType"></td>
      <td><a href="" data-bind="text: vesselName, click: $parent.goToPage.bind($data, $data.localIndex, 'detail')"></td>
      <td class="col_r" data-bind="text: vesselID"></td>
      <td data-bind="text: vesselRecordAddedDate"></td>     
      <td  data-bind="text: vesselWatchOnText, css: { 'watchOn': vesselWatchOn()==1}" style="text-align:center"></td>     
    </tr>
  </tbody>
</table>
</script>

<script type="text/html" id="viewMessages">
<h2>Send A Notification Message</h2>
<section class="form-style-3">
<fieldset>
<div>  
<label for="f_method"><span>Method:</span></label>
<input type="radio" name="f_method" value="sms" data-bind="checked: adminVesselsModel.f_method">SMS
<input type="radio" name="f_method" value="email" data-bind="checked: adminVesselsModel.f_method">Email
<input type="radio" name="f_method" value="notification" data-bind="checked: adminVesselsModel.f_method">Web Notification
</div>
<div>
  <label for="destination"><span>Destination:</span></label>
<input id="destination" name="f_destination" type="text" list="notif" size="50" data-bind="value: adminVesselsModel.f_destination">
<!-- ko if: adminVesselsModel.f_method()=="notification" -->
<div>Click field above to select notification group name</div>
<!-- /ko -->
<!-- ko if: adminVesselsModel.f_method()=="sms" -->
<div>Type 11 digit SMS phone number above</div>
<!-- /ko -->  
<!-- ko if: adminVesselsModel.f_method()=="email" -->
<div>Type in email address above</div>
<!-- /ko -->
<datalist id="notif">
  <option value="admin">
  <option value="all">
  <option value="passenger">
</datalist>
</div>
</fieldset>
<fieldset>
<div>
<label for="subject"><span>Subject:</span></label>
<input id="subject" name="f_subject" type="text" size="50" data-bind="value: adminVesselsModel.f_subject">
</div>
<div>
<label for="message"><span>Message:</span></label>
<textarea id="message" name="f_message" size="140" data-bind="value: adminVesselsModel.f_message"></textarea>
</div>

<div class="button_cont"><a class="example_b" data-bind="click: function() { apiSendMessage() }" href="">Send Message</a></div>
</fieldset>
</section>
<h2>Log of last 50 notifications sent</h2>

<table>
  <thead>
    <tr>
        
      <th>Alert ID</th>
      <th>Time Sent</th>
      <th>Event Type</th>
      <th>Message To</th>
      <th>Message ID</th>
      <th>Status/Cost</th>
    </tr>
  </thead>
  <tbody data-bind="foreach: logsList">
    <tr data-bind="css: { isNew: recentUpdate}">     
      <td data-bind="text: alogAlertID"></td>
      <td data-bind="text: dateStr"></td>
      <td><span data-bind="text: alogType"></span> <span data-bind="text: alogDirection"></span></td>
      <td data-bind="text: alogMessageTo"></td>
      <td data-bind="text: alogMessageID"></td>    
      <td class="col_r"><span data-bind="text: alogMessageStatus"></span> <span data-bind="text: alogMessageCost"></span></td>
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
      <p><input type="text" data-bind="value: formVesselID" size="9" maxlength="9" placeholder="9-Digits">
      <button data-bind="click: apiLookupVessel()">Submit</button></p>
  </fieldset>
  <h1 style="color:red" data-bind="visible: adminVesselsModel.errorMsg()!=null">ERROR: <span data-bind="text: adminVesselsModel.errorMsg"></span></h1>
  <div data-bind="visible: formEditOn,  template: {name: 'viewDetail', data: adminVesselsModel.vesselDetail }"></div>
</script>

<!-- Only one div is visible at a time of the next two. -->
<div data-bind="visible: selectedView().view=='viewList',    template: {name: 'viewList', data: adminVesselsModel }">LOADING VESSELS...</div>
<div data-bind="visible: selectedView().view=='viewDetail',  template: {name: 'viewDetail', data: adminVesselsModel.vesselDetail }">One Moment...</div>
<div data-bind="visible: selectedView().view=='viewAdd',     template: {name: 'viewAdd', data: adminVesselsModel }"></div>
<div data-bind="visible: selectedView().view=='viewMessages',     template: {name: 'viewMessages', data: adminLogsModel }"></div>

