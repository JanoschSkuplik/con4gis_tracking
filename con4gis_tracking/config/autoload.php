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
	// Modules
	'JanoschOltmanns\ModuleSsoLogin'            => 'system/modules/con4gis_tracking/modules/ModuleSsoLogin.php',

	// Classes
	'JanoschOltmanns\TrackingService'           => 'system/modules/con4gis_tracking/classes/TrackingService.php',
	'JanoschOltmanns\Tracking'                  => 'system/modules/con4gis_tracking/classes/Tracking.php',

	// Models
	'JanoschOltmanns\C4gTrackingModel'          => 'system/modules/con4gis_tracking/models/C4gTrackingModel.php',
	'JanoschOltmanns\C4gTrackingTracksModel'    => 'system/modules/con4gis_tracking/models/C4gTrackingTracksModel.php',
	'JanoschOltmanns\C4gTrackingPositionsModel' => 'system/modules/con4gis_tracking/models/C4gTrackingPositionsModel.php',
	'JanoschOltmanns\C4gTrackingPoisModel'      => 'system/modules/con4gis_tracking/models/C4gTrackingPoisModel.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'j_app4gis'          => 'system/modules/con4gis_tracking/templates/javascript',
	'mod_centralcontent' => 'system/modules/con4gis_tracking/templates/elements',
));
