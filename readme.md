##Overview

Importer allows you to import multiple kinds of data from local or remote data sources into ExpressionEngine content such as channel entries or members.

For example, you can have an XML file on a remote SFTP server and Importer will retrieve, parse, and import that data into new Channel Entries on a scheduled basis. Or, perhaps you need to combine multiple Relationship custom fields into a single Playa field. Importer can do both and more. Not only that, but its code is designed to be expandable allowing us or your favorite developer to add additional data types or content types for new kinds of importing.

Importer uses the concept of import profiles. An import profile stores the source and structure of your data, and then you indicate how to import it into ExpressionEngine. It is your way of educating the module how to handle your imported data. Using this method, you could create multiple import profiles for the same CSV, JSON or XML file and import it into more than one Channel, even into multiple ExpressionEngine sites. Importer is often able to reconcile the data for you, updating existing records or adding new records as necessary.  Not only that, but by using the Cron feature you can regularly import data on a scheduled basis without any additional work.

Check out the [Data Types](https://solspace.com/expressionengine/legacy/importer/data_types/), [Data Sources](https://solspace.com/expressionengine/legacy/importer/data_sources/) and [Content Types](https://solspace.com/expressionengine/legacy/importer/content_types/) documentation for more information.

Importer for Channel Entries also includes full support for Pixel & Tonic's [Playa](http://pixelandtonic.com/playa) and [Matrix](http://pixelandtonic.com/matrix) field types.

##Important Notes

Importer by [Solspace, Inc.](http://solspace.com) is a discontinued product and is provided here for free to users that wish to still use it.
**USE OF IMPORTER FROM THIS REPO COMES WITH NO SUPPORT OR GUARANTEE THAT IT WILL WORK. WE WILL NOT UPDATE THIS REPO OR ACCEPT ANY PULL REQUESTS.**

Last ExpressionEngine version known to work on is **EE 2.10.x**

Documentation can be found here:
https://solspace.com/expressionengine/legacy/importer
