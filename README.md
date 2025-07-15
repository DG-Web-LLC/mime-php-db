# mime-php-db

A PHP Media Type Database. This database is a large complication of mime types and basic information about them. The library includes a build script that aggregates data from three main source:

- https://hg.nginx.org/nginx/raw-file/default/conf/mime.types
- https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
- https://www.iana.org/assignments/media-types/media-types.xml

> [!NOTE]
> This package is not designed to provide or replace php mime type detection.

## Installation

Run the follow composer command:

```bash
composer require dg-web-llc/mime-php-db
```

Or add the follow entries to your root composer.json file in the repositories collection and require object respectively:

```json
"repositories" [
    {
        "type": "vcs",
        "url": "https://github.com/DG-Web-LLC/mime-php-db.git"
    }
]
```

```json
"require" : {
    "dg-web-llc/mime-php-db": "dev-main"
}
```

Then run the composer command:

```bash
composer update
```

### Database Update and Build

> [!NOTE]
> To ensure you package stays up to date, it is recommend to run the update command after first install and periodically.

> [!WARNING]
> This operation can take multiple minutes to run.

For ease of use, the library is designed to run an "update-data-sources" script from composer. To enable this feature you must first create the following entry in your root composer.json file's script object.

```json
"scripts": {
    "update-mimedb": [
        "@putenv COMPOSER=vender/dg-web-llc/mime-php-db/composer.json",
        "@composer update-data-sources"
    ]
}
```

Once this entry is added you will be able to run the script with the follow command:

```base
composer update-mimedb
```

### Configuration

The configuration file for this package is a statically accessed class of constants named Config. This is take advantage of namespace's name collision prevention. It is normally not required to edit any of the entries contained in the Config class.

#### HTTP_ATTEMPTS

This configuration setting defines the number of request attempts preformed before an http request will throw an error. This is to account for the event a request times out or fails during the data source aggregation. The default is 5.

```php
const HTTP_ATTEMPTS = 5;
```

#### DATA_DIR

This configuration setting defines the location of the data directory. If the specified directory does not exist an error will occur.

```php
const DATA_DIR = __DIR__.DIRECTORY_SEPARATOR.'data';
```

## Usage

