Stop russian war. **🇺🇦 Free Ukraine!**

# sentry

This is a fork. DSN JS not yet implemented.

![Screenshot](images/sentry.png?raw=true)

Run the following queries to update and delete old configuration values (to v2+):
```sql
UPDATE core_config_data SET path = REPLACE(path, "/amg-sentry/", "/sentry/") WHERE path LIKE "%/amg-sentry/%";
UPDATE core_config_data SET path = REPLACE(path, "-", "_") WHERE path LIKE "%/sentry/%";
UPDATE core_config_data SET path = "dev/sentry/dsn_js_front" WHERE path LIKE "dev/sentry/dsn_js";
DELETE FROM core_config_data WHERE path LIKE "dev/sentry/php_errors";
DELETE FROM core_config_data WHERE path LIKE "dev/sentry/php_exceptions";
DELETE FROM core_config_data WHERE path LIKE "dev/sentry/ignore_error_control_operator";
DELETE FROM core_config_data WHERE path LIKE "dev/sentry/%" AND path NOT LIKE "dev/sentry/logger"
  AND path NOT LIKE "dev/sentry/dsn_js_front" AND scope_id != 0;
```

To install:
- run `composer require luigifab/openmage-sentry`
- apply `openmage.diff` or `openmage-more.diff`
- apply `errors.diff`

To upgrade:
- revert `openmage.diff` or `openmage-more.diff`
- upgrade with composer
- apply `openmage.diff` or `openmage-more.diff`

After any upgrade of OpenMage, apply `errors.diff` and `openmage[-more].diff` again.

For configuration, go to: `System / Configuration / Developer / Sentry`.

Notes:
- errors when profiling with Blackfire are not sent to Sentry
- errors are sent to Sentry after `fastcgi_finish_request` in the `__destruct()`
- run _varnish-sentry.php_ in a `screen`

---

- Current version: 2.2.0 (10/10/2023)
- Compatibility: OpenMage 19.x / 20.x / 21.x, PHP 7.2 / 7.3 / 7.4 / 8.0 / 8.1 / 8.2 / 8.3
- License: OSL 3.0

If you like, take some of your time to improve some translations, go to https://bit.ly/2HyCCEc.
