<?php
/**
 * PDFBucket PHP Library.
 *
 * Allows you to integrate easily with pdfbucket.io.
 *
 * @author PDFBucket Team
 */
class PdfBucket
{
    const AES_256_CTR = 'AES-256-CTR';
    const URI_REGEX = '/(http|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/~\+#]*[\w\-\@?^=%&amp;\/~\+#])?/';

    /**
     * Create an instance of the class.
     *
     * @param string $apiKey    API Key
     * @param string $apiSecret API Secret
     * @param string $apiHost   API Host, example: api.pdfbucket.io
     */
    public function __construct($apiKey, $apiSecret, $apiHost)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->apiHost = $apiHost;
    }

    /**
     * Generate API Request using an encrypted url.
     *
     * @param string $uri         Source URL with the HTML content
     * @param string $orientation Portrait | Landscape
     * @param string $pageSize    A4 | Letter
     * @param string $margin      Margin in pixels
     * @param float  $zoom        Zoom scaling factor, It must be between 0.0 and 1.0
     *
     * @return string url with the request to pdfbucket.io
     */
    public function generateUrl($uri, $orientation, $pageSize, $margin, $zoom)
    {
        $this->validParams($uri, $orientation, $pageSize, $margin, $zoom);

        $queryOptions = ['encrypted_uri' => $this->encrypt($uri)];

        return $this->buildUrl($orientation, $pageSize, $margin, $zoom, $queryOptions);
    }

    /**
     * Generate API Request using an plain url.
     *
     * @param string $uri         Source URL with the HTML content
     * @param string $orientation Portrait | Landscape
     * @param string $pageSize    A4 | Letter
     * @param string $margin      Margin in pixels
     * @param float  $zoom        Zoom scaling factor, It must be between 0.0 and 1.0
     *
     * @return string url with the request to pdfbucket.io
     */
    public function generatePlainUrl($uri, $orientation, $pageSize, $margin, $zoom)
    {
        $this->validParams($uri, $orientation, $pageSize, $margin, $zoom);

        $signature = $this->sign($uri, $orientation, $pageSize, $margin, $zoom);

        $queryOptions = ['uri' => $uri, 'signature' => $signature];

        return $this->buildUrl($orientation, $pageSize, $margin, $zoom, $queryOptions);
    }

    /**
     * Sign query params with the api key.
     *
     * @param string $uri         Source URL with the HTML content
     * @param string $orientation Portrait | Landscape
     * @param string $pageSize    A4 | Letter
     * @param string $margin      Margin in pixels
     * @param float  $zoom        Zoom scaling factor, It must be between 0.0 and 1.0
     *
     * @return string SHA1 Hash
     */
    public function sign($uri, $orientation, $pageSize, $margin, $zoom)
    {
        $params = implode(',', [
          $this->apiKey,
          $uri,
          $orientation,
          $pageSize,
          $margin,
          $zoom,
        ]);

        return sha1($params.$this->apiSecret);
    }

    /**
     * Encrypt uri to prevent exposing private data, using a symmetric encryption.
     *
     * @param string $uri URI
     *
     * @return string Encrypted URI
     */
    public function encrypt($uri)
    {
        $ivLength = openssl_cipher_iv_length(self::AES_256_CTR);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $decodedKey = base64_decode($this->apiSecret);
        $ciphertext = openssl_encrypt($uri, self::AES_256_CTR, $decodedKey, OPENSSL_RAW_DATA, $iv);
        $binaryContent = $iv.$ciphertext;

        return base64_encode($binaryContent);
    }

    /**
     * Build api request.
     *
     * @param array $queryOptions request options
     *
     * @return string URL for the API Request
     */
    private function buildUrl($orientation, $pageSize, $margin, $zoom, $extraOptions = array())
    {
        $defaultOptions = array(
            'api_key' => $this->apiKey,
            'orientation' => $orientation,
            'page_size' => $pageSize,
            'margin' => $margin,
            'zoom' => $zoom,
        );

        $queryOptions = array_merge($defaultOptions, $extraOptions);

        return implode('', [
            'https://',
            $this->apiHost,
            '/api/convert?',
            http_build_query($queryOptions),
        ]);
    }

    /**
     * Validates request params.
     *
     * @param string $uri         Source URL with the HTML content
     * @param string $orientation Portrait | Landscape
     * @param string $pageSize    A4 | Letter
     * @param string $margin      Margin in pixels
     * @param float  $zoom        Zoom scaling factor, It must be between 0.0 and 1.0
     *
     * @throws PdfBucketException if some validation is failed
     *
     * @return bool true if all validations passes
     */
    private function validParams($uri, $orientation, $pageSize, $margin, $zoom)
    {
        if (!preg_match(self::URI_REGEX, $uri)) {
            throw new PdfBucketException("Invalid URI $uri");
        }

        if (!in_array(strtolower($orientation), ['landscape', 'portrait'])) {
            throw new PdfBucketException("Invalid orientation $orientation");
        }

        if (!in_array(strtolower($pageSize), ['a4', 'letter'])) {
            throw new PdfBucketException("Invalid page size $pageSize");
        }

        $floatZoom = (float) $zoom;

        if ($floatZoom > 1.0 || $floatZoom < 0.0) {
            throw new PdfBucketException("Invalid zoom $zoom");
        }

        return true;
    }
}
