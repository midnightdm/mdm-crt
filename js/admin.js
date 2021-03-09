class Vessel {
  constructor() {
    this.vesselID            = ko.observable();
    this.vesselName          = ko.observable();
    this.vesselCallSign      = ko.observable();
    this.vesselType          = ko.observable();
    this.vesselLength        = ko.observable();
    this.vesselWidth         = ko.observable();
    this.vesselDraft         = ko.observable();
    this.vesselHasImage      = ko.observable();
    this.vesselImageUrl      = ko.observable();
    this.vesselOwner         = ko.observable();
    this.vesselBuilt         = ko.observable();
    this.vesselWatchOn       = ko.observable();    
    this.vesselRecordAddedTS = ko.observable();
    this.vesselWatchOnText   = ko.computed(function (){
      var that = this;
      if(that.vesselWatchOn()==1) {
        return "Yes";
      } else if(that.vesselWatchOn()==0) {
        return "No";
      } else {
        return that.vesselWatchOn();
      }
    }, this);
    this.vesselRecordAddedDate = ko.computed(function() {
      var that = this;
      if(that.vesselRecordAddedTS()!==null) {
        return formatTime(that.vesselRecordAddedTS());
      } else {
        return "Unknown";
      }
    }, this); 
    this.toggleWatch = function() {
      var that = this;
      if(that.vesselWatchOn()==1) {
          that.vesselWatchOn(0);            
          apiVesselWatchOn(that.vesselID, 0, that);
      } else if(that.vesselWatchOn()==0) {            
          apiVesselWatchOn(that.vesselID, 1, that);     
      }
    }      
  }
}
  
function apiVesselWatchOn(id, watchOn, callBack) {
  $.post("api_vesselWatchOn", { vesselID: id, vesselWatchOn: watchOn })
      .done(function(data) {
      if(data.status == 200) {
          callback.vesselWatchOn(watchOn);
      }
  });
}

function initVesselsList() {
  console.log("Running initVesselsList()"); 
  var o, dat = vesselList;
  //Test whether vesselsList properly loaded with the page
  if(!Array.isArray(dat) || !dat.length) {
    alert("JSON Data Could Not Load\n "+ dat.length)
    
    $.getJSON(adminVesselsModel.url, {}, function(dat) {
      for(var i=0, len=dat.length; i<len; i++) {           
        o = new Vessel();      
        o.vesselRecordAddedTS(dat[i].vesselRecordAddedTS);
        o.vesselID(dat[i].vesselID);
        o.vesselName(dat[i].vesselName);
        o.vesselCallSign(dat[i].vesselCallSign);
        o.vesselType(dat[i].vesselType);
        o.vesselLength(dat[i].vesselLength);
        o.vesselWidth(dat[i].vesselWidth);
        o.vesselDraft(dat[i].vesselDraft);
        o.vesselHasImage(dat[i].vesselHasImage);
        o.vesselImageUrl(dat[i].vesselImageUrl);
        o.vesselOwner(dat[i].vesselOwner);
        o.vesselBuilt(dat[i].vesselBuilt);
        o.vesselWatchOn(dat[i].vesselWatchOn);              
        adminVesselsModel.vesselList.push(o);
      }
    }); 
    
  } else {
    for(var i=0, len=dat.length; i<len; i++) {           
      o = new Vessel();      
      o.vesselRecordAddedTS(dat[i].vesselRecordAddedTS);
      o.vesselID(dat[i].vesselID);
      o.vesselName(dat[i].vesselName);
      o.vesselCallSign(dat[i].vesselCallSign);
      o.vesselType(dat[i].vesselType);
      o.vesselLength(dat[i].vesselLength);
      o.vesselWidth(dat[i].vesselWidth);
      o.vesselDraft(dat[i].vesselDraft);
      o.vesselHasImage(dat[i].vesselHasImage);
      o.vesselImageUrl(dat[i].vesselImageUrl);
      o.vesselOwner(dat[i].vesselOwner);
      o.vesselBuilt(dat[i].vesselBuilt);
      o.vesselWatchOn(dat[i].vesselWatchOn);              
      adminVesselsModel.vesselList.push(o);
    }
    console.log("vesselsList length="+dat.length);
  } 
  //Initialize detail view with first vessel so it's not null
  adminVesselsModel.vesselDetail(adminVesselsModel.vesselList()[0]);  
  console.log("vesselDetail.vesselID="+adminVesselsModel.vesselDetail().vesselID());            
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
  
function formatTime(ts) {
  var d, day, days, dh, h, m, merd, str;
  ts = new Date(ts*1000);
  /*
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
  */
  return ts.toDateString();
}  


//Usage arrayName.sort(compareType) or arrayName.sort(compareName)

function compareType(a, b) {
  if (a.vesselType() < b.vesselType()) return -1;
  if (a.vesselType() > b.vesselType()) return 1;
  return 0;
}

function compareName(a, b) {
  if (a.vesselName() < b.vesselName())  return -1;
  if (a.vesselName() > b.vesselName()) return 1;    
  return 0;
}
function compareDate(a, b) {
  return a.vesselRecordAddedTS() - b.vesselRecordAddedTS();
}  

function compareWatch(a, b) {
  return a.vesselWatchOn() - b.vesselWatchOn();
} 


function VesselsModel() {
  var self = this;
  self.vesselList = ko.observableArray([]);
  self.vesselDetail = ko.observable(null);
  self.listStatus = ko.observable('All');
  self.listSort = ko.observable('Name');  
  self.listDirection = ko.observable('desc')
  self.pageView = ko.observable('viewList');
  self.filter = ko.observable('All');
  self.watchedLinkIsSelected = ko.observable(false);
  self.allLinkIsSelected = ko.observable(true);
  self.url = "api_vessels";
  self.sortByType = function () {
    self.listSort('Type');
    if(self.listDirection()=="desc") {
      self.vesselList().reverse(compareType);
      self.listDirection('asc');
    } else if(self.listDirection()=="asc") {
      self.vesselList().sort(compareType);
      self.listDirection('desc');
    }  
    console.log("Sort is now by "+self.listSort()+", "+self.listDirection());  
  };
  self.sortByName = function () {
    self.listSort('Name');
    if(self.listDirection()=="desc") {
      self.vesselList().reverse(compareName);
      self.listDirection('asc');
    } else if(self.listDirection()=="asc") {
      self.vesselList().sort(compareName);
      self.listDirection('desc');
    }
    console.log("Sort is now by "+self.listSort()+", "+self.listDirection()); 
  };
  self.sortByWatch = function () {
    self.listSort('Watched');
    if(self.listDirection()=="desc") {
      self.vesselList().reverse(compareWatch);
      self.listDirection('asc');
    } else if(self.listDirection()=="asc") {
      self.vesselList().sort(compareWatch);
      self.listDirection('desc');
    }
    console.log("Sort is now by "+self.listSort()+", "+self.listDirection()); 
  };
  self.sortByDate = function () {
    self.listSort('Date');
    if(self.listDirection()=="desc") {
      self.vesselList().reverse(compareDate);
      self.listDirection('asc');
    } else if(self.listDirection()=="asc") {
      self.vesselList().sort(compareDate);
      self.listDirection('desc');
    }
    console.log("Sort is now by "+self.listSort()+", "+self.listDirection()); 
  };
  self.switchFilter = function (key) {
    self.filter(key);
    self.pageView('viewList');
    var all = key=="All" ? true : false;
    self.allLinkIsSelected(all);
    self.watchedLinkIsSelected(!all);
    console.log("filter changed to "+self.filter());
    //$('nav-link selected').removeClass('selected');
    //$('#' + key + 'Link').addClass('selected');
  };
  self.switchEditView = function (index) {
    self.pageView('viewDetail');
    self.vesselDetail(self.vesselList()[index]);
  };
  self.vesselListFiltered = ko.computed(function () {
    if (self.filter() == "All") {
      return self.vesselList();
    } else {
      return ko.utils.arrayFilter(self.vesselList(), function (i) {
        return i.vesselWatchOn() == 1;
      });
    }
  }, self);
  
  console.log("pageView= "+self.pageView()+'\n'
    +"listSort= "+self.listSort()+'\n'
    +"filter= "+self.filter()
  )
}

var adminVesselsModel = new VesselsModel();


/*
var adminVesselsModel = {
  vesselList: ko.observableArray([]),
  vesselDetail: ko.observable(null),
  listStatus: ko.observable('All'),
  listSort: ko.observable('Name'),
  pageView: ko.observable('viewList'),
  filter: ko.observable('All'),
  url: "api_vessels",
  sortByType: function() {
    this.vesselList().sort('compareType');   
  },
  sortByName: function() {
    this.vesselList().sort('compareName');   
  },
  sortByWatch: function() {
    this.vesselList().sort('compareWatch');   
  },
  sortByDate: function() {
    this.vesselList().sort('compareDate');   
  },
  switchFilter: function(filter) {
    $('nav-link selected').removeClass('selected');    
    this.pageView('viewList');
    $('#'+filter+'Link').addClass('selected');
    this.filter(filter);
  },
  switchEditView: function(index) {    
    this.pageView('viewDetail');
    this.vesselDetail(this.vesselList()[index])
  }
}; 

adminVesselsModel.vesselListFiltered = ko.computed(function(){
    if(this.filter()=="All") {
      return this.vesselList();
    } else {
      return ko.utils.arrayFilter(this.vesselList(), function(i) {
       return i.type == this.filter();
      });
    }
  }, adminVesselsModel);

*/

$( document ).ready(function() {  
  initVesselsList();
  ko.applyBindings(adminVesselsModel);
});

  