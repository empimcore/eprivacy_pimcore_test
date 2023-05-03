<?php
/**
 *
 * PHP version 8.0
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 * @category   PHP
 * @package
 * @subpackage Filter
 * @author FirstName LastName <mail>
 * @copyright 2009 FirstName LastName
 * @link
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id:$
 */

namespace App\Controller;
use Pimcore\Model\DataObject;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Pimcore\Controller\FrontendController;

/**
 * @todo Description of class Erecht24Controller
 * @author
 * @version
 * @package
 * @subpackage
 * @category
 * @link
 */
class Erecht24Controller extends FrontendController {

    private CurlHttpClient $httpClient;
    private string $apiKey;
    private string $pluginKey;
    private string $eRecht24ApiUrl;
    /**
     * @todo Description of function __construct
     * @param
     * @return
     */
    public function __construct() {
        $this->httpClient = HttpClient::create();
	$this->apiKey = 'e81cbf18a5239377aa4972773d34cc2b81ebc672879581bce29a0a4c414bf117';
        $this->pluginKey = 'hxsddw3ouZtcHT7WaE2W5urEyHvXV4g9ewPd7i4rY3CMN5iP9q3exHfkmhxLTgLo';
        $this->eRecht24ApiUrl = 'https://api.e-recht24.de/v2';
    }

    /**
     * @todo Description of function getErecht24HtmlContent
     * @param  string $documentName[optional]           default value : 'imprint'
     * @param  string $documentLanguageCode[optional]   default value : 'en'
     * @return string $htmlCodeFromRequestedDocument
     * @testWith  ->  curl -X GET "https://api.e-recht24.de/v2/imprint" -H "Content-Type: application/json" -H "Accept: application/json" \
     *                                                                  -H "eRecht24-api-key: e81cbf18a5239377aa4972773d34cc2b81ebc672879581bce29a0a4c414bf117" \
     *                                                                  -H "eRecht24-plugin-key: hxsddw3ouZtcHT7WaE2W5urEyHvXV4g9ewPd7i4rY3CMN5iP9q3exHfkmhxLTgLo"
     */
    private function getErecht24HtmlContent(string $documentName = 'imprint', string $documentLanguageCode = 'en') : string {
        $language = 'html_' . strtolower($documentLanguageCode);
        $url = $this->eRecht24ApiUrl . '/' . $documentName;
        $response = $this->httpClient->request('GET', $url, [
								'headers' => [
										'Content-Type: application/json',
									   	'Accept: application/json',
									   	'eRecht24-api-key: ' . $this->apiKey,
										'eRecht24-plugin-key: ' . $this->pluginKey
									     ],
                                                            ]);
        // $statusCode = 200
        $statusCode = $response->getStatusCode();
        // $contentType = 'application/json'
        $contentType = $response->getHeaders() ['content-type'][0];
        $content = $response->getContent();
        $contentArray = $response->toArray();
        $htmlCodeFromRequestedDocument = '<html><h1>No html found</h1></html>';
        if (array_key_exists($language, $contentArray)) {
            $htmlCodeFromRequestedDocument = $contentArray[$language];
        }
        return $htmlCodeFromRequestedDocument;
    }

    /**
     * @todo Description of function getERecht24DocumentTypeFromRequest
     * @param Request $request
     * @return string $validDocumentType
     */
    private function getErecht24DocumentTypeFromRequest(Request $request) : string {
        $requestUri = $request->getUri();
        $requestUriArray = str_contains($requestUri, '/') ? explode('/', $requestUri) : ['imprint'];
        $documentType = strtolower(array_pop($requestUriArray));
        switch ($documentType) {
            case 'imprint':
            case 'impressum':
        	$validDocumentType = 'imprint';
    	    break;
            case 'privacypolicy':
            case 'datenschutz':
                $validDocumentType = 'privacyPolicy';
            break;
            case 'privacypolicysocialmedia':
            case 'datenschutz-bestimmungen-von-soziale-medien':
                $validDocumentType = 'privacyPolicySocialMedia';
            break;
            default:
                $validDocumentType = 'imprint';
            break;
       }

       return $validDocumentType;
    }

    /**
     * @todo Description of function defaultAction
     * @param  Request $request
     * @return Response $response
     * @Route("/imprint")
     * @Route("/impressum")
     * @Route("/privacypolicy")
     * @Route("/datenschutz")
     * @Route("/privacypolicysocialmedia")
     * @Route("/datenschutz-bestimmungen-von-soziale-medien")
     *
     */
    public function defaultAction(Request $request) : Response {
        $languageCode = strtolower($request->getLocale());
        $documentType = $this->getErecht24DocumentTypeFromRequest($request);
        $content = $this->getErecht24HtmlContent($documentType, $languageCode);
        $response = new Response();
        $response->setContent($content);
        $response->setStatusCode(Response::HTTP_OK);
        // sets a HTTP response header
        $response->headers->set('Content-Type', 'text/html');
        return $response;
    }
}
?>
