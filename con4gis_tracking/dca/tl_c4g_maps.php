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

$GLOBALS['TL_DCA']['tl_c4g_maps']['palettes']['t_pois'] = '{general_legend},name,profile,profile_mobile,published;{map_legend},is_map;{location_legend},location_type,loc_geox,loc_geoy,locstyle,loc_only_in_parent,loc_label,tooltip,popup_info,routing_to,loc_linkurl,loc_onclick_zoomto,loc_minzoom,loc_maxzoom;{protection_legend:hide},protect_element;';

$GLOBALS['TL_DCA']['tl_c4g_maps']['palettes']['t_tracks'] = '{general_legend},name,profile,profile_mobile,published;{map_legend},is_map;{location_legend},location_type,loc_geox,loc_geoy,locstyle,loc_only_in_parent,loc_label,tooltip,popup_info,routing_to,loc_linkurl,loc_onclick_zoomto,loc_minzoom,loc_maxzoom;{protection_legend:hide},protect_element;';