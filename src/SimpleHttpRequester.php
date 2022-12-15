<?php

namespace HtaccessCapabilityTester;

class SimpleHttpRequester implements HttpRequesterInterface
{
	/**
	 * Make a HTTP request to a URL.
	 *
	 * @param  string  $url  The URL to make the HTTP request to
	 *
	 * @return  HttpResponse  A HttpResponse object, which simply contains body, status code and response headers.
	 *                        In case the request itself fails, the status code is "0" and the body should contain
	 *                        error description (if available)
	 */
	public function makeHttpRequest($url)
	{
		$response = wp_remote_get($url);
		if (is_wp_error($response)) {
			return new HttpResponse('The following request failed: ' . $url, '0', []);
		}

		$body = wp_remote_retrieve_body($response);
		if (is_wp_error($body)) {
			return new HttpResponse('The following request failed: ' . $url, '0', []);
		}

		$statusCode = wp_remote_retrieve_response_code($response);

		// Create headers map
		$headers = wp_remote_retrieve_headers($response)->getAll();
		if (is_wp_error($body)) {
			return new HttpResponse('The following request failed: ' . $url, '0', []);
		}

		return new HttpResponse($body, $statusCode, $headers);
	}
}