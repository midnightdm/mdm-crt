


<script src="<?php echo $main['path'];?>js/jquery-3.5.1.min.js"></script>
<script src="<?php echo $main['path'];?>js/knockout-3.5.1.js"></script>
<script defer async
src="https://maps.googleapis.com/maps/api/js?key=<?php echo getEnv('MDM_CRT_MAP_KEY');?>&callback=initMap">
</script> 
<script src="<?php echo $main['path'];?>js/livescan.js"></script>


<div id="post-menu-body">
  <div id="content-container">
    <div class="left-pane">
      <div id="map"></div>
    </div>        
    
    <div id="scans">
      <ul data-bind="foreach: livescans">
          <li>
            <div class="label-wrap" data-bind="click: toggleExpanded">
              <h4 class="map-label" data-bind="text: mapLabel"></h4>
              <h4 class="tile-title" data-bind="text: name"></h4> 
              <img class="dir-img" data-bind="attr: {src: dirImg }"/>              
            </div>
          <div data-bind="visible: expandedViewOn, template: {name: 'viewDetail', data: $data}"></div>
        </li>
      </ul>
          <!-- ko if count(livescans)==0 -->
          <h1 class="announcement">NO VESSELS IN RANGE CURRENTLY</h1>
      <!-- /ko   -->      
    </div>
    <img id="compass" src="<?php echo $main['path'];?>images/compass.png" height="200" alt="Compass Image"/>
  </div>
</div>  


<script type="text/html" id="viewDetail">
        
        <div class="tile-body" data-bind="css:{ on: expandedViewOn()}">

          <h3>Vessel Data:</h3>

          <div id="vessel-data-group">
            <div class="block">              
              <span class="tlabel">Direction:</span> 
              <span></span>             
              <span class="ttext" data-bind="text: dir"><span>              
            </div>
            <div class="block">
              <span class="tlabel">Speed:</span>
              <span></span>
              <span class="ttext" data-bind="text: speed"></span>
            </div>
            <div class="block">
              <span class="tlabel">Course:</span>
              <span></span>
              <span class="ttext" data-bind="text: course"></span>
            </div>
            <div class="block" data-bind="css: {zoomed: isZoomed}">
              <span class="tlabel">Location:</span><a href="#" data-bind="click: zoomMap">
              <span class="ttext" data-bind="text: lat"></span>
              <span class="ttext" data-bind="text: lng"></span></a>
            </div>            
            <div class="block">
              <span class="tlabel">Vessel Type:</span>
              <span class="ttext" data-bind="text: type"></span>
            </div>
            <div class="block">
              <span class="tlabel">MMSI:</span>
              <span class="ttext" data-bind="text: id"></span>
            </div>
            <div class="block">
              <span class="tlabel">Call Sign:</span>
              <span></span>
              <span class="ttext" data-bind="text: callsign"></span>
            </div>                
            <div class="block">
              <span class="tlabel">Length:</span>
              <span></span>
              <span class="ttext" data-bind="text: length"></span>
            </div>
            <div class="block">
              <span class="tlabel">Width:</span>
              <span></span>
              <span class="ttext" data-bind="text: width"></span>
            </div>
            <div class="block">
              <span class="tlabel">Draft:</span>
              <span></span>
              <span class="ttext" data-bind="text: draft"></span>
            </div>
            <img class="vessel-img" data-bind="visible: hasImage, attr:{ src:imageUrl}"/>                           
          </div>
            
          <h3>Waypoints:</h3>         
            
          <div id="waypoint-data-group" data-bind="text: localVesselText"></div>
            <div data-bind="if: liveIsLocal()==0">
              <div class="block chk">
                <span class="tlabel">3 North:</span>
                <span class="bullet" data-bind="css: {reached: liveMarkerAlphaWasReached}"></span>
                <span class="ttext" data-bind="text:alphaTime">Not Yet Reached</span>
              </div>
              <div class="block chk">
                <span class="tlabel">Lock 13:</span>
                <span class="bullet" data-bind="css: {reached: liveMarkerBravoWasReached}"></span>
                <span class="ttext" data-bind="text: bravoTime">Not Yet Reached</span>
              </div>
              <div class="block chk">
                <span class="tlabel">RR Bridge:</span>
                <span class="bullet" data-bind="css: {reached: liveMarkerCharlieWasReached}"></span>
                <span class="ttext" data-bind="text: charlieTime">Not Yet Reached</span>
              </div>          
              <div class="block chk">
                <span class="tlabel">3 South:</span>
                <span class="bullet" data-bind="css: {reached: liveMarkerDeltaWasReached}"></span>
                <span class="ttext" data-bind="text: deltaTime">Not Yet Reached</span>
              </div>                    
            </div>
          </div>
        </div>
        
</script>