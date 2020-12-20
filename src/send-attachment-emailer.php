<?php

class Send_Attachment_Emailer {

	private $allowed_files = array(
		'jpg' => 'image/jpeg',
		'png' => 'image/png',
		'gif' => 'image/gif',
	);
	
	public function __construct() {
		add_action( 'admin_post_email_with_attachment', [ $this, 'send_email' ] );
	}
	
	public function send_email() {
		// Validate nonce from the form field
		$email_with_attachment_nonce = filter_input( INPUT_POST, "email-with-attachment-nonce", FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $email_with_attachment_nonce, "email-with-attachment-nonce" ) ) {
			$this->exit_and_handle_upload_failure( 'nonce not verified' );
		}
		$to = filter_input( INPUT_POST, 'email', FILTER_SANITIZE_STRING );
		$message = filter_input( INPUT_POST, 'message', FILTER_SANITIZE_STRING );
		$subject = filter_input( INPUT_POST, 'name', FILTER_SANITIZE_STRING );
		
		$temp_file_path = false;
		
		// Handle attachment and possible errors
		if ( empty( $_FILES['vip-documents'] ) ) {
			$this->exit_and_handle_upload_failure( 'No file attached' );
		}
		
		$attachment_arr = $_FILES['vip-documents'];
		// Check if temp name is set, if not make empty string will handle later with file_exists check
		$attachment_tmp_name = isset( $attachment_arr['tmp_name'] ) ? $attachment_arr['tmp_name'] : '';
		
		// Checks if undefined or multiple, unlink temp file and exit
		if ( ! isset( $attachment_arr['error'] ) || is_array( $attachment_arr['error'] ) ) {
			$this->exit_and_handle_upload_failure( 'Invalid parameters', $attachment_tmp_name );
		}
		
		// Check the error value
		switch ( $attachment_arr['error'] ) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_NO_FILE:
				$this->exit_and_handle_upload_failure( 'No file sent', $attachment_tmp_name );
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$this->exit_and_handle_upload_failure( 'Exceeded filesize limit', $attachment_tmp_name );
			default:
				$this->exit_and_handle_upload_failure( 'Unknown error', $attachment_tmp_name );
		}
		
		// Check filesize compared to what the server has it set as
		if ( $attachment_arr['size'] > $this->return_bytes( ini_get( 'post_max_size' ) ) ) {
			$this->exit_and_handle_upload_failure( 'Exceeded filesize limit', $attachment_tmp_name );
		}
		
		// Check MIME Type by yourself.
		$finfo = new finfo( FILEINFO_MIME_TYPE );
		if (false === $ext = array_search(
			$finfo->file( $attachment_arr['tmp_name'] ),
			$this->allowed_files,
			true
		)) {
			$this->exit_and_handle_upload_failure( 'Invalid file format', $attachment_tmp_name );
		}
		
		// Passed the checks so attempt to move
		$temp_file_path = get_temp_dir() . basename( $_FILES['vip-documents']['name'] );
		// Copy the tmp file to one with the file name and extension
		$did_it_move = move_uploaded_file( $attachment_tmp_name, $temp_file_path );
		if ( ! $did_it_move ) {
			$this->exit_and_handle_upload_failure( 'File not copied', $attachment_tmp_name );
		}
		
		// Headers
		$headers = array('MIME-Version: 1.0');
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		
		$check_send = wp_mail( $to, $subject, $message, $headers, $temp_file_path );
		if ( $check_send ) {
			$this->email_success_redirect( [ $attachment_tmp_name, $temp_file_path ] );
		} else {
			$this->exit_and_handle_upload_failure( 'Email failed to send', [ $attachment_tmp_name, $temp_file_path ] );
		}
	}

	/**
	 * When email is successful, unlink files and redirect to thank you page.
	 * 
	 * @param mixed $files_to_unlink - pass a file(s) path here to delete before redirect
	 * @uses $this->delete_temp_files
	 * @uses wp_safe_redirect
	 */
	private function email_success_redirect( $files_to_unlink ) {
		if ( $files_to_unlink ) {
			$this->delete_temp_files( $files_to_unlink );
		}
		wp_safe_redirect('/thank-you/');
		exit();
	}

	/**
	 * On upload failure, return to home and pass a failure message as query arg
	 * @param string $failure_message - quick message why it failed
	 * @param mixed $files_to_unlink - pass a file(s) path here to delete before redirect
	 * 
	 * @uses $this->delete_temp_files
	 * @uses wp_safe_redirect
	 * @uses add_query_arg
	 * @uses home_url
	 */
    	private function exit_and_handle_upload_failure( string $failure_message, $files_to_unlink = false ) {
		if ( $files_to_unlink ) {
			$this->delete_temp_files( $files_to_unlink );
		}
		wp_safe_redirect(
		    esc_url(
			add_query_arg( 'upload_failure_message', $failure_message, home_url() )
		    )
		);
        	exit();
	}
	

	/**
	 * Method to remove tempory upload files
	 * @param mixed - $files_to_unlink - can be array of file strings or single file string
	 */
	private function delete_temp_files( $files_to_unlink ) {
		if ( ! is_array( $files_to_unlink ) ) {
			if ( file_exists( $files_to_unlink ) ) {
				unlink( $files_to_unlink );
			}
			return null;
		}
		foreach ( $files_to_unlink as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}
		return null;
	}

	/**
	 * Convert size string to bytes
	 * @param string $val - ex: 7m
	 * @return int $val - converted to bytes
	 */
	private function return_bytes( $val ) {
		$val = trim( $val );
		$last = strtolower( $val[ strlen( $val ) - 1 ]);
		$val = (int) $val;
		switch( $last ) {
			case 'g':
				$val *= (1024 * 1024 * 1024); //1073741824
				break;
			case 'm':
				$val *= (1024 * 1024); //1048576
				break;
			case 'k':
				$val *= 1024;
				break;
		}
		return $val;
	}
}
