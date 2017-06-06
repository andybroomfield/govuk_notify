README.txt
==========

GovUK Notify is a module to enable you to send emails from your Drupal installation via the UK Government's GovUK Notify service (https://www.notifications.service.gov.uk/)

GovUK Notify is a scalable notifications platform for use by UK Government departments and agencies.

INSTALLATION
------------

1. In the root of your Drupal installation run 

    composer require php-http/guzzle6-adapter alphagov/notifications-php-client

2. Enable this module


CONFIGURATION
-------------

You'll need to generate an API key by logging in to GOV.UK Notify and going to the API integration page. You should also create a template that contains the placeholder ((subject)) in the subject and ((message)) in the message body. This is the 'default' template that will be used in the case of no specific template being set to send a particular message.

Go to /admin/config/govuk_notify/settings to configure the module.

1. In the field 'API Key' enter your GovUK Notify API Key.

2. In the field 'Default template ID' enter the ID of your default template.

3 (optional). To test the system enter an email address in the 'Test email address' field. When you click the 'Save configuration' button an email will be sent to the email address that you've entered. NB If your account is still in trial mode then you'll only be able to send emails to members of your team.


FUTURE WORK
-----------

Extend to send SMS and other types of notifications as supported by GovUK Notify.
