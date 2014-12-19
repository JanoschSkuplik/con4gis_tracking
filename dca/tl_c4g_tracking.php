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
 * Table tl_comments
 */
$GLOBALS['TL_DCA']['tl_c4g_tracking'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
        'ctable'                      => array('tl_c4g_tracking_pois', 'tl_c4g_tracking_tracks'),
        'enableVersioning'            => true,
        'onload_callback'             => array
        (
            array('tl_c4g_tracking', 'showConfigHint')
        ),
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary'
			)
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 2,
			'fields'                  => array('name'),
			'flag'                    => 1,
			'panelLayout'             => 'sort,search,limit'
		),
		'label' => array
		(
			'fields'                  => array('name'),
			'format'                  => '%s',
			//'label_callback'          => array('tl_theme', 'addPreviewImage')
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			)
		),
		'operations' => array
		(
            'pois' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_c4g_tracking']['pois'],
                'href'                => 'table=tl_c4g_tracking_pois',
                'icon'                => 'system/modules/con4gis_tracking/assets/location_flag.png',
                //'button_callback'     => array('tl_theme', 'editModules')
            ),
            'tracks' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_c4g_tracking']['tracks'],
                'href'                => 'table=tl_c4g_tracking_tracks',
                'icon'                => 'system/modules/con4gis_tracking/assets/location_track.png',
                //'button_callback'     => array('tl_theme', 'editLayout')
            ),
            'edit' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_c4g_tracking']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif'
            ),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_c4g_tracking']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_c4g_tracking']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif',
				'attributes'          => 'style="margin-right:3px"'
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{title_legend},name;{config_legend}'
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_tracking']['name'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>true, 'unique'=>true, 'decodeEntities'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
			'sql'                     => "varchar(128) NOT NULL default ''"
		),

	)
);


/**
 * Class tl_c4g_tracking
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @package   con4gis_tracking
 * @author    Janosch Oltmanns
 * @copyright Janosch Oltmanns in cooperation with Küstenschmiede GmbH Software & Design 201
 */
class tl_c4g_tracking extends Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}

    /**
     * Show a hint if a track-configuration is not set in the root-page-settings
     * @param object
     */
    public function showConfigHint($dc)
    {
        if (Input::get('act') == 'edit')
        {
            return;
        }

        //$objTrackingConfigs = \C4gTrackingModel::findAll();
        Message::addInfo($GLOBALS['TL_LANG']['c4gTracking']['tracking_hint']);
    }
}