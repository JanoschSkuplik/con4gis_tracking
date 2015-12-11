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

class TrackingService extends \Controller
{

    private $arrReturn = array();
    private $blnDebugMode = false;

    public function __construct()
    {
        if ($this->Input->get('debug') && ($this->Input->get('debug')=='1' || $this->Input->get('debug')=='true'))
        {
            $this->blnDebugMode = true;
        }
    }

    public function generate()
    {

        \System::loadLanguageFile('tl_c4g_tracking');

        $strMethod = 'tracking' . ucfirst(\Input::get('method'));

        if (method_exists($this, $strMethod))
        {
            if ($this->$strMethod())
            {
                return json_encode($this->arrReturn);
            }
            return json_encode($this->getErrorReturn($GLOBALS['TL_LANG']['c4gTracking']['method_error'] . $strMethod));
        }
        else
        {
            return false;
        }

    }

    private function trackingGetLive()
    {

        if (\Input::get('maps'))
        {
            $intMapsItem = \Input::get('maps');
        }

        $this->import('Database');
        $time = time();
        $strTimeSelect = $time - (60*60);
        //$strTimeSelect = 0;

        /*$objPositions = $this->Database->prepare("SELECT * FROM (SELECT tl_c4g_tracking_positions.*, tl_c4g_tracking_tracks.name, tl_c4g_tracking_tracks.comment, tl_c4g_tracking_tracks.visibility FROM tl_c4g_tracking_positions  LEFT JOIN tl_c4g_tracking_tracks ON tl_c4g_tracking_positions.track_uuid=tl_c4g_tracking_tracks.uuid WHERE tl_c4g_tracking_positions.tstamp>? ORDER BY tl_c4g_tracking_positions.tstamp DESC) as inv GROUP BY track_uuid")
                                               ->execute($strTimeSelect);*/

        if (\Input::get('id'))
        {

            if (is_array(\Input::get('id')))
            {
                // multiple devices
                $arrDevices = \Input::get('id');

                $arrIds = implode(',', array_map('intval', $arrDevices));

                $objPositions = $this->Database->prepare("SELECT tl_c4g_tracking_devices.name, tl_c4g_tracking_positions.* FROM tl_c4g_tracking_devices LEFT JOIN tl_c4g_tracking_positions ON tl_c4g_tracking_devices.lastPositionId=tl_c4g_tracking_positions.id WHERE tl_c4g_tracking_devices.lastPositionId>0 AND tl_c4g_tracking_devices.id IN (" . $arrIds . ")")
                  ->execute();
            }
            else
            {
                // single devices
                $intDeviceId = \Input::get('id');
                $objPositions = $this->Database->prepare("SELECT tl_c4g_tracking_devices.name, tl_c4g_tracking_positions.* FROM tl_c4g_tracking_devices LEFT JOIN tl_c4g_tracking_positions ON tl_c4g_tracking_devices.lastPositionId=tl_c4g_tracking_positions.id WHERE tl_c4g_tracking_devices.lastPositionId>0 AND tl_c4g_tracking_devices.id=?")
                  ->execute($intDeviceId);
            }
        }
        elseif (\Input::get('useGroup'))
        {
            $intGroupId = \Input::get('useGroup');
            $objPositions = $this->Database->prepare("SELECT tl_c4g_tracking_devices.name, tl_c4g_tracking_positions.* FROM tl_c4g_tracking_devices LEFT JOIN tl_c4g_tracking_positions ON tl_c4g_tracking_devices.lastPositionId=tl_c4g_tracking_positions.id WHERE tl_c4g_tracking_devices.lastPositionId>0 AND tl_c4g_tracking_devices.groupId=?")
                                            ->execute($intGroupId);
        }
        else
        {
            // Fallback: keine weiteren Einstellungen -> alle Geräte mit Positionsdaten
            $objPositions = $this->Database->prepare("SELECT tl_c4g_tracking_devices.name, tl_c4g_tracking_positions.* FROM tl_c4g_tracking_devices LEFT JOIN tl_c4g_tracking_positions ON tl_c4g_tracking_devices.lastPositionId=tl_c4g_tracking_positions.id WHERE tl_c4g_tracking_devices.lastPositionId>0")
              ->execute();
        }



        if ($objPositions->numRows) {

            $arrFeatures = array();
            while ($objPositions->next())
            {

                $arrFeatures[] = array
                (
                    'type' => 'Feature',
                    'properties' => array
                    (
                        'name' => $objPositions->name ? $objPositions->name : $objPositions->comment,
                        'popup' => array(
                            'content' => 'devices:live;id,' . $objPositions->id . ';maps,' . $intMapsItem
                        )
                    ),
                    'geometry' => array
                    (
                        'type' => 'Point',
                        'coordinates' => array
                        (
                            (float) $objPositions->longitude,
                            (float) $objPositions->latitude
                        )
                    )
                );
                // Todo: alle Daten im properties-objekt bereit stellen
            }
            $arrReturn = array();
            $arrReturn['type'] = "FeatureCollection";
            $arrReturn['features'] = $arrFeatures;

            $this->arrReturn = $arrReturn;
            return true;
        }
        $this->arrReturn = array();
        return true;
    }

    private function trackingGetBoxTrack()
    {


        $blnUseFromFilter = false;
        $blnUseToFilter = false;

        $this->import('Database');
        $varBoxId = \Input::get('id');

        if (!is_array($varBoxId))
        {
            $varBoxId = array(
                0 => $varBoxId
            );
        }



        if (\Input::get('filterFrom'))
        {
            $blnUseFromFilter = true;
            $strFromFilter = \Input::get('filterFrom');
        }
        if (\Input::get('filterTo'))
        {
            $blnUseToFilter = true;
            $strToFilter = \Input::get('filterTo');
        }
        //filterFrom=1421017200&filterTo=1434060000

        $arrFeatures = array();

        foreach ($varBoxId as $intBoxId)
        {
            //echo $intBoxId;
            $arrCoordinates = array();

            $strAdditionalWhere = "";

            $arrParams = array();

            $arrParams[] = $intBoxId;

            if ($blnUseFromFilter)
            {
                $strAdditionalWhere .= " AND tstamp>?";
                $arrParams[] = $strFromFilter;
            }

            if ($blnUseToFilter)
            {
                $strAdditionalWhere .= " AND tstamp<?";
                $arrParams[] = $strToFilter;
            }


            $objPositions = $this->Database->prepare("SELECT * FROM tl_c4g_tracking_positions WHERE device=?" . $strAdditionalWhere . " ORDER BY tstamp DESC")
                                           ->execute($arrParams);

            if ($objPositions->numRows)
            {
                while ($objPositions->next())
                {
                    $arrCoordinates[] = array
                    (
                        (float) $objPositions->longitude,
                        (float) $objPositions->latitude
                    );
                }

                $arrGeometry = array();
                $arrGeometry['type'] = 'LineString';
                $arrGeometry['coordinates'] = $arrCoordinates;


                $arrFeatures[] = array
                (
                    'type' => 'Feature',
                    'geometry' => $arrGeometry,
                    'properties' => array
                    (
                        'projection' => 'EPSG:4326'
                    )
                );




            }

        }

        $arrReturn = array(
            'type' => 'FeatureCollection',
            'features' => $arrFeatures
        );


        $this->arrReturn = $arrReturn;
        return true;

    }

    private function trackingGetTrack()
    {
        $this->import('Database');
        $arrCoordinates = array();

        $trackId = \Input::get('id');

        $objPositions = $this->Database->prepare("SELECT * FROM tl_c4g_tracking_positions WHERE track_uuid=?")
                                       ->execute($trackId);

        if ($objPositions->numRows)
        {
          while ($objPositions->next())
          {
            $arrCoordinates[] = array
            (
                (float) $objPositions->longitude,
                (float) $objPositions->latitude
            );
          }
        }

        $arrGeometry = array();
        $arrGeometry['type'] = 'LineString';
        $arrGeometry['coordinates'] = $arrCoordinates;

        $arrFeatures = array();
        $arrFeatures[] = array
        (
            'type' => 'Feature',
            'geometry' => $arrGeometry,
            'properties' => array
            (
                'projection' => 'EPSG:4326'
            )
        );
        // Todo: alle Daten im properties-objekt bereit stellen

        $objPois = $this->Database->prepare("SELECT * FROM tl_c4g_tracking_pois WHERE trackUuid=?")
                                       ->execute($trackId);
        if ($objPois->numRows > 0)
        {
          while ($objPois->next())
          {
            $arrFeatures[] = array
            (
                'type' => 'Feature',
                'properties' => array
                (
                  'name' => $objPois->name ? $objPois->name : $objPois->comment
                ),
                'geometry' => array
                (
                    'type' => 'Point',
                    'coordinates' => array
                    (
                        (float) $objPois->longitude,
                        (float) $objPois->latitude
                    )
                )
            );
              // Todo: alle Daten im properties-objekt bereit stellen
          }
        }

        $arrReturn = array();
        $arrReturn['type'] = "FeatureCollection";
        $arrReturn['features'] = $arrFeatures;

        $this->arrReturn = $arrReturn;
        return true;
    }


    private function trackingNewPoi()
    {

        $arrPositionData = array();

        if ($this->blnDebugMode)
        {
            \Input::setPost('user',\Input::get('user'));
            \Input::setPost('configuration',\Input::get('configuration'));
            \Input::setPost('latitude',\Input::get('latitude'));
            \Input::setPost('longitude',\Input::get('longitude'));
            \Input::setPost('trackid',\Input::get('trackid'));
            \Input::setPost('accuracy',\Input::get('accuracy'));
            \Input::setPost('speed',\Input::get('speed'));
            \Input::setPost('imei',\Input::get('imei'));
        }

        $blnHasError = false;
        if (!\Input::post('user'))
        {
            $this->arrReturn = $this->getErrorReturn($GLOBALS['TL_LANG']['c4gTracking']['no_username']);
            $blnHasError = true;
        }
        if (!\Input::post('configuration'))
        {
            $this->arrReturn = $this->getErrorReturn($GLOBALS['TL_LANG']['c4gTracking']['no_config']);
            $blnHasError = true;
        }
        if (!\Input::post('latitude'))
        {
            $this->arrReturn = $this->getErrorReturn($GLOBALS['TL_LANG']['c4gTracking']['no_latitude'] );
            $blnHasError = true;
        }
        if (!\Input::post('longitude'))
        {
            $this->arrReturn = $this->getErrorReturn($GLOBALS['TL_LANG']['c4gTracking']['no_longitude']);
            $blnHasError = true;
        }

        $strName = "";
        if (\Input::post('name'))
        {
            $strName = \Input::post('name');
        }

        $intTrackId = 0;
        if (\Input::post('trackid'))
        {
            $intTrackId = \Input::post('trackid');
        }

        if (!$blnHasError)
        {

            $arrPositionData['latitude'] = \Input::post('latitude');
            $arrPositionData['longitude'] = \Input::post('longitude');

            // optional data
            $timeStamp = false;
            $arrAdditionalData = array();

            if (\Input::post('accuracy'))
            {
                $arrPositionData['accuracy'] = \Input::post('accuracy');
            }
            if (\Input::post('speed'))
            {
                $arrPositionData['speed'] = \Input::post('speed');
            }
            if (\Input::post('timestamp'))
            {
                $timeStamp = \Input::post('timestamp');
            }

            if (\Input::post('positiontype'))
            {
                $arrAdditionalData['positiontype'] = \Input::post('positiontype');
            }
            if (\Input::post('imei'))
            {
                $arrAdditionalData['imei'] = \Input::post('imei');
            }
            if (\Input::post('batterystatus'))
            {
                $arrAdditionalData['batterystatus'] = \Input::post('batterystatus');
            }
            if (\Input::post('networkinfo'))
            {
                $arrAdditionalData['networkinfo'] = \Input::post('networkinfo');
            }

            $arrPositionData['additionalData'] = $arrAdditionalData;

            $this->arrReturn['error'] = false;

            $this->arrReturn['track'] = \Tracking::setNewPoi(\Input::post('configuration'), \Input::post('user'), (\Input::post('privacy') ? \Input::post('privacy') : "privat"), $strName, $intTrackId, $timeStamp, $arrPositionData);


        }

        return true;
    }

    private function trackingNewPositionFromBox()
    {

        if ($this->blnDebugMode)
        {
            $arrParams = array('api_key','date','imei','latitude','longitude','phoneNo','speed','mileage','driverId','temperature','status');

            foreach ($arrParams as $strParam)
            {
                if (\Input::get($strParam))
                {
                    \Input::setPost($strParam, \Input::get($strParam));
                }
            }
        }

        // check mandatory params
        if (!\Input::post('api_key') || !\Input::post('date') || !\Input::post('imei') || !\Input::post('latitude') || !\Input::post('longitude'))
        {
            return false;
        }

        // check api_key
        $objTracking = \C4gTrackingModel::findBy('apiKey', \Input::post('api_key'));
        if ($objTracking === null)
        {
            return false;
        }

        // check imei number
        $objTrackingBox = \C4gTrackingDevicesModel::findByImeiEndpiece(\Input::post('imei'));
        if ($objTrackingBox === null)
        {
            return false;
        }

        $arrAdditionalData = array(
            'boxPhoneNo' => \Input::post('phoneNo') ? \Input::post('phoneNo') : '',
            'boxMileage' => \Input::post('mileage') ? \Input::post('mileage') : '',
            'boxDriverId' => \Input::post('driverId') ? \Input::post('driverId') : '',
            'boxTemperature' => \Input::post('temperature') ? \Input::post('temperature') : '',
            'boxStatus' => \Input::post('status') ? \Input::post('status') : '',
            'imei' => \Input::post('imei')
        );

        \Tracking::setNewPosition("devices", \Input::post('latitude'), \Input::post('longitude'), \Input::post('accuracy'), \Input::post('speed'), \Input::post('date'), $arrAdditionalData);


        return true;
    }

    /**
     *
     * @GET-Parameter vom SMS-Gateway
     * sender
     * timestamp (wann SMS-Gateway die SMS empfangen hat YYYYmmddHHiiss)
     * text -> Inhalt der SMS
     * msgid
     * apikey
     *
     * @return bool
     */
    private function trackingNewPositionFromSms()
    {

        $blnHasError = false;

        if ($this->blnDebugMode)
        {
            \Input::setPost('text',\Input::get('text'));
        }

        if (!\Input::post('text'))
        {
            $this->arrReturn = $this->getErrorReturn($GLOBALS['TL_LANG']['c4gTracking']['no_data']);
            $blnHasError = true;
        }

        $strSmsContent = \Input::post('text');

        $arrSmsContent = explode(';', $strSmsContent);

        if (!is_array($arrSmsContent) || sizeof($arrSmsContent) == 0)
        {
            $this->arrReturn = $this->getErrorReturn($GLOBALS['TL_LANG']['c4gTracking']['data_error']);
            $blnHasError = true;
        }

        if (!$blnHasError)
        {

            if ($arrSmsContent[0] == "newPosition")
            {

                $arrAdditionalData = array();

                $arrAdditionalData['trackUuid'] = $arrSmsContent[1];
                $strLatitude = $arrSmsContent[2];
                $strLongitude = $arrSmsContent[3];
                $strTimestamp = $arrSmsContent[4];

                $arrAdditionalData = array();
                if ($arrSmsContent[5])
                {
                    $strBatterystatus = $arrSmsContent[5];
                    $arrAdditionalData['batterystatus'] = $strBatterystatus;
                }

                $this->arrReturn['error'] = false;

                $this->arrReturn['track'] = \Tracking::setNewPosition("tracks", $strLatitude, $strLongitude, 0, 0, $strTimestamp, $arrAdditionalData);


            }
        }


        return true;

    }

    private function trackingNewPosition()
    {

        if ($this->blnDebugMode)
        {
            \Input::setPost('track',\Input::get('track'));
            \Input::setPost('latitude',\Input::get('latitude'));
            \Input::setPost('longitude',\Input::get('longitude'));
        }

        $blnHasError = false;
        if (!\Input::post('track'))
        {
            $this->arrReturn = $this->getErrorReturn($GLOBALS['TL_LANG']['c4gTracking']['no_track']);
            $blnHasError = true;
        }
        if (!\Input::post('latitude'))
        {
            $this->arrReturn = $this->getErrorReturn($GLOBALS['TL_LANG']['c4gTracking']['no_latitude'] );
            $blnHasError = true;
        }
        if (!\Input::post('longitude'))
        {
            $this->arrReturn = $this->getErrorReturn($GLOBALS['TL_LANG']['c4gTracking']['no_longitude']);
            $blnHasError = true;
        }
        if (!$blnHasError)
        {
            // optional data
            $longAccuracy = 0;
            $longSpeed = 0;
            $timeStamp = false;
            $arrAdditionalData = array();

            if (\Input::post('accuracy'))
            {
                $longAccuracy = \Input::post('accuracy');
            }
            if (\Input::post('speed'))
            {
                $longAccuracy = \Input::post('speed');
            }
            if (\Input::post('timestamp'))
            {
                $timeStamp = \Input::post('timestamp');
            }

            if (\Input::post('positiontype'))
            {
                $arrAdditionalData['positiontype'] = \Input::post('positiontype');
            }
            if (\Input::post('imei'))
            {
                $arrAdditionalData['imei'] = \Input::post('imei');
            }
            if (\Input::post('batterystatus'))
            {
                $arrAdditionalData['batterystatus'] = \Input::post('batterystatus');
            }
            if (\Input::post('networkinfo'))
            {
                $arrAdditionalData['networkinfo'] = \Input::post('networkinfo');
            }

            $this->arrReturn['error'] = false;
            $this->arrReturn['track'] = \Tracking::setNewPosition(\Input::post('track'), \Input::post('latitude'), \Input::post('longitude'), $longAccuracy, $longSpeed, $timeStamp, $arrAdditionalData);

        }

        return true;
    }

    private function trackingNewTrack()
    {

        if ($this->blnDebugMode)
        {
            \Input::setPost('user',\Input::get('user'));
            \Input::setPost('configuration',\Input::get('configuration'));
        }

        $blnHasError = false;
        if (!\Input::post('user'))
        {
            $this->arrReturn = $this->getErrorReturn($GLOBALS['TL_LANG']['c4gTracking']['no_username']);
            $blnHasError = true;
        }
        if (!\Input::post('configuration'))
        {
            $this->arrReturn = $this->getErrorReturn($GLOBALS['TL_LANG']['c4gTracking']['no_config']);
            $blnHasError = true;
        }
        $strName = "";
        if (\Input::post('name'))
        {
            $strName = \Input::post('name');
        }
        if (!$blnHasError)
        {
            $longAccuracy = 0;
            $longSpeed = 0;
            $timeStamp = false;
            $arrAdditionalData = array();

            if (\Input::post('accuracy'))
            {
                $longAccuracy = \Input::post('accuracy');
            }
            if (\Input::post('speed'))
            {
                $longAccuracy = \Input::post('speed');
            }
            if (\Input::post('timestamp'))
            {
                $timeStamp = \Input::post('timestamp');
            }
            if (\Input::post('positiontype'))
            {
                $arrAdditionalData['positiontype'] = \Input::post('positiontype');
            }
            if (\Input::post('imei'))
            {
                $arrAdditionalData['imei'] = \Input::post('imei');
            }
            if (\Input::post('batterystatus'))
            {
                $arrAdditionalData['batterystatus'] = \Input::post('batterystatus');
            }
            if (\Input::post('networkinfo'))
            {
                $arrAdditionalData['networkinfo'] = \Input::post('networkinfo');
            }

            $this->arrReturn['error'] = false;
            $arrTrackData = \Tracking::setNewTrack(\Input::post('configuration'), \Input::post('user'), $strName, $timeStamp, \Input::post('privacy'), $arrAdditionalData);

            $this->arrReturn['track'] = $arrTrackData;

            /* Store start location */
            if ($arrTrackData['trackId'] && \Input::post('latitude') && \Input::post('longitude'))
            {
              \Tracking::setNewPosition($arrTrackData['trackUuid'], \Input::post('latitude'), \Input::post('longitude'), $longAccuracy, $longSpeed, $timeStamp, $arrAdditionalData);
            }

        }

        return true;
    }

    private function trackingLoginUser()
    {
        if ($this->blnDebugMode)
        {
            \Input::setPost('user',\Input::get('user'));
            \Input::setPost('password',\Input::get('password'));
        }

        $blnHasError = false;

        if (!\Input::post('user') && !\Input::post('password'))
        {
            $this->arrReturn = $this->getErrorReturn($GLOBALS['TL_LANG']['c4gTracking']['no_user_password']);
            $blnHasError = true;
        }
        elseif (!\Input::post('user'))
        {
            $this->arrReturn = $this->getErrorReturn($GLOBALS['TL_LANG']['c4gTracking']['no_username']);
            $blnHasError = true;
        }
        elseif (!\Input::post('password'))
        {
            $this->arrReturn = $this->getErrorReturn($GLOBALS['TL_LANG']['c4gTracking']['no_password']);
            $blnHasError = true;
        }

        if ($blnHasError)
        {
            return true;
        }

        \Input::setPost('username', \Input::post('user'));

        $this->import('FrontendUser', 'User');
        if (!$this->User->login())
        {
            $this->arrReturn = $this->getErrorReturn($GLOBALS['TL_LANG']['c4gTracking']['wrong_login']);
            $blnHasError = true;
        }
        else
        {

            $arrTrackingConfig = \Tracking::getTrackingConfig();

            if ($arrTrackingConfig['limitAccess'])
            {
                $arrAllowedGroups = $arrTrackingConfig['accessGroups'];

                $blnIsInAccessGroup = false;

                foreach ($arrAllowedGroups as $group)
                {
                    if ($this->User->isMemberOf($group))
                    {
                        $blnIsInAccessGroup = true;
                    }
                }

                if (!$blnIsInAccessGroup)
                {
                    $this->arrReturn = $this->getErrorReturn($GLOBALS['TL_LANG']['c4gTracking']['no_group_access']);
                    $blnHasError = true;
                }

            }

            if (!$blnHasError)
            {
                $this->import('Database');
                $strUniqId = md5(uniqid());
                $this->Database->prepare("UPDATE tl_member SET ssoHash=? WHERE id=?")->execute($strUniqId,$this->User->id);

                $this->arrReturn['error'] = false;
                $this->arrReturn['userId'] = $this->User->id;
                $this->arrReturn['userName'] = $this->User->username;
                $this->arrReturn['userHash'] = $strUniqId;
                $this->arrReturn['userRealName'] = ($this->User->firstname ? ($this->User->firstname . " ") : '') . $this->User->lastname;
                $this->arrReturn['trackingConfig'] = $arrTrackingConfig;
            }

        }

        return true;
    }

    private function trackingGetConfiguration()
    {
        $this->arrReturn['error'] = false;
        $this->arrReturn['trackingConfig'] = \Tracking::getTrackingConfig();
        return true;
    }

    private function trackingTest()
    {
        $this->arrReturn = array
        (
            'error' => false
        );

        return true;
    }

    private function trackingFalseReturn()
    {
        return false;
    }

    private function trackingRegisterDevice()
    {

        $blnHasError = false;
        if (!\Input::post('configuration'))
        {
            $this->arrReturn = $this->getErrorReturn($GLOBALS['TL_LANG']['c4gTracking']['no_config']);
            $blnHasError = true;
        }

        if (!$blnHasError)
        {
            $strType = \Input::post('type');
            $strImei = \Input::post('imei');
            $strToken = \Input::post('token');
            $intCofingId = \Input::post('configuration');


            $arrSet = array
            (
                'pid' => $intCofingId,
                'tstamp' => time(),
                'type' => $strType,
                'imei' => $strImei,
                'token' => $strToken
            );

            $objDevice = \C4gTrackingDevicesModel::findBy('imei', $strImei);

            if ($objDevice !== null)
            {

            }
            else
            {
                $objDevice = new \C4gTrackingDevicesModel();
            }


            $objDevice->setRow($arrSet)->save();

            $this->arrReturn['error'] = false;
        }

        return true;
    }

    private function getErrorReturn($strMessage)
    {
        $arrReturn = array();
        $arrReturn['error'] = true;
        $arrReturn['message'] = $strMessage;
        return $arrReturn;
    }

    private function trackingGetLastPositionForMember()
    {
        $intMemberId = \Input::get('member');
        $intMaxAge = \Input::get('max') ? \Input::get('max') : 0;

        $this->arrReturn['error'] = false;
        $this->arrReturn['position'] = $this->getLastPositionForMember($intMemberId, $intMaxAge);

        return true;
    }

    public function getLastPositionForMember($intMemberId, $intMaxAge=0)
    {

        $this->import('Database');

        if ($intMaxAge == 0)
        {
            $strTimeStamp = 0;
        }
        else
        {
            $strTimeStamp = time() - (60*$intMaxAge);
        }

        $objPositionsFromTracks = $this->Database->prepare("SELECT * FROM
        (SELECT tl_c4g_tracking_positions.*, tl_c4g_tracking_tracks.name, tl_c4g_tracking_tracks.member, tl_c4g_tracking_tracks.visibility FROM tl_c4g_tracking_positions  LEFT JOIN tl_c4g_tracking_tracks ON tl_c4g_tracking_positions.track_uuid=tl_c4g_tracking_tracks.uuid WHERE tl_c4g_tracking_positions.tstamp>? ORDER BY tl_c4g_tracking_positions.tstamp DESC) as inv WHERE member=? GROUP BY track_uuid ORDER BY tstamp DESC")
                                       ->limit(1)
                                       ->execute($strTimeStamp, $intMemberId);

        if ($objPositionsFromTracks->numRows > 0)
        {
            return $objPositionsFromTracks;
        }
        else
        {
            return false;
        }

    }

}