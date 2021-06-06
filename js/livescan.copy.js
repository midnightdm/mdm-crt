//Working copy for experimentation crated 6/4/21


/* * * * * * * *
 *
 *  Project Goal: update map points of moving vessels with predictive positions between transponder upates
 * 
 *  //Multiply knots by 1.852 to get KPH

//Divide KPH by 3600 to get kilometers traveled in one second



//lat, lng in degrees. Bearing in degrees. Distance in Km
calculateNewPostionFromBearingDistance = function(lat, lng, bearing, distance) {
  var R = 6371; // Earth Radius in Km

  var lat2 = Math.asin(Math.sin(Math.PI / 180 * lat) * Math.cos(distance / R) + Math.cos(Math.PI / 180 * lat) * Math.sin(distance / R) * Math.cos(Math.PI / 180 * bearing));
  var lon2 = Math.PI / 180 * lng + Math.atan2(Math.sin( Math.PI / 180 * bearing) * Math.sin(distance / R) * Math.cos( Math.PI / 180 * lat ), Math.cos(distance / R) - Math.sin( Math.PI / 180 * lat) * Math.sin(lat2));

  return [180 / Math.PI * lat2 , 180 / Math.PI * lon2];
};

calculateNewPostionFromBearingDistance(60,25,30,1)
[60.007788047871614, 25
 *

 *
 */


//LiveScan Object class
class LiveScan {
  constructor() {
    this.liveLastScanTS = ko.observable();
    this.plotTS         = null;
    this.position       = ko.observable();
    this.lat            = ko.observable();
    this.lng            = ko.observable();
    this.id             = ko.observable();
    this.name           = ko.observable();
    this.liveLocation   = ko.observable();
    this.mapLabel       = null;
    this.btnText        = ko.observable("+");
    this.dir            = ko.observable("undetermined");
    this.callsign       = ko.observable();
    //this.timerOutput    = ko.observable();
    this.speed          = ko.observable();
    this.course         = ko.observable();    
    this.length         = ko.observable();
    this.width          = ko.observable();
    this.draft          = ko.observable();
    this.marker         = ko.observable();    
    this.isZoomed       = ko.observable(false);
    this.hasImage       = ko.observable();
    this.imageUrl       = ko.observable();
    this.type           = ko.observable();
    this.liveIsLocal    = ko.observable(false);
    this.liveMarkerAlphaWasReached = ko.observable(false);
    this.liveMarkerAlphaTS         = ko.observable(null);
    this.liveMarkerBravoWasReached = ko.observable(false);
    this.liveMarkerBravoTS         = ko.observable(null);
    this.liveMarkerCharlieWasReached = ko.observable(false);
    this.liveMarkerCharlieTS         = ko.observable(null);
    this.liveMarkerDeltaWasReached = ko.observable(false);
    this.liveMarkerDeltaTS         = ko.observable(null);
    this.expandedViewOn            = ko.observable(false);
    this.lastMovementTS            = ko.observable(new Date());
    this.isMoving                  = ko.observable(false);
    this.moveTimer                 = ko.observable(null);
    this.dataAge                   = ko.observable("age-green");
    this.prevLat                   = ko.observable();
    this.prevLng                   = ko.observable();
    this.localVesselText           = ko.computed(function (){
      if(this.liveIsLocal()==1) {
        return "Passages are not logged for this local operations vessel as it doesn't cross all four monitored waypoints.";
      } else if(this.liveIsLocal()==0) {
        return "";
      }
    }, this);
    this.toggleExpanded = function() {
      this.expandedViewOn() ? this.expandedViewOn(false) : this.expandedViewOn(true);
    }

    this.lastMovementAgo           = ko.computed(function () {
      var now  = Date.now();
      var diff = Math.floor((now - this.lastMovementTS().getTime())/60000);
      //return "now: "+now +"last: " + this.lastMovementTS().getTime() + "now - diff = "+diff;
      return diff>1 ? diff + " Minutes Ago" : "Current";
    }, this);
    
    this.url = ko.computed(function () {
      return "../logs/vessel/" + this.id();
    }, this);
    this.dirImg = ko.computed(function () {
      switch(this.dir()) {
        case "undetermined": return "../images/qmark.png"; break;
        case "upriver"     : return "../images/uparr.png"; break;
        case "downriver"   : return "../images/dwnarr.png"; break;
      }
    }, this);
    this.alphaTime = ko.computed(function() {
      if(this.liveMarkerAlphaTS()===null) {
        return "Not Yet Reached";
      } else {
      return formatTime(this.liveMarkerAlphaTS());
      }
    }, this); 
    this.bravoTime = ko.computed(function() {
      if(this.liveMarkerBravoTS()===null) {
        return "Not Yet Reached";
      } else {
        return formatTime(this.liveMarkerBravoTS());
      }       
    }, this); 
    this.charlieTime = ko.computed(function() {
      if(this.liveMarkerCharlieTS()===null) {
        return "Not Yet Reached";
      } else {
        return formatTime(this.liveMarkerCharlieTS());
      }      
    }, this); 
    this.deltaTime = ko.computed(function() {
      if(this.liveMarkerDeltaTS()===null) {
        return "Not Yet Reached";
      } else {
        return formatTime(this.liveMarkerDeltaTS());
      }
      
    }, this); 
    this.zoomMap = function() {
      if(this.isZoomed()) {
        map.setCenter(liveScanModel.clinton);
        map.setZoom(12);      
        this.isZoomed(false);
      } else {
        map.setCenter(this.position());
        map.setZoom(15);
        this.isZoomed(true);
      }
    };


  }
};
  

function LiveScanModel() {
  var self = this;
  self.livescans = ko.observableArray([]);
  self.clinton   = {lat: 41.857202, lng:-90.184084};
  self.url       = "../livescanjson";
  self.INTERVAL  = 20000;
  self.labelIndex = 0;
  
  //Status vars
  self.selectedView = ko.observable( {view: 'viewList', idx: null} );
  self.nowPage      = ko.observable('list');
  self.lastPage     = ko.observable('list');
  self.mapZoom      = ko.observable(12);
  self.markerList   = [];
  self.markersOn    = ko.observable(false);
  self.infoOn       = ko.observable(false);
  self.loopCount    = 0;
  
  self.toggleMileLabels = function() {
      if(self.infoOn()==false) {
        self.infoOn(true);
        console.log("Opening markers...");
        for(var i=0, len=liveScanModel.markerList.length; i<len; i++) {
          liveScanModel.markerList[i].info.open(map, liveScanModel.markerList[i].line.path);
        }  
        map.setZoom(14);
        //map.center(liveScanModel.clinton);
        //map.center(liveScanModel.clinton);
      } else {
        self.infoOn(false);
        console.log("Closing markers..");
        for(var i=0, len=liveScanModel.markerList.length; i<len; i++) {
            liveScanModel.markerList[i].info.close();
        }
      }
      //map.setCenter(liveScanModel.clinton);
  };

  self.goToPage = function(index, name=null) {
    switch(name) {
      case "detail": {
        var lastView = self.nowPage();
        self.selectedView( {view: 'viewDetail', idx: index} );
        self.lastPage(lastView);
        self.nowPage('detail');
        break;
      }
      case "list": {
        var lastView = self.nowPage();
        self.selectedView( {view: 'viewList', idx: index} );
        self.lastPage(lastview);
        self.nowPage('list');
        break;
      }
    }
  }
};

function initLiveScan() {
  $.getJSON(liveScanModel.url, {}, function(dat) {
    var key, o, marker, coords, course;
    for(var i=0, len=dat.length; i<len; i++) {           
      o = new LiveScan();      
      o.liveLastScanTS(new Date(dat[i].liveLastScanTS * 1000));
      o.position(new google.maps.LatLng(dat[i].position.lat, dat[i].position.lng));
      o.lat(dat[i].position.lat);
      o.lng(dat[i].position.lng);
      o.id(dat[i].id);
      o.name(dat[i].name);
      o.liveLocation(dat[i].liveLocation);
      o.mapLabel = lab[++liveScanModel.labelIndex];
      o.dir(dat[i].dir);
      o.callsign(dat[i].callsign);
      o.liveIsLocal(dat[i].liveIsLocal);
      o.speed(dat[i].speed);
      o.course(dat[i].course);
      o.width(dat[i].width);
      o.length(dat[i].length);
      o.draft(dat[i].draft);
      o.hasImage(dat[i].vessel.vesselHasImage);
      o.imageUrl(dat[i].vessel.vesselImageUrl);
      o.type(dat[i].vessel.vesselType);
      o.otherDataLabel = "od"+dat[i].id;
      o.lastMovementTS(new Date());
      // SHIP ICON ADD ON 4/1/21...
      course = parseInt(dat[i].course.slice(0,-3));
      coords = getShipSpriteCoords(course);     
      icon = {
        url: "https://www.clintonrivertraffic.com/images/ship-icon-sprite-cyan.png",
        origin: new google.maps.Point(coords[0], coords[1]),
        size: new google.maps.Size(55, 55)
      }
      marker = new google.maps.Marker({
        position: new google.maps.LatLng(dat[i].position.lat, dat[i].position.lng),
        title: dat[i].name, 
        label: o.mapLabel, 
        icon: icon,
        map: map
      });
      o.marker(marker);
      o.liveMarkerAlphaWasReached(dat[i].liveMarkerAlphaWasReached);
      if(o.liveMarkerAlphaWasReached()) {
        o.liveMarkerAlphaTS(new Date(dat[i].liveMarkerAlphaTS * 1000));
      }  
      o.liveMarkerBravoWasReached(dat[i].liveMarkerBravoWasReached);
      if(o.liveMarkerBravoWasReached()) {
        o.liveMarkerBravoTS(new Date(dat[i].liveMarkerBravoTS * 1000));
      }      
      o.liveMarkerCharlieWasReached(dat[i].liveMarkerCharlieWasReached);
      if(o.liveMarkerCharlieWasReached()) {
        o.liveMarkerCharlieTS(new Date(dat[i].liveMarkerCharlieTS * 1000));
      }     
      o.liveMarkerDeltaWasReached(dat[i].liveMarkerDeltaWasReached);
      if(o.liveMarkerDeltaWasReached()) {
        o.liveMarkerDeltaTS(new Date(dat[i].liveMarkerDeltaTS * 1000));
      }      
      liveScanModel.livescans.push(o);
    }     
    liveScanModel.labelIndex = i;   
  });
  //setInterval(updateLiveScan, 20000);
  setInterval(loopTimer, 1000);
  setInterval(dataAgeCalc, 60000);
}

function changeDetected () {
  adminVesselsModel.formChanged(true);
  console.log('formChanged(true)');
}


function getKeyOfId(arr, id) {
  var key = -1, count = 0;
  ko.utils.arrayForEach(arr, function (obj) {
    if(id == obj.id()) {
      key = count;
    }
    count++;
  });  return key;
}

function getShipSpriteCoords(course) {
  if(course >=   0 && course <=  15) return [  0,   0];
  if(course >=  16 && course <=  30) return [ 55,   0];
  if(course >=  31 && course <=  45) return [110,   0];
  if(course >=  46 && course <=  60) return [165,   0];
  if(course >=  61 && course <=  75) return [220,   0];
  if(course >=  76 && course <=  90) return [275,   0];
  if(course >=  91 && course <= 105) return [  0,  55];
  if(course >= 106 && course <= 120) return [ 55,  55];
  if(course >= 121 && course <= 135) return [110,  55];
  if(course >= 136 && course <= 150) return [165,  55];
  if(course >= 151 && course <= 165) return [220,  55];
  if(course >= 166 && course <= 180) return [275,  55];
  if(course >= 181 && course <= 195) return [  0, 110];
  if(course >= 196 && course <= 210) return [ 55, 110];
  if(course >= 211 && course <= 225) return [110, 110];
  if(course >= 226 && course <= 240) return [165, 110];
  if(course >= 241 && course <= 255) return [220, 110];
  if(course >= 256 && course <= 270) return [275, 110];
  if(course >= 271 && course <= 285) return [  0, 165];
  if(course >= 286 && course <= 300) return [ 55, 165];
  if(course >= 301 && course <= 315) return [110, 165];
  if(course >= 316 && course <= 330) return [165, 165];
  if(course >= 331 && course <= 345) return [220, 165];
  if(course >= 346)                  return [275, 165];
}

function loopTimer() {
  //Run updateLiveScan() on one of every 20 loops...
  console.log("loop:"+liveScanModel.loopCount);
  if(liveScanModel.loopCount>19) {
    liveScanModel.loopCount = 0;
    updateLiveScan();
    return;
  }
  //...otherwise run predictMovement()
  liveScanModel.loopCount++;
  predictMovement();
}

function updateLiveScan() {
  $.getJSON(liveScanModel.url, {}, function(dat) {
    var o, icon, marker, coords, course, key = null, now;

    //Loop inbound data array
    for(var i=0, len=dat.length; i<len; i++) {
      key = getKeyOfId(liveScanModel.livescans(), dat[i].id); 
      if(key > -1) {
        o = liveScanModel.livescans()[key];
        o.dir(dat[i].dir);
        o.position(new google.maps.LatLng(dat[i].position.lat, dat[i].position.lng));
        o.liveLocation(dat[i].liveLocation);
        o.speed(dat[i].speed);
        o.course(dat[i].course);
        o.lat(dat[i].position.lat);
        o.lng(dat[i].position.lng);
        o.marker().setPosition(new google.maps.LatLng(dat[i].position.lat, dat[i].position.lng));
        // ICON ADD ON 4/21/21...
        course = parseInt(dat[i].course.slice(0,-3));
        coords = getShipSpriteCoords(course);
        icon = {
          url: "https://www.clintonrivertraffic.com/images/ship-icon-sprite-cyan.png",
          origin: new google.maps.Point(coords[0], coords[1]),
          size: new google.maps.Size(55, 55)
        }
        o.marker().setIcon(icon);

        //Remove 'kts' from speed & change to int for a movement test
        var speed = parseInt(o.speed().slice(0,-3));
        if(speed>0) { //If transponder reported movement...
          if((o.lng() != o.prevLng()) || (o.lat() != o.prevLat())) { //...did its location change?           
            //Yes means the transponder report is current. Update time value.
            now = Date.now();          
            o.lastMovementTS().setTime(now);
            o.isMoving(true);
            //Reported speed with no position change means stale data. Don't update time value.
          } else {
            o.isMoving(false);
          }
        } //0 speed & 0 movement is ok. Just means vessel is idle
        o.prevLat(o.lat());
        o.prevLng(o.lng());
        o.liveMarkerAlphaWasReached(dat[i].liveMarkerAlphaWasReached);
        o.liveMarkerBravoWasReached(dat[i].liveMarkerBravoWasReached);
        o.liveMarkerCharlieWasReached(dat[i].liveMarkerCharlieWasReached);
        o.liveMarkerDeltaWasReached(dat[i].liveMarkerDeltaWasReached);
        if(o.liveMarkerAlphaWasReached()) {
          if(dat[i].liveMarkerAlphaTS != null) {
            o.liveMarkerAlphaTS(new Date(dat[i].liveMarkerAlphaTS * 1000));
          }          
        }
        if(o.liveMarkerBravoWasReached()) {
          if(dat[i].liveMarkerBravoTS != null) {
            o.liveMarkerBravoTS(new Date(dat[i].liveMarkerBravoTS * 1000));
          }          
        }
        
        if(o.liveMarkerCharlieWasReached()) {
          if(dat[i].liveMarkerCharlieTS != null) {
            o.liveMarkerCharlieTS(new Date(dat[i].liveMarkerCharlieTS * 1000));
          }  
        }        
        if(o.liveMarkerDeltaWasReached()) {
          if(dat[i].liveMarkerDeltaTS != null) {
            o.liveMarkerDeltaTS(new Date(dat[i].liveMarkerDeltaTS * 1000));
          }  
        }                
        o.liveLastScanTS().setTime(dat[i].liveLastScanTS * 1000);
        o.plotTS = dat[i].liveLastScanTS;
        //o.timerOutput("");
        o.hasImage(dat[i].vessel.vesselHasImage);
        o.imageUrl(dat[i].vessel.vesselImageUrl);
        o.type(dat[i].vessel.vesselType);
      } else {
        o = new LiveScan();
        console.log("Adding new vessel " + dat[i].name);
        o.liveLastScanTS(new Date(dat[i].liveLastScanTS * 1000));
        o.position(dat[i].position);
        o.lat(dat[i].position.lat);
        o.lng(dat[i].position.lng);
        o.id(dat[i].id);
        o.name(dat[i].name);
        o.liveLocation(dat[i].liveLocation);
        o.mapLabel = lab[++liveScanModel.labelIndex];
        o.dir(dat[i].dir);
        o.callsign(dat[i].callsign);
        o.speed(dat[i].speed);
        o.course(dat[i].course);
        o.width(dat[i].width);
        o.draft(dat[i].draft);
        o.hasImage(dat[i].vessel.vesselHasImage);
        o.imageUrl(dat[i].vessel.vesselImageUrl);
        o.type(dat[i].vessel.vesselType);
        // ICON ADD ON 4/21/21...
        course = parseInt(dat[i].course.slice(0,-3));
        coords = getShipSpriteCoords(course);
        icon = {
          url: "https://www.clintonrivertraffic.com/images/ship-icon-sprite-cyan.png",
          origin: new google.maps.Point(coords[0], coords[1]),
          size: new google.maps.Size(55, 55)
        }
        marker = new google.maps.Marker({
          position: new google.maps.LatLng(dat[i].position.lat, dat[i].position.lng),
          title: dat[i].name, 
          label: o.mapLabel,
          icon: icon, 
          map: map
        });
        o.marker(marker);
        o.liveMarkerAlphaWasReached(dat[i].liveMarkerAlphaWasReached);
        o.liveMarkerAlphaTS(new Date(dat[i].liveMarkerAlphaTS * 1000));
        o.liveMarkerBravoWasReached(dat[i].liveMarkerBravoWasReached);
        o.liveMarkerBravoTS(new Date(dat[i].liveMarkerBravoTS * 1000));
        o.liveMarkerCharlieWasReached(dat[i].liveMarkerCharlieWasReached);
        o.liveMarkerCharlieTS(new Date(dat[i].liveMarkerCharlieTS * 1000));
        o.liveMarkerDeltaWasReached(dat[i].liveMarkerDeltaWasReached);
        o.liveMarkerDeltaTS(new Date(dat[i].liveMarkerDeltaTS * 1000));
        liveScanModel.livescans.push(o);
      }
    }    
  });
  deleteOldScans();
}

function predictMovement() {
  var speed, distance, bearing, point, coords, icon;
  //Loop through live vessels
  ko.utils.arrayForEach(liveScanModel.livescans(), function (o) {
    //Skip if vessel not moving or bogus position data
    if( o.isMoving() && (o.lat() > 1) && (o.lng() > 1) ) {
      //Remove 'kts' from speed & change to int 
      speed = parseInt(o.speed().slice(0,-3));
      //Multiply knots by 1.852 to get KPH
      speed = speed * 1.852;
      //Divide KPH by 3600 to get kilometers traveled in one second
      distance = speed / 3600;
      //Clean course 
      bearing = parseInt(o.course().slice(0,-3));
      //Predict next point
      console.log(o.name() +" moved "+distance+ "KM");
      point = calculateNewPositionFromBearingDistance(o.lat(), o.lng(), bearing, distance);
      //Update view model
      o.lat(point[0]);
      o.lng(point[1]);
      o.marker().setPosition(new google.maps.LatLng(point[0], point[1]));
      // Do more
      coords = getShipSpriteCoords(bearing);
      icon = {
        url: "https://www.clintonrivertraffic.com/images/ship-icon-sprite-cyan.png",
        origin: new google.maps.Point(coords[0], coords[1]),
        size: new google.maps.Size(55, 55)
      }
      o.marker().setIcon(icon);     
    }
  });  
}



function calculateNewPositionFromBearingDistance(lat, lng, bearing, distance) {
  var R = 6371; // Earth Radius in Km
  var lat2 = Math.asin(Math.sin(Math.PI / 180 * lat) * Math.cos(distance / R) + Math.cos(Math.PI / 180 * lat) * Math.sin(distance / R) * Math.cos(Math.PI / 180 * bearing));
  var lon2 = Math.PI / 180 * lng + Math.atan2(Math.sin( Math.PI / 180 * bearing) * Math.sin(distance / R) * Math.cos( Math.PI / 180 * lat ), Math.cos(distance / R) - Math.sin( Math.PI / 180 * lat) * Math.sin(lat2));
  return [180 / Math.PI * lat2 , 180 / Math.PI * lon2];
}


function dataAgeCalc() {
  var now = Date.now(),  tt, arr=liveScanModel.livescans();
  for(var i=0, len=arr.length;  i<len; i++) {
    tt = Math.floor((now-arr[i].lastMovementTS().getTime())/60000);
    //console.log("dataAgeCalc(): tt floor value = "+tt);
    if(tt <  5)            { 
      arr[i].dataAge("age-green"); 
      //console.log(arr[i].name()+" is age-green at "+tt);
    }
    if(tt >  4 && tt < 15) { 
      arr[i].dataAge("age-yellow"); 
      //console.log(arr[i].name()+" is age-yellow at "+tt);
    }
    if(tt > 14 && tt < 30) { 
      arr[i].dataAge("age-orange"); 
      //console.log(arr[i].name()+" is age-orange at "+tt);
    }
    if(tt > 29)            { 
      arr[i].dataAge("age-brown");  
      //console.log(arr[i].name()+" is age-brown at "+tt);
    }   
    if(tt > 30)            { 
      console.log("Removing "+arr[i].name()+" as outdated."); 
      liveScanModel.livescans.splice(i,1); }
  }
} 

function deleteOldScans() {
  var a, l = 0, arr = [], i = 0, now = Date.now();
  ko.utils.arrayForEach(liveScanModel.livescans, function(obj) {
    if((now - 1800000)> obj.lastMovementTS().getTime()) {
      arr.push(i); //array of indexes to remove    
    }
    i++;
  });
  l = arr.length;
  if(l) { //proceed only if any found above
    for(a in arr) {
      liveScanModel.livescans(liveScanModel.livescans().splice(a,1));
    }    
  }    
}

function addMileMarkers() {
  var dat = [
    {id:486, lngA:-90.50971806363766, latA:41.52215220467504, lngB:-90.5092203536731, latB:41.51372097487243}, 
    {id:487, lngA:-90.48875678287305, latA:41.521402024002950, lngB:-90.48856266269104, latB:41.5145424556308},
    {id:488, lngA:-90.47251555885472, latA:41.52437816051497, lngB:-90.47036467716465, latB:41.51537456609466},
    {id:489, lngA:-90.45698288389242, latA:41.53057735758976, lngB:-90.45000250745086, latB:41.52480546208061},
    {id:490, lngA:-90.4461928429114, latA:41.54182560886835, lngB:-90.43804967962095, latB:41.53668343008653},
    {id:491, lngA:-90.43225148614556, latA:41.55492191671779, lngB:-90.42465891516093, latB:41.54714647168962},
    {id:492, lngA:-90.42215634673808, latA:41.56423876538352, lngB:-90.41359632007243, latB:41.55879211219473},
    {id:493, lngA:-90.40755589318907, latA:41.57200066107595, lngB:-90.40121765684347, latB:41.56578132917156},
    {id:494, lngA:-90.39384285792221, latA:41.57842796885789, lngB:-90.38766103940617, latB:41.57132529050489},
    {id:495, lngA:-90.37455561078977, latA:41.58171517893158, lngB:-90.37097459099577, latB:41.57455780093269},
    {id:496, lngA:-90.35418070340366, latA:41.5875726488084, lngB:-90.34989801453619, latB:41.58193114855811},
    {id:497, lngA:-90.34328730016247, latA:41.59576427084198, lngB:-90.33608085417411, latB:41.59502112101575},
    {id:498, lngA:-90.34404272829823, latA:41.61119012348694, lngB:-90.33646143861851, latB:41.6111032102589},
    {id:499, lngA:-90.3472745860646, latA:41.62454773858045, lngB:-90.33663122233754, latB:41.62387063319586},
    {id:500, lngA:-90.3480736269221, latA:41.63971945269969, lngB:-90.33817941381621, latB:41.63955239006518},
    {id:501, lngA:-90.34380831321272, latA:41.65683003496228, lngB:-90.33649979303949, latB:41.65484790099703},
    {id:502, lngA:-90.33988256792307, latA:41.66828476005874, lngB:-90.3286147300638, latB:41.66790001449647},
    {id:503, lngA:-90.33882199131011, latA:41.68036827724283, lngB:-90.32843393740198, latB:41.6798418644646},
    {id:504, lngA:-90.32382303252616, latA:41.69122269168967, lngB:-90.31540075610307, latB:41.68607027095535},
    {id:505, lngA:-90.31560815565506, latA:41.70162133249737, lngB:-90.31077421309571, latB:41.70093421981962},
    {id:506, lngA:-90.32324160813617, latA:41.71865527148766, lngB:-90.3144828164786, latB:41.71893714129034},
    {id:507, lngA:-90.32043157100178, latA:41.73305742526379, lngB:-90.31219715829357, latB:41.73209034176453},
    {id:508, lngA:-90.30911551889101, latA:41.74805205206862, lngB:-90.30381674016407, latB:41.74473810650169},
    {id:509, lngA:-90.29387889379554, latA:41.75940105234584, lngB:-90.29012440316585, latB:41.7570342469618},
    {id:510, lngA:-90.28216840054604, latA:41.76853414046849, lngB:-90.27848788377898, latB:41.76498972749543},
    {id:511, lngA:-90.2654809443937, latA:41.77464017600214, lngB:-90.26200151315392, latB:41.770651247585950},
    {id:512, lngA:-90.24800719074986, latA:41.7843434632554, lngB:-90.24263626100766, latB:41.77880910965498},
    {id:513, lngA:-90.23473410036074, latA:41.79168622191222, lngB:-90.2284317808665, latB:41.78595112826723},
    {id:514, lngA:-90.2156953508097, latA:41.7973419581181, lngB:-90.21337944364016, latB:41.79404084443492},
    {id:515, lngA:-90.19822143802581, latA:41.8025198609788, lngB:-90.19581674354208, latB:41.79898355228364},
    {id:516, lngA:-90.18352536643455, latA:41.80932693789443, lngB:-90.17633565144088, latB:41.80648691881999},
    {id:517, lngA:-90.18485994749022, latA:41.8234823269278, lngB:-90.18032482162711, latB:41.82393548957531},
    {id:518, lngA:-90.18522602598576, latA:41.83743971204904, lngB:-90.18253482993897, latB:41.83749106584514},
    {id:519, lngA:-90.17908346056349, latA:41.8513020234478, lngB:-90.17295527825956, latB:41.850379130804},
    {id:521, lngA:-90.17297767304423, latA:41.87737306056449, lngB:-90.16660198044828, latB:41.8760873927711},
    {id:522, lngA:-90.16238975538499, latA:41.89065244219969, lngB:-90.15871961546813, latB:41.88892630366035},
    {id:523, lngA:-90.15857648612955, latA:41.9046208778465, lngB:-90.15204920435555, latB:41.90255202787517},
    {id:524, lngA:-90.15922331108948, latA:41.91811211350211, lngB:-90.14839637939535, latB:41.91635929261279},
    {id:525, lngA:-90.15792090703236, latA:41.92858462810474, lngB:-90.15049176877096, latB:41.92853047111586},
    {id:526, lngA:-90.15891810761379, latA:41.94107477739816, lngB:-90.15487203752069, latB:41.94093309207638},
    {id:527, lngA:-90.15826136438079, latA:41.95580911610016, lngB:-90.15471943137281, latB:41.95564703478067},
    {id:528, lngA:-90.15816804572817, latA:41.96889364389622, lngB:-90.15019283497713, latB:41.96669934783529},
    {id:529, lngA:-90.14371602128973, latA:41.9845638044717, lngB:-90.13909858199787, latB:41.98346713433521},
    {id:530, lngA:-90.14570019987397, latA:41.99916325249763, lngB:-90.13663421169025, latB:41.99881259011114},
    {id:531, lngA:-90.14783536451105, latA:42.01128988436283, lngB:-90.13865142581066, latB:42.01213328702114},
    {id:532, lngA:-90.15182067613138, latA:42.02602246774179, lngB:-90.14629338851498, latB:42.02665221822929},
    {id:533, lngA:-90.16063756667363, latA:42.03651491321578, lngB:-90.15151752534508, latB:42.03730169372241},
    {id:534, lngA:-90.16890457045166, latA:42.04885717910146, lngB:-90.16066649304122, latB:42.04930465441836},
    {id:535, lngA:-90.16873927252988, latA:42.06458933574678, lngB:-90.16266001168944, latB:42.06507225175709},
    {id:536, lngA:-90.16914609409496, latA:42.0804515612181, lngB:-90.16249823994366, latB:42.07970814767357},
    {id:537, lngA:-90.16729803875997, latA:42.09221812981502, lngB:-90.1579947493362, latB:42.09136054497117},
    {id:538, lngA:-90.16382083849952, latA:42.10622273166468, lngB:-90.15894760458957, latB:42.10600456364353},
    {id:539, lngA:-90.16773051913361, latA:42.11833177709393, lngB:-90.16024166340684, latB:42.12179322620005},
    {id:540, lngA:-90.18197341024099, latA:42.12474496670414, lngB:-90.18304430150994, latB:42.12795599576975},
    {id:520, lngA:-90.17610039282224, latA:41.86515500754595, lngB:-90.17058699252856, latB:41.86429560522607}  
  ];
  
  if(liveScanModel.markerList.length == 0) {
    console.log("Loading markerList array data");
    for(var i=0, len=dat.length; i<len; i++) {
      liveScanModel.markerList[i] = {
        line: new google.maps.Polyline({
          path: [
            new google.maps.LatLng(dat[i].latA, dat[i].lngA),
            new google.maps.LatLng(dat[i].latB, dat[i].lngB)
          ],
          strokeColor: "#34A16B",
          strokeWeight: 2
        }),
        info: new google.maps.InfoWindow({
          content: "Mile "+dat[i].id,
          position: new google.maps.LatLng(dat[i].latA, dat[i].lngA)
        })
      };
      liveScanModel.markerList[i].line.setMap(map);
    }
  }     
}


function formatTime(ts) {
  var d, day, days, dh, h, m, merd, str;
  if(ts=="Not Yet Reached") { return ts; }
  ts = new Date(ts);
  h = ts.getHours();
  m = ts.getMinutes();
  if(m < 10) { m = "0" + m; }
  d = ts.getDay();
  days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
  day  = days[d];
  merd = h>=12 ? 'pm':'am';
  if(h>12) { 
    dh = h-12; 
  } else if(h==0) {
    dh = 12;
  } else {
    dh = h;
  }
  str = dh +":"+m+merd+" "+day;
  return str;
}

var liveScanModel = new LiveScanModel();


var map, 
  red="#ff0000",
  lab = "ABCDEFGHIJKLMNOPQRSTUVWXYZ*#@&~1234567890abcdefghijklmnopqrstuvwxyz";

function initMap() {   
  var alphaLine, bravoLine, charlieLine, deltaLine, milemarkers;  
  map = new google.maps.Map(document.getElementById("map"), {
    zoom: 12, center: liveScanModel.clinton, mapTypeId: "hybrid"
  });
    
  alphaLine = new google.maps.Polyline({
    path: [{lat: 41.938785, lng: -90.173893}, {lat: 41.938785, lng: -90.108296}],
    strokeColor: red,
    strokeWeight: 2
  });
  alphaLine.setMap(map);

  bravoLine = new google.maps.Polyline({
    path: [{lat: 41.897258, lng: -90.174}, {lat: 41.897258, lng: -90.154058}],
    strokeColor: red,
    strokeWeight: 2
  });
  bravoLine.setMap(map);

  charlieLine = new google.maps.Polyline({
    path: [{lat: 41.836353, lng: -90.186610}, {lat: 41.836353, lng: -90.169705}],
    strokeColor: red,
    strokeWeight: 2
  });
  charlieLine.setMap(map);
  
  deltaLine = new google.maps.Polyline({
    path: [{lat: 41.800704, lng: -90.212768}, {lat: 41.800704, lng: -90.188677}],
    strokeColor: red,
    strokeWeight: 2
  });
  deltaLine.setMap(map);
  addMileMarkers();
}




$( document ).ready(function() {
  ko.applyBindings(liveScanModel);
  initLiveScan();
  
});
