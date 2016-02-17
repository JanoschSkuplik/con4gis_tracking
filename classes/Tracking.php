<?php

/**
 * Contao Open Source CMS
 *
 * @version   php 5
 * @package   con4gis_tracking
 * @author    Janosch Oltmanns
 * @license   GNU/LGPL http://opensource.org/licenses/lgpl-3.0.html
 * @copyright Janosch Oltmanns in cooperation with Küstenschmiede GmbH Software & Design 2014 - 2015
 * @link      http://janosch-oltmanns.de https://www.kuestenschmiede.de
 */

namespace c4g;

class Tracking extends \Controller
{

  public static function setNewPosition($strParentTable, $dblLatitude, $dblLongitude, $longAccuracy = 0, $longSpeed = 0, $timeStamp = false, $arrAdditionalData = array())
  {

    $varTime = time();
    $time = $timeStamp ? $timeStamp : $varTime;//time();

    $arrSet = array
    (
      'pTable' => $strParentTable,
      'tstamp' => $time,
      'serverTstamp' => $varTime,
      'latitude' => $dblLatitude,
      'longitude' => $dblLongitude,
      'accuracy' => $longAccuracy,
      'speed' => $longSpeed
    );

    $objDatabase = \Database::getInstance();

    $blnHasDevice = false;

    if ($arrAdditionalData['imei'])
    {
      $arrSet['imei'] = $arrAdditionalData['imei'];

      $intDeviceId = self::getDeviceIdByImei($arrAdditionalData['imei']);

      if ($intDeviceId)
      {
        $arrSet['device'] = $intDeviceId;
        $blnHasDevice = true;
      }
    }

    if ($arrAdditionalData && sizeof($arrAdditionalData) > 0)
    {
      $dataForBlob = array();
      foreach ($arrAdditionalData as $key => $varValue)
      {
        if ($objDatabase->fieldExists($key, "tl_c4g_tracking_positions"))
        {
          $arrSet[$key] = $varValue;
        }
        else
        {
          $dataForBlob[$key] = $varValue;
        }
      }

      if ($dataForBlob && sizeof($dataForBlob) > 0)
      {
        $arrSet['additionalData'] = $dataForBlob;
      }
    }

    $objPosition = new \C4gTrackingPositionsModel();
    $objPosition->setRow($arrSet)->save();


    if ($blnHasDevice)
    {
      // UPDATE DEVICE TABLE REFERENCE
      \Database::getInstance()->prepare("UPDATE tl_c4g_tracking_devices SET lastPositionId=? WHERE id=?")
        ->execute($objPosition->id, $intDeviceId);
    }

    return $objPosition->id;

  }

  public static function setNewDevicePositions()
  {
    echo self::setNewPosition("test", 12, 12, 0, 0, false, array('pid' => 'dfdf', 'test' => 'dfgdfg'));
  }

  public static function setNewPoi($intConfiguration, $intMemberId, $strVisibility = "privat", $strName = "", $intTrackUuid = 0, $timeStamp, $arrPositionData = array())
  {

    $strUuid = uniqid('', true);
    $timeStamp = $timeStamp ? $timeStamp : time();

    // Save Position into position-table
    $intPositionId = self::setNewPosition("pois", $arrPositionData['latitude'], $arrPositionData['longitude'], $arrPositionData['longitude'], $arrPositionData['longitude'], $timeStamp, $arrPositionData['additionalData']);

    $arrSet = array
    (
      'tstamp' => $timeStamp,
      'pid' => $intConfiguration,
      'uuid' => $strUuid,
      'member' => $intMemberId,
      'name' => $strName,
      'visibility' => $strVisibility,
      'trackUuid' => $intTrackUuid
    );

    $arrSet['positionId'] = $intPositionId;

    if ($arrPositionData['additionalData']['imei'])
    {

      $intDeviceId = self::getDeviceIdByImei($arrPositionData['additionalData']['imei']);

      if ($intDeviceId)
      {
        $arrSet['device'] = $intDeviceId;
      }
    }


    $objPoi = new \C4gTrackingPoisModel();
    $objPoi->setRow($arrSet)->save();


    $arrTrackingPoi = array();
    $arrTrackingPoi['poiId'] = $objPoi->id;
    $arrTrackingPoi['poiUuid'] = $strUuid;

    $arrTrackingPoi['version'] = $GLOBALS['con4gis_tracking_extension']['version'];

    return $arrTrackingPoi;

  }

  public static function setNewTrack($intConfiguration, $intMemberId, $strVisibility = "privat", $strName = "", $timeStamp, $arrPositionData = array())
  {

    $timeStamp = $timeStamp ? $timeStamp : time();
    $strUuid = uniqid('', true);

    $intPositionId = self::setNewPosition("tracks", $arrPositionData['latitude'], $arrPositionData['longitude'], $arrPositionData['longitude'], $arrPositionData['longitude'], $timeStamp, $arrPositionData['additionalData']);

    $arrSet = array
    (
      'tstamp' => $timeStamp,
      'pid' => $intConfiguration,
      'uuid' => $strUuid,
      'member' => $intMemberId,
      'name' => $strName,
      'visibility' => $strVisibility
    );

    $arrSet['lastPositionId'] = $intPositionId;

    if ($arrPositionData['imei'])
    {
      $arrSet['imei'] = $arrPositionData['imei'];
    }

  }


  public static function xsetNewPosition($intTrackUuid, $dblLatitude, $dblLongitude, $longAccuracy = 0, $longSpeed = 0, $timeStamp = false, $arrAdditionalData = array())
  {
    $arrTrackingPosition = array();
    $time = $timeStamp ? $timeStamp : time();

    $arrSet = array
    (
      'tstamp' => $time,
      'track_uuid' => $intTrackUuid,
      'latitude' => $dblLatitude,
      'longitude' => $dblLongitude,
      'accuracy' => $longAccuracy,
      'speed' => $longSpeed
    );

    if ($arrAdditionalData && sizeof($arrAdditionalData) > 0)
    {
      if ($arrAdditionalData['positiontype'])
      {
        $arrSet['positiontype'] = $arrAdditionalData['positiontype'];
      }
      if ($arrAdditionalData['imei'])
      {
        $arrSet['imei'] = $arrAdditionalData['imei'];
      }
      if ($arrAdditionalData['batterystatus'])
      {
        $arrSet['batterystatus'] = $arrAdditionalData['batterystatus'];
      }
      if ($arrAdditionalData['networkinfo'])
      {
        $arrSet['networkinfo'] = $arrAdditionalData['networkinfo'];
      }
    }

    $objPosition = new \C4gTrackingPositionsModel();
    $objPosition->setRow($arrSet)->save();

    $arrTrackingPosition['positionId'] = $objPosition->id;

    $arrTrackingConfig['version'] = $GLOBALS['con4gis_tracking_extension']['version'];

    return $arrTrackingPosition;
  }

  public static function xsetNewPoi($intConfiguration, $intUserId, $dblLatitude, $dblLongitude, $longAccuracy = 0, $longSpeed = 0, $strName = "", $timeStamp = false, $strVisibility = "privat", $intTrackUuid = 0, $arrAdditionalData = array())
  {
    $arrTrackingPoi = array();
    $time = $timeStamp ? $timeStamp : time();
    $strUuid = uniqid('', true);

    $arrSet = array
    (
      'tstamp' => $time,
      'pid' => $intConfiguration,
      'uuid' => $strUuid,
      'member' => $intUserId,
      'latitude' => $dblLatitude,
      'longitude' => $dblLongitude,
      'accuracy' => $longAccuracy,
      'speed' => $longSpeed,
      'name' => $strName,
      'visibility' => $strVisibility,
      'trackUuid' => $intTrackUuid
    );


    if ($arrAdditionalData && sizeof($arrAdditionalData) > 0)
    {
      if ($arrAdditionalData['positiontype'])
      {
        $arrSet['positiontype'] = $arrAdditionalData['positiontype'];
      }
      if ($arrAdditionalData['imei'])
      {
        $arrSet['imei'] = $arrAdditionalData['imei'];
      }
      if ($arrAdditionalData['batterystatus'])
      {
        $arrSet['batterystatus'] = $arrAdditionalData['batterystatus'];
      }
      if ($arrAdditionalData['networkinfo'])
      {
        $arrSet['networkinfo'] = $arrAdditionalData['networkinfo'];
      }
    }

    $objPoi = new \C4gTrackingPoisModel();
    $objPoi->setRow($arrSet)->save();

    $arrTrackingPoi['poiId'] = $objPoi->id;
    $arrTrackingPoi['poiUuid'] = $strUuid;

    $arrTrackingPoi['version'] = $GLOBALS['con4gis_tracking_extension']['version'];

    return $arrTrackingPoi;
  }

  public static function xsetNewTrack($intConfiguration, $intUserId, $strName = "", $timeStamp = false, $strVisibility = "privat", $arrAdditionalData = array())
  {
    $arrTrackingTrack = array();
    $time = $timeStamp ? $timeStamp : time();
    $strUuid = uniqid('', true);

    $arrSet = array
    (
      'tstamp' => $time,
      'pid' => $intConfiguration,
      'uuid' => $strUuid,
      'member' => $intUserId,
      'name' => $strName,
      'visibility' => $strVisibility
    );

    if ($arrAdditionalData && sizeof($arrAdditionalData) > 0)
    {
      if ($arrAdditionalData['imei'])
      {
        $arrSet['imei'] = $arrAdditionalData['imei'];
      }
      /*if ($arrAdditionalData['batterystatus'])
      {
          $arrSet['batterystatus'] = $arrAdditionalData['batterystatus'];
      }
      if ($arrAdditionalData['networkinfo'])
      {
          $arrSet['networkinfo'] = $arrAdditionalData['networkinfo'];
      }*/
    }

    $objTrack = new \C4gTrackingTracksModel();
    $objTrack->setRow($arrSet)->save();

    $arrTrackingTrack['trackId'] = $objTrack->id;
    $arrTrackingTrack['trackUuid'] = $strUuid;

    $arrTrackingTrack['version'] = $GLOBALS['con4gis_tracking_extension']['version'];

    return $arrTrackingTrack;
  }

  public static function getTrackingConfig()
  {
    $objRootPage = \Frontend::getRootPageFromUrl();

    $arrTrackingConfig = array
    (
      'hasTrackingConfiguration' => false
    );

    if ($objRootPage->c4gtracking_configuration)
    {

      $objTrackingConfiguration = $objRootPage->getRelated('c4gtracking_configuration');

      if ($objTrackingConfiguration !== null)
      {
        $arrTrackingConfig['hasTrackingConfiguration'] = true;

        $arrTrackingInformation = $objTrackingConfiguration->row();

        foreach ($arrTrackingInformation as $key => $value)
        {
          $arrTrackingConfig[$key] = self::manipulateTrackingInfo($key, $value);

          if (is_array(deserialize($arrTrackingConfig[$key])))
          {
            $arrTrackingConfig[$key] = deserialize($arrTrackingConfig[$key]);
          }

        }

      }
      else
      {
        $arrTrackingConfig['message'] = "no tracking configuration";
      }

    }
    else
    {
      $arrTrackingConfig['message'] = "no tracking configuration";
    }

    $arrTrackingConfig['version'] = $GLOBALS['con4gis_tracking_extension']['version'];

    return $arrTrackingConfig;

  }

  private static function getDeviceIdByImei($strImei)
  {

    $objDevice = \C4gTrackingDevicesModel::findByImeiEndpiece($strImei);

    if ($objDevice !== null)
    {
      return $objDevice->id;
    }

    return false;

  }

  public static function getIgnitionStatus($indDeviceId)
  {
    // 12 = Zündung an
    // 13 = Zündung aus
    $objDatabase = \Database::getInstance();
    $objIgnitionInfo = $objDatabase->prepare("SELECT * FROM tl_c4g_tracking_positions WHERE device=? AND (boxStatus=? OR boxStatus=?) ORDER BY tstamp DESC")
                                   ->limit(1)
                                   ->execute($indDeviceId, 12, 13);
    if ($objIgnitionInfo->numRows)
    {
      return $objIgnitionInfo->boxStatus == 12;
    }
    return null;
  }

  private static function manipulateTrackingInfo($strKey, $strValue)
  {
    switch ($strKey)
    {
      case "tstamp":
        //$objDate = new \Date($strValue);
        //$strValue = $objDate->datim;
        break;
    }
    return $strValue;
  }

  public static function sendPushNotificationByToken($intConfiguraion, $strType, $strToken, $strContent)
  {

    $objTracking = \C4gTrackingModel::findBy('id', $intConfiguraion);
    if ($objTracking === null)
    {
      return;
    }

    switch ($strType)
    {
      case "android":
        self::sendGoogleCloudMessage($strToken, $strContent, $objTracking);
        break;
    }

  }

  private static function sendGoogleCloudMessage($strToken, $strContent, $objTracking)
  {
    $strGoogleApiKey = $objTracking->pushGcmApiKey;
    $strGoogleGcmUrl = 'https://android.googleapis.com/gcm/send';


    $arrGcmHeaders = array
    (
      'Authorization: key=' . $strGoogleApiKey,
      'Content-Type: application/json'
    );

    $arrGcmFields = array
    (
      'registration_ids' => array
      (
        $strToken
      ),
      'data' => array
      (
        'message' => $strContent
      )
    );

    // Open connection
    $ch = curl_init();

    // Set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $strGoogleGcmUrl);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $arrGcmHeaders);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Disabling SSL Certificate support temporarly
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arrGcmFields));

    // Execute post
    $result = curl_exec($ch);
    if ($result === FALSE)
    {
      //CakeLog::write('log','Gc,notofication failed. Id:' . $id . '; Msg: ' . curl_error($ch));
      //die('Curl failed: ' . curl_error($ch));
    }

    // Close connection
    curl_close($ch);
  }

}