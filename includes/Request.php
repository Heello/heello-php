<?

namespace Heello;

/**
 * Wrapper for the HTTP_Request 2 library
 */
class Request {
	const GET = \HTTP_Request2::METHOD_GET;
	const POST = \HTTP_Request2::METHOD_POST;

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
		if ($this->method == self::GET) {
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

	public function send($require_auth = false) {
		if ($require_auth) {
			$tokens = Client::config()->get_tokens();
			$this->addParameter('access_token', $tokens['access']);
		}

		try {
			$response = $this->request->send();
			if ($response->getStatus() == 200) {
				$body = $response->getBody();

				if (strlen($body) > 0) {
					return json_decode($body);
				}

				return true;
			} elseif ($response->getStatus() == 401) {
				$error = json_decode($response->getBody())->error;
				if ($error == APIException::EXPIRED_TOKEN) {
					throw new ExpiredAccessTokenException();
				}
			} else {
				throw new APIException(
					'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
					$response->getReasonPhrase()
				);
			}
		} catch (HTTP_Request2_Exception $e) {
			throw new APIException('Error: ' . $e->getMessage());
		}
	}

	public static function get_request_url_base() {
		$url = "http" . (API::SECURE ? "s" : "") . "://";
		$url .= API::DOMAIN;
		$url .= "/" . API::VERSION . "/";

		return $url;
	}
}
