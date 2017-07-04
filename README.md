cronlog
====

Server

* Cron `MAILTO=cronlog+type@server`

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
