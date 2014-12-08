<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Con4gis_tracking
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
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
	'JanoschOltmanns\Tracking'                  => 'system/modules/con4gis_tracking/classes/Tracking.php',
	'JanoschOltmanns\TrackingFrontend'          => 'system/modules/con4gis_tracking/classes/TrackingFrontend.php',
	'JanoschOltmanns\TrackingService'           => 'system/modules/con4gis_tracking/classes/TrackingService.php',

	// Models
	'JanoschOltmanns\C4gTrackingModel'          => 'system/modules/con4gis_tracking/models/C4gTrackingModel.php',
	'JanoschOltmanns\C4gTrackingPoisModel'      => 'system/modules/con4gis_tracking/models/C4gTrackingPoisModel.php',
	'JanoschOltmanns\C4gTrackingPositionsModel' => 'system/modules/con4gis_tracking/models/C4gTrackingPositionsModel.php',
	'JanoschOltmanns\C4gTrackingTracksModel'    => 'system/modules/con4gis_tracking/models/C4gTrackingTracksModel.php',

	// Modules
	'JanoschOltmanns\ModuleSsoLogin'            => 'system/modules/con4gis_tracking/modules/ModuleSsoLogin.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_centralcontent' => 'system/modules/con4gis_tracking/templates/elements',
	'j_app4gis'          => 'system/modules/con4gis_tracking/templates/javascript',
));
