
var map, 
red="#ff0000";

function initMap() {   
    var alphaLine, bravoLine, charlieLine, deltaLine, mapCenter, marker;  
    mapCenter = new google.maps.LatLng(vesselPos.lat, vesselPos.lng);
    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 12, center: mapCenter, mapTypeId: "hybrid"
    });
    marker = new google.maps.Marker({
        position: new google.maps.LatLng(vesselPos.lat, vesselPos.lng),
        title: vesselName, 
        map: map
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

  