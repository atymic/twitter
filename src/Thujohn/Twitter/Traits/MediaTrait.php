<?php namespace Thujohn\Twitter\Traits;

use Exception;

Trait MediaTrait {

	/**
	 * Upload media (images or videos) to Twitter, to use in a Tweet or Twitter-hosted Card.
	 *
	 * Parameters :
	 * - media: stream of data to be uploaded
	 */
	public function uploadMedia($parameters = [])
	{
		if (! array_key_exists('media', $parameters))
		{
			throw new Exception('Parameter required missing : media');
		}

		$media = $parameters['media'];
		$mediaMimeType = $this->getMediaMime($media);

		if ($this->isVideoUpload($mediaMimeType)) {
			return $this->uploadVideoMedia($parameters);
		} else {
			return $this->uploadImageMedia($parameters);
		}
	}

	/**
	 * @param $mime
	 *
	 * @return bool
	 */
	private function isVideoUpload($mime) {
		return strstr($mime, 'video/') !== false;
	}

	/**
	 * @param $parameters
	 *
	 * @return mixed
	 */
	private function uploadImageMedia($parameters) {
		return $this->post('media/upload', $parameters, true);
	}

	/**
	 * Triggers a synchronous upload of the video file in chunks
	 *
	 * @param $parameters
	 */
	private function uploadVideoMedia($parameters) {
		$init_parameters = [
			'command' => 'INIT',
			'media_type' => $this->getMediaMime($parameters['media']),
			'total_bytes' => strlen($parameters['media'])
		];
		$init_result = $this->post('media/upload', $init_parameters, true);

		if (isset($init_result->errors)) {
			return $init_result;
		}

		$chunked = $this->splitFileInChunks($parameters['media']);
		$i = 0;
		foreach ($chunked as $chunk) {
			$append_parameters = [
				'command' => 'APPEND',
				'media_id' => $init_result->media_id_string,
				'segment_index' => $i,
				'media' => $chunk
			];
			$append_result = $this->post('media/upload', $append_parameters, true);

			if (isset($append_result->errors)) {
				return $append_result;
			}

			$i++;
		}

		$fin_result = $this->post('media/upload', [
			'command' => 'FINALIZE',
			'media_id' => $init_result->media_id_string
		], true);

		return $fin_result;
	}

	/**
	 * @param $stringContents
	 * @param int $chunks
	 *
	 * @return array
	 */
	private function splitFileInChunks($stringContents, $chunks = 10) {
		$total_length = strlen($stringContents);
		$chunk_length = ceil($total_length / $chunks);

		return str_split($stringContents, $chunk_length);
	}

	/**
	 * @param $binary
	 *
	 * @return mixed
	 */
	private function getMediaMime($binary) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$v = finfo_buffer($finfo, $binary);
		finfo_close($finfo);

		return $v;
	}

}
