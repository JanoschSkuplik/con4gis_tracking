<?php

/**
 * Contao Open Source CMS
 *
 * @version   php 5
 * @package   con4gis_tracking
 * @author    Janosch Oltmanns
 * @license   GNU/LGPL http://opensource.org/licenses/lgpl-3.0.html
 * @copyright Janosch Oltmanns in cooperation with Küstenschmiede GmbH Software & Design 2014 - 2015
 * @link      http://janosch-oltmanns.de https://www.kuestenschmiede.de
 */


/**
 * Table tl_module
 */

$GLOBALS['TL_DCA']['tl_module']['palettes']['c4g_tracklist'] = '{title_legend},name,headline,type;{config_legend},jumpTo,showTracks,showPois,showWithoutFilter,editWithoutFilter;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['c4g_trackedit'] = '{title_legend},name,headline,type;{config_legend},jumpTo,editWithoutFilter;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['fields']['showTracks'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['showTracks'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['showPois'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['showPois'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['showWithoutFilter'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['showWithoutFilter'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['editWithoutFilter'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['editWithoutFilter'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "char(1) NOT NULL default ''"
);