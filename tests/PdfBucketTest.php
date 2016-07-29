<?php

class PdfBucketTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->apiKey = '635FBFIB3RL5BG68KEEC7HDA6N3I7PV2';
        $this->apiSecret = '5jqzA88Qzdpy+nz/ouWMAVSwzq3AOCV8LjvwflKmLQs=';
        $this->apiHost = 'test.pdfbucket.io';

        $this->defaultRequestParams = array(
            'uri' => 'https://www.google.com',
            'orientation' => 'landscape',
            'pageSize' => 'A4',
            'margin' => '0px',
            'zoom' => '1.0',
        );

        $this->pdfbucket = new PdfBucket($this->apiKey, $this->apiSecret, $this->apiHost);
    }

    public function testEncryptContent()
    {
        $options = $this->defaultRequestParams;

        $base64Ciphertext = $this->pdfbucket->encrypt($options['uri']);
        $this->assertFalse(empty($base64Ciphertext));
    }

    public function testSignParams()
    {
        $options = $this->defaultRequestParams;

        $sha = $this->pdfbucket->sign(
            $options['uri'],
            $options['orientation'],
            $options['pageSize'],
            $options['margin'],
            $options['zoom']
        );

        $this->assertFalse(empty($sha));
    }

    public function testGenerateUrl()
    {
        $options = $this->defaultRequestParams;

        $encryptedUrl = $this->pdfbucket->generateUrl(
            $options['uri'],
            $options['orientation'],
            $options['pageSize'],
            $options['margin'],
            $options['zoom']
        );

        $parsedUrl = parse_url($encryptedUrl);
        $queryString = $parsedUrl['query'];
        parse_str($queryString, $requestOptions);

        $this->assertFalse(empty($encryptedUrl));
        $this->assertFalse(empty($parsedUrl));
        $this->assertEquals('https', $parsedUrl['scheme']);
        $this->assertEquals($this->apiHost, $parsedUrl['host']);
        $this->assertEquals('/api/convert', $parsedUrl['path']);
        $this->assertFalse(empty($queryString));
        $this->assertFalse(empty($requestOptions['encrypted_uri']));
        $this->assertEquals($options['orientation'], $requestOptions['orientation']);
        $this->assertEquals($options['pageSize'], $requestOptions['page_size']);
        $this->assertEquals($options['margin'], $requestOptions['margin']);
        $this->assertEquals($options['zoom'], $requestOptions['zoom']);
    }

    public function testGeneratePlainUrl()
    {
        $options = $this->defaultRequestParams;

        $plainUrl = $this->pdfbucket->generatePlainUrl(
            $options['uri'],
            $options['orientation'],
            $options['pageSize'],
            $options['margin'],
            $options['zoom']
        );

        $parsedUrl = parse_url($plainUrl);
        $queryString = $parsedUrl['query'];
        parse_str($queryString, $requestOptions);

        $this->assertFalse(empty($plainUrl));
        $this->assertFalse(empty($parsedUrl));
        $this->assertEquals('https', $parsedUrl['scheme']);
        $this->assertEquals($this->apiHost, $parsedUrl['host']);
        $this->assertEquals('/api/convert', $parsedUrl['path']);
        $this->assertFalse(empty($queryString));
        $this->assertEquals($options['uri'], $requestOptions['uri']);
        $this->assertEquals($options['orientation'], $requestOptions['orientation']);
        $this->assertEquals($options['pageSize'], $requestOptions['page_size']);
        $this->assertEquals($options['margin'], $requestOptions['margin']);
        $this->assertEquals($options['zoom'], $requestOptions['zoom']);
    }

    /**
     * @expectedException PdfBucketException
     * @expectedExceptionMessageRegExp /Invalid orientation landscapeless/
     */
    public function testGeneratePlainUrlWithInvalidOrientation()
    {
        $options = $this->defaultRequestParams;

        $plainUrl = $this->pdfbucket->generatePlainUrl(
            $options['uri'],
            'landscapeless',
            $options['pageSize'],
            $options['margin'],
            $options['zoom']
        );

        $this->assertEquals(null, $plainUrl);
    }

    /**
     * @expectedException PdfBucketException
     * @expectedExceptionMessageRegExp /Invalid page size A5/
     */
    public function testGeneratePlainUrlWithInvalidPageSize()
    {
        $options = $this->defaultRequestParams;

        $this->pdfbucket->generatePlainUrl(
            $options['uri'],
            $options['orientation'],
            'A5',
            $options['margin'],
            $options['zoom']
        );
    }

    /**
     * @expectedException PdfBucketException
     * @expectedExceptionMessageRegExp /Invalid zoom 1.2/
     */
    public function testGeneratePlainUrlWithInvalidZoom()
    {
        $options = $this->defaultRequestParams;

        $this->pdfbucket->generatePlainUrl(
            $options['uri'],
            $options['orientation'],
            $options['pageSize'],
            $options['margin'],
            1.2
        );
    }

    /**
     * @expectedException PdfBucketException
     * @expectedExceptionMessageRegExp /Invalid URI http:foobar.com/
     */
    public function testGeneratePlainUrlWithInvalidUri()
    {
        $options = $this->defaultRequestParams;

        $this->pdfbucket->generatePlainUrl(
            'http:foobar.com',
            $options['orientation'],
            $options['pageSize'],
            $options['margin'],
            $options['zoom']
        );
    }
}
