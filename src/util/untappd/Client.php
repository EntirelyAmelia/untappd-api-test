<?php

namespace util\untappd;

class Client
{
    /** @const Client constant Base API url for Untappd API */
    const BASE_API_URL = 'https://api.untappd.com/v4/';

    /** @var string Client id to use for API requests */
    private $clientId;

    /** @var string Client secret to use for API requests */
    private $clientSecret;

    /**
     * Constructs a new Client object
     * @param string $_clientId     Client id to use for API requests
     * @param string $_clientSecret Client secret to use for API requests
     * @throws \Exception If params are null or empty
     */
    public function __construct(string $_clientId, string $_clientSecret)
    {
        if (!$_clientId || !$_clientSecret) {
            throw new \Exception('Client ID and client secret are required.');
        }

        $this->clientId = $_clientId;
        $this->clientSecret = $_clientSecret;
    }

    /**
     * Gets the checkins for a designated beer id
     * @param  int   $beerId Beer id to look up checkins for
     * @return array<Checkin> Array of checkin objects
     * @throws \Exception
     */
    public function getCheckins(int $beerId): array
    {
        $apiMethodName = 'beer/checkins';

        $result = $this->makeGetRequest(
            sprintf($apiMethodName . '/%s', $beerId)
        );

        $decoded = json_decode($result, true);

        if (!$decoded) {
            throw new \Exception('Unable to decode response.');
        }

        $response = $decoded['response'] ?? null;
        if (!$response) {
            throw new \Exception('Missing response key.');
        }

        $checkins = $response['checkins']['items'] ?? null;

        if (!$checkins || !is_array($checkins) || count($checkins) < 1) {
            throw new \Exception('No checkins found in response.');
        }

        $ret = [];
        foreach ($checkins as $checkin) {
            $obj = $this->getCheckinObject($checkin);
            $ret[] = $obj;
        }

        return $ret;
    }

    /**
     * Converts an array of checkin data into a Checkin object
     * @param  array   $data Checkin data from API response
     * @return Checkin A checkin object
     */
    private function getCheckinObject(array $data): Checkin
    {
        $obj = new Checkin();

        $obj->id = $data['checkin_id'] ?? null;
        $obj->rating = $data['rating_score'] ?? null;
        $obj->comment = $data['checkin_comment'] ?? '';

        if ($beer = $data['beer'] ?? null) {
            $obj->beerName = $beer['beer_name'] ?? '';
        }

        if ($brewery = $data['brewery'] ?? null) {
            $obj->breweryName = $brewery['brewery_name'] ?? '';
        }

        if ($user = $data['user'] ?? null) {
            $obj->username = $user['user_name'] ?? '';
            $obj->userFirstName = $user['first_name'] ?? '';
            $obj->userLastName = $user['last_name'] ?? '';
            $obj->userPhotoUrl = $user['user_avatar'] ?? '';
        }

        if ($venue = $data['venue'] ?? null) {
            $obj->locationName = $venue['venue_name'] ?? '';
        }

        return $obj;
    }

    /**
     * Generates a GET request and excutes it with curl.
     * @param  string $method API method to request
     * @param  array $params Query params to use for request
     * @return string Response from API. Generally a JSON.
     * @throws \Exception\CurlException For any non-200 responses or if the response data is empty
     */
    private function makeGetRequest(string $method, array $params = []): string
    {
        $queryParams = array_merge($params, $this->getAuth());
        $queryString = '';

        foreach ($queryParams as $key => $value) {
            if (!empty($queryString)) {
                $queryString .= '&';
            }

            $queryString .= $key . '=' . $value;
        }

        $url = sprintf(
            self::BASE_API_URL . '%s?%s',
            $method,
            $queryString
        );

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode != 200) {
            throw new \Exception\CurlException('Received ' . $httpCode . ' response from API. URL: ' . $url);
        } elseif (!$response) {
            throw new \Exception\CurlException('Null response received.');
        }

        return $response;
    }

    /**
     * Gets an array of the authorization params to use for a request
     * @return array
     */
    private function getAuth(): array
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];
    }
}
