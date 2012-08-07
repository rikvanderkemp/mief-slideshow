# Todo #

This plugin is still in development. The following are known issues and need to be addressed

- Improve chain location for checking uploads and handling of the options form
- Allow more then one slideshow
    - basics are there:
        - delete a slideshow
        - rename a slideshow
- Allow options such as width, height, speed of fade through options screen

# Changelog #

1.2
* Width, height are adjustable
* Option to disable the navigation buttons
* Some headings and touch ups on the upload/edit screen

1.1.1
* Bug fix initial installation where the installer does not install all tables
* Updated README file

1.1
* Added support for multiple slideshows
* Added preparation for storing settings such as widht,height etc
* Removes photos from disk


# Installation #

Easily install the plugin following these instructions:

1) Download and copy the module to wp-content/plugins/mief-slideshow
   Make sure that 'mief_slideshow.php' is at the following location:
        /wp-content/plugins/mief-slideshow/mief_slideshow.php

2) Go into your admin panel and activate 'Mief.nl - Slideshow' under Plugins

3) To upload new files to your slideshow (only 1 is possible right now) go to:
        Plugins -> Slideshow and follow the instructions

4) Insert the proper template tag in your currently activated theme.
   Eg. in the Twenty Eleven theme you can edit Header.php and insert the tag at this location:

    <body <?php body_class(); ?>>
    <div id="page" class="hfeed">
        <header id="branding" role="banner">
                <hgroup>
                    /* Bunch of code here */
                </hgroup>

                <?php  mief_slideshow(1); ?>

    Notice the mief_slideshow(1); ? That is the template tag to add. For each slideshow there is an unique tag
    this will be shown in the editor of that slideshow.

5) You are all set! Have fun!

# Thanks to #

http://www.oxygen-icons.org/
For its icons I use for the navigation.

Ron van Rutten <http://www.ronvanrutten.com>
Who needed a plugin to replace his flash based slideshow!