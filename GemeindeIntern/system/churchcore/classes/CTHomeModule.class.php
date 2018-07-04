<?php
/**
 * Home Module class
 *
 * TODO: maybe this functions all belong to another module and should called from the respective class like
 *
 * public function updateEventService($params) {
 *   return CTServiceModule::updateEventService($params);
 * }
 */
class CTHomeModule extends CTAbstractModule {

  /**
   *
   * @see CTModuleInterface::getMasterData()
   */
  public function getMasterData() {
    global $user, $base_url, $files_dir, $config;
    $res["modulename"] = "churchcore";
    $modules = churchcore_getModulesSorted();
    if (in_array("churchdb", $modules)) {
      include_once ('./'. CHURCHDB. '/churchdb_db.php');
      $res["mygroups"] = churchdb_getMyGroups($user->id, false, false);
      foreach ($res["mygroups"] as $g) {
        if (!isset($g->status_no) || 
            (($g!= null) && ($g->members_allowedmail_eachother_yn== 0) && ($g->status_no!= 1) && ($g->status_no!= 2))) {
          unset($res["mygroups"][$g->id]);
        }
      }
    }
    if (in_array("churchcal", $modules)) {
      include_once ('./'. CHURCHCAL. '/churchcal_db.php');
      $res["meetingRequests"] = churchcal_getMyMeetingRequest();
    }
    return $res;
  }

  /**
   * update event service
   *
   * @param array $params
   * @return array
   */
  public function updateEventService($params) {
    include_once ('./'. CHURCHSERVICE. '/churchservice_ajax.php');
    return churchservice_updateEventService($params);
  }

  /**
   * undo Last Update Event Service
   *
   * @param array $params
   * @throws CTNoPermission
   */
  public function undoLastUpdateEventService($params) {
    global $user;
    if ($params["old_id"] !=  $params["new_id"]) {
      $db = db_query("SELECT * FROM {cs_eventservice} 
                      WHERE id=:id AND modified_pid=:user_id",
                      array (':id' => $params["new_id"], ':user_id' => $user->id))
                      ->fetch();
      if (!$db) throw new CTNoPermission("undoLastUpdateEventService", "home");

      db_query('DELETE FROM {cs_eventservice} 
                WHERE id=:id and modified_pid=:user_id',
                array (':id' => $params["new_id"], ':user_id' => $user->id));
      
      db_query('UPDATE {cs_eventservice} SET valid_yn=1 
                WHERE id=:id ', 
                array (':id' => $params["old_id"]));
    }
    else {
      db_query('UPDATE {cs_eventservice} SET valid_yn=1, cdb_person_id=:user_id, zugesagt_yn=0, name=:name 
                WHERE id=:id and modified_pid=:user_id',
                array (':id' => $params["old_id"], 
                       ':user_id' => $user->id,
                       'name' => "$user->vorname $user->name",
                ));
    }
  }

  /**
   * add reason to event service
   *
   * @param array $params
   */
  public function addReasonToEventService($params) {
    global $user;
    db_query('UPDATE {cs_eventservice} SET reason=:reason 
              WHERE id=:id AND modified_pid=:user_id',
              array (':reason' => $params["reason"], 
                     ':id' => $params["id"], 
                     ':user_id' => $user->id,
              ));
  }

  /**
   *
   * @param array $params
   * @throws CTException
   */
  public function sendEMail($params) {
    global $user;
    include_once ('./'. CHURCHDB. '/churchdb_db.php');
    $groups = churchdb_getMyGroups($user->id, true, false);
    if (empty($groups[$params["groupid"]])) throw new CTException("Group is not allowed!");
    $ids = churchdb_getAllPeopleIdsFromGroups(array($params["groupid"]));
    churchcore_sendEMailToPersonIDs(implode(",", $ids), "[" . getConf('site_name') . "] " . t('message.from.x', "$user->vorname  $user->name"), $params["message"], null, true);
  }

  /**
   *
   * @param array $params
   */
  public function updateMeetingRequest($params) {
    include_once (CHURCHCAL. '/churchcal_db.php');
    churchcal_updateMeetingRequest($params);
  }

}
