# BB REST API v1.0
REST API for bbPress, based on WP REST API

## Routes

Proper documentation will be produced as soon as the code is a bit more stable (and clean).
Just as a quick reference, these are the routes currently available:

```bash
wp-json/bbpress/v1/forums/
wp-json/bbpress/v1/forums/<id>
wp-json/bbpress/v1/topics/
wp-json/bbpress/v1/topics/<id>
wp-json/bbpress/v1/replies/
wp-json/bbpress/v1/replies/<id>
wp-json/bbpress/v1/tags/
wp-json/bbpress/v1/stats/
```

For all of the routes except the statistics, a few parameters to filter the results are already available.
To start testing the params which are not documented, you can check this as a strating point:

```bash
wp-json/bbpress/v1
```

## Licensing

The lincensing is still to be chosen; the code is currently published for discussion and testing purposes. If you want to use it, please do it responsibly (and only in development environments).

## References

The code in this repository is heavily based on the following references, both as a learning resource and for directly sourcing the code.
Final licensing will need to keep this in mind; 
* [BP-REST](https://github.com/buddypress/BP-REST) (and forked repositories)
* [bbP API](https://wordpress.org/plugins/bbp-api) ([GitHub Repository](https://github.com/ePascalC/bbp-API))
