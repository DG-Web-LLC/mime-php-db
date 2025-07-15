# mime-php-db

A PHP Media Type Database. This database is a large complication of mime types and basic information about them. The library includes a build script that aggregates data from three main source:

- https://hg.nginx.org/nginx/raw-file/default/conf/mime.types
- https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
- https://www.iana.org/assignments/media-types/media-types.xml

> [!NOTE]
> This package is not designed to provide or replace php mime type detection

## Installation

Run the follow composer command

```bash
composer require dg-web-llc/mime-php-db
```

or add the follow entries to your composer.json file

### repositories

```json
{
    "type": "vcs",
    "url": "https://github.com/DG-Web-LLC/mime-php-db.git"
}
```

### require

```json
"dg-web-llc/mime-php-db": "dev-main"
```

then run the composer command

```bash
composer update
```

### Database Update and Build

## Usage

