<div class="box">
    <div class="heading">
        <h1><img alt="" src="<?php echo theme_url('assets/images/icons/calendar.png'); ?>"><?php echo $title; ?></h1>

        <div class="buttons">
            <a class="button" href="#" onClick="$('#event-form').submit()"><span>Save</span></a>
        </div>
    </div>
    <div class="content">        
        
        <?php echo form_open(null, 'id="event-form"'); ?>
            <div class="form">

                <div>
                    <?php echo form_label('<span class="required">*</span> Title:', 'title'); ?>
                    <?php echo form_input(array('name' => 'title', 'value' => set_value('title', isset($Event->title) ? $Event->title : ''), 'size' => 50)); ?>
                </div>

                <div>
                    <?php echo form_label('Details:', 'details', array('style' => '')); ?>
                    <div style="display: inline-block;">
                        <?php echo form_textarea(array('name' => 'details', 'id' => 'details', 'value' => set_value('details', isset($Event->details) ? $Event->details : ''))); ?>
                    </div>
                </div>

                <?php if ($event_type == 'Event'): ?>
                <div id="event-link">
                    <?php echo form_label('Event Link:', 'event_link'); ?>
                    <span>
                        <label><?php echo form_radio(array('name' => 'event_link', 'value' => 'NONE', 'checked' => set_radio('event_link', 'NONE', (! isset($Event->event_link) || $Event->event_link == 'NONE') ? TRUE : FALSE))); ?> None</label>
                        <label><?php echo form_radio(array('name' => 'event_link', 'value' => 'INTERNAL', 'checked' => set_radio('event_link', 'INTERNAL', (isset($Event->event_link) && $Event->event_link == 'INTERNAL') ? TRUE : FALSE))); ?> Internal Link</label>
                        <label><?php echo form_radio(array('name' => 'event_link', 'value' => 'EXTERNAL', 'checked' => set_radio('event_link', 'EXTERNAL', (isset($Event->event_link) && $Event->event_link == 'EXTERNAL') ? TRUE : FALSE))); ?> External Link</label>
                    </span>
                
                    <section class="event-link-section">
                        <div id="event-link-internal" style="display: none;">
                            <?php echo form_label('<span class="required">*</span> Page:', 'event_link_internal'); ?>
                            <?php echo form_dropdown('event_link_internal', $Pages, set_value('event_link_internal', isset($Event->event_link_internal) ? ltrim($Event->event_link_internal, '/') : ''))?>
                        </div>
                        <div id="event-link-external" style="display: none;">
                            <?php echo form_label('<span class="required">*</span> URL:', 'event_link_external'); ?>
                            <?php echo form_input(array( 'name' => 'event_link_external', 'value' => set_value('event_link_external', isset($Event->event_link_external) ? $Event->event_link_external : ''), 'size' => 80)); ?>
                        </div>
                    </section>

                    <section id="event-link-target" class="event-link-section">
                        <?php echo form_label('Event Link Target:', 'event_link_target'); ?>
                        <?php echo form_dropdown('event_link_target', array('_blank' => '_blank', '_self' => '_self', '_parent' => '_parent', '_top' => '_top'), set_value('event_link_target', isset($Event->event_link_target) ? $Event->event_link_target : '_self')); ?>
                    </section>
                </div>
                <?php endif; ?>

                <div class="color-pallet">
                    <?php echo form_label('<span class="required">*</span> Event Color:', 'event_color', array('class' => 'event-color-label')); ?>
                    <?php echo form_hidden('event_text_color', set_value('title', isset($Event->event_text_color) ? $Event->event_text_color : '')); ?>
                    <?php echo form_hidden('event_bg_color', set_value('title', isset($Event->event_bg_color) ? $Event->event_bg_color : '')); ?>
                    <!-- Color Options -->
                    <?php foreach ($colors as $color): ?>
                        <a style="<?php echo $color['style']; ?>" class="<?php echo $color['class']; ?>"><p>T</p></a>
                    <?php endforeach; ?>
                </div>

                <div>
                    <?php echo form_label('<span class="required">*</span> Park Closed:', 'park_closed'); ?>
                    <span>
                        <label><?php echo form_radio(array('name' => 'park_closed', 'value' => '0', 'checked' => set_radio('park_closed', '0', (empty($Event->park_closed)) ? true : false))); ?> No</label>
                        <label><?php echo form_radio(array('name' => 'park_closed', 'value' => '1', 'checked' => set_radio('park_closed', '1', ( ! empty($Event->park_closed)) ? true : false))); ?> Yes</label>
                    </span>
                </div>

            </div>
        <?php echo form_close(); ?>

    </div>
</div>

<script>
    $(document).ready(function() {
        
        // CKEditor
        var full_config = {
            toolbar: [
                { name: 'styles', items: [ 'Styles','Format','Font','FontSize' ] },
                { name: 'colors', items: [ 'TextColor','BGColor' ] },
                { name: 'paragraph', items: [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','- ','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
                { name: 'tools', items: [ 'Maximize' ] },
                '/',
                { name: 'basicstyles', items: [ 'Bold','Italic','Underline','Subscript','Superscript','Strike','-','RemoveFormat' ] },
                { name: 'clipboard', items: [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
                { name: 'editing', items: [ 'Find','Replace','-','Scayt' ] },
                { name: 'insert', items: [ 'Image','MediaEmbed','Table','HorizontalRule','SpecialChar','Iframe' ] },
                { name: 'links', items: [ 'Link','Unlink','Anchor' ] },
                { name: 'document', items: [ 'Source' ] }
            ],
            entities:                   true,
            extraPlugins:               'stylesheetparser,mediaembed',
            height:                     '200px',
            filebrowserBrowseUrl:       '/application/themes/admin/assets/js/kcfinder/browse.php?type=files',
            filebrowserImageBrowseUrl:  '/application/themes/admin/assets/js/kcfinder/browse.php?type=images',
            filebrowserFlashBrowseUrl:  '/application/themes/admin/assets/js/kcfinder/browse.php?type=flash',
            filebrowserUploadUrl:       '/application/themes/admin/assets/js/kcfinder/upload.php?type=files',
            filebrowserImageUploadUrl:  '/application/themes/admin/assets/js/kcfinder/upload.php?type=images',
            filebrowserFlashUploadUrl:  '/application/themes/admin/assets/js/kcfinder/upload.php?type=flash'
        };

        $('textarea#details').ckeditor(full_config);


        // Link Type Changes
        changeLinkType = function() {

            var linkType = $("#event-link input[type='radio']:checked").val();

            if (linkType == 'NONE') {
                $('#event-link-internal').hide();
                $('#event-link-external').hide();
                $('#event-link-target').hide();

            } else {

                if (linkType == 'INTERNAL') {
                    $('#event-link-internal').show();
                    $('#event-link-external').hide();

                } else if (linkType == 'EXTERNAL') {
                    $('#event-link-internal').hide();
                    $('#event-link-external').show();
                }

                $('#event-link-target').show();
            }
        };

        $('#event-link input[type="radio"]').change(function() {
            changeLinkType();
        });

        changeLinkType();


        // A color is clicked on
        $( ".color-pallet a" ).click(function(e) {

            // Highlight the selected color
            $(this).parent().children().removeClass( "highlight" );
            $(this).addClass( "highlight" );

            // Get selected colors
            var textColor = rgb2hex( $(this).css( "color" ) );
            var backgroundColor = rgb2hex( $(this).css( "background-color" ) );

            // Set the hidden inputs to the values of the selected color
            $( "input[name=event_text_color]" ).val(textColor);
            $( "input[name=event_bg_color]" ).val(backgroundColor);
        });


        // Convert RGB color values to Hex
        function rgb2hex(rgb) {
            if (/^#[0-9A-F]{6}$/i.test(rgb)) return rgb;

            rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
            function hex(x) {
                return ("0" + parseInt(x).toString(16)).slice(-2);
            }
            return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
        }        

    });

</script>