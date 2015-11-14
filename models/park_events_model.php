<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

Class Park_events_model extends CI_Model
{
    
    public function __construct()
    {
        parent::__construct();

        $this->config->load('calendar');
    } 


    public function get_events($year, $month, $site_id = 0, $order = 'ASC')
    {
        $query = $this->db->query("
            SELECT
                id,
                DAY(calendar_day) AS calendar_day,
                event_text_color,
                event_bg_color,
                title,
                details,
                event_link,
                event_link_internal,
                event_link_external,
                event_link_target,
                park_closed,
                park_hours,
                site_id
            FROM
                park_events
            WHERE
                YEAR(calendar_day) = {$year} AND
                MONTH(calendar_day) = {$month} AND                
                site_id = {$site_id}
            ORDER BY
                id {$order}
        ");  

        if ($query->num_rows() < 1)
        {
            return array();
        }

        return $query->result();
    }


    public function get_events_html($year, $month, $site_id = 0)
    {
        $data = array();
        $events_html = array();
        
        // Get the events
        $events = $this->get_events($year, $month, $site_id);      

        foreach ($events as $row)
        {   
            // Determin the event link type
            if ($row->event_link == 'NONE') 
            {
                $event_link_url = '';
            }
            else
            {
                $event_link_url = ($row->event_link == 'INTERNAL') ? site_url($row->event_link_internal) : $row->event_link_external;
            }

            $data['event_title'] = ($row->park_closed) ? $this->config->item('calendar_park_closed') : $row->title;
            $data['event_ref'] = "event-{$row->id}";
            $data['event_styles'] = sprintf($this->config->item('calendar_style_template'), $row->event_bg_color, $row->event_text_color);
            $data['event_link_url'] = $event_link_url;
            $data['event'] = $row;
            
            // Initalise the array for this day
            $day = $row->calendar_day;            
            
            if ( ! array_key_exists($day, $events_html)) 
            {
                $events_html[$day] = '';
            }

            // Generate the event and append to the day
            $event_html = $this->load->view('calendar/event', $data, true);

            if ($row->park_hours) 
            {
                // Add the hours event to the beginning of the string
                $events_html[$day] = $event_html . $events_html[$day];
            }
            else
            {
                // Add the events on to the end of the string
                $events_html[$day] .= $event_html;
            }
        }            

        // Fill in any missing days with TBA
        $tba_data = array();
        $tba_data['event_title'] = $this->config->item('calendar_hours_tba');
        $tba_data['event_styles'] = sprintf($this->config->item('calendar_style_template'), $this->config->item('calendar_hours_tba_bg'), $this->config->item('calendar_hours_tba_text'));
        $tba_html = $this->load->view('calendar/event', $tba_data, true);

        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        for ($day = 1; $day <= $days_in_month; $day++)
        {
            if ( ! array_key_exists($day, $events_html)) 
            {
                $events_html[$day] = $tba_html;
            }
        }

        return $events_html;
    }


    public function get_event($park_event_id)
    {
        $query = $this->db->query("
            SELECT
                calendar_day,
                event_text_color,
                event_bg_color,
                title,
                details,
                event_link,
                event_link_internal,
                event_link_external,
                event_link_target,
                park_closed,
                park_hours
            FROM
                park_events
            WHERE
                id = {$park_event_id}
        ");  

        if ($query->num_rows() < 1)
        {
            return false;
        }

        return $query->row();
    }    


    public function add_park_hours($date, $title, $site_id)
    {
        // Do clean up first
        $this->delete_old_events();

        $color_options = $this->config->item('calendar_color_options');

        $data = array(
            'calendar_day'     => $date,
            'event_text_color' => $color_options[0]['text-color'],
            'event_bg_color'   => $color_options[0]['background-color'],
            'title'            => $title,
            'details'          => '',
            'event_link'       => '',
            'park_hours'       => 1,
            'site_id'          => $site_id
        );
        
        $this->db->insert('park_events', $data);
        
        $park_events_id = $this->db->insert_id();
        return $park_events_id;         
    }


    public function add_event($data)
    {        
        $this->db->insert('park_events', $data);
        
        $park_events_id = $this->db->insert_id();
        return $park_events_id;         
    }


    public function update_event($id, $data)
    {              
        $this->db->where('id', $id);
        $this->db->update('park_events', $data);

        return true;
    }    


    public function delete_event($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('park_events');

        return true;
    }


    public function delete_old_events()
    {
        // Delete any events older than 2 months
        $query = $this->db->query("
            DELETE
            FROM
                park_events
            WHERE
                calendar_day < DATE_SUB(CURDATE(), INTERVAL 2 MONTH)
        "); 
      
        return true;
    }   


    public function get_hours($date)
    {
        $query = $this->db->query("
            SELECT
                title
            FROM
                park_events
            WHERE
                calendar_day = '{$date}' AND
                park_hours = 1 AND
                site_id = {$this->settings->site_id}
        ");  

        if ($query->num_rows() < 1)
        {
            return '';
        }

        $row = $query->row();
        return $row->title;
    } 

}
