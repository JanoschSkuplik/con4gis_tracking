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

$GLOBALS['FE_MOD']['miscellaneous']['c4g_ssologin'] = 'ModuleSsoLogin';


/**
 * Backend Modules
 */
$GLOBALS['BE_MOD']['con4gis']['c4g_tracking'] = array
(
   'tables'      => array('tl_c4g_tracking', 'tl_c4g_tracking_devices', 'tl_c4g_tracking_pois',
   'tl_c4g_tracking_tracks', 'tl_c4g_tracking_positions'),
   'icon'	 		=> 'system/modules/con4gis_tracking/assets/icon.gif',
);

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['dispatchAjax']['trackingDispatchAjax'] = array('TrackingService', 'trackingDispatchAjax');