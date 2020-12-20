# wpvip_email_form_with_attachment
Plugin that allows a form to send an email with an attachment in a wp vip setup.

This plugin uses the admin post hook to get POST data from a form to send an email with an attactment.

# Setup

Add this plugin to your plugin folder and enable via wp-admin or the wp-cli.

The form needs to follow the framework shown in examples/example-form.php.

Copy this form into where you would like to use it in your theme code and be sure not to change the action, name, or value attributes as the plugin is looking for those specifically.

# Allowing New File Upload Types

Edit the class property, $allowed_files, in send-attachment-emailer.php to allow the user to upload other files.

# On Success / On Failure Pages

Currently, on success will redirect to the /thank-you/ page to alert the user that their form was accepted and the email should be sent.

On failure on the other hand redirects to your site's front page and passses the upload_failure_message as a GET param in the url.

This param can be checked for and used to create a better designed message to alert the user of their form submission failure.

NOTE: Will add an example of this above method in the examples folder.

