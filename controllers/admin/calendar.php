<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Calendar extends Admin_Controller
{
    private $current_year = '';
    private $current_month = '';
    private $event_link_template = '';
    

    public function __construct()
    {
        parent::__construct();

        $this->config->load('calendar');
        $this->event_link_template = $this->config->item('calendar_event_link_template');

        $this->current_year = date("Y");
        $this->current_month = date("n");        
    }


    public function index()
    {
        $site_id = $this->uri->segment(4);

        if ( ! $site_id) 
        {
            return show_404();
        }

        // Get the year and month from the URL
        $year = $this->uri->segment(5, $this->current_year);
        $month = $this->uri->segment(6, $this->current_month);     

        if ((! is_numeric($year)) || ( ! is_numeric($month)))
        {
            return show_404();
        }

        $data = array();
        $data['breadcrumb'] = set_crumbs(array('settings/sites' => 'Sites', current_url() => 'Calendar'));

        // Load site name
        $Site = $this->load->model('settings/sites_model');
        $Site->where('id', $site_id)->get();
        $data['Site'] = $Site;

        // Generate a range of dates to view
        $start_range = strtotime('-2 month');
        $end_range = strtotime('+12 month');

        $data['date_range'] = array();
        $current = $start_range;

        while($current <= $end_range)
        {
            $data['date_range'][] = array(
                'title'   => date('F Y', $current),
                'value'   => date('Y-m', $current),
                'current' => (date('m', $current) == $month && date('Y', $current) == $year) ? 'selected' : ''
            );

            // Advance to the next month in the range
            $current = strtotime('+1 month', $current);
        }

        // Fetch the events in reverse order as 'array_unshift' is putting events in at the beginning of the array
        $order = 'DESC';

        // Load the park hours and events
        $this->load->model('park_events_model');
        $events = $this->park_events_model->get_events($year, $month, $site_id, $order);

        // Generate an array of days
        $days = array();
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $calendar_input_template = $this->config->item('calendar_input_template');
        $no_event_id = 0;

        for ($day = 1; $day <= $days_in_month; $day++)
        {
            $timestamp = mktime(12, 0, 0, $month, $day, $year);

            // Generate a unique URL for each day of the month
            $ymd = date('Y/m/d', $timestamp);
            $add_url = "/" . ADMIN_PATH . "/calendar/edit-event/{$site_id}/{$no_event_id}/{$ymd}";
            $add_link = sprintf($this->event_link_template, $add_url, 'Add');

            $days[$day] = array(
                'date' => date('l, F j, Y', $timestamp),
                'hours' => sprintf($calendar_input_template, date('Y-m-d', $timestamp), strtolower(date('l', $timestamp))),
                'events' => array(
                    $add_link,
                    $add_link,
                    $add_link
                )
            );
        }

        // Add the events to the days
        foreach ($events as $event) 
        {
            $day = $event->calendar_day;

            if ($event->park_hours) 
            {
                // Park hours
                $hours = array(
                    'id' => $event->id,
                    'title' => ($event->park_closed) ? $this->config->item('calendar_park_closed') : $event->title,
                    'event_text_color' => $event->event_text_color,
                    'event_bg_color' => $event->event_bg_color,
                    'details' => $event->details,
                    'site_id' => $event->site_id
                );
                $days[$day]['hours'] = $this->render_event($hours);
            }
            else
            {
                // Park events
                $event = array(
                    'id' => $event->id,
                    'title' => $event->title,
                    'event_text_color' => $event->event_text_color,
                    'event_bg_color' => $event->event_bg_color,
                    'details' => $event->details,
                    'site_id' => $event->site_id
                );

                // Add events to the beginning of the array
                array_unshift($days[$day]['events'], $this->render_event($event));
            }
        }

        // Holds all days of the months with accossiated events
        $data['days'] = $days;

        // Render the view
        $this->template->view('admin/calendar', $data);
    }


    public function save_hours()
    {
        $site_id = $this->uri->segment(4);

        if ( ! $site_id) 
        {
            return show_404();
        }

        $this->load->model('park_events_model');

        // Get the submitted park hours
        $park_hours = $this->input->post();

        // Get the date range
        $date_range = $park_hours['date-range'];
        unset($park_hours['date-range']);

        foreach ($park_hours as $date => $title)
        {
            if ($title) 
            {
                // Save the hours
                $this->park_events_model->add_park_hours($date, $title, $site_id);
            }
        }

        // Extract the year and month from the date range
        $dates = explode('-', $date_range);
        $year = $dates[0];
        $month = $dates[1];

        $this->session->set_flashdata('message', '<p class="success">Park hours saved successfully.</p>');
        redirect(ADMIN_PATH . "/calendar/index/{$site_id}/{$year}/{$month}");
    }


    public function edit_event()
    {
        $site_id = $this->uri->segment(4);
        $park_event_id = $this->uri->segment(5);

        if ( ! $site_id)
        {
            return show_404();
        }

        $data = array();
        $data['breadcrumb'] = set_crumbs(array('settings/sites' => 'Sites', "calendar/index/{$site_id}" => 'Calendar', current_url() => 'Calendar Events'));
        $this->load->model('park_events_model');

        // Load site name
        $Site = $this->load->model('settings/sites_model');
        $Site->where('id', $site_id)->get();
        $data['Site'] = $Site;

        // Determine if the event is being added or edited
        if ($park_event_id === '0') 
        {
            $data['edit_mode'] = $edit_mode = false;

            // Get the day that this new event is to be added for
            $year = $this->uri->segment(6);
            $month = $this->uri->segment(7);
            $day = $this->uri->segment(8);

            $calendar_day = date('Y-m-d', mktime(12, 0, 0, $month, $day, $year));
        }
        else
        {
            $data['edit_mode'] = $edit_mode = true;

            // Load the event            
            $data['Event'] = $Event = $this->park_events_model->get_event($park_event_id);            
        }

        // Determine event type
        if ($edit_mode) 
        {
            $edit_type = 'Edit';
            $data['event_type'] = $event_type = ($Event->park_hours) ? 'Park Hours' : 'Event';            
        } 
        else 
        {
            $edit_type = 'Add';
            $data['event_type'] = $event_type = 'Event';            
        }     

        // Validate Form
        $this->form_validation->set_rules('title', 'Title', "trim|required");
        $this->form_validation->set_rules('details', 'Details', "trim");
        $this->form_validation->set_rules('event_bg_color', 'Event Color', "trim|required");
        $this->form_validation->set_rules('park_closed', 'Park Closed', "trim|required");

        if ($event_type == 'Event') 
        {
            $this->form_validation->set_rules('event_link', 'Event Link', "trim");
            $this->form_validation->set_rules('event_link_target', 'Event Link Target', "trim");

            // Validate links dependingon link type
            if ($this->input->post('link_type') == 'INTERNAL')
            {
                $this->form_validation->set_rules('link_internal', 'Page', "trim|required");
            }
            else if ($this->input->post('link_type') == 'EXTERNAL')
            {
                $this->form_validation->set_rules('link_external', 'URL', "trim|required");
            }
        }        

        if ($this->form_validation->run() == TRUE)
        {
            $park_event_data = array(
                'event_text_color'    => $this->input->post('event_text_color'),
                'event_bg_color'      => $this->input->post('event_bg_color'),
                'title'               => ($this->input->post('park_closed')) ? $this->config->item('calendar_park_closed') : $this->input->post('title'),
                'details'             => $this->input->post('details'),
                'event_link'          => ($event_type == 'Event') ? $this->input->post('event_link') : '',
                'event_link_internal' => ($event_type == 'Event') ? $this->input->post('event_link_internal') : '',
                'event_link_external' => ($event_type == 'Event') ? $this->input->post('event_link_external') : '',
                'event_link_target'   => ($event_type == 'Event') ? $this->input->post('event_link_target') : '',
                'park_closed'         => ($this->input->post('park_closed')) ? 1 : 0,
            );

            // Save event data
            if ($edit_mode) 
            {
                // Edit existing event
                $this->park_events_model->update_event($park_event_id, $park_event_data);                
            }
            else
            {
                $park_event_data['calendar_day'] = $calendar_day;
                $park_event_data['park_hours'] = 0;
                $park_event_data['site_id'] = $site_id;

                // Add new event
                $this->park_events_model->add_event($park_event_data);
            }

            // Work out the month and year of the edited event
            if ($edit_mode) 
            {
                $dates = explode('-', $Event->calendar_day);
                $year = $dates[0];
                $month = $dates[1];
            }

            redirect(ADMIN_PATH . "/calendar/index/{$site_id}/{$year}/{$month}");
        }

        // Get all the sites pages
        $this->load->model('content/entries_model');

        $Pages = $this->entries_model
            ->where('status', 'published')
            ->where('site_id', $site_id)
            ->where('slug !=', 'NULL')
            ->order_by('title')
            ->get();

        $data['Pages'] = option_array_value($Pages, 'slug', 'title', array(''  => '- SELECT -'));

        // Get all Color options
        $data['colors'] = array();

        foreach($this->config->item('calendar_color_options') as $option_number => $colors)
        {
            if ($edit_mode) 
            {
                $highlight = ($colors['background-color'] == $Event->event_bg_color && $colors['text-color'] == $Event->event_text_color) ? true : false;
            }
            else
            {
                $highlight = ($option_number == 0) ? true : false;
            }

            $colorAttributes = array(
                'style' => "background-color: {$colors['background-color']}; color: {$colors['text-color']};",
                'class' => ($highlight) ? 'highlight' : ''
            );

            $data['colors'][] = $colorAttributes;
        }        

        // Set Title
        $data['title'] = "{$edit_type} {$Site->name} Calendar {$event_type}";

        // Load the view
        $this->template->add_package(array('ckeditor', 'ck_jq_adapter'));
        $this->template->view('admin/events', $data);
    }


    public function delete_event()
    {
        $site_id = $this->uri->segment(4);
        $park_event_id = $this->uri->segment(5);

        if (( ! $site_id) || ( ! $park_event_id))
        {
            return show_404();
        }

        $this->load->model('park_events_model');

        // Get the event dates
        $Event = $this->park_events_model->get_event($park_event_id);   
        $dates = explode('-', $Event->calendar_day);
        $year = $dates[0];
        $month = $dates[1];

        // Delete the event        
        $this->park_events_model->delete_event($park_event_id);

        $this->session->set_flashdata('message', '<p class="success">Event deleted successfully.</p>');
        redirect(ADMIN_PATH . "/calendar/index/{$site_id}/{$year}/{$month}");
    }


    private function render_event($event)
    {
        $details = ($event['details']) ? 'details' : '';
        $style = sprintf($this->config->item('calendar_style_template'), $event['event_bg_color'], $event['event_text_color']);
        $event_html = "<span class='event admin-event {$details}' style='{$style}'>{$event['title']}</span>";

        $edit_url = "/" . ADMIN_PATH . "/calendar/edit-event/{$event['site_id']}/{$event['id']}";
        $edit_link_html = sprintf($this->event_link_template, $edit_url, 'Edit');

        $delete_url = "/" . ADMIN_PATH . "/calendar/delete-event/{$event['site_id']}/{$event['id']}";
        $delete_link_html = sprintf($this->event_link_template, $delete_url, 'Delete');        

        return "{$event_html} {$edit_link_html}&nbsp;&nbsp;{$delete_link_html}";
    }

}
