<?php if (isset($event_link_url) && $event_link_url): ?>

    <span class="event" style="<?php echo $event_styles; ?>"><a href="<?php echo $event_link_url; ?>" target="<?php echo $event->event_link_target; ?>"><?php echo $event_title; ?></a></span>

<?php elseif (isset($event->details) && $event->details): ?>

    <span class="event" style="<?php echo $event_styles; ?>"><a href='<?php echo "#{$event_ref}"; ?>'><?php echo $event_title; ?></a></span>

    <section id="<?php echo $event_ref; ?>" style="display: none;">
        <?php echo $event->details; ?>
    </section>

<?php else: ?>

    <span class="event" style="<?php echo $event_styles; ?>"><?php echo $event_title; ?></span>

<?php endif; ?>