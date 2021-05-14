// OBJECT DEFINITIONS ...

class Vessel {
  constructor() {
    this.localIndex          = null;
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
    this.vesselHasImageText   = ko.computed(function (){
      var that = this;
      if(that.vesselHasImage()==1) {
        return "Yes";
      } else if(that.vesselHasImage()==0) {
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
    this.tryImageUrl = function(this) {
      var that = this, j=false;
      if(j==false) {
        that.vesselImageUrl("https://www.clintonrivertraffic.com/vessels/jpg/"+that.vesselID);
        j=true;
      } else if(j==true) {
        that.vesselImageUrl("https://www.clintonrivertraffic.com/images/vessels/no-image-placard.jpg");
        j=false;
      }
    }
        
  }
}

class Log {
  constructor() {
    this.alogID            = null;
    this.alogAlertID       = null;
    this.alogType          = null;
    this.alogTS            = null;
    this.dateStr           = null; 
    this.alogDirection     = null;
    this.alogMessageType   = null;
    this.alogMessageTo     = null;
    this.alogMessageID     = null;
    this.alogMessageCost   = null;
    this.alogMessageStatus = null;
    this.recentUpdate       = ko.observable(false);
  }
}

// MODELS

function LogsModel() {
  var self = this;
  self.logsList = ko.observableArray([]);

}

function VesselsModel() {
  var self = this;
  self.vesselList = ko.observableArray([]);
  self.vesselDetail = ko.observable(null);
  self.detailSubscrption = null;
  //Status vars
  self.selectedView = ko.observable( {view:'viewList', idx: null} );
  self.selectedLink = ko.observable( {passenger: false, watched: false, all: true, add: false} );
  self.lastPage     = ko.observable('all');
  self.nowPage      = ko.observable('all');
  self.errorMsg     = ko.observable(null);
  self.formVesselID = ko.observable("");
  self.formEditOn   = ko.observable(false);
  self.formSaved    = ko.observable(false);
  self.formChanged  = ko.observable(false);
  //Message form inputs
  self.f_method  = ko.observable(null);
  self.f_subject = ko.observable(null);
  self.f_message = ko.observable(null);
  self.f_destination = ko.observable(null);
  self.clearMsgForm = function() {
    self.f_method(null);
    self.f_subject(null);
    self.f_message(null);
    self.f_destination(null);
  }
  
  self.url = "api_vessels";

  self.goToPage = function(index, name=null) {
    console.log("goToPage()");
    //Clear form status on all page refreshes
    self.errorMsg(null);
    self.formVesselID("");
    self.formSaved(false);
    self.formChanged(false);
    switch(name) {
      case "add": {
        var lastView = self.nowPage();
        self.selectedView({view:'viewAdd', idx: index});
        self.selectedLink( {passenger: false, watched: false, all: false, add: true, messages: false} );
        self.lastPage(lastView);
        self.nowPage('add');
        break;
      }
      case "all": {
        var lastView = self.nowPage();
        self.selectedView({view:'viewList', idx: index});
        self.selectedLink( {passenger: false, watched: false, all: true, add: false, messages: false} );
        self.lastPage(lastView);
        self.nowPage('all');
        break;
      }
      case "watched": {
        var lastView = self.nowPage();
        self.selectedView( {view:'viewList', idx: index} );
        self.selectedLink( {passenger: false, watched: true, all: false, add: false, messages: false} );
        self.lastPage(lastView);
        self.nowPage('watched');
        break;
      }
      case "detail":{
        var lastView = self.nowPage();
        self.selectedView( {view:'viewDetail', idx: index});
        self.vesselDetail(self.vesselList()[index]);
        self.lastPage(lastView);
        var reupVal = self.vesselDetail().vesselWatchOn()==1 ? true: false;
        self.vesselDetail().vesselWatchOn(reupVal);
        self.nowPage('detail');
        break;
      }  
      case "passenger": {
        var lastView = self.nowPage();
        self.selectedView( {view:'viewList', idx: index} );
        self.selectedLink( {passenger: true, watched: false, all: false, add: false, messages: false} );
        self.lastPage(lastView);
        self.nowPage('passenger');
        break;
      }   
      case "messages": {
        var lastView = self.nowPage();
        self.selectedView( {view:'viewMessages', idx: index} );
        self.selectedLink( {passenger: false, watched: false, all: false, add: false, messages:true} );
        self.lastPage(lastView);
        self.nowPage('messages');
        apiGetMessagesLog();
        break;
      }
    }
    console.log("pageView= "+self.selectedView().view +'\n'
    +"goToPage(index= "+ index + ", name= "+name +")" );
  };
  
  self.resetFormSaved = function() {
    self.formSaved(false);
  }

  self.vesselListFiltered = ko.computed(function () {  
    if (self.selectedLink().all) {
      return self.vesselList();
    } else if (self.selectedLink().passenger) {
      return ko.utils.arrayFilter(self.vesselList(), function (i) {        
        return  /\w*assenger\w*/.test(i.vesselType());
      });
    } else {  
      return ko.utils.arrayFilter(self.vesselList(), function (i) {
        return i.vesselWatchOn() == 1;
      });
    }
  }, self);

}


// API FUNCTIONS ...

  
function apiInsertNewVessel() {
  var o = adminVesselsModel.vesselDetail();
  $.post("api_SetVessel", {
    postType: "insert",
    vesselID: o.vesselID(), 
    vesselName: o.vesselName(),
    vesselCallSign: o.vesselCallSign(),
    vesselType: o.vesselType(),
    vesselLength: o.vesselLength(),
    vesselWidth: o.vesselWidth(),
    vesselDraft: o.vesselDraft(),
    vesselHasImage: o.vesselHasImage(),
    vesselImageUrl: o.vesselImageUrl(),
    vesselOwner: o.vesselOwner(),
    vesselBuilt: o.vesselBuilt(),
    vesselWatchOn: o.vesselWatchOn()
  }, 'json').done(function(data) {
    console.log(data);
    var dataR = JSON.parse(data);
    console.log(JSON.stringify(dataR));
    console.log("status: "+dataR.status+", code: "+dataR.code+", message: "+dataR.message);
    if(dataR.code == 400) {
      adminVesselsModel.errorMsg(dataR.message);
    } else if(dataR.code==200) {
      adminVesselsModel.formSaved(true);
      adminVesselsModel.formChanged(false);
      //setTimeout(adminVesselsModel.resetFormSaved, 5000);
      adminVesselsModel.vesselDetail().vesselRecordAddedTS(dataR.timestamp);
    }
  });
}

function apiUpdateVessel() {
  var o = adminVesselsModel.vesselDetail();
  $.post("api_SetVessel", {    
    postType: "update", 
    vesselID: o.vesselID(), 
    vesselName: o.vesselName(),
    vesselCallSign: o.vesselCallSign(),
    vesselType: o.vesselType(),
    vesselLength: o.vesselLength(),
    vesselWidth: o.vesselWidth(),
    vesselDraft: o.vesselDraft(),
    vesselHasImage: o.vesselHasImage(),
    vesselImageUrl: o.vesselImageUrl(),
    vesselOwner: o.vesselOwner(),
    vesselBuilt: o.vesselBuilt(),
    vesselWatchOn: o.vesselWatchOn() 
  }, 'json').done(function(data) {
    console.log(data);
    data = JSON.parse(data);
    console.log("submitted vesselWatchOn= "+o.vesselWatchOn());
    console.log("submitted vesselHasImage= "+o.vesselHasImage());
    console.log("status: "+data.status+", code: "+data.code+", message: "+data.message);
    if(data.code == 400) {
      adminVesselsModel.errorMsg(data.message);
    } else if(data.code==200) {
      adminVesselsModel.formSaved(true);
      setTimeout(adminVesselsModel.resetFormSaved, 5000);
      adminVesselsModel.formChanged(false);
      adminVesselsModel.vesselDetail().vesselRecordAddedTS(data.timestamp);
    }
  });
}

function apiGetMessagesLog() {
  if(adminVesselsModel.nowPage() != "messages") {
    console.log('apiGetMessagesLog() triggered when messages page not selected.');
    return;
  };
  console.log("apiGetMessagesLog()");
  $.post("api_getMessagesLog", { }, 'json')
      .done(function(data) {
        //console.log(JSON.stringify(data));
        var dataR = JSON.parse(data);
        console.log(JSON.stringify(dataR));
        console.log("apiGetMessagesLog() status: "+dataR.status+", code: "+dataR.code+", message: "+dataR.message);
        if(dataR.code == 400) {
          adminVesselsModel.errorMsg(dataR.message);
        } else if(dataR.code==200) {
          initLogsList(dataR.data);
        }
  });
}

function apiSendMessage() {
  if(adminVesselsModel.nowPage() != "messages") {
    console.log('apiSendMessage() triggered when messages page not selected.');
    return;
  };
  $.post("api_sendMessage", {
    alertMethod: adminVesselsModel.f_method(),
    alertDest: adminVesselsModel.f_destination(),
    text: adminVesselsModel.f_message(),
    subject: adminVesselsModel.f_subject(),
    event: null,
    dir: null,
    alertID: "Sent From Admin Page"
  }, 'json').done(function(data) {
    var dataR = JSON.parse(data);
        console.log(JSON.stringify(dataR));
        console.log("apiSendMessage() status: "+dataR.status+", code: "+dataR.code+", message: "+dataR.message);
        if(dataR.code == 400) {
          adminVesselsModel.errorMsg(dataR.message);
        } else if(dataR.code==200) {
          adminVesselsModel.clearMsgForm();
          apiGetMessagesLog();
          
        }
  });
}

function apiLookupVessel() {
  if(adminVesselsModel.nowPage() != "add") {
    console.log('apiLookupVessel() triggered when add page not selected.');
    return;
  };
  var id = adminVesselsModel.formVesselID();
  console.log("apiLookupVessel("+id+")");
  $.post("api_lookupVessel", { vesselID: id })
      .done(function(data) {
        data = JSON.parse(data);
        console.log(JSON.stringify(data));
        console.log("status: "+data.status+", code: "+data.code+", message: "+data.message);
        if(data.code == 400) {
          adminVesselsModel.errorMsg(data.message);
        } else if(data.code==200) {
          updateVesselsList(data.data);
          adminVesselsModel.formEditOn(true);
          adminVesselsModel.formSaved(false);
        }
  }, 'json');
}


// Object initialization & update functions

function updateVesselsList(dat) {
  var o;           
  o = new Vessel();
  o.localIndex = adminVesselsModel.vesselList().length;      
  o.vesselRecordAddedTS(dat.vesselRecordAddedTS);
  o.vesselID(dat.vesselID);
  o.vesselName(dat.vesselName);
  o.vesselCallSign(dat.vesselCallSign);
  o.vesselType(dat.vesselType);
  o.vesselLength(dat.vesselLength);
  o.vesselWidth(dat.vesselWidth);
  o.vesselDraft(dat.vesselDraft);
  o.vesselHasImage(dat.vesselHasImage);
  o.vesselImageUrl(dat.vesselImageUrl);
  o.vesselOwner(dat.vesselOwner);
  o.vesselBuilt(dat.vesselBuilt);
  o.vesselWatchOn(dat.vesselWatchOn);              
  adminVesselsModel.vesselList.push(o);
  adminVesselsModel.vesselDetail(o);
  console.log("vesselsList length="+adminVesselsModel.vesselList().length);            
}

function initVesselsList() {
  var o, dat = vesselList;
  for(var i=0, len=dat.length; i<len; i++) {           
    o = new Vessel();
    o.localIndex = i;      
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
  
  //Initialize detail view with first vessel so it's not null
  adminVesselsModel.vesselDetail(adminVesselsModel.vesselList()[0]);  
  console.log("vesselDetail.vesselID="+adminVesselsModel.vesselDetail().vesselID());            
}

function initLogsList(dat) {
  var o;
  //Purge any old list
  adminLogsModel.logsList([]);
  for(var i=0, len=dat.length; i<len; i++) {
    o = new Log();
    o.alogID            = dat[i].alogID;
    o.alogAlertID       = dat[i].alogAlertID;
    o.alogType          = dat[i].alogType;
    o.alogTS            = dat[i].alogTS;
    o.dateStr           = dat[i].dateStr;
    o.alogDirection     = dat[i].alogDirection;
    o.alogMessageType   = dat[i].alogMessageType;
    o.alogMessageTo     = dat[i].alogMessageTo;
    o.alogMessageID     = dat[i].alogMessageID
    o.alogMessageCost   = dat[i].alogMessageCost;
    o.alogMessageStatus = dat[i].alogMessageStatus;
    //Show as new if less than 10 minutes old
    if(((Math.round(Date.now()/1000)) - o.alogTS)<600 ) {
      o.recentUpdate(true);
    }
    adminLogsModel.logsList.push(o);
  } 
}


//Independent functions

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
  
function formatTime(ts) {
  var d, day, days, dh, h, m, merd, str;
  ts = new Date(ts*1000);
  return ts.toDateString();
}    

//Script main action
var adminVesselsModel = new VesselsModel(),
   adminLogsModel = new LogsModel();

$( document ).ready(function() {  
  initVesselsList();
  ko.applyBindings(adminVesselsModel);
  //ko.applyBindings(adminLogsModel);
});


