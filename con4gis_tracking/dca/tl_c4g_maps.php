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
 * Table tl_c4g_maps
 */

$GLOBALS['TL_DCA']['tl_c4g_maps']['palettes']['__selector__'][] = 'memberVisibility';
$GLOBALS['TL_DCA']['tl_c4g_maps']['palettes']['__selector__'][] = 'useDatabaseStatus';

$GLOBALS['TL_DCA']['tl_c4g_maps']['palettes']['tPois'] = '{general_legend},name,profile,profile_mobile,published;{map_legend},is_map;{location_legend},location_type,tDontShowIfEmpty,memberVisibility,useDatabaseStatus,locstyle,data_layername,data_hidelayer,popup_info,routing_to,loc_linkurl,loc_onclick_zoomto,loc_minzoom,loc_maxzoom;{protection_legend:hide},protect_element;';

$GLOBALS['TL_DCA']['tl_c4g_maps']['palettes']['tTracks'] = '{general_legend},name,profile,profile_mobile,published;{map_legend},is_map;{location_legend},location_type,tDontShowIfEmpty,memberVisibility,useDatabaseStatus,data_layername,data_hidelayer,loc_onclick_zoomto,loc_minzoom,loc_maxzoom;{protection_legend:hide},protect_element;';

$GLOBALS['TL_DCA']['tl_c4g_maps']['palettes']['tLive'] = '{general_legend},name,profile,profile_mobile,published;{map_legend},is_map;{location_legend},location_type,memberVisibility,useDatabaseStatus,data_layername,data_hidelayer,loc_only_in_parent,loc_minzoom,loc_maxzoom;{protection_legend:hide},protect_element;';

$GLOBALS['TL_DCA']['tl_c4g_maps']['subpalettes']['memberVisibility_specialGroups'] = 'specialGroups';
$GLOBALS['TL_DCA']['tl_c4g_maps']['subpalettes']['memberVisibility_specialMember'] = 'specialMembers';
$GLOBALS['TL_DCA']['tl_c4g_maps']['subpalettes']['useDatabaseStatus'] = 'databaseStatus';

$GLOBALS['TL_DCA']['tl_c4g_maps']['fields']['useDatabaseStatus'] = array
(
  'label'                   => &$GLOBALS['TL_LANG']['tl_module']['protected'],
  'exclude'                 => true,
  'filter'                  => true,
  'inputType'               => 'checkbox',
  'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr'),
  'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_c4g_maps']['fields']['databaseStatus'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['databaseStatus'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'options'                 => array('privat','membergroups','owngroups','public'),
    'eval'                    => array('multiple'=>true, 'tl_class'=>'clr'),
    'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_c4g_maps']['fields']['tDontShowIfEmpty'] = array
(
  'label'                   => &$GLOBALS['TL_LANG']['tl_module']['tDontShowIfEmpty'],
  'exclude'                 => true,
  'filter'                  => true,
  'inputType'               => 'checkbox',
  'eval'                    => array('submitOnChange'=>false, 'tl_class'=>'clr'),
  'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_c4g_maps']['fields']['memberVisibility'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['source'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'options'                 => array('own','ownGroups','specialGroups','specialMember','all'),
    'eval'                    => array('includeBlankOption'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(32) NOT NULL default ''"
);


$GLOBALS['TL_DCA']['tl_c4g_maps']['fields']['specialGroups'] = array
(
			'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_maps']['allowedGroups'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'foreignKey'              => 'tl_member_group.name',
			'eval'                    => array('mandatory'=>false, 'multiple'=>true, 'tl_class'=>'clr'),
			'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_c4g_maps']['fields']['specialMembers'] = array
(
			'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_maps']['specialMembers'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'foreignKey'              => 'tl_member.email',
			'eval'                    => array('mandatory'=>false, 'multiple'=>true, 'tl_class'=>'clr'),
			'sql'                     => "blob NULL"
);
$GLOBALS['TL_DCA']['tl_c4g_maps']['fields']['specialGroups'] = array
(
			'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_maps']['specialGroups'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'foreignKey'              => 'tl_member_group.name',
			'eval'                    => array('mandatory'=>false, 'multiple'=>true, 'tl_class'=>'clr'),
			'sql'                     => "blob NULL"
);
