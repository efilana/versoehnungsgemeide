// Constructor
function MaintainView() {
  MaintainStandardView.call(this);
  this.name="MaintainView";
}

Temp.prototype = MaintainStandardView.prototype;
MaintainView.prototype = new Temp();

function getMaintainView() {
  return new MaintainView();
}

MaintainView.prototype.renderMenu = function() {
  this_object=this;
  
  if (masterData.cdb_gruppen==null) {
    var elem = form_showCancelDialog("Masterdaten werden geladen...","Bitte warten..");
    churchInterface.jsendRead({func:"getChurchDBMasterData" }, function(ok, json) {
      each(json, function(k,a) {
        masterData[k]=a;
      });
      elem.dialog("close");
      this_object.renderList();
    });
  }
  
  menu = new CC_Menu(_("menu"));
  menu.addEntry(_("back.to.main.menu"), "apersonview", "arrow-left");
  menu.addEntry(_("help"), "ahelp", "question-sign");

  if (!menu.renderDiv("cdb_menu"))
    $("#cdb_menu").hide();
  else {
    $("#cdb_menu a").click(function () {
      if ($(this).attr("id")=="apersonview") {
        churchInterface.setCurrentView(listView, false);
      }
      else if ($(this).attr("id")=="ahelp") {
        churchcore_openNewWindow("http://intern.churchtools.de/?q=help&doc=ChurchService-Stammdaten");
      }
      return false;
    });
  }  
};