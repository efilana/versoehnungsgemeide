/**
 * Format the result of getEventChangeImpact()
 * @param {[type]} data
 * @param {[type]} func if confirm(true) otherwise (false)
 */
function confirmImpactOfEventChange(data, func) {
  var rows = new Array();
  var showConfirm = false;
  if (data.hint!=null) {
    rows.push('<p>' + data.hint);
    showConfirm = true;
  }

  // Render Impacts of Cal
  if (churchcore_countObjectElements(data.cal)>0) {
    rows.push('<h4>Änderungen in '+masterData.churchcal_name+'</h4><ul>');
    var dates = new Object();
    each(data.cal, function(k, a) {
      rows.push('<li>' +  'Datum: ' + a.startdate.toDateEn(false).toStringDe(false) + '<ul>');
      var fields = new Array();
      each(a.changes, function(j, c) {
        fields.push(_(j) + ": " + c["new"] + ' <span class="old">' + c["old"] + "</span>");
      });
      rows.push(fields.join(", "));
      rows.push('</ul>');
    });
    rows.push('</ul>');
  }
  // Render Impacts of bookings
  if (churchcore_countObjectElements(data.bookings)>0) {
    var dates = new Object();
    each(data.bookings, function(k, a) {
      if (a.status.indexOf("no.change")!=0) {
        if (dates[a.startdate] == null) dates[a.startdate] = new Array();
        dates[a.startdate].push(a);
      }
    });
    var rows_cr = new Array();
    each(dates, function(k, a) {
      rows_cr.push('<li>' +  'Datum: ' + k.toDateEn(false).toStringDe(false) + '<ul>');
      each(a, function(i,b) {
        rows_cr.push('<li>' + masterData.resources[b.booking.resource_id].bezeichnung+': ');
        if (b.changes == null) {
          rows_cr.push('<i>'+_(b.status)+'</i>');
        }
        else {
          showConfirm = true;
          var fields = new Array();
          each(b.changes, function(j, c) {
            fields.push(_(j) + ": " + c["new"] + ' <span class="old">' + c["old"] + "</span>");
          });
          rows_cr.push(fields.join(", "));
        }
      });
      rows_cr.push('</ul>');
    });
    if (rows_cr.length > 0) {
      rows.push('<h4>Auswirkung auf '+masterData.churchresource_name+'</h4><ul>');
      rows.push(rows_cr.join(""));
      rows.push('</ul>');
    }
  }

  // Render impact of services
  if (churchcore_countObjectElements(data.services)>0) {
    showConfirm=true;
    rows.push('<h4>Auswirkung auf '+masterData.churchservice_name+'</h4>');
    rows.push('<ul>');
    each(data.services, function(k,a) {
      rows.push('<li>' + a.date.toDateEn().toStringDe(true) +" " + a.service + ": " + a.name);
      if ( !a.confirmed ) rows.push('? <i>(unbestätigt)</i>');
      else rows.push('<i> (bestätigt)</i>');
    });
    rows.push('</ul>');
  }

  // Check if there is something to confirm
  if (rows.length==0 || !showConfirm) func(true);
  else {
    $txt = '<legend>Änderungen in anderen Modulen</legend><p>Die folgende Änderungen haben Auswirkungen '
           + 'auf andere Module. Bitte sorgsam prüfen!' + rows.join("");
    var elem = form_showDialog("Bestätigung der Auswirkungen", $txt, 500, 500, {
      "Ausführen": function() {
        elem.dialog("close");
        func(true);
      }
    });
    elem.dialog("addcancelbutton");
  }
}

function eventDifferentDates(a, b) {
  if (a.startdate.getTime()!=b.startdate.getTime() ||
      a.enddate.getTime()!=b.enddate.getTime())
    return true;

  if (a.repeat_id!=b.repeat_id)
    return true;

  if (a.repeat_id>0) {
    if ((a.repeat_until==null && b.repeat_id!=null) ||
      (a.repeat_until!=null && b.repeat_id==null) ||
      (a.repeat_until!=null && b.repeat_until!=null && a.repeat_until.getTime()!=b.repeat_until.getTime()))
    return true;
  }

  if (JSON.stringify(a.exceptions)!=JSON.stringify(b.exceptions))
    return true;

  if (JSON.stringify(a.additions)!=JSON.stringify(b.additions))
    return true;

  return false;
}

// Object CCEvent

/**
 * Read the date in format En or as Date. If d is null, then it returns null
 * @param {[type]} d
 */
function readDate(d) {
  if (d == null) return null;
  else if (d instanceof Date) return d;
  else return d.toDateEn(true);
}

function CCEvent(source) {
  var t = this;
  t.startdate = null
  t.enddate = null
  t.repeat_id = 0;
  t.name = "Event";
  t.saveSuccess = function() { alert("Please overwrite saveSuccess!") };
  t.saveSplitSuccess = function() { alert("Please overwrite saveSplitSuccess!") };
  each(source, function(k, a) {
    if (k == "startdate" || k == "enddate" || k == "cal_startdate"
            || k == "cal_enddate" || k == "repeat_until") t[k] = readDate(a);
    else t[k] = a;
  });
}

/**
 * Delete all functions of the event object
 */
CCEvent.prototype.clean = function() {
  var c = this.clone();
  each(c, function(k, a) {
    if ($.isFunction(a)) delete c[k];
  });
  return c;
}

CCEvent.prototype.clone = function () {
  var t = this;
  var e = jQuery.extend(true, {}, this);
  if (e.cc_cal_id == null) delete e.cc_cal_id;
  e.startdate = new Date(t.startdate.getTime());
  e.enddate = new Date(t.enddate.getTime());
  if (t.repeat_until!=null)
    e.repeat_until=new Date(t.repeat_until.getTime());
  each(t.csevents, function(k,a) {
    a.startdate = new Date(a.startdate.getTime());
  });
  return e;
};


/**
 * [_addException description]
 * @param {[type]} currentEvent
 * @param {[type]} date
 * @param {[type]} deleteCS default false, true means csevents will be deleted, otherwise action:delete
 */
CCEvent.prototype.addException = function (date, deleteCS) {
  var t = this;
  if (t.exceptions==null) t.exceptions = new Object();
  else if (t.exceptions instanceof Array) {
    // Change array to object, for adding -1 etc.
    t.exceptions = t.exceptions.reduce(function(o, v, i) {
      o[i] = v;
      return o;
    }, {});
  }
  if (t.exceptionids==null) t.exceptionids = 0;
  t.exceptionids = t.exceptionids-1;
  t.exceptions[t.exceptionids]
        ={id:t.exceptionids, except_date_start:date.toStringEn(), except_date_end:date.toStringEn()};
  // Add Exception for CS Events
  var csId = getCSEventId(t, date, true);
  if (csId!=null) {
    if (deleteCS==null || !deleteCS) t.csevents[csId].action="delete";
    else delete t.csevents[csId];
  }
}
/**
* check the necessary updates between newEvent and originEvent
* @func: func(false) if something is wrong, func(true) if is everything fine!
*/
CCEvent.prototype.save = function (originEvent, func) {
  var t = this;
  var o = t.clone();

  if (t.id != null) {
    o.func = "update" + t.name;
    o.currentEvent_id = t.id;
  }
  else
    o.func = "create" + t.name;

  churchInterface.jsendWrite(o, function(ok, data) {
    if (!ok) {
      alert(_("error.occured") + data);
      if (func != null) func(false);
    }
    else {
      t.saveSuccess(t, originEvent, data);
      if (func != null) func(true);
    }
  });
}

/**
 * Saves the splitted events to the server and refresh FullCalender
 * @param {Object} newEvent
 * @param {Object} pastEvent
 * @param {Object} originEvent
 * @param {Function} func func(flase) or func(true)
 */
CCEvent.prototype.saveSplitted = function (newEvent, pastEvent, splitDate, untilEnd, func) {
  var t = this;
  // If special case, pastEvent is not necessary (see doSplit() for more informations)
  // Second possiblite: No series, so no pastEvent
  // Third one: If I only click on delete, then newEvent is pastEvent to prevent creating new event
  if (pastEvent == null || pastEvent.id == newEvent.id) {
    newEvent.save(t, func);
  }
  else {
    var o = {
      func : "saveSplitted" + t.name,
      newEvent : newEvent,
      pastEvent : pastEvent,
      splitDate : splitDate,
      untilEnd_yn : ( untilEnd ? 1 : 0 )
    };
    if (t.type != null) o.type = t.type;
    churchInterface.jsendWrite(o, function(ok, data) {
      if (!ok) {
        alert(_("error.occured") + data);
        if (func!=null) func(false);
      }
      else {
        if (newEvent.id == null) newEvent.id = data.id;
        if (newEvent.bookings!=null) each(data.bookingIds, function(k,a) {
          newEvent.bookings[k].id = a;
          newEvent.bookings[a] = newEvent.bookings[k];
          delete newEvent.bookings[k];
        });
        t.saveSplitSuccess(newEvent, pastEvent, t);
        if (func!=null) func(true);
      }
    });
  }
}

/**
 * Is same Id
 * @param {Object} event
 */
CCEvent.prototype.isEqual = function (event) {
  return (event !=null && this.id == event.id);
};


/**
 * Ask user to doSplit. When event is no series, it will func(false)
 * @param {[type]} myEvent
 * @param {[type]} position (clientX, clientY)
 * @param {[type]} func (null = cancel || false=single event or single event in series || true=untilEnd)
 */
CCEvent.prototype.askForSplit = function (position, func) {
  var t = this;
  if (!t.isSeries()) func(false);
  // When no position is given, it is called from startpage, so display whole series
  else if (position == null || position.clientX == null) (func(true));
  else {
    $("#popupmenu").popupmenu({
      entries: ["Nur diesen Termin ändern", "Diesen und zukünftige ändern", "Abbruch"],
      pos: position,
      remove: function() {
        func(null);
      },
      click: function(result) {
        if (result==0) func(false);
        else if (result==1) func(true);
        else func(null);
      }
    });
    $("#popupmenu").popupmenu("show");
  }
};

/**
 * Splits the current Event into two, one for the past and one for the new one created.
 * If this is not a series, just return a cloned event and pastEvent is null
 * func(newEvent, pastEvent)
 */
CCEvent.prototype.doSplit = function (splitDate, untilEnd, func) {
  var t = this;
  var newEvent = t.clone();

  // if this is not a series, nothing to do!
  if (!t.isSeries()) {
    func(newEvent, null);
  }
  else {
    splitDate = splitDate.withoutTime();
    var delta = splitDate.getTime() - t.startdate.withoutTime().getTime();
    var pastEvent = t.clone();
    newEvent.startdate = new Date( newEvent.startdate.getTime() + delta );
    newEvent.enddate = new Date( newEvent.enddate.getTime() + delta );
    newEvent.old_id = newEvent.id;

    if (!untilEnd) {  // Only single day
      delete newEvent.id;
      newbookingid=-1;
      reIdBookings(newEvent);
      pastEvent.addException(splitDate, true);
      newEvent.repeat_id = 0;
      deleteOlderExceptionsAndAdditions(newEvent, splitDate, true);
      deleteNewerExceptionsAndAdditions(newEvent, splitDate, true);
      delete newEvent.repeat_until;
      delete newEvent.exceptions;
      delete newEvent.additions;
      func(newEvent, pastEvent);
    }
    else {  // Split until end
      // If editing first of series, change the whole series, no new Event is neccesary
      if (t.startdate.withoutTime().getTime() == splitDate.getTime()) func(newEvent, null);
      else {
        delete newEvent.id;
        reIdBookings(newEvent);
        deleteOlderExceptionsAndAdditions(newEvent, splitDate, true);
        // Change repeat until date
        var d = new Date(splitDate.getTime());
        d.addDays(-1);
        pastEvent.repeat_until = d;
        deleteNewerExceptionsAndAdditions(pastEvent, splitDate, true);
        func(newEvent, pastEvent);
      }
    }
  }
}

var newbookingid=-1;
function reIdBookings(newEvent) {
  each(newEvent.bookings, function(k, a) {
    if (k > 0) {
      a.old_id = a.id;
      delete a.id;
      newEvent.bookings[newbookingid] = a;
      delete newEvent.bookings[k];
      newbookingid = newbookingid - 1;
    }
  });
  return newEvent;
}

CCEvent.prototype.isSeries = function () {
  var t = this;
  return t.repeat_id != null && t.repeat_id > 0;
}



/**
 * Checks the impact of the event changes on the the server and display a dialog when there is
 * something to confirm.
 * @param newEvent - Changed Event
 * @param originEvent - Origin Event
 * @param splitDate - Where the Event should be devided
 * @param func Function callback with func(true) or func(false)
 */
CCEvent.prototype.prooveEventChangeImpact = function (newEvent, pastEvent, splitDate, untilEnd, func) {
  var t = this;
  // If it is an existing event, otherwise there nothing to prove
  if (t.id == null) {
    func(true);
    return;
  }
  var o = new Object();
  o.func = "getEventChangeImpact";
  o.newEvent = newEvent;
  o.pastEvent = pastEvent;
  o.originEvent = t;
  o.splitDate = splitDate;
  o.untilEnd_yn = (untilEnd ? 1 : 0);

  churchInterface.jsendWrite(o, function(ok, data) {
    if (!ok) {
      alert(_("error.occured") + data);
      if (func!=null) func(false);
    }
    else {
      confirmImpactOfEventChange(data, function(ok) { func(ok); });
    }
  });
}

function getCSEventId(currentEvent, date, withoutTime) {
  var id=null;
  if (withoutTime==null) withoutTime=false;
  each(currentEvent.csevents, function(k,e) {
    if ((withoutTime && e.startdate.withoutTime().getTime()==date.withoutTime().getTime())
        || (!withoutTime && e.startdate.getTime()==date.getTime())) {
      id=k;
      return false;
    }
  });
  return id;
}

function addCSEvent(currentEvent, csevent) {
  if (currentEvent.newCSEventId == null) currentEvent.newCSEventId = 0;
  currentEvent.newCSEventId = currentEvent.newCSEventId - 1;
  if (currentEvent.csevents == null) currentEvent.csevents = new Object();
  currentEvent.csevents[currentEvent.newCSEventId] = csevent;
}

/**
 * [deleteNewerExceptionsAndAdditions description]
 * @param {[type]} event
 * @param {[type]} date
 * @param {[type]} deleteCS default false, true means csevents will be deleted, otherwise action:delete
 */
function deleteNewerExceptionsAndAdditions(event, date, deleteCS) {
  each(event.exceptions, function(k,a) {
    if ((a != null) && (a.except_date_start.toDateEn(false).getTime() > date.getTime())) {
      delete event.exceptions[k];
    }
  });
  each(event.additions, function(k,a) {
    if ((a != null) && (a.add_date.toDateEn(false).getTime() >= date.getTime())) {
      delete event.additions[k];
    }
  });
  each(event.csevents, function(k,a) {
    if ((a != null) && (a.startdate.withoutTime().getTime() >= date.getTime())) {
      if (deleteCS==null || !deleteCS) event.csevents[k].action="delete";
      else delete event.csevents[k];
    }
  })
}

/**
 * [deleteOlderExceptionsAndAdditions description]
 * @param {[type]} event
 * @param {[type]} date
 * @param {[type]} deleteCS default false, true means csevents will be deleted, otherwise action:delete
 */
function deleteOlderExceptionsAndAdditions(event, date, deleteCS) {
  each(event.exceptions, function(k,a) {
    if ((a != null) && (a.except_date_start.toDateEn(false).getTime() < date.getTime())) {
      delete(event.exceptions[k]);
    }
  });
  each(event.additions, function(k,a) {
    if ((a != null) && (a.add_date.toDateEn(false).getTime() <= date.getTime())) {
      delete(event.additions[k]);
    }
  });
  each(event.csevents, function(k,a) {
    if ((a != null) && (a.startdate.withoutTime().getTime() < date.getTime())) {
      if (deleteCS==null || !deleteCS) event.csevents[k].action="delete";
      else delete event.csevents[k];
    }
  })
}
