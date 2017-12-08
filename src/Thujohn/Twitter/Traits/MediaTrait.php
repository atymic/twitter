<?php namespace Thujohn\Twitter\Traits;

use BadMethodCallException;

Trait MediaTrait {

	/**
	 * Upload media (images) to Twitter, to use in a Tweet or Twitter-hosted Card.
	 *
	 * Parameters :
	 * - media
	 * - media_data
	 */
	public function uploadMedia($parameters = [])
	{
	        if (!array_key_exists('media', $parameters) && !array_key_exists('media_data', $parameters))
	        {
	            throw new BadMethodCallException('Parameter required missing : media or media_data');
	        }

	        if (array_key_exists('media', $parameters) && array_key_exists('media_data', $parameters))
	        {
	            throw new BadMethodCallException('You cannot use media and media_data at the same time');
	        }

		return $this->post('media/upload', $parameters, true);
	}

	/**
	 * Upload media (video) to Twitter, to use in a Tweet or Twitter-hosted Card.
	 * Upload is chunked into pieces to support big files.
	 * Parameters :
	 * - media_file (path to local file)
	 *
	 * @param array $parameters
	 * @param int $chunk_size (defaults to 1 Mb)
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function uploadMediaChunked( $parameters = [], $chunk_size = null )
	{
		$chunk_size = $chunk_size ?: ( 1024 * 1024 );

		if ( !array_key_exists( 'media_file', $parameters ) )
		{
			throw new Exception( 'Parameter required missing : media_file' );
		}

		if ( !file_exists($parameters['media_file']) )
		{
			throw new Exception('File does not exist : ' . $parameters['media_file'] );
		}

		// Initialize upload

		$init_result = $this->post( 'media/upload', [
			'command' => 'INIT',
			'media_type' => $this->getFileMime( $parameters['media_file'] ),
			'total_bytes' => filesize($parameters['media_file'])
		], true );

		if ( isset($init_result->errors) )
		{
			return $init_result;
		}

		// Read file by chunks

		if ( !$handle = fopen( $parameters['media_file'], 'rb' ) )
		{
			throw new Exception( 'Error opening file : ' . $parameters['media_file'] );
		}

		$i = 0;

		while ( !feof( $handle ) )
		{
			$buffer = fread( $handle, $chunk_size );

			$append_result = $this->post( 'media/upload', [
				'command' => 'APPEND',
				'media_id' => $init_result->media_id_string,
				'segment_index' => $i++,
				'media' => $buffer
			], true );

			if ( isset($append_result->errors) )
			{
				fclose( $handle );
				return $append_result;
			}
		}

		fclose( $handle );

		// Finalize transfer

		$fin_result = $this->post( 'media/upload', [
			'command' => 'FINALIZE',
			'media_id' => $init_result->media_id_string
		], true );

		return $fin_result;
	}

	/**
	 * Gets mime type from provided file name
	 *
	 * @param string $filename
	 *
	 * @return mixed
	 */
	protected function getFileMime( $filename )
	{
		$finfo = finfo_open( FILEINFO_MIME_TYPE );
		$mime = finfo_file( $finfo, $filename );
		finfo_close($finfo);

		return $mime;
	}
}
