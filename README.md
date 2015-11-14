# Boomers Parks

A collection of Californian amusement parks and water parks. 

## Project

The client required a calendar module to display the park opening hours and events. The module needed to be configurable via the sites admin.

## Structure

The website is built on CodeIgniter and this module utilizes the CI Calendar library. A fully functional event system has been added to allow the client to administer park hours and events.

```
boomersparks/
+-- assets/
¦	+-- css/
¦		+-- calendar.css
¦	+-- js/
¦		+-- calendar.js
¦		+-- jquery.equalheights.js
+-- controllers/
¦	+-- admin/
¦		+-- calendar.php
¦	+-- hours.php
+-- models/
¦	+-- park_events_model.php
+-- views/
¦	+-- admin/
¦   	+-- calendar.php
¦   	+-- events.php
¦	+-- calendar.php
¦	+-- event.php
+-- plugin.php
```
