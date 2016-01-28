<?php

/**
 * @package   c4g\Test
 * @author    Coastforge
 * @license   GNU/LGPL
 * @copyright Küstenschmiede GmbH Software & Design
 */


namespace c4g\Tracking;

class TrackingPluginLoader
{

    public function loadTrackingPlugin()
    {

        // load language script
        if ($GLOBALS['TL_LANGUAGE'] == 'de') {
            $GLOBALS['TL_JAVASCRIPT']['c4g-maps-constant-i18n'] = 'system/modules/con4gis_tracking/assets/js/c4g-maps-plugin-trackingfilter-constant-i18n-de.js';
        } else {
            // use english as fallback
            $GLOBALS['TL_JAVASCRIPT']['c4g-maps-constant-i18n'] = 'system/modules/con4gis_tracking/assets/js/c4g-maps-plugin-trackingfilter-constant-i18n-en.js';
        }


        $GLOBALS['TL_JAVASCRIPT']['c4g-maps-plugin-trackingdatafilter-constant'] = 'system/modules/con4gis_tracking/assets/js/c4g-maps-plugin-trackingfilter-constant.js';

        $GLOBALS['TL_JAVASCRIPT']['c4g-maps-plugin-trackingdatafilter-jquery-simple-datetimepicker'] = 'system/modules/con4gis_tracking/assets/js/jquery-simple-datetimepicker/1.13.0/jquery.simple-dtpicker.js';

        $GLOBALS['TL_JAVASCRIPT']['c4g-maps-plugin-trackingdatafilter'] = 'system/modules/con4gis_tracking/assets/js/c4g-maps-plugin-trackingdatafilter.js';

        $GLOBALS['TL_CSS']['c4g-maps-plugin-trackingdatafilter-jquery-simple-datetimepicker'] = 'system/modules/con4gis_tracking/assets/js/jquery-simple-datetimepicker/1.13.0/jquery.simple-dtpicker.css';
        $GLOBALS['TL_CSS']['c4g-maps-plugin-trackingdatafilter'] = 'system/modules/con4gis_tracking/assets/css/c4g-maps-plugin-trackingdatafilter.css';

    }
}
