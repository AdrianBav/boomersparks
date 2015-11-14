<div class="box">
    <div class="heading">
        <h1><img alt="" src="<?php echo theme_url('assets/images/icons/calendar.png'); ?>"><?php echo "Edit {$Site->name} Calendar"; ?></h1>

        <div class="buttons">
            <a class="button" href="#" onClick="$('#calendar-form').submit()"><span>Save Park Hours</span></a>
        </div>
    </div>
    <div class="content">
        
        <!-- Quick Fill -->
        <div id="quick-fill">
            <table class="list">
                <thead>
                    <tr>
                        <th>Weekly Template</th>
                        <th width="12%">Sunday</th>
                        <th width="12%">Monday</th>
                        <th width="12%">Tuesday</th>
                        <th width="12%">Wednesday</th>
                        <th width="12%">Thursday</th>
                        <th width="12%">Friday</th>
                        <th width="12%">Saturday</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><a class="button" id="quick-fill-button"><span>Populate Calendar</span></a></td>
                        <td><input type="text" name="sunday" value=""></td>
                        <td><input type="text" name="monday" value=""></td>
                        <td><input type="text" name="tuesday" value=""></td>
                        <td><input type="text" name="wednesday" value=""></td>
                        <td><input type="text" name="thursday" value=""></td>
                        <td><input type="text" name="friday" value=""></td>
                        <td><input type="text" name="saturday" value=""></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php echo form_open(site_url(ADMIN_PATH . "/calendar/save-hours/{$Site->id}"), 'id=calendar-form'); ?>
            <table id="calendar-table" class="list">
                <thead>
                    <tr>
                        <th>
                            <select name="date-range" id="year-month-select">
                            <?php foreach($date_range as $date): ?>                            
                                <option value="<?php echo $date['value']; ?>" <?php echo $date['current']; ?>><?php echo $date['title']; ?></option>
                            <?php endforeach; ?>
                            </select>
                        </th>
                        <th width="22%" class="center">Park Hours</th>
                        <th width="22%" class="center">Event 1</th>
                        <th width="22%" class="center">Event 2</th>
                        <th width="22%" class="center">Event 3</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($days as $day): ?>
                    <tr>
                        <td><?php echo $day['date']; ?></td>
                        <td class="center"><?php echo $day['hours']; ?></td>
                        <td class="center"><?php echo $day['events'][0]; ?></td>
                        <td class="center"><?php echo $day['events'][1]; ?></td>
                        <td class="center"><?php echo $day['events'][2]; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>            
            </table>
        <?php echo form_close(); ?>

    </div>
</div>

<script>
    $(document).ready( function() {

        // Quick fill
        $( "#quick-fill-button" ).click(function(e) {

            $( "#calendar-form .sunday" ).val( $( "#quick-fill input[name=sunday]" ).val() );
            $( "#calendar-form .monday" ).val( $( "#quick-fill input[name=monday]" ).val() );
            $( "#calendar-form .tuesday" ).val( $( "#quick-fill input[name=tuesday]" ).val() );
            $( "#calendar-form .wednesday" ).val( $( "#quick-fill input[name=wednesday]" ).val() );
            $( "#calendar-form .thursday" ).val( $( "#quick-fill input[name=thursday]" ).val() );
            $( "#calendar-form .friday" ).val( $( "#quick-fill input[name=friday]" ).val() );
            $( "#calendar-form .saturday" ).val( $( "#quick-fill input[name=saturday]" ).val() );
        });


        // Change the date period
        $( "#year-month-select" ).change(function(e) {

            // Extract the dates from the select box value
            var dateRange = $(this).val();
            var dates = dateRange.split('-');
            var year = dates[0];
            var month = dates[1];

            // Build the URL
            var controllerPath = '<?php echo site_url(ADMIN_PATH . "/calendar/index/{$Site->id}"); ?>';
            url = controllerPath.concat('/', year, '/', month);
            
            // Reload the page
            window.location.href = url;
        });

    });
</script>