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

class TrackingService extends \Controller
{

    private $arrReturn = array();
    private $blnDebugMode = false;

    public function __construct()
    {
        if ($this->Input->get('debug') && ($this->Input->get('debug')=='1' || $this->Input->get('debug')=='true'))
        {
            $this->blnDebugMode = true;
        }
    }

    public function trackingDispatchAjax()
    {

        $strMethod = 'tracking' . ucfirst(\Input::get('method'));

        if (method_exists($this, $strMethod))
        {
            if ($this->$strMethod())
            {
                return $this->arrReturn;
            }
            return $this->getErrorReturn('method error in ' . $strMethod);
        }
        else
        {
            return false;
        }

    }

    private function trackingNewPoi()
    {

        if ($this->blnDebugMode)
        {
            \Input::setPost('user',\Input::get('user'));
            \Input::setPost('configuration',\Input::get('configuration'));
            \Input::setPost('latitude',\Input::get('latitude'));
            \Input::setPost('longitude',\Input::get('longitude'));
        }

        $blnHasError = false;
        if (!\Input::post('user'))
        {
            $this->arrReturn = $this->getErrorReturn('No username');
            $blnHasError = true;
        }
        if (!\Input::post('configuration'))
        {
            $this->arrReturn = $this->getErrorReturn('No configuration submittet');
            $blnHasError = true;
        }
        if (!\Input::post('latitude'))
        {
            $this->arrReturn = $this->getErrorReturn('No latitude submittet');
            $blnHasError = true;
        }
        if (!\Input::post('longitude'))
        {
            $this->arrReturn = $this->getErrorReturn('No longitude submittet');
            $blnHasError = true;
        }
        if (!$blnHasError)
        {
            $this->arrReturn['track'] = \Tracking::setNewPoi(\Input::post('configuration'), \Input::post('user'), \Input::post('latitude'), \Input::post('longitude'));
        }

        return true;
    }

    private function trackingNewPosition()
    {

        if ($this->blnDebugMode)
        {
            \Input::setPost('track',\Input::get('track'));
            \Input::setPost('latitude',\Input::get('latitude'));
            \Input::setPost('longitude',\Input::get('longitude'));
        }

        $blnHasError = false;
        if (!\Input::post('track'))
        {
            $this->arrReturn = $this->getErrorReturn('No track');
            $blnHasError = true;
        }
        if (!\Input::post('latitude'))
        {
            $this->arrReturn = $this->getErrorReturn('No latitude submittet');
            $blnHasError = true;
        }
        if (!\Input::post('longitude'))
        {
            $this->arrReturn = $this->getErrorReturn('No longitude submittet');
            $blnHasError = true;
        }
        if (!$blnHasError)
        {
            $this->arrReturn['track'] = \Tracking::setNewPosition(\Input::post('track'), \Input::post('latitude'), \Input::post('longitude'));
        }

        return true;
    }

    private function trackingNewTrack()
    {

        if ($this->blnDebugMode)
        {
            \Input::setPost('user',\Input::get('user'));
            \Input::setPost('configuration',\Input::get('configuration'));
        }

        $blnHasError = false;
        if (!\Input::post('user'))
        {
            $this->arrReturn = $this->getErrorReturn('No username');
            $blnHasError = true;
        }
        if (!\Input::post('configuration'))
        {
            $this->arrReturn = $this->getErrorReturn('No configuration submittet');
            $blnHasError = true;
        }
        if (!$blnHasError)
        {
            $this->arrReturn['track'] = \Tracking::setNewTrack(\Input::post('configuration'), \Input::post('user'));
        }

        return true;
    }

    private function trackingLoginUser()
    {
        if ($this->blnDebugMode)
        {
            \Input::setPost('user',\Input::get('user'));
            \Input::setPost('password',\Input::get('password'));
        }

        $blnHasError = false;
        if (!\Input::post('user') && !\Input::post('password'))
        {
            $this->arrReturn = $this->getErrorReturn('No username and password');
            $blnHasError = true;
        }
        elseif (!\Input::post('user'))
        {
            $this->arrReturn = $this->getErrorReturn('No username');
            $blnHasError = true;
        }
        elseif (!\Input::post('password'))
        {
            $this->arrReturn = $this->getErrorReturn('No password');
            $blnHasError = true;
        }

        if ($blnHasError)
        {
            return true;
        }

        \Input::setPost('username', \Input::post('user'));

        $this->import('FrontendUser', 'User');
        if (!$this->User->login())
        {
            $this->arrReturn = $this->getErrorReturn('wrong username or password');
            $blnHasError = true;
        }
        else
        {

            $this->arrReturn['userId'] = $this->User->id;
            $this->arrReturn['userName'] = $this->User->username;
            $this->arrReturn['userRealName'] = ($this->User->firstname ? ($this->User->firstname . " ") : '') . $this->User->lastname;
            $this->arrReturn['trackingConfig'] = \Tracking::getTrackingConfig();


        }

        return true;
    }

    private function trackingTest()
    {
        $this->arrReturn = array
        (
            'error' => false
        );

        return true;
    }

    private function trackingFalseReturn()
    {
        return false;
    }

    private function getErrorReturn($strMessage)
    {
        $arrReturn = array();
        $arrReturn['error'] = true;
        $arrReturn['message'] = $strMessage;
        return $arrReturn;
    }


}