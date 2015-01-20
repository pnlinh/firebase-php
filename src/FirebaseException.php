<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Kreait\Firebase;

use Ivory\HttpAdapter\Message\ResponseInterface;
use Ivory\HttpAdapter\Message\RequestInterface;

/**
 * @link https://www.firebase.com/docs/rest/api/#section-error-conditions Firebase Error Conditions
 */
class FirebaseException extends \Exception
{
    /**
     * @var \Ivory\HttpAdapter\Message\RequestInterface|null
     */
    private $request;

    /**
     * @var \Ivory\HttpAdapter\Message\ResponseInterface|null
     */
    private $response;

    public function hasRequest()
    {
        return $this->request !== null;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setRequest(RequestInterface $request = null)
    {
        $this->request = $request;
    }

    public function hasResponse()
    {
        return $this->response !== null;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response = null)
    {
        $this->response = $response;
    }

    public static function urlIsInvalid($url)
    {
        return new self(sprintf('The url "%s" is invalid.', $url));
    }

    public static function baseUrlSchemeMustBeHttps($url)
    {
        return new self(sprintf('The base url must point to an https URL, "%s" given.', $url));
    }

    public static function locationKeyContainsForbiddenChars($key, $forbiddenChars)
    {
        return new self(
            sprintf(
                'The location key "%s" contains on of the following invalid characters: %s',
                $key,
                $forbiddenChars
            )
        );
    }

    public static function locationHasTooManyKeys($allowed, $given)
    {
        return new self(sprintf('A location key must not have more than %s keys, %s given.', $allowed, $given));
    }

    public static function locationKeyIsTooLong($allowed, $given)
    {
        return new self(sprintf('A location key must not be longer than %s bytes, %s bytes given.', $allowed, $given));
    }

    public static function serverError(
        RequestInterface $request = null,
        ResponseInterface $response = null,
        \Exception $previous = null
    ) {
        $requestBody = null;
        $responseBody = null;

        if ($request->hasBody()) {
            $requestBody = (string) $request->getBody()->getContents();
        }

        if ($response && $response->hasBody()) {
            $responseBody = (string) $response->getBody()->getContents();
        }

        $message = sprintf(
            'Server error (%s) for URL %s with data "%s"',
            $response ? $response->getStatusCode() : 'Unknown',
            $request->getUrl(),
            $requestBody
        );

        if ($responseBody && $responseData = json_decode($responseBody, true)) {
            if (isset($responseData['error'])) {
                $message = sprintf('%s: %s', $message, $responseData['error']);
            }
        }

        $e = new self($message, null, $previous);
        $e->setRequest($request);
        $e->setResponse($response);

        return $e;
    }
}