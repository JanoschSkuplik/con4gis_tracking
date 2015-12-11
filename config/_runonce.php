<?php

namespace c4g;

class con4gisTrackingRunonceJob extends \Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->import('Database');
    }

    public function run()
    {

        if ($this->Database->tableExists('tl_c4g_tracking_positions'))
        {

            if ($this->Database->fieldExists('track_uuid', 'tl_c4g_tracking_positions'))
            {
                // rename track_uuid-Field into trackUuid
                $this->Database->execute("ALTER TABLE `tl_c4g_tracking_positions` CHANGE `track_uuid` `trackUuid` varchar(23) NOT NULL default ''");

            }
        }

    }

}
$objCon4gisTrackingRunonceJob = new con4gisTrackingRunonceJob();
$objCon4gisTrackingRunonceJob->run();