<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Hours extends Public_Controller
{
    private $current_year = '';
    private $current_month = '';


    function __construct()
    {
        parent::__construct();

        $this->config->load('calendar');
        $this->load->helper('calendar');

        $this->current_year = date("Y");
        $this->current_month = date("n");        
    }


    public function index()
    {
        // Get the year and month from the URL
        $year = $this->uri->segment(3, $this->current_year);
        $month = $this->uri->segment(4, $this->current_month);

        if ((! is_numeric($year)) || ( ! is_numeric($month)))
        {
            return show_404();
        }

        // Disallow users to view any time period before the current month
        $when = calendar_when($year, $month, $this->current_year, $this->current_month);

        if ($when == 'past')
        {
            $year = $this->current_year;
            $month = $this->current_month;
        }

        // If current month has no events, find next active month
        if ( ! has_events($year, $month)) 
        {
            $next_active_date = get_next_active_date($year, $month);

            if ($next_active_date === false)
            {
                // No future dates in the database
                $calendar_html = '<h2>No future dates.</h2>';
                
                $this->render($calendar_html, $when);
                return;
            } 
            else
            {
                // Jump to next active month
                $year = $next_active_date['year'];
                $month = $next_active_date['month'];
                
                // Set a flag indicating that we have skipped blank months
                if ( ! $this->session->userdata('skipped_blank_months')) 
                {
                    $jump_data = array(
                        'skipped_blank_months' => TRUE,
                        'jumped_to_year' => $year,
                        'jumped_to_month' => $month                    
                    );
                    $this->session->set_userdata($jump_data);
                }

                $slugs = $this->uri->slash_segment(1, 'leading') . $this->uri->slash_segment(2, 'leading');
                redirect("{$slugs}/{$year}/{$month}");
            }
        }

        if ($this->session->userdata('skipped_blank_months')) 
        {
            $jumped_to_year = $this->session->userdata('jumped_to_year');
            $jumped_to_month = $this->session->userdata('jumped_to_month');

            if ($jumped_to_year == $year && $jumped_to_month == $month) 
            {
                $when .= ' skipped';
            }
        }

        // Load the park hours and events
        $this->load->model('park_events_model');
        $events_html = $this->park_events_model->get_events_html($year, $month, $this->settings->site_id);

        // Generate the calendar
        $prefs = array (
           'day_type'       => $this->config->item('calendar_day_type'),
           'show_next_prev' => $this->config->item('calendar_show_next_prev'),
           'next_prev_url'  => base_url() . $this->uri->slash_segment(1, 'trailing') . $this->uri->slash_segment(2, 'trailing'),
           'template'       => $this->config->item('calendar_template')
        );
        $this->load->library('calendar', $prefs);
        $calendar_html = $this->calendar->generate($year, $month, $events_html);

        // Render the calendar
        $this->render($calendar_html, $when);
    }


    private function render($calendar_html, $when)
    {
        // Load the page entry
        $slug = $this->uri->slash_segment(1, 'trailing') . $this->uri->segment(2);
        $Page = $this->cache->model('entries_cache_model', 'cacheable_get_by', array('slug' => $slug), 'entries');

        // Render the calendar view
        $calendar_data = array();
        $calendar_data['when'] = $when;
        $calendar_data['calendar'] = $calendar_html;        
        $calendar_view = $this->load->view('calendar', $calendar_data, true);

        // Set the calendar as extra page data
        $extra_page_data = array();
        $extra_page_data['calendar_placeholder'] = $calendar_view;

        // Build the page
        $data = array();
        $data['_content'] = $Page->build_content($extra_page_data);
       
        // Add calendar functionality
        $this->template->add_stylesheet('/application/modules/calendar/assets/css/calendar.css');
        $this->template->add_javascript('/application/modules/calendar/assets/js/jquery.equalheights.js');
        $this->template->add_javascript('/application/modules/calendar/assets/js/calendar.js');
        $this->template->add_package('fancyBox');        

        // Prevent robots from indexing calendar page
        $this->template->add_page_head('<meta name="robots" content="noindex" />');

        // Render the view
        $this->template->set('entry_id', $Page->id);
        $this->template->set('content_type', $Page->content_types->short_name);
        $this->template->view('pages', $data);
    }

}
