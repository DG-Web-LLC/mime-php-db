# mime-php-db

A PHP Media Type Database. This database is a large compilation of mime types and basic information about them. The library includes a build script that aggregates data from three main sources:

- [https://hg.nginx.org/nginx/raw-file/default/conf/mime.types](https://hg.nginx.org/nginx/raw-file/default/conf/mime.types)
- [https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types](https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types)
- [https://www.iana.org/assignments/media-types/media-types.xml](https://www.iana.org/assignments/media-types/media-types.xml)

> [!NOTE]
> This package is not designed to provide or replace php mime type detection.

## Installation

Run the follow composer command:

```bash
composer require dg-web-llc/mime-php-db
```

Or add the follow entries to your root composer.json file in the repositories collection and require object respectively:

```json
"repositories": [
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
> To ensure your package stays up to date, it is required to run the "update-data-sources" command after first install and then recommended to run periodically.

> [!WARNING]
> This is a long running operation that can take multiple minutes to run.

For ease of use, the library is designed to run an "update-data-sources" script from composer. To enable this feature you must first create the following entry in your root composer.json file's scripts object.

```json
"scripts": {
    "update-mimedb": [
        "@putenv COMPOSER=vendor/dg-web-llc/mime-php-db/composer.json",
        "@composer update-data-sources"
    ]
}
```

Once this entry is added you will be able to run the script with the follow command:

```bash
composer update-mimedb
```

The data sources can also be updated directly from PHP, the only requirement is to ensure that your composer vendor/autoload.php file is included.

> [!WARNING]
> The build script emits directory info to the standard output stream (php://stdout).

```php
require './vendor/autoload.php';

DGWebLLC\MimePhpDb\Scripts\Build::buildDataSource();
```

### Configuration

The configuration file for this package is a statically accessed class of constants named Config. It is normally not required to edit any of the entries contained in the Config class.

#### HTTP_ATTEMPTS

This configuration setting defines the number of request attempted before an http error is throw. This is to account for the event a request times out or fails during the data source aggregation. The default is 5.

```php
const HTTP_ATTEMPTS = 5;
```

#### DATA_DIR

This configuration setting defines the location of the data directory. If the specified directory does not exist an error will occur.

```php
const DATA_DIR = __DIR__.DIRECTORY_SEPARATOR.'data';
```

## Usage

Usage of the package should be done through the class wrapper MimeDb. It provides array like functionality and an integrated filter method based on php's array_filter function.

### Data Structure

The aggregated data source files are a combination of tab delimited and comma delimited text files. Each line represent an entry and each column is delimited by a tab. Arrays contained in the columns are delimited by commas.

```txt
application/java-archive	apache,iana,nginx	jar,war,ear
```

- name - The media type name or mime type. This follows the standard format of type/subtype.
- source - The data source that defined the media type, this can be one or more sources.
  - apache - [Apache common media types](https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types)
  - iana - [IANA media types](https://www.iana.org/assignments/media-types/media-types.xml)
  - nginx - [Nginx media types](https://hg.nginx.org/nginx/raw-file/default/conf/mime.types)
  - custom - The custom media types are defined directly in this package.
- extensions - The associated extensions, this can be zero or more.

### Creating the object

```php
// Imports the Object From namespace DGWebLLC\MimePhpDb
use DGWebLLC\MimePhpDb\MimeDb;

// Creates the MimeDb object
$db = new MimeDb();
```

### Fetching a mime type by name

```php
// Prints the extensions array to the screen
print_r($db['application/java-archive']->extensions);

/*
Expected Output: Array
(
    [0] => jar
    [1] => war
    [2] => ear
)
*/
```

### Iterating over MimeDb

```php
// Iterates over the dataset using a foreach loop until application/java-archive is found
foreach ($db as $mimeType) {
    print_r($mimeType);

    if ($mimeType->name == 'application/java-archive')
        break;
}
```

### Applying filters

```php
// Similar to the array_filter method, a callback function can be defined to filter the array

// This filter returns an object collection containing the application/java-archive media type
$mime = $db->filter(function ($mime) {
    return $mime->name == 'application/java-archive';
});

// This filter returns an object collection containing all media types that correspond to 
// the extension .jar
$mime = $db->filter(function ($mime) {
    return in_array('jar', $mime->extensions);
});
```

For more information about array filter callbacks see the [array_filter](https://www.php.net/manual/en/function.array-filter.php) method.
