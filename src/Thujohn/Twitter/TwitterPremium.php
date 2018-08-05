<?php

namespace Thujohn\Twitter;

use Thujohn\Twitter\Twitter;
use RunTimeException;
use Illuminate\Support\Facades\Log;

/**
 * Class responsible for accessing Twitter Premium API
 *
 * @category Twitter
 * @package  Thujohn\Twitter
 * @author   Lucas Reis <lucas@programmer.com.br>
 * @license  https://github.com/thujohn/twitter/blob/master/LICENSE - LICENSE
 * @link     https://github.com/thujohn/twitter
 */
class TwitterPremium extends Twitter
{

    /**
     * Search for tweets using Twitter Premium API
     *
     * @param array $parameters - Parameters needed for the search
     *
     * @return response
     */
    public function getSearch($parameters = [])
    {
        $this->_checkRequiredParameter('query', $parameters);
        $this->_checkRequiredParameter('url', $parameters);

        return $this->get('search/tweets', $parameters);
    }

    /**
     * Check if the $parameter has been passed in the array ($parameters)
     *
     * @param string $parameter  -
     * @param string $parameters -
     *
     * @throws BadMethodCallException
     * @return void
     */
    private function _checkRequiredParameter($parameter, $parameters)
    {
        if (!array_key_exists($parameter, $parameters)) {
            throw new BadMethodCallException('Parameter required missing : '.$parameter);
        }
    }

    /**
     * Verifica se o parâmetro foi passado
     *
     * @param string $name          - Service's name that`s going to be consumed
     * @param string $requestMethod - HTTP method
     * @param array  $parameters    - Filters of the request
     * @param bool   $multipart     -
     * @param string $extension     -
     *
     * @return response
     */
    public function query($name, $requestMethod = 'GET', $parameters = [], $multipart = false, $extension = 'json')
    {
        $this->config['host'] = $this->tconfig['API_URL'];

        if ($multipart) {
            $this->config['host'] = $this->tconfig['UPLOAD_URL'];
        }

        $url = $parameters['url'];

        unset($parameters['url']);

        $this->log('METHOD : '.$requestMethod);
        $this->log('QUERY : '.$name);
        $this->log('URL : '.$url);
        $this->log('PARAMETERS : '.http_build_query($parameters));
        $this->log('MULTIPART : '.($multipart ? 'true' : 'false'));

        parent::user_request(
            [
                'method'    => $requestMethod,
                'host'      => $name,
                'url'       => $url,
                'params'    => $parameters,
                'multipart' => $multipart,
            ]
        );

        $response = $this->response;

        $format = 'object';

        if (isset($parameters['format'])) {
            $format = $parameters['format'];
        }

        $this->log('FORMAT : '.$format);

        $error = $response['error'];

        if ($error) {
            $this->log('ERROR_CODE : '.$response['errno']);
            $this->log('ERROR_MSG : '.$response['error']);

            $this->setError($response['errno'], $response['error']);
        }

        if (isset($response['code']) && ($response['code'] < 200 || $response['code'] > 206)) {
            $_response = $this->jsonDecode($response['response'], true);
            $error_msg =  $_response['error']['message'];
            $this->log('ERROR_MSG : '.$error_msg);

            $this->setError("", $error_msg);
            throw new RunTimeException($error_msg);
        }

        switch ($format) {
            default:
            case 'object':
                $response = $this->jsonDecode($response['response']);
                break;

            case 'json':
                $response = $response['response'];
                break;

            case 'array':
                $response = $this->jsonDecode($response['response'], true);
                break;
        }

        return $response;
    }

    /**
     * Save log messages
     *
     * @param string $message -
     *
     * @return response
     */
    protected function log($message)
    {
        $this->log[] = $message;
    }
}
