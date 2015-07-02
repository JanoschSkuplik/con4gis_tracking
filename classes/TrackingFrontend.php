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
        'tLive'
    );

    public function addLocations($level, $child)
    {

        if (in_array($child->location_type, $this->arrAllowedLocationTypes))
        {
            $arrData = array();

            switch ($child->location_type)
            {
                case "tPois":
                    $arrData[0]['parent'] = $level;
                    $arrData[0]['id'] = $child->id;
                    $arrData[0]['type'] = 'struct';
                    $arrData[0]['layername'] = $child->data_layername;
                    $arrData[0]['hidelayer'] = $child->data_hidelayer > 0 ? $child->data_hidelayer : '';
                    $arrChildData = $this->getPoiData($child);
                    if (sizeof($arrChildData) == 0 && $child->tDontShowIfEmpty)
                    {
                      return;
                    }
                    $arrData = array_merge($arrData, $arrChildData);
                    break;
                case "tTracks":
                    $arrData[0]['parent'] = $level;
                    $arrData[0]['id'] = $child->id;
                    $arrData[0]['type'] = 'struct';
                    $arrData[0]['layername'] = $child->data_layername;
                    $arrData[0]['hidelayer'] = $child->data_hidelayer > 0 ? $child->data_hidelayer : '';
                    $arrChildData = $this->getTrackData($child);
                    if (sizeof($arrChildData) == 0 && $child->tDontShowIfEmpty)
                    {
                      return;
                    }
                    $arrData = array_merge($arrData, $arrChildData);
                    break;

                case "tLive":
                    $arrData[0]['parent'] = $level;
                    $arrData[0]['id'] = $child->id;
                    $arrData[0]['type'] = 'liveTracking';
                    $arrData[0]['locstyle'] = $child->locstyle > 0 ? $child->locstyle : '';
                    $arrData[0]['layername'] = $child->data_layername;
                    $arrData[0]['hidelayer'] = $child->data_hidelayer > 0 ? $child->data_hidelayer : '';

                    $GLOBALS['TL_BODY'][] = '<script src="system/modules/con4gis_tracking/assets/liveTracking.js"></script>';

                    break;
            }

            return $arrData;
        }

        return;
    }

    protected function getTrackData($child)
    {
        $arrTrackData = array();

        $strType = $child->memberVisibility ? $child->memberVisibility : "all";

        $arrMember = array();
        $arrVisibility = array();

        $blnUseDatabaseStatus = false;
        if ($child->useDatabaseStatus)
        {
          $arrAllowedStatus = deserialize($child->databaseStatus);
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
                    'parent' => $child->id,
                    'id' => $child->id . $objTracks->id,
                    'type' => 'ajax',
                    'locstyle' => $child->locstyle > 0 ? $child->locstyle : '',
                    'url' => 'system/modules/con4gis_core/api/trackingService?method=getTrack&id=' . $objTracks->uuid,
                    'layername' => $child->data_layername ? ($objTracks->name . ' (' . \Date::parse('d.m.Y H:i', $objTracks->tstamp) . ')') : '',
                    'hidelayer' => $child->data_hidelayer > 0 ? $child->data_hidelayer : '',
                    'popupInfo' => $objTracks->name
                );
            }
        }

        return $arrTrackData;
    }

    protected function getPoiData($child)
    {
        $arrPoiData = array();

        $strType = $child->memberVisibility ? $child->memberVisibility : "all";

        $arrMember = array();
        $arrVisibility = array();

        $blnUseDatabaseStatus = false;
        if ($child->useDatabaseStatus)
        {
          $arrAllowedStatus = deserialize($child->databaseStatus);
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
                    'parent' => $child->id,
                    'id' => $child->id . $objPois->id,
                    'type' => 'geojson',
                    'layername' =>  $child->data_layername ? ($objPois->name . ' (' . \Date::parse('d.m.Y H:i', $objPois->tstamp) . ')') : '',
                    'hidelayer' => $child->data_hidelayer > 0 ? $child->data_hidelayer : ''
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

                $arrPoiData[] = array
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
                );
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