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

class Tracking extends \Controller
{

    /**
     * Tracking version
     */
    const VERSION = '0.0.1';

    public static function setNewPosition($intTrackId, $dblLatitude, $dblLongitude, $longAccuracy=0, $longSpeed=0)
    {
        $arrTrackingPosition = array();
        $time = time();

        $arrSet = array
        (
            'tstamp'    => $time,
            'pid'    => $intTrackId,
            'latitude' => $dblLatitude,
            'longitude' => $dblLongitude,
            'accuracy' => $longAccuracy,
            'speed' => $longSpeed
        );

        $objPosition = new \C4gTrackingPositionsModel();
        $objPosition->setRow($arrSet)->save();

        $arrTrackingPosition['positionId'] = $objPosition->id;

        return $arrTrackingPosition;
    }

    public static function setNewPoi($intConfiguration, $intUserId, $dblLatitude, $dblLongitude, $longAccuracy=0, $longSpeed=0, $strComment="")
    {
        $arrTrackingPoi = array();
        $time = time();
        $strUuid = uniqid('', true);

        $arrSet = array
        (
            'tstamp'    => $time,
            'pid'    => $intConfiguration,
            'uuid'  => $strUuid,
            'member' => $intUserId,
            'latitude' => $dblLatitude,
            'longitude' => $dblLongitude,
            'accuracy' => $longAccuracy,
            'speed' => $longSpeed
        );

        $objPoi = new \C4gTrackingPoisModel();
        $objPoi->setRow($arrSet)->save();

        $arrTrackingPoi['poiId'] = $objPoi->id;

        return $arrTrackingPoi;
    }

    public static function setNewTrack($intConfiguration, $intUserId)
    {
        $arrTrackingTrack = array();
        $time = time();
        $strUuid = uniqid('', true);

        $arrSet = array
        (
            'tstamp'    => $time,
            'pid'    => $intConfiguration,
            'uuid'  => $strUuid,
            'member' => $intUserId
        );

        $objTrack = new \C4gTrackingTracksModel();
        $objTrack->setRow($arrSet)->save();

        $arrTrackingTrack['trackId'] = $objTrack->id;

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

                foreach ($arrTrackingInformation as $key=>$value)
                {
                    $arrTrackingConfig[$key] = self::manipulateTrackingInfo($key, $value);
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

        return $arrTrackingConfig;

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

}