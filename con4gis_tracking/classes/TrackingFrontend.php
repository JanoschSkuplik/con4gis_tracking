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

class TrackingFrontend extends \Frontend
{

    private $arrAllowedLocationTypes = array
    (
        't_pois',
        't_tracks'
    );

    public function addLocations($level, $child)
    {

        if (in_array($child->location_type, $this->arrAllowedLocationTypes))
        {
            $arrData = array();

            switch ($child->location_type)
            {
                case "t_pois":
                    $arrData[0]['parent'] = $level;
                    $arrData[0]['id'] = $child->id;
                    $arrData[0]['type'] = 'struct';
                    $arrData[0]['layername'] = $child->name;
                    $arrData[0]['hidelayer'] = $child->data_hidelayer;

                    $arrData = array_merge($arrData, $this->getPoiData($child->id));

                    break;
                case "t_tracks":
                    $arrData[0]['parent'] = $level;
                    $arrData[0]['id'] = $child->id;
                    $arrData[0]['type'] = 'struct';
                    $arrData[0]['layername'] = $child->name;
                    $arrData[0]['hidelayer'] = $child->data_hidelayer;

                    $arrData = array_merge($arrData, $this->getTrackData($child->id));

                    break;
            }

            return $arrData;
        }

        return;
    }

    protected function getTrackData($level)
    {
        $arrTrackData = array();

        $objTracks = \C4gTrackingTracksModel::findAll();

        if ($objTracks !== null)
        {
            while($objTracks->next())
            {
                $arrTrackData[] = array
                (
                    'parent' => $level,
                    'id' => $level . $objTracks->id,
                    'type' => 'struct',
                    'layername' => $objTracks->name . ' (' . \Date::parse('d.m.Y H:i', $objTracks->tstamp) . ')',
                    'hidelayer' => '1'
                );
            }
        }

        return $arrTrackData;
    }

    protected function getPoiData($level)
    {
        $arrPoiData = array();

        $objPois = \C4gTrackingPoisModel::findAll();

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
                    'parent' => $level,
                    'id' => $level . $objPois->id,
                    'type' => 'geojson',
                    'layername' => $objPois->name . ' (' . \Date::parse('d.m.Y H:i', $objPois->tstamp) . ')',
                    'hidelayer' => '1'
                );
                $arrPoiData[] = array
                (
                    'parent' => $level . $objPois->id,
                    'geox' => $objPois->longitude,
                    'geoy' => $objPois->latitude,
                    'locstyle' => '7',
                    'label' => '',
                    'onclick_zoomto' => '0',
                    'minzoom' => '0',
                    'maxzoom' => '0',
                    'graphicTitle' => '',
                    'popupInfo' => $objPois->name . ' (' . \Date::parse('d.m.Y H:i', $objPois->tstamp) . ')',
                    'linkurl' => ''
                );
            }
        }

        return $arrPoiData;

    }

}