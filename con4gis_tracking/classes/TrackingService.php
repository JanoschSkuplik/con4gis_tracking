<?php

/**
 * Contao Open Source CMS
 *
 * @version   php 5
 * @package   con4gis_tracking
 * @author    Janosch Oltmanns
 * @license   GNU/LGPL http://opensource.org/licenses/lgpl-3.0.html
 * @copyright Janosch Oltmanns in cooperation with Küstenschmiede GmbH Software & Design 2014
 * @link      http://janosch-oltmanns.de https://www.kuestenschmiede.de
 */

namespace JanoschOltmanns;

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
        $this->import('Database');
        $time = time();
        $strTimeSelect = $time - (60*60);
        //$strTimeSelect = 0;

        $objPositions = $this->Database->prepare("SELECT * FROM(SELECT tl_c4g_tracking_positions.*, tl_c4g_tracking_tracks.name, tl_c4g_tracking_tracks.comment, tl_c4g_tracking_tracks.visibility FROM tl_c4g_tracking_positions  LEFT JOIN tl_c4g_tracking_tracks ON tl_c4g_tracking_positions.pid=tl_c4g_tracking_tracks.id WHERE tl_c4g_tracking_positions.tstamp>? ORDER BY tl_c4g_tracking_positions.tstamp DESC) as inv GROUP BY pid")
                                               ->execute($strTimeSelect);
        if ($objPositions->numRows)
        {
          
            $arrFeatures = array();
            while ($objPositions->next())
            {
              
                //print_r($objPositions->row());
              
                $arrFeatures[] = array
                (
                    'type' => 'Feature',
                    'properties' => array
                    (
                      'name' => $objPositions->name ? $objPositions->name : $objPositions->comment
                    ),
                    'geometry' => array
                    (
                        'type' => 'Point',
                        'coordinates' => array
                        (
                            $objPositions->longitude,
                            $objPositions->latitude
                        )
                    )
                );
            }
            $arrReturn = array();
            $arrReturn['type'] = "FeatureCollection";
            $arrReturn['features'] = $arrFeatures;

            $this->arrReturn = $arrReturn;
            return true;
        }
        return false;
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
                $objPositions->longitude,
                $objPositions->latitude
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
                        $objPois->longitude,
                        $objPois->latitude
                    )
                )
            );
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

        if ($this->blnDebugMode)
        {
            \Input::setPost('user',\Input::get('user'));
            \Input::setPost('configuration',\Input::get('configuration'));
            \Input::setPost('latitude',\Input::get('latitude'));
            \Input::setPost('longitude',\Input::get('longitude'));
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
            $this->arrReturn['error'] = false;
            $this->arrReturn['track'] = \Tracking::setNewPoi(\Input::post('configuration'), \Input::post('user'), \Input::post('latitude'), \Input::post('longitude'), 0, 0, $strName, false, \Input::post('privacy'), $intTrackId);
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
            $this->arrReturn['error'] = false;
            $this->arrReturn['track'] = \Tracking::setNewPosition(\Input::post('track'), \Input::post('latitude'), \Input::post('longitude'));
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
            $this->arrReturn['error'] = false;
            $arrTrackData = \Tracking::setNewTrack(\Input::post('configuration'), \Input::post('user'), $strName, false, \Input::post('privacy'));
            $this->arrReturn['track'] = $arrTrackData;
            
            /* Store start location */
            if ($arrTrackData['trackId'] && \Input::post('latitude') && \Input::post('longitude'))
            {
              \Tracking::setNewPosition($arrTrackData['trackUuid'], \Input::post('latitude'), \Input::post('longitude'));
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
            $this->import('Database');
            $strUniqId = md5(uniqid());
            $this->Database->prepare("UPDATE tl_member SET ssoHash=? WHERE id=?")->execute($strUniqId,$this->User->id);
            
            $this->arrReturn['error'] = false;
            $this->arrReturn['userId'] = $this->User->id;
            $this->arrReturn['userName'] = $this->User->username;
            $this->arrReturn['userHash'] = $strUniqId;
            $this->arrReturn['userRealName'] = ($this->User->firstname ? ($this->User->firstname . " ") : '') . $this->User->lastname;
            $this->arrReturn['trackingConfig'] = \Tracking::getTrackingConfig();


        }

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

    private function getErrorReturn($strMessage)
    {
        $arrReturn = array();
        $arrReturn['error'] = true;
        $arrReturn['message'] = $strMessage;
        return $arrReturn;
    }


}