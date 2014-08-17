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


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'JanoschOltmanns',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'JanoschOltmanns\Tracking'                 => 'system/modules/con4gis_tracking/classes/Tracking.php',
	'JanoschOltmanns\TrackingService'          => 'system/modules/con4gis_tracking/classes/TrackingService.php',

	// Models
	'JanoschOltmanns\C4gTrackingModel'         => 'system/modules/con4gis_tracking/models/C4gTrackingModel.php',
	'JanoschOltmanns\C4gTrackingPoisModel'     => 'system/modules/con4gis_tracking/models/C4gTrackingPoisModel.php',
	'JanoschOltmanns\C4gTrackingPositionsModel' => 'system/modules/con4gis_tracking/models/C4gTrackingPositionsModel.php',
	'JanoschOltmanns\C4gTrackingTracksModel'   => 'system/modules/con4gis_tracking/models/C4gTrackingTracksModel.php',
));
