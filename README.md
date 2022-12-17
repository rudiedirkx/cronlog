cronlog
====

Server

* Cron `MAILTO=logs@your-cronlog.com`, or
* `cronlog_upload.sh your_cron_command`

App

* Configure types
* Configure triggers per type
    * Name, e.g. `Errors` or `Renewed`
    * Regex, e.g. `Notice: ` or `Requesting certificate`
    * Color
* Count number of instances per trigger for every incoming mail

| Origin | DateTime        | Size | Errors    | Renewed |
| ------ | --------------- | ---- | --------- | ------- |
| Devver | Today 05:00     | 828  | 0         | 2       |
| I-Res  | Today 05:00     | 632  | 0         | 0       |
| Devver | Yesterday 05:00 | 632  | 11        | 0       |
| I-Res  | Yesterday 05:00 | 632  | 0         | 0       |

* Read incoming cronlogs from
    *  email: `CRONLOG_MAIL_IMPORTERS` in `env.php`, or
    * uploaded logs: `UploadedImporterCollector` in `inv.bootstrap.php`
