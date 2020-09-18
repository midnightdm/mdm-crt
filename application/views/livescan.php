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
              <span class="ttext" data-bind="text: dir"></span>
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
          <div><button data-bind="text: btnText, click:expandTile"></button></div>
          <div data-bind="attr: {id:otherDataLabel}, html: otherDataHtml" class="tile-extra">            
          </div>
        </div>
      </li>
    </ul>
  </div>
  <script src="../../js/jquery-3.5.1.min.js"></script>
  <script src="../../js/knockout-3.5.1.js"></script>
  <script defer
  src="https://maps.googleapis.com/maps/api/js?key=<?php echo getEnv('MDM_CRT_MAP_KEY');?>&callback=initMap">
  </script> 
  <script src="../../js/livescan.js"></script>
