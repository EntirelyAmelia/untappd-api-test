<?php

class App
{
    /** @const App constant Client id for Untapped API */
    const CLIENT_ID = '6667163DB18BB8A954FDD45F4DC5058645B6AE74';

    /** @const App constant Client secret for Untapped API */
    const CLIENT_SECRET = '24230F8EB2C281ACDCC9466AA70B289B5715CF18';

    /**
     * Process action for getting checkins.
     *
     * Uses 'beerid' query param to retrieve relevant checkins and outputs them in a list
     * @return void
     */
    public function actionGetCheckins(): void
    {
        $beerId = $this->getQueryParam('beerid');

        if (!$beerId || !filter_var($beerId, FILTER_VALIDATE_INT)) {
            throw new \Exception('beerId must be an integer.');
        }

        $client = new util\untappd\Client(self::CLIENT_ID, self::CLIENT_SECRET);

        $result = $client->getCheckins($beerId);

        usort($result, ['util\untappd\Checkin', 'sortByFirstName']);

        $this->outputCheckins($result);
    }

    /**
     * Gets the specified query param from the url.
     * @param string $key Key for param to retrieve
     * @return string The value for the key. `null` if the key does not exist
     */
    private function getQueryParam(string $key): string
    {
        parse_str($_SERVER["QUERY_STRING"], $query);
        return $query[$key] ?? null;
    }

    /**
     * Renders the checkin twig.
     * @param array $checkins An array of checkin objects to output
     */
    private function outputCheckins(array $checkins = []): void
    {
        $loader = new Twig_Loader_Filesystem('templates');
        $twig = new Twig_Environment($loader, []);

        echo $twig->render('checkins.twig', ['checkins' => $checkins]);
    }
}
