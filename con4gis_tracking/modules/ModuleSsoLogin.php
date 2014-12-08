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

namespace JanoschOltmanns;



class ModuleSsoLogin extends \Module
{

  /**
   * Template
   * @var string
   */
  protected $strTemplate = 'mod_centralcontent';

  /**
   * Display a wildcard in the back end
   * @return string
   */
  public function generate()
  {
    if (TL_MODE == 'BE')
    {
      $objTemplate = new \BackendTemplate('be_wildcard');

      $objTemplate->wildcard = '### SSO LOGIN ###';
      $objTemplate->title = $this->headline;
      $objTemplate->id = $this->id;
      $objTemplate->link = $this->name;
      $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

      return $objTemplate->parse();
    }

    if (TL_MODE == "BE" || FE_USER_LOGGED_IN)
    {
      return '';
    }

    return parent::generate();
  }


  /**
   * Generate the module
   */
  protected function compile()
  {
    if (!\Input::get('ssoLogin'))
    {
      return;
    }

    $ssoHash = \Input::get('ssoLogin');

    $objSession = \Session::getInstance();

    if ($objSession)

      $objUser = \MemberModel::findBy('ssoHash', $ssoHash);

    if ($objUser !== null)
    {
      $strHash = sha1(session_id() . (!\Config::get('disableIpCheck') ? \Environment::get('ip') : '') . 'FE_USER_AUTH');

      // Remove old sessions
      $this->Database->prepare("DELETE FROM tl_session WHERE tstamp<? OR hash=?")
      ->execute((time() - \Config::get('sessionTimeout')), $strHash);

      // Insert the new session
      $this->Database->prepare("INSERT INTO tl_session (pid, tstamp, name, sessionID, ip, hash) VALUES (?, ?, ?, ?, ?, ?)")
      ->execute($objUser->id, time(), 'FE_USER_AUTH', session_id(), \Environment::get('ip'), $strHash);

      // Set the cookie
      $this->setCookie('FE_USER_AUTH', $strHash, (time() + \Config::get('sessionTimeout')), null, null, false, true);

    }


    if ($this->jumpTo && ($objTarget = $this->objModel->getRelated('jumpTo')) !== null)
    {
      $strRedirect = $this->jumpToOrReload($objTarget->row());
    }
    else
    {
      $this->reload();
    }

  }
}
