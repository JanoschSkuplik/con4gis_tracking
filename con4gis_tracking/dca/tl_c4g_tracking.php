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
        'ctable'                      => array('tl_c4g_tracking_devices', 'tl_c4g_tracking_pois', 'tl_c4g_tracking_tracks'),
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
            'devices' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_theme']['css'],
                'href'                => 'table=tl_c4g_tracking_devices',
                'icon'                => 'system/modules/con4gis_tracking/assets/icon-devices.gif',
                //'button_callback'     => array('tl_theme', 'editCss')
            ),
            'pois' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_theme']['modules'],
                'href'                => 'table=tl_c4g_tracking_pois',
                'icon'                => 'system/modules/con4gis_tracking/assets/icon-pois.gif',
                //'button_callback'     => array('tl_theme', 'editModules')
            ),
            'tracks' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_theme']['layout'],
                'href'                => 'table=tl_c4g_tracking_tracks',
                'icon'                => 'system/modules/con4gis_tracking/assets/icon-tracks.gif',
                //'button_callback'     => array('tl_theme', 'editLayout')
            ),
            'edit' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_theme']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif'
            ),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_theme']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_theme']['show'],
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
			'label'                   => &$GLOBALS['TL_LANG']['tl_theme']['name'],
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
        /*if ($_POST || Input::get('act') != 'edit')
        {
            echo "erer";
            return;
        }*/
        //Message::addInfo(sprintf($GLOBALS['TL_LANG']['tl_content']['includeTemplates'], 'moo_mediabox', 'j_colorbox'));

        //print_r(get_class_methods(new \C4gTrackingModel));
        $objTrackingConfigs = \C4gTrackingModel::findAll();
        echo $objTrackingConfigs->count();
        Message::addInfo('Test');


    }
}