<?php

class Rest_Lxp_Teacher
{
	/**
	 * Register the REST API routes.
	 */
	public static function init()
	{
		if (!function_exists('register_rest_route')) {
			// The REST API wasn't integrated into core until 4.4, and we support 4.0+ (for now).
			return false;
		}

		register_rest_route('lms/v1', '/teacher/treks/saved', array(
			array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array('Rest_Lxp_Teacher', 'treks_saved'),
				'permission_callback' => '__return_true'
			)
		));

		register_rest_route('lms/v1', '/teachers', array(
			array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array('Rest_Lxp_Teacher', 'get_one'),
				'permission_callback' => '__return_true'
			)
		));
		
		register_rest_route('lms/v1', '/teachers/save', array(
			array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array('Rest_Lxp_Teacher', 'create'),
				'permission_callback' => '__return_true',
				'args' => array(
					'user_email' => array(
					   'required' => true,
					   'type' => 'string',
					   'description' => 'user email name',  
					   'format' => 'email',
					   'validate_callback' => function($param, $request, $key) {
							$user_by_email = get_user_by("email", trim($request->get_param('user_email')));
							$user_by_login = get_user_by("login", trim($request->get_param('user_email')));
							if ( $user_by_email && intval($request->get_param('teacher_post_id')) > 0 && $user_by_email->data->user_email !== trim($request->get_param('user_email_default')) ) {
								return false;
							} else if ($request->get_param('teacher_post_id') == 0) {
								return ( !($user_by_email || $user_by_login) ? true : false );
							} if ( trim($request->get_param('user_email')) == '' ) {
								return false;
							} else {
								return true;
							}
						}
				  	),
					'first_name' => array(
						'required' => true,
						'type' => 'string',
						'description' => 'user first name',
						'validate_callback' => function($param, $request, $key) {
							return strlen( $param ) > 1;
						}
					),
					'last_name' => array(
						'required' => true,
						'type' => 'string',
						'description' => 'user last name',
						'validate_callback' => function($param, $request, $key) {
							return strlen( $param ) > 1;
						}
					),
					'user_password' => array(
						'required' => true,
						'type' => 'string',
						'description' => 'user login password',
						'validate_callback' => function($param, $request, $key) {
							$teacher_post_id = intval($request->get_param('teacher_post_id'));
							if ($teacher_post_id < 1) {
								return strlen( $param ) > 1;
							} else {
								return true;
							}
						}
					),
					'teacher_school_id' => array(
						'required' => true,
						'type' => 'integer',
						'description' => 'user school id',
						'validate_callback' => function($param, $request, $key) {
							return strlen( $param ) > 0;
						}
					),
					'about' => array(
						'required' => false,
						'type' => 'string',
						'description' => 'user about description',
						'validate_callback' => function($param, $request, $key) {
							return strlen( $param ) > 1;
						}
					),
			   )
			),
		));
		
		register_rest_route('lms/v1', '/update/teacher', array(
			array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array('Rest_Lxp_Teacher', 'update_teacher'),
				'permission_callback' => '__return_true',
				'args' => array(
					'user_email' => array(
					   'required' => true,
					   'type' => 'string',
					   'description' => 'user login name',  
					   'format' => 'email'
				   ),
				   'login_name' => array(
					'required' => true,
					'type' => 'string',
					'description' => 'user login name name'
				),
				'first_name' => array(
					'required' => true,
					'type' => 'string',
					'description' => 'user first name',
				),
				'last_name' => array(
					'required' => true,
					'type' => 'string',
					'description' => 'user last name',
				),
				'id' => array(
					'required' => true,
					'type' => 'integer',
					'description' => 'user account id',
				),
				   
			   )
			),
		));
		
	}

	public static function treks_saved($request) {
		$teacher_post_id = intval($request->get_param('teacher_post_id'));
		$is_saved = boolval($request->get_param('is_saved'));
		$trek_id = intval($request->get_param('trek_id'));
		// add/delete treacher 'treks_saved' post metadata
		if ($is_saved) {
			add_post_meta($teacher_post_id, 'treks_saved', $trek_id, true);
			return wp_send_json_success("Teacher TREK Saved!");
		} else {
			delete_post_meta($teacher_post_id, 'treks_saved', $trek_id);
			return wp_send_json_success("Teacher TREK Removed!");
		}
	}

	public static function create($request) {		
		
		// ============= Teacher Post =================================
		$school_admin_id = $request->get_param('school_admin_id');
		$teacher_post_id = intval($request->get_param('teacher_post_id'));
		$teacher_name = trim($request->get_param('user_email'));
		$teacher_description = trim($request->get_param('about'));
		
		$shool_post_arg = array(
			'post_title'    => wp_strip_all_tags($teacher_name),
			'post_content'  => $teacher_description,
			'post_status'   => 'publish',
			'post_author'   => $school_admin_id,
			'post_type'   => TL_TEACHER_CPT
		);
		if (intval($teacher_post_id) > 0) {
			$shool_post_arg['ID'] = "$teacher_post_id";
		}
		// Insert / Update
		$teacher_post_id = wp_insert_post($shool_post_arg);
		if(get_post_meta($teacher_post_id, 'grades', json_encode($request->get_param('grades')))) {
			update_post_meta($teacher_post_id, 'grades', json_encode($request->get_param('grades')));
		} else {
			add_post_meta($teacher_post_id, 'grades', json_encode($request->get_param('grades')), true);
		}
		// ============= Profile Picture =============================
		/* $file = $request->get_file_params();
		$profilePicture = isset($file['profile_picture']) ? $file['profile_picture'] : null;
		if ($profilePicture['size'] > 0) {
			$mimes = array(
				'bmp'  => 'image/bmp',
				'gif'  => 'image/gif',
				'jpe'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'jpg'  => 'image/jpeg',
				'png'  => 'image/png',
				'tif'  => 'image/tiff',
				'tiff' => 'image/tiff'
			);
			
			$overrides = array(
				'mimes'     => $mimes,
				'test_form' => false
			);
			 
			$upload = wp_handle_upload( $file['profile_picture'], $overrides );

			if ( $upload && !isset( $upload['error'] ) ) {
				// File uploaded successfully. 
				$uploadedFileURL = $upload['url'];
				$uploadedFileName = basename($upload['url']);
				
				// Add Featured Image to Post
				$image_url        = $uploadedFileURL; // Define the image URL here
				$image_name       = $uploadedFileName;
				$upload_dir       = wp_upload_dir(); // Set upload folder
				$image_data       = file_get_contents($image_url); // Get image data
				$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
				$filename         = basename( $unique_file_name ); // Create image file name
	
				// Check folder permission and define file location
				if( wp_mkdir_p( $upload_dir['path'] ) ) {
					$file = $upload_dir['path'] . '/' . $filename;
				} else {
					$file = $upload_dir['basedir'] . '/' . $filename;
				}
				
				// Create the image  file on the server
				file_put_contents( $file, $image_data );
	
				// Check image file type
				$wp_filetype = wp_check_filetype( $filename, null );
				
				// Set attachment data
				$attachment = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_title'     => sanitize_file_name( $filename ),
					'post_content'   => '',
					'post_status'    => 'inherit'
				);
				
				// Create the attachment
				$attach_id = wp_insert_attachment( $attachment, $file, $teacher_post_id );
	
				// Include image.php
				require_once(ABSPATH . 'wp-admin/includes/image.php');
	
				// Define attachment metadata
				$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
	
				// Assign metadata to attachment
				wp_update_attachment_metadata( $attach_id, $attach_data );
	
				// And finally assign featured image to post
				set_post_thumbnail( $teacher_post_id, $attach_id );	
			}	
		} */
		
		// ========== Teacher Admin ===========
		$teacher_admin_data = array(
			'user_login' => trim($request->get_param('user_email')),
			'user_email' => trim($request->get_param('user_email')),
			'first_name' => trim($request->get_param('first_name')),
			'last_name' => trim($request->get_param('last_name')),
			'display_name' => trim($request->get_param('first_name')) . ' ' . trim($request->get_param('last_name')),
			'role' => 'lxp_teacher'
		);
		
		if (trim($request->get_param('user_password'))) {
			$teacher_admin_data['user_pass'] = trim($request->get_param('user_password'));
		}

		$lxp_teacher_admin_id = get_post_meta($teacher_post_id, 'lxp_teacher_admin_id', true);
		if ($lxp_teacher_admin_id) {
			$teacher_admin_data["ID"] = $lxp_teacher_admin_id;
		}
		$teacher_admin_id  = wp_insert_user($teacher_admin_data);
		if (trim($request->get_param('user_password'))) {
			wp_set_password( trim($request->get_param('user_password')), $teacher_admin_id );
		}

		if (!boolval($lxp_teacher_admin_id) && $teacher_admin_id) {
			if(get_post_meta($teacher_post_id, 'lxp_teacher_admin_id', $teacher_admin_id)) {
				update_post_meta($teacher_post_id, 'lxp_teacher_admin_id', $teacher_admin_id);
			} else {
				add_post_meta($teacher_post_id, 'lxp_teacher_admin_id', $teacher_admin_id, true);
			}
			
			if(get_post_meta($teacher_post_id, 'lxp_teacher_school_id', true)) {
				update_post_meta($teacher_post_id, 'lxp_teacher_school_id', trim($request->get_param('teacher_school_id')));
			} else {
				add_post_meta($teacher_post_id, 'lxp_teacher_school_id', trim($request->get_param('teacher_school_id')), true);
			}
		}

        return wp_send_json_success("Teacher Saved!");
    }

    public static function get_one($request) {
		$teacher_id = $request->get_param('teacher_id');
		$teacher = get_post($teacher_id);
		$teacher->grades = json_decode(get_post_meta($teacher_id, 'grades', true));
		$admin = get_userdata(get_post_meta($teacher_id, 'lxp_teacher_admin_id', true));
		$admin->data->first_name = get_user_meta($admin->ID, 'first_name', true);
		$admin->data->last_name = get_user_meta($admin->ID, 'last_name', true);
		return wp_send_json_success(array("teacher" => $teacher, "admin" => $admin));
	}

    public static function update_teacher()
	{
        $user_data = array(
            'ID' => $_POST['id'],
            'user_login' => $_POST['login_name'],
            'first_name' => $_POST['first_name'],
            'last_name' =>$_POST['last_name'],
            'user_email' =>$_POST['user_email'],
            'display_name' =>$_POST['first_name'] . ' ' .$_POST['last_name'],
            'user_pass' =>$_POST['login_pass']
         );
         wp_send_json_success (wp_update_user($user_data));
		 
	}
}
