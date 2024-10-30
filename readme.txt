=== Convert Media to EDD ===

 * Contributors: wooninjas, rahilwazir 
 * Plugin Name: Convert Media to EDD
 * Description: This EDD addon converts the selected media library images to EDD downloads
 * Tags: media, products, edd, attachment, downloadable products, convert, library, images, easy digital downloads
 * Version: 1.0.0
 * Author: WooNinjas
 * Author URI: https://wooninjas.com
 * Text Domain: cmedd
 * Requires at least: 4.0
 * Tested up to: 4.7.2
 * License: GPLv2 or later

This EDD addon converts the selected media library images to EDD downloads. It provides an easy way to convert media images in bulk to EDD.

== Description ==

This addon depends on Easy Digital Downloads, so It must be installed first before installing this addon. This addons enables the admin to select the images from media library which should be converted into EDD downloads. The addon offers more options to associate with the EDD downloads.

A new menu is created after install by the name **Convert Media to EDD**


Plugin menu page has following fields:
	
*   Description (full wysiwyg editor)
*   Download categories
*   Price
*   Download Limit
*   Download notes


These fields are self explanatory and all fields are optional except the Media attachment field, which is required.

There are two buttons on the plugin page execute and reset

= Execute =

Once clicked, it will send request to execute cron job immediately.
Any errors before processing cron job (invalid nonce) will be displayed on top of the page as notice.
After Cron successfully executed, the updated job status will be displayed on top of the page as notice.

= Reset =

This button is important when you want to run the cron job again and clear out the previous pending/completed job.
The reset button will only be clickable when any previous job was scheduled.
When you Reset the previous job, it's status entries (total/processed/status) will be removed (not the downloads that's been created), which is used to track running job.
This allows to schedule new job when clicked on Execute
Note: When you Execute after Reset, the previous download posts will be preserved and the next Execute will just create new downloads.

= Polling =

On Frontend ajax polls every 2 seconds to find out the job status and update message on top of the page. Polling will only runs when job is running.

= Cron Job =

The job runs immediately on successful request from frontend.
The job keep track of the following field/values to resume or notify job status in frontend.

**Status**: completed/progress

**Processed**: number of images has been processed

**Remaining**: number of images remains to be processed

**Total**: total number of media library image items (jpg,png,gif)
The processed field is main key here to remember. As this required for resume and next batch of images to process.
The cron executes first 10 images from the media library and then pause for 1 second. (This prevents load on server) and the processed field is updated with image offset i.e. from 0 to 10. So on next iteration it will remember where to continue/resume from.

**Note**: When site admin upload images to media library while job is running, it will update the total count the images on Media library and processed the new uploaded images as well.
If no images are found on given (total number of processed) offset, it will be assumed that the job is completed, because there are no more images found for further operation. (Status will be updated on frontend)

= Logs =
* Log file is located in wp-content/cmedd_cron/cmedd.log

== Installation ==

This section describes how to install the plugin and get it working.

For an automatic installation through WordPress:

Go to the 'Add New' plugins screen in your WordPress admin area
Search for 'Convert Media EDD'
Click 'Install Now' and activate the plugin
'Convert Media to EDD' admin menu will show up in dashboard.

For a manual installation via FTP:

Upload the 'Convert Media to EDD' directory to the '/wp-content/plugins/' directory
Activate the plugin through the 'Plugins' screen in your WordPress admin area
'Convert Media to EDD' admin menu will show up in dashboard.

To upload the plugin through WordPress, instead of FTP:

Upload the downloaded zip file on the 'Add New' plugins screen (see the 'Upload' tab) in your WordPress admin area and activate.
Activate the plugin through the 'Plugins' screen in your WordPress admin area
'Convert Media to EDD' admin menu will show up in dashboard.

Note: EDD Plugin must be installed first.

== Screenshots ==

1. The admin Menu that shows up once the plugin is installed and activated.
2. The plugin page to convert images to EDD downloads.

== Changelog ==

= 1.0 =

