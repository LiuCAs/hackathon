<?php
namespace AppBundle\Utils;

class Location
{
    protected $_elasticHost;
    protected $_elasticPort;

    public function __construct($elasticHost, $elasticPort)
    {
        $this->_elasticHost = $elasticHost;
        $this->_elasticPort = $elasticPort;
    }

    public function gdzieJestSeba($code, $text)
    {
        $query = array(
            'from' => 0,
            'size' => 10,
            'min_score' => 0.005,
            'query' => array(
                'bool' => array(
                    'must' => array(
                        array(
                            'match' => array(
                                'code' => array(
                                    'query' => (string) $code,
                                    'operator' => 'and',
                                    'boost' => 0.1
                                )
                            )
                        )
                    )
                )
            )
        );
        foreach ($text as $element) {
            $query['query']['bool']['should'][]['match']['name'] = array(
                'query' => (string) $element,
                'operator' => 'or'
            );
        }

        return $this->request('/teryt/ulice/_search', 'POST', $query);
    }

    private function request($path, $method = 'GET', array $content = array())
    {
        $url = $this->_elasticHost . $path;

        $jsonData = json_encode($content);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PORT, $this->_elasticPort);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

}