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
 * @filesource
 */


/**
 * Global settings
 */
$GLOBALS['con4gis_tracking_extension']['installed']    = true;
$GLOBALS['con4gis_tracking_extension']['version']      = '1.0.0';

/**
 * Frontend Modules
 */
$GLOBALS['FE_MOD']['miscellaneous']['c4g_ssologin'] = 'ModuleSsoLogin';
$GLOBALS['FE_MOD']['miscellaneous']['c4g_tracklist'] = 'ModuleTrackList';
$GLOBALS['FE_MOD']['miscellaneous']['c4g_trackedit'] = 'ModuleTrackEdit';

/**
 * Backend Modules
 */
array_insert($GLOBALS['BE_MOD']['con4gis'], 5, array
(
	'c4g_tracking' => array
	(
    'tables'      => array('tl_c4g_tracking', 'tl_c4g_tracking_devices', 'tl_c4g_tracking_pois',
    'tl_c4g_tracking_tracks', 'tl_c4g_tracking_positions'),
    'icon'	 		=> 'system/modules/con4gis_tracking/assets/tracking.png',
	)
));

/**
 * Hooks
 */
//$GLOBALS['TL_HOOKS']['dispatchAjax']['trackingDispatchAjax'] = array('TrackingService', 'trackingDispatchAjax');
$GLOBALS['TL_HOOKS']['c4gAddLocationsParent']['tracking'] = array('TrackingFrontend','addLocations');
$GLOBALS['TL_CRON']['daily'][] = array('TrackingFrontend', 'runCronJob');

/**
 * Rest-API
 */
$GLOBALS['TL_API']['trackingService'] = 'TrackingService';

$GLOBALS['c4g_locationtypes'][] = 'tPois';
$GLOBALS['c4g_locationtypes'][] = 'tTracks';
$GLOBALS['c4g_locationtypes'][] = 'tLive';