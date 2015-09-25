<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
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
	'c4g',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'c4g\Tracking'                    => 'system/modules/con4gis_tracking/classes/Tracking.php',
	'c4g\TrackingFrontend'            => 'system/modules/con4gis_tracking/classes/TrackingFrontend.php',
	'c4g\TrackingService'             => 'system/modules/con4gis_tracking/classes/TrackingService.php',

	// Models
	'c4g\C4gTrackingBoxesModel'       => 'system/modules/con4gis_tracking/models/C4gTrackingBoxesModel.php',
	'c4g\C4gTrackingBoxlocationModel' => 'system/modules/con4gis_tracking/models/C4gTrackingBoxlocationModel.php',
	'c4g\C4gTrackingDevicesModel'     => 'system/modules/con4gis_tracking/models/C4gTrackingDevicesModel.php',
	'c4g\C4gTrackingModel'            => 'system/modules/con4gis_tracking/models/C4gTrackingModel.php',
	'c4g\C4gTrackingPoisModel'        => 'system/modules/con4gis_tracking/models/C4gTrackingPoisModel.php',
	'c4g\C4gTrackingPositionsModel'   => 'system/modules/con4gis_tracking/models/C4gTrackingPositionsModel.php',
	'c4g\C4gTrackingTracksModel'      => 'system/modules/con4gis_tracking/models/C4gTrackingTracksModel.php',

	// Modules
	'c4g\ModuleSsoLogin'              => 'system/modules/con4gis_tracking/modules/ModuleSsoLogin.php',
	'c4g\ModuleTrackEdit'             => 'system/modules/con4gis_tracking/modules/ModuleTrackEdit.php',
	'c4g\ModuleTrackList'             => 'system/modules/con4gis_tracking/modules/ModuleTrackList.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_centralcontent' => 'system/modules/con4gis_tracking/templates/elements',
	'j_app4gis'          => 'system/modules/con4gis_tracking/templates/javascript',
	'mod_trackedit'      => 'system/modules/con4gis_tracking/templates/modules',
	'mod_tracklist'      => 'system/modules/con4gis_tracking/templates/modules',
));
