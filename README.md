# PdfBucket

[![Build Status](https://travis-ci.org/pdfbucket/pdfbucket-php.svg?branch=master)](https://travis-ci.org/pdfbucket/pdfbucket-php)

PdfBucket PHP Library, Allows you to integrate easily with pdfbucket.io.

## Installation

To add this package as a local, per-project dependency to your project, simply add a dependency on `pdfbucket/pdfbucket` to your project's `composer.json` file. Here is a minimal example of a `composer.json` file that just defines a dependency on PdfBucket:

```json
  {
    "require": {
        "pdfbucket/pdfbucket": "~0.0.1"
    }
  }
```

## Usage

```php
try {
    $pdfBucket = new PdfBucket('<API KEY>', '<API SECRET>', 'api.pdfbucket.io');
    $uri = 'https://www.google.com';
    $orientation = 'landscape';
    $pageSize = 'A4';
    $margin = '0px';
    $zoom = '1.0';
    $encryptedUrl = $pdfBucket->generateUrl($uri, $orientation, $pageSize, $margin, $zoom);
    $plainUrl = $pdfBucket->generatePlainUrl($uri, $orientation, $pageSize, $margin, $zoom);
} catch (PdfBucketException $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}
```
