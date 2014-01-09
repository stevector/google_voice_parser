# Google Voice Parsing App

## WARNING: This code is not meant to be installed on any publicly available server.

This repository holds a Symfony app (built from the [Symfony Standard Edition](https://github.com/symfony/symfony-standard)) for parsing Google Voice Data. It was written to provide a graph for a blog post on stevector.com.

If you have thoughts on how this code should be restructured, please file an issue or comment on an existing one. [Or comment on this pull request on stevector.com.](https://github.com/stevector/stevector.github.io/pull/10)

## Produce your own graph of exclamation point usage in Google Voice

### Install this app

**Clone the repo into a directory that can be accessed as a local web server.**

```
git clone git@github.com:stevector/google_voice_parser.git
```

**Copy the parameters file into place.**

```
cd google_voice_parser
cp app/config/parameters.yml.dist app/config/parameters.yml
```

**Install the Composer-specified dependencies**

```
composer install
```

**Add your Google Voice files**

Add all the html files from a [Google Takeout](https://www.google.com/settings/takeout) export to the directory `app/google_voice_export/`.

**Browse to the chart**

Go to web/app.php/google_voice/chart relative to your install location.
