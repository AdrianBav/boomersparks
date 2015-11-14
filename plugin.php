<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Calendar_plugin extends Plugin
{

    /*
     * Today's Hours
     *
     * Returns the day's hours
     *
     * @return array
     */
    public function todays_hours()
    {        
        $today = date('Y-m-d');

        $this->load->model('park_events_model');
        $hours = $this->park_events_model->get_hours($today);

        return $hours;
    }

}
