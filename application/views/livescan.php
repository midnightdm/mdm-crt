<div class="left-pane">
  <div id="map"></div>
</div>        
  <div id="scans">
    <ul data-bind="foreach: livescans">
      <li>
        <div>            
          <span class="map-label" data-bind="text: mapLabel"></span>
          <span class="tile-title" data-bind="text: name"></span>                       
        </div>
        <div class="tile-body" data-bind="attr:{class: dataAge}">
          <div class="block">              
              <span class="tlabel">Direction:</span>              
              <img class="dir-img" data-bind="attr: {src: dirImg }"/>
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
              <span class="tlabel">Report:</span>
              <span></span>
              <span class="ttext" data-bind="text: lastMovementAgo"></span>
          </div>
          <h3>Checkpoints</h3>
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
          <div>
          <h3>Other Data</h3>
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
        </div>
      </li>
    </ul>
  </div>  
  <script src="<?php echo $main['path'];?>js/jquery-3.5.1.min.js"></script>
  <script src="<?php echo $main['path'];?>js/knockout-3.5.1.js"></script>
  <script defer
  src="https://maps.googleapis.com/maps/api/js?key=<?php echo getEnv('MDM_CRT_MAP_KEY');?>&callback=initMap">
  </script> 
  <script src="<?php echo $main['path'];?>js/livescan.js"></script>
