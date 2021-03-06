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
 * Run in a custom namespace, so the class can be replaced
 */
namespace JanoschOltmanns;


/**
 * Reads and writes news archives
 *
 * @package   con4gis_tracking
 * @author    Janosch Oltmanns
 * @copyright Janosch Oltmanns in cooperation with Küstenschmiede GmbH Software & Design 2014 - 2015
 */
class C4gTrackingTracksModel extends \Model
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_c4g_tracking_tracks';

    public static function findWithPositions(array $arrMemberIds=array(), array $arrVisibilityStatus=array())
    {

      $arrWhere = array();
      if (sizeof($arrMemberIds) > 0)
      {
        $arrWhere[] = "tl_c4g_tracking_tracks.member IN(" . implode(",", $arrMemberIds) . ")";
      }

      if (sizeof($arrVisibilityStatus) > 0)
      {
        $arrWhere[] = "(tl_c4g_tracking_tracks.visibility='" . implode("' OR tl_c4g_tracking_tracks.visibility='", $arrVisibilityStatus) . "')";
      }

      $strWhere = "";
      if (sizeof($arrWhere) > 0)
      {
        $strWhere = " WHERE " . implode(" AND ", $arrWhere) . " AND forDelete!=1";
      }
      else
      {
        $strWhere = " WHERE forDelete!=1";
      }

   		$objDatabase = \Database::getInstance();

   		$objResult = $objDatabase->execute("SELECT tl_c4g_tracking_tracks.* FROM tl_c4g_tracking_tracks LEFT JOIN tl_c4g_tracking_positions ON tl_c4g_tracking_tracks.uuid=tl_c4g_tracking_positions.track_uuid" . $strWhere . " GROUP BY tl_c4g_tracking_tracks.id");
   		//echo $objResult->__get('query');
   		return static::createCollectionFromDbResult($objResult, 'tl_c4g_tracking_tracks');

    }
}



