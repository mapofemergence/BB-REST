# BB REST API v1.0
REST API for bbPress, based on WP REST API

## Routes

Proper documentation will be produced as soon as the code is a bit more stable (and clean).
Just as a quick reference, these are the routes currently available:

```bash
wp-json/bbpress/v1/core
wp-json/bbpress/v1/forums
wp-json/bbpress/v1/forums/<id>
wp-json/bbpress/v1/topics
wp-json/bbpress/v1/topics/<id>
wp-json/bbpress/v1/replies
wp-json/bbpress/v1/replies/<id>
wp-json/bbpress/v1/tags
wp-json/bbpress/v1/stats
```

For all of the routes except the statistics, a few parameters to filter the results are already available.
To start testing the params which are not documented, you can check this as a starting point:

```bash
wp-json/bbpress/v1
```

or refer to the temporary, W.I.P. documentation available at <https://mapofemergence.github.io/BB-REST>

> WARNING: not all of the reported parameters might work on all endpoints, as some of them are still being implemented (or still to be removed from the inherited ones)

## License

A lincense is still to be chosen; the code is currently published for discussion and testing purposes. If you want to use it, please do it responsibly (and only in development environments).

## References

The code in this repository is (more or less heavily) based on the following references, both as a learning resource and for directly sourcing the code.
Final licensing will need to keep this in mind. 
* [BB-API](https://github.com/thenbrent/BB-API)
* [BP-REST](https://github.com/buddypress/BP-REST) (and [its forks](https://github.com/modemlooper/BP-REST/network))
* [bbP API](https://wordpress.org/plugins/bbp-api) ([GitHub Repository](https://github.com/ePascalC/bbp-API))
* [class.jetpack-bbpress-json-api-compat.php](https://plugins.trac.wordpress.org/browser/jetpack/trunk/class.jetpack-bbpress-json-api-compat.php)

## Notes

The work collected here is the result of some sparse research and a few specific requirements for a personal project.
An attempt to present this was done with [a topic on bbPress forum](https://bbpress.org/?post_type=topic&p=181440), which as of today is still pending approval.

Here is a summary of that post:

Since there has been some conversation regarding a REST API for bbPress, already, it would be nice to build an interest group to begin a proper, articulated discussion and to coordinate the works if possible.

A few (both recent and old) topics discussing this on [bbPress.org](https://bbpress.org):
* <https://bbpress.org/forums/topic/is-there-an-api-for-bbpress>
* <https://bbpress.org/forums/topic/sdkapi>
* <https://bbpress.org/forums/topic/use-bbpress-with-an-iosandroid-app>
* <https://bbpress.org/forums/topic/query-topics-with-wp-rest-api>
* <https://bbpress.org/forums/topic/is-there-a-rest-api>

Things to consider, when approaching such a critical task:
* schemas, routes and endpoints should be carefully chosen to minimize backward-compatibility issues, as the API is developed
* when dealing with APIs, performance is paramount; therefore, the logic to prepare the REST responses should be solid and use the right bbPress calls
* ideally, the development of such an API should be tightly linked to the development of the bbPress plugin itself and, ideally, become part of the core at some point
* bbPess is often installed together with BuddyPess and certain features of the two plugins are interwoven so it would be crucial to decide what calls pertain to what API and how (if) the two would interact
