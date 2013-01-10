<?

namespace Heello;

/**
 * Wrapper for the HTTP_Request 2 library
 */
class Request {
	const GET = \HTTP_Request2::METHOD_GET;
	const POST = \HTTP_Request2::METHOD_POST;
	const PUT = \HTTP_Request2::METHOD_PUT;
	const DELETE = \HTTP_Request2::METHOD_DELETE;

	private $method, $url, $request;

	function __construct($method, $url) {
		$this->method = $method;

		$this->url = self::get_request_url_base();
		$this->url .= trim($url, "/ ");
		$this->url .= '.json';

		$this->request = new \HTTP_Request2($this->url, $this->method);

		// Set this to false, could be a bug in the library. Even sites with valid ssl were causing issues.
		$this->request->setConfig('ssl_verify_peer',false);
	}

	public function addParameter($key, $val) {
		if ($this->method == self::GET || $this->method == self::DELETE || $this->method == self::PUT)  {
			$url = $this->request->getUrl();
			$url->setQueryVariable($key, $val);
		} else {
			$this->request->addPostParameter($key, $val);
		}
	}

	public function addParameters($params) {
		foreach ($params as $key => $val) {
			$this->addParameter($key, $val);
		}
	}

	public function addAttachments(&$method_args, $endpoint_options, $parent_key=false){
		foreach ($method_args as $key => &$val){
			if (is_array($val)){
				$this->addAttachments($val, $endpoint_options, $key);
			} else{
				if (in_array($key, get($endpoint_options,'attachments'))){
					if (file_exists($val)){
						$attachment_key = $parent_key ? "{$parent_key}[{$key}]" : $key;
						$this->request->addUpload($attachment_key,$val,'media');
						unset($method_args[$key]);
					}
				}
			}
		}
	}

	public function send($require_auth = false) {
		$tokens = Client::config()->get_tokens();
		if ($require_auth || $tokens['access']) {
			$this->addParameter('access_token', $tokens['access']);
		} else{
			$client_config = Client::config()->get_client();
			$this->addParameter('key', $client_config['id']);
			$this->addParameter('secret', $client_config['secret']);
		}

		try {
			$response = $this->request->send();
			if ($response->getStatus() == 200 || $response->getStatus() == 201) {
				$body = $response->getBody();

				if (strlen($body) > 0) {
					return json_decode($body)->response;
				}

				return true;
			} elseif ($response->getStatus() == 401) {
				$error = json_decode($response->getBody())->error;
				if ($error == APIException::EXPIRED_TOKEN) {
					throw new ExpiredAccessTokenException();
				} else{
					throw new APIException(
						$response->getStatus() . ' ' . $response->getReasonPhrase()
					);
				}
			} else {
				throw new APIException(
					$response->getStatus() . ' ' . $response->getReasonPhrase()
				);
			}
		} catch (HTTP_Request2_Exception $e) {
			throw new APIException('Error: ' . $e->getMessage());
		}
	}

	public static function get_request_url_base() {
		$url = "http" . (API::SECURE ? "s" : "") . "://";
		$url .= API::DOMAIN . "/";

		return $url;
	}
}
