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

class TrackingFrontend extends \Frontend
{

    private $arrAllowedLocationTypes = array
    (
        'tPois',
        'tTracks',
        'tLive',
        'tBoxes'
    );

    public function __construct()
   	{
   		parent::__construct();
        if (FE_USER_LOGGED_IN)
        {
            $this->import('FrontendUser', 'User');
            $this->User->authenticate();
        }

    }

    public function getInfoWindowContent($strTable, $intId, $arrData)
    {

        if ($strTable == "devices")
        {
            $arrTrackingData = array();

            $blnUseTimeZoneSettings = false;

            if (strpos($intId,";")!==false)
            {

                $arrTrackingInfoSettings = explode(';',$intId);

                if (is_array($arrTrackingInfoSettings))
                {
                  foreach ($arrTrackingInfoSettings as $varTrackingInfo)
                  {

                    if (strpos($varTrackingInfo, ",")!==false)
                    {
                      $arrTrackingSingleInfo = explode(',', $varTrackingInfo);
                      if (is_array($arrTrackingSingleInfo))
                      {
                        if ($arrTrackingSingleInfo[0] == "id")
                        {
                          $intPositionId = $arrTrackingSingleInfo[1];
                        }
                        if ($arrTrackingSingleInfo[0] == "maps")
                        {
                          $intMapsItem = $arrTrackingSingleInfo[1];
                        }
                      }


                    }
                  }
                }

              $objLayer = \C4gMapsModel::findById($intMapsItem);

              $strPopupContentRaw = "";

              $objPositions = \C4gTrackingPositionsModel::findBy('id', $intPositionId);
              if ($objPositions !== null)
              {
                $arrPositionData = $objPositions->row();

                $arrTemplateData = array();

                if (($objDevice = $objPositions->getRelated('device')) !== null)
                {
                  $arrDeviceData = $objDevice->row();

                    if ($arrDeviceData['timeZone'] && $arrDeviceData['timeZone']!=\Config::get('timeZone'))
                    {
                        $blnUseTimeZoneSettings = true;
                        $strTimeZoneSettings = $arrDeviceData['timeZone'];
                    }

                  foreach ($arrDeviceData as $key=>$varValue)
                  {
                    $arrTemplateData["device" . ucfirst($key)] = $varValue;
                  }

                }

                foreach ($arrPositionData as $key=>$varValue)
                {
                  $arrTemplateData["position" . ucfirst($key)] = $varValue;
                }

              }

              if ($objLayer !== null)
              {
                if ($objLayer->popup_info)
                {

                  $strPopupContentRaw = $objLayer->popup_info;

                  foreach ($arrTemplateData as $key=>$varValue)
                  {
                      if(strpos(strtolower($key), "tstamp")!==false)
                      {
                          if ($key == "positionTstamp")
                          {
                              if ($blnUseTimeZoneSettings)
                              {

                                  // store local and device timezone
                                  $dateTimeZoneDevice = new \DateTimeZone($strTimeZoneSettings);
                                  $dateTimeZoneServer = new \DateTimeZone(\Config::get('timeZone'));

                                  // get one date-time-object for device timezone
                                  $dateTimeDevice = new \DateTime("now", $dateTimeZoneDevice);

                                  // get the offset of the timezone
                                  $timeOffset = $dateTimeZoneServer->getOffset($dateTimeDevice);

                                  // recalculate timestamp with given offset
                                  $varTimeStamp = $varValue + $timeOffset;

                                  $arrTemplateData[$key] = \Date::parse(\Config::get('datimFormat'), $varTimeStamp);

                              }
                              else
                              {
                                  $arrTemplateData[$key] = \Date::parse(\Config::get('datimFormat'), $varValue);
                              }
                          }
                          else
                          {
                              $arrTemplateData[$key] = \Date::parse(\Config::get('datimFormat'), $varValue);
                          }

                      }

                    if (is_array(deserialize($varValue)))
                    {
                      unset($arrTemplateData[$key]);
                      $arrArrayData = deserialize($varValue, true);

                      foreach ($arrArrayData as $dataKey=>$dataVarValue)
                      {
                        $arrTemplateData[$key . ucfirst($dataKey)] = $dataVarValue;
                      }

                    }

                  }

                  //print_r($arrTemplateData);

                  $regSearch = '/(\$\{\w*\})/';

                  if (preg_match_all($regSearch, $strPopupContentRaw, $arrPlaceholder, PREG_PATTERN_ORDER))
                  {

                    foreach ($arrPlaceholder[0] as $strPlaceholder)
                    {

                      if ($strPlaceholder == '${allData}')
                      {

                        $strAllData = "<dl>";

                        foreach ($arrTemplateData as $key=>$varValue)
                        {
                          $strAllData .= "<dt>" . $key . "<dt>";
                          $strAllData .= "<dd>" . $varValue . "<dd>";
                        }
                        $strAllData .= "</dl>";

                        $strPopupContentRaw = str_replace($strPlaceholder, $strAllData, $strPopupContentRaw);
                      }

                      else
                      {
                        $strPlaceholderRaw = str_replace(array('${', '}'), '', $strPlaceholder);
                        $strPopupContentRaw = str_replace($strPlaceholder, $arrTemplateData[$strPlaceholderRaw], $strPopupContentRaw);
                      }

                    }
                  }
                }
              }

              $arrData['content'] = $strPopupContentRaw;

            }
        }

        return $arrData;
    }

    public function addLocations($level, $child)
    {

        if (in_array($child['type'], $this->arrAllowedLocationTypes))
        {
            $arrData = array();

            switch ($child['type'])
            {
                case "tPois":
                    $arrData['pid'] = $level;
                    $arrData['id'] = $child['id'];
                    $arrData['type'] = 'none';
                    $arrData['display'] = $child['display'];
                    $arrData['name'] = \String::decodeEntities($child['name']);
                    $arrData['hide'] = $child['hide'] > 0 ? $child['hide'] : '';
                    $arrChildData = $this->getPoiData($child);
                    if (sizeof($arrChildData) == 0 && $child->tDontShowIfEmpty)
                    {
                      return;
                    }
                    else
                    {
                        $arrData['hasChilds'] = true;
                        $arrData['childsCount'] = sizeof($arrChildData);
                        $arrData['childs'] = $arrChildData;
                    }
                    break;
                case "tTracks":
                    $arrData['pid'] = $level;
                    $arrData['id'] = $child['id'];
                    $arrData['type'] = 'none';
                    $arrData['display'] = $child['display'];
                    $arrData['name'] = \String::decodeEntities($child['name']);
                    $arrData['hide'] = $child['hide'];
                    $arrChildData = $this->getTrackData($child);
                    if (sizeof($arrChildData) == 0 && $child->tDontShowIfEmpty)
                    {
                      return;
                    }
                    else
                    {
                        $arrData['hasChilds'] = true;
                        $arrData['childsCount'] = sizeof($arrChildData);
                        $arrData['childs'] = $arrChildData;
                    }
                    break;
                case "tBoxes":
                    $arrData['pid'] = $level;
                    $arrData['id'] = $child['id'];
                    $arrData['type'] = 'none';
                    $arrData['display'] = $child['display'];
                    $arrData['name'] = \String::decodeEntities($child['name']);
                    $arrData['hide'] = $child['hide'];
                    $arrData['content'] = '';//$child['hide'];
                    $arrChildData = $this->getBoxTrackData($child);
                    if (sizeof($arrChildData) == 0 && $child->tDontShowIfEmpty)
                    {
                        return;
                    }
                    else
                    {
                        $arrData['hasChilds'] = true;
                        $arrData['childsCount'] = sizeof($arrChildData);
                        $arrData['childs'] = $arrChildData;
                    }
                    break;
                case "tLive":
                    $arrData['pid'] = $level;
                    $arrData['id'] = $child['id'];
                    $arrData['origType'] = 'liveTracking';
                    $arrData['locstyle'] = $child['raw']->locstyle;
                    $arrData['display'] = $child['display'];
                    $arrData['name'] = \String::decodeEntities($child['name']);
                    $arrData['hide'] = $child['hide'];
                    $arrData['filterable'] = $child['raw']->isFilterable ? 1 : 0;


                    if ($child['raw']->liveTrackingType == "tLive_alleach" || $child['raw']->liveTrackingType == "tLive_groupeach" || $child['raw']->liveTrackingType == "tLive_deviceeach")
                    {


                      $arrChildData = $this->getLiveChildData($child);
                      $arrData['hasChilds'] = true;
                      $arrData['childsCount'] = sizeof($arrChildData);
                      $arrData['childs'] = $arrChildData;

                        if ($child['raw']->isFilterable)
                        {
                            $arrData['filterable'] = 0;//"&maps=" . $child['id'];
                        }

                    }
                    else
                    {

                      if ($child['raw']->liveTrackingType == "tLive_group")
                      {

                        $strUrl = "system/modules/con4gis_core/api/index.php/trackingService?method=getLive&maps=" . $child['id'] . "&useGroup=" . $child['id'];
                          if ($child['raw']->isFilterable)
                          {
                              $arrData['filterable'] = array();
                              $arrData['filterable']['urlParam'] = "&maps=" . $child['id'] . "&useGroup=" . $child['id'];

                              if ($child['raw']->filterLocationStyle)
                              {
                                  $arrData['filterable']['locationStyle'] = $child['raw']->filterLocationStyle;
                              }

                          }
                      }
                      elseif ($child['raw']->liveTrackingType == "tLive_device")
                      {

                        $arrDevices = deserialize($child['raw']->liveTrackingDevices, true);
                        $strUrl = "system/modules/con4gis_core/api/index.php/trackingService?method=getLive&maps=" . $child['id'] . "&id[]=" . implode('&id[]=', $arrDevices);
                          if ($child['raw']->isFilterable)
                          {
                              $arrData['filterable'] = array();
                              $arrData['filterable']['urlParam'] = "&maps=" . $child['id'];

                              if ($child['raw']->filterLocationStyle)
                              {
                                  $arrData['filterable']['locationStyle'] = $child['raw']->filterLocationStyle;
                              }
                              //$arrData['filterable'] = array(
                              //    "type" => "tLive_device",
                              //    "maps" => $child['id'],
                              //    "devices" => $arrDevices
                              //);//"maps=" . $child['id'] . "&id[]=" . implode('&id[]=', $arrDevices);
                          }
                      }
                      else
                      {
                        $strUrl = "system/modules/con4gis_core/api/index.php/trackingService?method=getLive&maps=" . $child['id'] . "";
                          if ($child['raw']->isFilterable)
                          {
                              $arrData['filterable'] = array();
                              $arrData['filterable']['urlParam'] = "&maps=" . $child['id'];

                              if ($child['raw']->filterLocationStyle)
                              {
                                  $arrData['filterable']['locationStyle'] = $child['raw']->filterLocationStyle;
                              }

                          }
                      }

                      $arrData['content'] = array
                      (
                        array
                        (
                          "type" => 'urlData',
                          "format" => 'GeoJSON',
                          "locationStyle" => $child['raw']->locstyle,
                          "data" => array
                          (
                            "url" => $strUrl
                          ),
                          "settings" => array
                          (
                            "loadAsync" => true,
                            "refresh" => true,
                            // "interval" => getTrackingConfig -> getHTTPInterval
                            "crossOrigin" => false
                          )
                        )
                      );
                    }


                    //$GLOBALS['TL_BODY'][] = '<script src="system/modules/con4gis_tracking/assets/liveTracking.js"></script>';

                    break;
            }

            return $arrData;
        }

        return;
    }

    protected function getLiveChildData($child)
    {
      //var_dump($child);
      $arrTrackData = array();


      if ($child['raw']->liveTrackingType == "tLive_group" || $child['raw']->liveTrackingType == "tLive_groupeach")
      {
        // 'tLive_all', 'tLive_alleach', 'tLive_group', 'tLive_group', 'tLive_device'
        $objDevice = \C4gTrackingDevicesModel::findBy('mapStructureId', $child['id'], array('order'=>'name'));
      }
      elseif ($child['raw']->liveTrackingType == "tLive_device" || $child['raw']->liveTrackingType == "tLive_deviceeach")
      {
        //print_r($child['raw']->liveTrackingDevices);
        $arrDevices = deserialize($child['raw']->liveTrackingDevices, true);
        $objDevice = \C4gTrackingDevicesModel::findMultipleByIds($arrDevices);
      }
      else
      {
        $objDevice = \C4gTrackingDevicesModel::findAll(array('order'=>'name'));
      }


      if ($objDevice !== null)
      {

        while ($objDevice->next())
        {

            $arrFilterData = array();

            if ($child['raw']->isFilterable)
            {
                $arrFilterData['urlParam'] = "&maps=" . $child['id'] . "&id=" . $objDevice->id;

                if ($child['raw']->filterLocationStyle)
                {
                    $arrFilterData['locationStyle'] = $child['raw']->filterLocationStyle;
                }

            }

          $arrTrackData[] = array
          (
            'pid' => $child['id'],
            'id' => $child['id'] . $objDevice->id,
            'name' => $objDevice->name ? \String::decodeEntities($objDevice->name) : $objDevice->id,
            'hide' => $child['hide'] > 0 ? $child['hide'] : '',
            'filterable' => $child['raw']->isFilterable ? $arrFilterData : 0,
            'display' => $child['display'],
            'content' => array
            (
              array
              (
                'id' => '',
                'type' => 'urlData',
                'format' => 'GeoJSON',
                'locationStyle' => $objDevice->locationStyle ? $objDevice->locationStyle : $child['raw']->locstyle,
                'data' => array
                (
                  'url' => "system/modules/con4gis_core/api/index.php/trackingService?method=getLive&maps=" . $child['id'] . "&id=" . $objDevice->id
                ),
                'settings' => array
                (
                  'loadAsync' => true,
                  'refresh' => true,
                  'crossOrigine' => false,

                )
              )
            )
          );
        }
      }
      return $arrTrackData;
    }

    protected function getBoxTrackData($child)
    {
        $arrTrackData = array();

        $objBoxes = \C4gTrackingBoxesModel::findAll();

        if ($objBoxes !== null)
        {
            while($objBoxes->next())
            {
                $arrTrackData[] = array
                (
                    'pid' => $child['id'],
                    'id' => $child['id'] . $objBoxes->id,
                    'type' => 'ajax',
                    'url' => 'system/modules/con4gis_core/api/trackingService?method=getBoxTrack&id=' . $objBoxes->id,
                    'name' => $child['name'] ? ($objBoxes->name . ' (' . \Date::parse('d.m.Y H:i', $objBoxes->tstamp) . ')') : '',
                    'hide' => $child['hide'] > 0 ? $child['hide'] : '',
                    'display' => $child['display'],
                    'popupInfo' => $objBoxes->name,
                    'content' => array
                    (
                        array
                        (
                            'id' => '',
                            'type' => 'urlData',
                            'format' => 'GeoJSON',
                            'locationStyle' => $child['raw']->locstyle,
                            'data' => array
                            (
                                'url' => 'system/modules/con4gis_core/api/trackingService?method=getBoxTrack&id=' . $objBoxes->id,
                                'popup' => array
                                (
                                    'content' => ''
                                )
                            ),
                            'settings' => array
                            (
                                'loadAsync' => true,
                                'refresh' => false,
                                'crossOrigine' => false,
                                'boundingBox' => false
                            )
                        )
                    )
                );
            }
        }

        return $arrTrackData;
    }

    protected function getTrackData($child)
    {

        $arrTrackData = array();

        $strType = $child['raw']->memberVisibility ? $child['raw']->memberVisibility : "all";

        $arrMember = array();
        $arrVisibility = array();

        $blnUseDatabaseStatus = false;
        if ($child['raw']->useDatabaseStatus)
        {
          $arrAllowedStatus = deserialize($child['raw']->databaseStatus);
          if (is_array($arrAllowedStatus) && sizeof($arrAllowedStatus)>0)
          {
            $blnUseDatabaseStatus = true;
          }
        }

        switch ($strType)
        {
            case "own":
              if (FE_USER_LOGGED_IN)
              {
                $this->import('FrontendUser', 'User');
                $arrMember[] = $this->User->id;
                if ($blnUseDatabaseStatus)
                {
                  $arrVisibility = $arrAllowedStatus;
                }
                $objTracks = \C4gTrackingTracksModel::findWithPositions($arrMember, $arrVisibility);
              }
              break;
            case "ownGroups":
              if (FE_USER_LOGGED_IN)
              {
                $this->import('FrontendUser', 'User');
                $arrMemberGroups = $this->User->__get('groups');
                if (is_array($arrMemberGroups))
                {
                  foreach($arrMemberGroups as $memberGroup)
                  {
                    $objMember = $this->Database->prepare("SELECT id,username FROM tl_member WHERE groups LIKE ?")
                                                ->execute('%"' . $memberGroup . '"%');
                    if ($objMember->numRows > 0)
                    {
                      while ($objMember->next())
                      {
                        if (!in_array($objMember->id, $arrMember))
                        {
                          $arrMember[] = $objMember->id;
                        }
                      }
                    }
                  }
                }
                $arrVisibility[] = "owngroups";
                $arrVisibility[] = "public";
                if ($blnUseDatabaseStatus)
                {
                  $arrVisibility = $arrAllowedStatus;
                }
                $objTracks = \C4gTrackingTracksModel::findWithPositions($arrMember, $arrVisibility);
              }
              break;
            case "specialGroups":
              $arrMemberGroups = deserialize($child->specialGroups, true);
              if (is_array($arrMemberGroups))
              {
                foreach($arrMemberGroups as $memberGroup)
                {
                  $objMember = $this->Database->prepare("SELECT id,username FROM tl_member WHERE groups LIKE ?")
                                              ->execute('%"' . $memberGroup . '"%');
                  if ($objMember->numRows > 0)
                  {
                    while ($objMember->next())
                    {
                      if (!in_array($objMember->id, $arrMember))
                      {
                        $arrMember[] = $objMember->id;
                      }
                    }
                  }
                }
              }
              $arrVisibility[] = "membergroups";
              $arrVisibility[] = "public";
              if ($blnUseDatabaseStatus)
              {
                $arrVisibility = $arrAllowedStatus;
              }
              $objTracks = \C4gTrackingTracksModel::findWithPositions($arrMember, $arrVisibility);
              break;
            case "specialMember":
              $arrVisibility[] = "public";
              if ($blnUseDatabaseStatus)
              {
                $arrVisibility = $arrAllowedStatus;
              }
              if ($child->specialMembers)
              {
                $arrMember = deserialize($child->specialMembers, true);
              }
              $objTracks = \C4gTrackingTracksModel::findWithPositions($arrMember, $arrVisibility);
              break;
            case "all":

                $arrVisibility[] = "public";
                if ($blnUseDatabaseStatus)
                {
                  $arrVisibility = $arrAllowedStatus;
                }
                $objTracks = \C4gTrackingTracksModel::findWithPositions($arrMember, $arrVisibility);
                break;
            default:

                break;
        }

        if ($objTracks !== null)
        {
            while($objTracks->next())
            {
                $arrTrackData[] = array
                (
                    'pid' => $child['id'],
                    'id' => $child['id'] . $objTracks->id,
                    'type' => 'ajax',
                    'url' => 'system/modules/con4gis_core/api/trackingService?method=getTrack&id=' . $objTracks->uuid,
                    'name' => $child['name'] ? ($objTracks->name . ' (' . \Date::parse('d.m.Y H:i', $objTracks->tstamp) . ')') : '',
                    'hide' => $child['hide'] > 0 ? $child['hide'] : '',
                    'display' => $child['display'],
                    'popupInfo' => $objTracks->name,
                    'content' => array
                    (
                        array
                        (
                            'id' => '',
                            'type' => 'urlData',
                            'format' => 'GeoJSON',
                            'locationStyle' => $child['raw']->locstyle,
                            'data' => array
                            (
                                'url' => 'system/modules/con4gis_core/api/trackingService?method=getTrack&id=' . $objTracks->uuid,
                                'popup' => array
                                (
                                    'content' => ''
                                )
                            ),
                            'settings' => array
                            (
                                'loadAsync' => true,
                                'refresh' => false,
                                'crossOrigine' => false,
                                'boundingBox' => false
                            )
                        )
                    )
                );
            }
        }

        return $arrTrackData;
    }

    protected function getPoiData($child)
    {
        $arrPoiData = array();

        $strType = $child['raw']->memberVisibility ? $child['raw']->memberVisibility : "all";

        $arrMember = array();
        $arrVisibility = array();

        $blnUseDatabaseStatus = false;
        if ($child['raw']->useDatabaseStatus)
        {
          $arrAllowedStatus = deserialize($child['raw']->databaseStatus);
          if (is_array($arrAllowedStatus) && sizeof($arrAllowedStatus)>0)
          {
            $blnUseDatabaseStatus = true;
          }
        }

        switch ($strType)
        {
            case "own":
              if (FE_USER_LOGGED_IN)
              {
                $this->import('FrontendUser', 'User');
                $arrMember[] = $this->User->id;
                if ($blnUseDatabaseStatus)
                {
                  $arrVisibility = $arrAllowedStatus;
                }
                $objPois = \C4gTrackingPoisModel::findWithMagic($arrMember, $arrVisibility);
              }
              break;
            case "ownGroups":
              if (FE_USER_LOGGED_IN)
              {
                $this->import('FrontendUser', 'User');
                $arrMemberGroups = $this->User->__get('groups');
                if (is_array($arrMemberGroups))
                {
                  foreach($arrMemberGroups as $memberGroup)
                  {
                    $objMember = $this->Database->prepare("SELECT id,username FROM tl_member WHERE groups LIKE ?")
                                                ->execute('%"' . $memberGroup . '"%');
                    if ($objMember->numRows > 0)
                    {
                      while ($objMember->next())
                      {
                        if (!in_array($objMember->id, $arrMember))
                        {
                          $arrMember[] = $objMember->id;
                        }
                      }
                    }
                  }
                }
                $arrVisibility[] = "owngroups";
                $arrVisibility[] = "public";
                if ($blnUseDatabaseStatus)
                {
                  $arrVisibility = $arrAllowedStatus;
                }
                $objPois = \C4gTrackingPoisModel::findWithMagic($arrMember, $arrVisibility);
              }
              break;
            case "specialGroups":
              $arrMemberGroups = deserialize($child->specialGroups, true);
              if (is_array($arrMemberGroups))
              {
                foreach($arrMemberGroups as $memberGroup)
                {
                  $objMember = $this->Database->prepare("SELECT id,username FROM tl_member WHERE groups LIKE ?")
                                              ->execute('%"' . $memberGroup . '"%');
                  if ($objMember->numRows > 0)
                  {
                    while ($objMember->next())
                    {
                      if (!in_array($objMember->id, $arrMember))
                      {
                        $arrMember[] = $objMember->id;
                      }
                    }
                  }
                }
              }
              $arrVisibility[] = "membergroups";
              $arrVisibility[] = "public";
              if ($blnUseDatabaseStatus)
              {
                $arrVisibility = $arrAllowedStatus;
              }
              $objPois = \C4gTrackingPoisModel::findWithMagic($arrMember, $arrVisibility);
              break;
            case "specialMember":
              $arrVisibility[] = "public";
              if ($blnUseDatabaseStatus)
              {
                $arrVisibility = $arrAllowedStatus;
              }
              if ($child->specialMembers)
              {
                $arrMember = deserialize($child->specialMembers, true);
              }
              $objPois = \C4gTrackingPoisModel::findWithMagic($arrMember, $arrVisibility);
              break;
            case "all":
                $arrVisibility[] = "public";
                if ($blnUseDatabaseStatus)
                {
                  $arrVisibility = $arrAllowedStatus;
                }
                $objPois = \C4gTrackingPoisModel::findWithMagic($arrMember, $arrVisibility);
                break;
            default:

                break;
        }

        if ($objPois !== null)
        {
            while($objPois->next())
            {
                if (!$objPois->longitude || !$objPois->longitude)
                {
                    continue;
                }
                $arrPoiData[] = array
                (
                    'pid' => $child['id'],
                    'id' => $child['id'] . $objPois->id,
                    'type' => 'single',
                    'display' => true,
                    'name' =>  $child['name'] ? ($objPois->name . ' (' . \Date::parse('d.m.Y H:i', $objPois->tstamp) . ')') : '',
                    'hide' => $child['hide'],
                    'content' => array
                    (
                        array(
                            'id' => '3',
                            'type' => 'GeoJSON',
                            'format' => 'GeoJSON',
                            'origType' => 'single',
                            'locationStyle' => '4',
                            'data' => array(
                                'type' => 'Feature',
                                'geometry' => array(
                                    'type' => 'Point',
                                    'coordinates' => array (
                                        (float) $objPois->longitude,
                                        (float) $objPois->latitude
                                    )
                                ),
                                'properties' => array
                                (
                                    'projection' => 'EPSG:4326',
                                )
                            )
                        )
                    )
                );



                $blnUsePopUp = false;
                $strPopUpInfo = "";
                if ($child->popup_info && $child->popup_info!="")
                {
                  $blnUsePopUp = true;
                  $arrDataForPopup = array
                  (
                    'name' => $objPois->name,
                    'time' =>\Date::parse('d.m.Y H:i', $objPois->tstamp),
                    'longitude' => $objPois->longitude,
                    'latitude' => $objPois->latitude
                  );
                  $strPopUpInfo = \String::parseSimpleTokens($child->popup_info, $arrDataForPopup);
                }

                /*$arrPoiData[] = array
                (
                    'parent' => $child->id . $objPois->id,
                    'geox' => $objPois->longitude,
                    'geoy' => $objPois->latitude,
                    'locstyle' => $child->locstyle,
                    'label' => '',
                    'onclick_zoomto' => '0',
                    'minzoom' => '0',
                    'maxzoom' => '0',
                    'graphicTitle' => '',
                    'popupInfo' => $strPopUpInfo,
                    'linkurl' => ''
                );*/
            }
        }

        return $arrPoiData;

    }

    public function runCronJob()
    {
      $objPoisForDelete = $this->Database->prepare("SELECT * FROM tl_c4g_tracking_pois WHERE forDelete=?")
                                         ->execute('1');
      if ($objPoisForDelete->numRows > 0)
      {
        $this->Database->prepare("DELETE FROM tl_c4g_tracking_pois WHERE forDelete=?")
                       ->execute('1');
      }

      $objTracksForDelete = $this->Database->prepare("SELECT * FROM tl_c4g_tracking_tracks WHERE forDelete=?")
                                         ->execute('1');

      if ($objTracksForDelete->numRows > 0)
      {
        while ($objTracksForDelete->next())
        {
          $intTrackUuid = $objTracksForDelete->uuid;
          $this->Database->prepare("DELETE FROM tl_c4g_tracking_positions WHERE track_uuid=?")
                          ->execute($intTrackUuid);
          $this->Database->prepare("DELETE FROM tl_c4g_tracking_tracks WHERE id=?")
                          ->execute($objTracksForDelete->id);
        }

      }
    }

}