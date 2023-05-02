<?php

namespace App\Controller;

use Pimcore\Model\DataObject;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Pimcore\Controller\FrontendController;


class Erecht24Controller extends FrontendController
{
	public function __construct()
	{
		$this->client = HttpClient::create();
	}
	
	/**
	 * // $name defines the privacy document name e.g imprint => impressum
	 * @param string $name
	 * // $lang defines the suffix part from the object key the is returned from the json response e.g html_en => 'english html code'
	 * @param string $lang
	 *
	 * Test with 
	 * curl -X GET "https://api.e-recht24.de/v2/imprint" -H "Content-Type: application/json" -H "Accept: application/json" \ 
	 *                                                   -H "eRecht24-api-key: e81cbf18a5239377aa4972773d34cc2b81ebc672879581bce29a0a4c414bf117" \
	 *						     -H "eRecht24-plugin-key: hxsddw3ouZtcHT7WaE2W5urEyHvXV4g9ewPd7i4rY3CMN5iP9q3exHfkmhxLTgLo"
	 *													 
	 *  api-key: https://api-docs.e-recht24.de/
	 *  plugin-key: https://github.com/fenepedia/contao-er24-rechtstexte/blob/main/src/ContaoErecht24RechtstexteBundle.php
       	 *  
	 */
	private function getErecht24Content($name = 'imprint', $lang = 'en')
	{
		$language = 'html_' . strtolower($lang);
		$url = 'https://api.e-recht24.de/v2/' . $name;
		$response = $client->request('GET', $url, [
			'headers' => [
				"Content-Type: application/json", 
				"Accept: application/json",
				"eRecht24-api-key: e81cbf18a5239377aa4972773d34cc2b81ebc672879581bce29a0a4c414bf117",
				"eRecht24-plugin-key: hxsddw3ouZtcHT7WaE2W5urEyHvXV4g9ewPd7i4rY3CMN5iP9q3exHfkmhxLTgLo"
			],
		]);
		
		// $statusCode = 200
		$statusCode = $response->getStatusCode();
		// $contentType = 'application/json'
		$contentType = $response->getHeaders()['content-type'][0];
		$content = $response->getContent();
		$content = $response->toArray();
		
		$html = '<html><h1>No html found</h1></html>';
		if (array_key_exists($language, $content)) {
			$html = $content[$language];
		}
		
		return $html;
	}
	
        /**
         * @param Request $request
         *
         * @Route("/privacy/{slug}")
         */
    	public function defaultAction(Request $request, string $slug = 'imprint')
    	{
		$content = $this->getErecht24Content($slug);
		$response = new Response();
		$response->setContent($content);
		$response->setStatusCode(Response::HTTP_OK);

		// sets a HTTP response header
		$response->headers->set('Content-Type', 'text/html');
		

        	return $response;
    	}
}

?>
