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
 * Run in a custom namespace, so the class can be replaced
 */
namespace JanoschOltmanns;


/**
 * Reads and writes news archives
 *
 * @package   con4gis_tracking
 * @author    Janosch Oltmanns
 * @copyright Janosch Oltmanns in cooperation with Küstenschmiede GmbH Software & Design 2014
 */
class C4gTrackingPoisModel extends \Model
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_c4g_tracking_pois';


    public static function findWithMagic(array $arrMemberIds=array(), array $arrVisibilityStatus=array())
    {
      $t = static::$strTable;
      $arrWhere = array();
      if (sizeof($arrMemberIds) > 0)
      {
        $arrWhere[] = "$t.member IN(" . implode(",", $arrMemberIds) . ")";
      }
      
      if (sizeof($arrVisibilityStatus) > 0)
      {
        $arrWhere[] = "($t.visibility='" . implode("' OR $t.visibility='", $arrVisibilityStatus) . "')";
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

   		$objResult = $objDatabase->execute("SELECT $t.* FROM $t" . $strWhere . "");
   		return static::createCollectionFromDbResult($objResult, $t);
      
    }

    public static function findPrivate($varMemberId, array $arrOptions=array())
   	{
   		$t = static::$strTable;

        $arrColumns = array("$t.visibility=? AND $t.member=?");
        $arrValues = array("privat", $varMemberId);

   		return static::findBy($arrColumns, $arrValues, $arrOptions);
   	}

    public static function findPublic(array $arrOptions=array())
   	{
   		$t = static::$strTable;

        $arrColumns = array("$t.visibility=?");
        $arrValues = array("public");

   		return static::findBy($arrColumns, $arrValues, $arrOptions);
   	}

}



