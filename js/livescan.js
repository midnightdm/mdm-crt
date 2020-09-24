//LiveScan Object class
class LiveScan {
  constructor() {
    this.liveLastScanTS = ko.observable();
    this.position       = ko.observable();
    this.lat            = ko.observable();
    this.lng            = ko.observable();
    this.id             = ko.observable();
    this.name           = ko.observable();
    this.mapLabel       = null;
    this.btnText        = ko.observable("+");
    this.dir            = ko.observable("undetermined");
    this.callsign       = ko.observable();
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
    this.prevLat                   = ko.observable();
    this.prevLng                   = ko.observable();
    this.lastMovementAgo           = ko.computed(function () {
      var now  = Date.now();
      var diff = (now - this.lastMovementTS().getTime())/60000;
      return diff>1 ? diff + " Minutes Ago" : "Current";
    }, this);
    this.dataAge = ko.computed(function () {
      var now = Date.now(), 
      tt = (now-this.lastMovementTS().getTime())/60000;
      if(tt <  5) return "age-green"; 
      if(tt < 15) return "age-yellow";
      if(tt < 30) return "age-orange"; 
      if(tt > 29) return "age-brown";     
    }, this);
    this.dirImg = ko.computed(function () {
      switch(this.dir()) {
        case "undetermined": return "images/qmark.png"; break;
        case "upriver"     : return "images/uparr.png"; break;
        case "downriver"   : return "images/dwnarr.png"; break;
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
    }
  }
};

function initLiveScan() {
  $.getJSON(liveScanModel.url, {}, function(dat) {
    var key, o, marker;
    for(var i=0, len=dat.length; i<len; i++) {           
      o = new LiveScan();      
      o.liveLastScanTS(new Date(dat[i].liveLastScanTS * 1000));
      o.position(new google.maps.LatLng(dat[i].position.lat, dat[i].position.lng));
      o.lat(dat[i].position.lat);
      o.lng(dat[i].position.lng);
      o.id(dat[i].id);
      o.name(dat[i].name);
      o.mapLabel = lab[++liveScanModel.labelIndex];
      o.dir(dat[i].dir);
      o.callsign(dat[i].callsign);
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
      marker = new google.maps.Marker({
        position: new google.maps.LatLng(dat[i].position.lat, dat[i].position.lng),
        title: dat[i].name, 
        label: o.mapLabel, 
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
  setInterval(updateLiveScan, 30000);
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

function updateLiveScan() {
  $.getJSON(liveScanModel.url, {}, function(dat) {
    var o, marker, key = null;
    //Loop inbount data array
    for(var i=0, len=dat.length; i<len; i++) {
      key = getKeyOfId(liveScanModel.livescans(), dat[i].id); 
      if(key > -1) {
        o = liveScanModel.livescans()[key];
        o.dir(dat[i].dir);
        o.position(new google.maps.LatLng(dat[i].position.lat, dat[i].position.lng));
        o.speed(dat[i].speed);
        o.course(dat[i].course);
        o.lat(dat[i].position.lat);
        o.lng(dat[i].position.lng);
        o.marker().setPosition(new google.maps.LatLng(dat[i].position.lat, dat[i].position.lng));
        if(o.lng() != o.prevLng() || o.lat() != o.prevLat()) {
          o.lastMovementTS().setTime(Date.now());
        }
        o.prevLat(o.lat());
        o.prevLng(o.lng());
        o.liveMarkerAlphaWasReached(dat[i].liveMarkerAlphaWasReached);
        o.liveMarkerBravoWasReached(dat[i].liveMarkerBravoWasReached);
        o.liveMarkerCharlieWasReached(dat[i].liveMarkerCharlieWasReached);
        o.liveMarkerDeltaWasReached(dat[i].liveMarkerDeltaWasReached);
        if(o.liveMarkerAlphaWasReached()) {
          o.liveMarkerAlphaTS().setTime(dat[i].liveMarkerAlphaTS * 1000);
        }
        if(o.liveMarkerBravoWasReached()) {
          o.liveMarkerBravoTS().setTime(dat[i].liveMarkerBravoTS * 1000);
        }
        
        if(o.liveMarkerCharlieWasReached()) {
          o.liveMarkerCharlieTS().setTime(dat[i].liveMarkerCharlieTS * 1000);
        }        
        if(o.liveMarkerDeltaWasReached()) {
          o.liveMarkerDeltaTS().setTime(dat[i].liveMarkerDeltaTS * 1000);
        }                
        o.liveLastScanTS().setTime(dat[i].liveLastScanTS * 1000);
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
        marker = new google.maps.Marker({
          position: new google.maps.LatLng(dat[i].position.lat, dat[i].position.lng),
          title: dat[i].name, 
          label: o.mapLabel, 
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

function deleteOldScans() {
  var a, l = 0, arr = [], i = 0, now = Date.now();
  ko.utils.arrayForEach(liveScanModel.livescans, function(obj) {
    if((now - 1800000)> obj.liveLastScanTS().getTime()) {
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

var liveScanModel = {
  livescans: ko.observableArray([]),
  clinton: {lat: 41.857202, lng:-90.184084},
  url: "livescanjson",
  INTERVAL: 15000,
  labelIndex: 0    
};
  
var map, 
  red="#ff0000",
  lab = "ABCDEFGHIJKLMNOPQRSTUVWXYZ*#@&~1234567890abcdefghijklmnopqrstuvwxyz";

function initMap() {   
  var alphaLine, bravoLine, charlieLine, deltaLine;  
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
}

$( document ).ready(function() {
  ko.applyBindings(liveScanModel);
  initLiveScan();
});
