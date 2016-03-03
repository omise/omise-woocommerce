Change Log
==========

[1.1.2] 2016-03-03
------------
- Changed expiration month and expiration year on credit card form to dropdown and update form's style.
- Added bootstrap files for Docker container for running unit tests with WP.
- Updated developer guide: how to run tests with Docker container.
- Created tests for `Omise_Util` class.
- Created tests for `Omise` class (API wrapper).
  - Changed method type from static to public in order to easily maintain test suites.
- Revised code to follow WordPress coding standards.
- Tested with WordPress 4.4.2 and WooCommerce 2.5.2.

[1.1.1] 2015-11-16
------------
- *`Added`* Added Omise-Version into the cURL request header.

[1.1.0] 2015-09-24
--------------------
- *`Added`* Adds support for 3-D Secure.

[1.0.2] 2015-03-23
--------------------
- *`Fixed`* Fix create token issue.

[1.0.1] 2015-03-10
--------------------
- *`Added`* Support fund transfers.

[1.0.0] 2015-01-20
--------------------
- *`Added`* First version supports.
  - Charge a card
  - Save a card
  - Delete a card


