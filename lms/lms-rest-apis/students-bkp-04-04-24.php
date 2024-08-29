<?php

class Rest_Lxp_Student
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

		register_rest_route('lms/v1', '/students/save', array(
			array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array('Rest_Lxp_Student', 'create'),
				'permission_callback' => '__return_true',
				'args' => array(
					'username' => array(
						'required' => true,
						'type' => 'string',
						'description' => 'user email name',
						'validate_callback' => function($param, $request, $key) {

							if ( is_email( strtolower(trim($request->get_param('username'))) ) || strlen(trim($request->get_param('username'))) == 0 ) {
								return false;
							}

							$ok = false;
							if (strtolower(trim($request->get_param('student_post_id'))) > 0) {
								$user_by_login = get_user_by ("login", strtolower(trim($request->get_param('username'))) );
								if ($user_by_login && $user_by_login->data->ID == strtolower(trim($request->get_param('student_post_id'))) && $user_by_login->data->user_login !== trim($request->get_param('username_default')) ) {
									$ok = true;
								}
							} 
							
							if (!is_email( strtolower(trim($request->get_param('username'))) ) && is_email( strtolower(trim($request->get_param('username_default'))) ) ) {
								$ok = true;
							}

							if (!is_email( strtolower(trim($request->get_param('username'))) ) && !strtolower(trim($request->get_param('username_default'))) ) {
								$ok = true;
							}

							if (
								( strtolower(trim($request->get_param('username'))) && strtolower(trim($request->get_param('username_default'))) )
								&& strtolower(trim($request->get_param('username'))) == strtolower(trim($request->get_param('username_default'))) 
							) {
								$ok = true;
							}

							return $ok;
						}
					),
					'first_name' => array(
						'required' => true,
						'type' => 'string',
						'description' => 'user first name',
						'validate_callback' => function($param, $request, $key) {
							return strlen( $param ) > 0;
						}
					),
					'last_name' => array(
						'required' => true,
						'type' => 'string',
						'description' => 'user last name',
						'validate_callback' => function($param, $request, $key) {
							return strlen( $param ) > 0;
						}
					),
					'user_password' => array(
						'required' => true,
						'type' => 'string',
						'description' => 'user login password',
						'validate_callback' => function($param, $request, $key) {
							$student_post_id = intval($request->get_param('student_post_id'));
							if ($student_post_id < 1) {
								return strlen( $param ) > 1;
							} else {
								return true;
							}
						}
					),
					'student_school_id' => array(
						'required' => true,
						'type' => 'integer',
						'description' => 'user school id',
						'validate_callback' => function($param, $request, $key) {
							return strlen( $param ) > 0;
						}
					),
					'teacher_id' => array(
						'required' => true,
						'type' => 'integer',
						'description' => 'teacher id',
						'validate_callback' => function($param, $request, $key) {
							return intval( $param ) > 0;
						}
					),
					'student_id' => array(
						'required' => false,
						'type' => 'string',
						'description' => 'user id',
						'validate_callback' => function($param, $request, $key) {
							return strlen( $param ) > 0;
						}
					),
				)
			),
		));

		register_rest_route('lms/v1', '/students', array(
			array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array('Rest_Lxp_Student', 'get_one'),
				'permission_callback' => '__return_true'
			)
		));
		
		register_rest_route('lms/v1', '/student/assign_grade', array(
			array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array('Rest_Lxp_Student', 'assign_grade'),
				'permission_callback' => '__return_true'
			)
		));

		register_rest_route('lms/v1', '/students/list', array(
			array(
				'methods' => WP_REST_Server::ALLMETHODS,
				'callback' => array('Rest_Lxp_Student', 'get_list'),
				'permission_callback' => '__return_true'
			)
		));

		register_rest_route('lms/v1', '/students/import', array(
			array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array('Rest_Lxp_Student', 'import'),
				'permission_callback' => '__return_true'
			),
		));

		register_rest_route('lms/v1', '/store/student', array(
			array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array('Rest_Lxp_Student', 'store_student'),
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
				'user_pass' => array(
					'required' => true,
					'type' => 'string',
					'description' => 'user login password',
				),
				'school_id' => array(
					'required' => true,
					'type' => 'integer',
					'description' => 'user school id',
				), 
			   )
			),
		));

        register_rest_route('lms/v1', '/get/student', array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array('Rest_Lxp_Student', 'get_student'),
				'permission_callback' => '__return_true',
				'args' => array(
	
				'id' => array(
					'required' => true,
					'type' => 'integer',
					'description' => 'user account id',
				),
				   
			   )
			),
		));
		
		register_rest_route('lms/v1', '/update/student', array(
			array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array('Rest_Lxp_Student', 'update_student'),
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

	// function to set grade add_post_meta for student with slide number
	public static function assign_grade($request) {
		$student_post_id = $request->get_param('student');
		$slide = $request->get_param('slide');
		$slide = intval($slide);
		$grade = $request->get_param('grade');
		$grade = intval($grade);
		$student_post_id = intval($student_post_id);
		$assignment = $request->get_param('assignment');
		$assignment_grade_key = "assignment_" . $assignment . "_slide_" . $slide . "_grade";
		
		if(get_post_meta($student_post_id, $assignment_grade_key, $grade, true)) {
			update_post_meta($student_post_id, $assignment_grade_key, $grade);
		} else {
			add_post_meta($student_post_id, $assignment_grade_key, $grade, true);
		}
		return wp_send_json_success(array('message' => 'Grade assigned successfully'));
	}

	// function to get grade for student with slid number
	public static function get_grade($request) {
		$student_post_id = $request->get_param('student');
		$assignment = $request->get_param('assignment');
		$assignment_grade_key = "assignment_" . $assignment . "_grade";
		$assignment_slide_key = "assignment_" . $assignment . "_slide";
		$grade = get_post_meta($student_post_id, $assignment_grade_key, true);
		$slid = get_post_meta($student_post_id, $assignment_slide_key, true);
		return wp_send_json_success(array('grade' => $grade, 'slid' => $slid));
	}


	public static function create($request) {		
		
		// ============= Student Post =================================
		$school_admin_id = $request->get_param('school_admin_id');
		$student_post_id = intval($request->get_param('student_post_id'));
		$student_name = strtolower( trim($request->get_param('username')) );
		$student_description = trim($request->get_param('about'));
		
		$school_post_arg = array(
			'post_title'    => wp_strip_all_tags(trim($request->get_param('last_name')) . ', ' . trim($request->get_param('first_name'))),
			'post_content'  => $student_description,
			'post_status'   => 'publish',
			'post_author'   => $school_admin_id,
			'post_type'   => TL_STUDENT_CPT
		);
		if (intval($student_post_id) > 0) {
			$school_post_arg['ID'] = "$student_post_id";
		}
		// Insert / Update
		$student_post_id = wp_insert_post($school_post_arg);
		if(get_post_meta($student_post_id, 'grades', json_encode($request->get_param('grades')))) {
			update_post_meta($student_post_id, 'grades', json_encode($request->get_param('grades')));
		} else {
			add_post_meta($student_post_id, 'grades', json_encode($request->get_param('grades')), true);
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
				$attach_id = wp_insert_attachment( $attachment, $file, $student_post_id );
	
				// Include image.php
				require_once(ABSPATH . 'wp-admin/includes/image.php');
	
				// Define attachment metadata
				$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
	
				// Assign metadata to attachment
				wp_update_attachment_metadata( $attach_id, $attach_data );
	
				// And finally assign featured image to post
				set_post_thumbnail( $student_post_id, $attach_id );	
			}	
		} */
		
		// ========== Student Admin ===========
		$student_admin_data = array(
			'first_name' => trim($request->get_param('first_name')),
			'last_name' => trim($request->get_param('last_name')),
			'display_name' =>  trim($request->get_param('last_name')) . ', ' . trim($request->get_param('first_name'))
		);
		
		if (trim($request->get_param('user_password'))) {
			$student_admin_data['user_pass'] = trim($request->get_param('user_password'));
		}
	
		$student_admin_id = null;
		$username = wp_strip_all_tags(trim($request->get_param('username')));

		$user_by_login = get_user_by ("login", strtolower(trim($request->get_param('username'))) );
		if ( intval($request->get_param('student_post_id')) < 1 && !$user_by_login ) {
			// create a new student user
			$student_admin_data['user_login'] = $username;
			$student_admin_data['user_email'] = $username . '@rpatreks.com';
			$student_admin_data['user_nicename'] = $username;
			$student_admin_data['role'] = 'lxp_student';
		} elseif ( $user_by_login && intval($request->get_param('student_post_id')) > 0  ) {
			// update existing student user
			$student_admin_id = $user_by_login->data->ID;
			$student_admin_data['ID'] = $student_admin_id;
			$student_admin_data['first_name'] = trim($request->get_param('first_name'));
			$student_admin_data['last_name'] = trim($request->get_param('last_name'));
			$student_admin_data['user_login'] = $username;
		} elseif (!is_email($username) && is_email($request->get_param('username_default'))) {
			// update the user which is already in the system as an email address
			$user_by_login = get_user_by ("login", strtolower(trim($request->get_param('username_default'))) );
			$student_admin_id = $user_by_login->data->ID;
			global $wpdb;
			$wpdb->update( $wpdb->users, array( 'user_login' => $username ), array( 'ID' => $student_admin_id ) );
			$student_admin_data['ID'] = $student_admin_id;
			$student_admin_data['user_login'] = $username;
			$student_admin_data['user_nicename'] = $username;
		}

		$student_admin_id = wp_insert_user($student_admin_data);
		
		if (trim($request->get_param('user_password'))) {
			wp_set_password( trim($request->get_param('user_password')), $student_admin_id );
		}
		
		if ($student_admin_id) {
			update_post_meta($student_post_id, 'lxp_student_admin_id', $student_admin_id);
			update_post_meta($student_post_id, 'lxp_student_school_id', trim($request->get_param('student_school_id')));
		}
		
		$lxp_teacher_id = $request->get_param('teacher_id');
		update_post_meta($student_post_id, 'lxp_teacher_id', ($lxp_teacher_id ? $lxp_teacher_id : 0));

		$student_id = $request->get_param('student_id');
		update_post_meta($student_post_id, 'student_id', ($student_id ? $student_id : 0));

		return wp_send_json_success("Student Saved!");
	}

	public static function get_list($request) {
		$students_ids = $request->get_param("students_ids");
		$students = array_map(function ($student_id)
		{
			$student = get_post($student_id);
			$student->grades = get_post_meta($student_id, 'grades', true);
			$admin = get_userdata(get_post_meta($student_id, 'lxp_student_admin_id', true));
			$student->admin_first_name = get_user_meta($admin->ID, 'first_name', true);
			$student->admin_last_name = get_user_meta($admin->ID, 'last_name', true);
			$student->name = $admin->data->display_name;
			$student->status = "In progress";
			$student->score = "0%";
			$student->progress = "0/0";
			return $student;
		}, $students_ids);
		return wp_send_json_success($students);
	}

	public static function get_one($request) {
		$student_id = $request->get_param('student_id');
		$student = get_post($student_id);
		$student->grades = json_decode(get_post_meta($student_id, 'grades', true));
		$teacher_id = get_post_meta($student_id, 'lxp_teacher_id', true);
		$lxp_student_id = get_post_meta($student_id, 'student_id', true);
		$student->teacher_id = $teacher_id ? $teacher_id : 0;
		$student->student_id = $lxp_student_id ? $lxp_student_id : 0;
		$admin = get_userdata(get_post_meta($student_id, 'lxp_student_admin_id', true));
		$admin->data->first_name = get_user_meta($admin->data->ID, 'first_name', true);
		$admin->data->last_name = get_user_meta($admin->data->ID, 'last_name', true);
		$adminStudent = array();
		$adminStudent["data"]["ID"] = $admin->data->ID;
		$adminStudent["data"]["user_login"] = $admin->data->user_login;
		$adminStudent["data"]["first_name"] = $admin->data->first_name;
		$adminStudent["data"]["last_name"] = $admin->data->last_name;
		$adminStudent["ID"] = $adminStudent["data"]["ID"];
		return wp_send_json_success(array("student" => $student, "admin" => $adminStudent));
	}

	public static function import($request)
	{
		$school_admin_id = $request->get_param('school_admin_id');
		$file = $request->get_file_params();
		$students_csv = isset($file['students']) ? $file['students'] : null;
		if ($students_csv['size'] > 0 && $students_csv['type'] == 'text/csv') {
			
			$overrides = array('test_form' => false);
			$upload = wp_handle_upload( $students_csv, $overrides );
			if ( $upload && !isset( $upload['error'] ) ) {
				$csv_file_url = $upload["url"];
				
				if (($handle = fopen($csv_file_url, "r")) !== false) {
					while (($row = fgetcsv($handle, 1000, ",")) !== false) {
						if (count($row) >= 4) {
							$first_name = trim($row[0]);
							$last_name = trim($row[1]);
							$user_display_name = $last_name . ', ' . $first_name;
							$username = strtolower( trim($row[2]) );
							$email = strtolower( trim($row[2]) ) . '@rpatreks.com';
							$password = trim($row[3]);
							$grades = explode('-', trim($row[4]));
							$student_id = trim($row[5]);

							if (!get_user_by('email', $email)) {
								$student_post_arg = array(
									'post_title'    => wp_strip_all_tags($user_display_name),
									'post_content'  => '',
									'post_status'   => 'publish',
									'post_author'   => $school_admin_id,
									'post_type'   => TL_STUDENT_CPT
								);
								// Insert Student post
								$student_post_id = wp_insert_post($student_post_arg);
	
								// ========== Student Admin ===========
								$student_admin_data = array(
									'user_login' => $username,
									'user_email' => $email,
									'first_name' => $first_name,
									'last_name' => $last_name,
									'display_name' => $user_display_name,
									'user_pass' => $password,
									'role' => 'lxp_student'
								);
								$student_admin_id  = wp_insert_user($student_admin_data);
								if ($student_admin_id) {
									wp_set_password( $password, $student_admin_id );
									add_post_meta($student_post_id, 'lxp_student_admin_id', $student_admin_id, true);
									add_post_meta($student_post_id, 'lxp_student_school_id', trim($request->get_param('student_school_id')), true);
								}

								$lxp_teacher_id = $request->get_param('teacher_id');
								update_post_meta($student_post_id, 'lxp_teacher_id', ($lxp_teacher_id ? $lxp_teacher_id : 0));
								update_post_meta($student_post_id, 'grades', json_encode($grades));
								update_post_meta($student_post_id, 'student_id', ($student_id ? $student_id : 0));
							}
						}		
					}
					fclose($handle);
				}
				return wp_send_json_success("Students imported successfully.");
			} else {
				return  wp_send_json_error("File could not uploaded.", 400);
			} 

		} else {
			return  wp_send_json_error("Invalid file . Upload valid CSV file.", 400);
		}

		return wp_send_json_success("");
	}

	public static function store_student()
	{
        $user_data = array(
            'user_login' => $_POST['login_name'],
            'first_name' => $_POST['first_name'],
            'last_name' =>$_POST['last_name'],
            'user_email' =>$_POST['user_email'],
            'display_name' =>$_POST['first_name'] . ' ' .$_POST['last_name'],
            'user_pass' =>$_POST['login_pass'],
            'role' => 'student'
         );
         $user_id  = wp_insert_user($user_data);
		 if(isset( $user_id->errors)){
			return  wp_send_json_error($user_id->errors, 400);
		 }
		 return wp_send_json_success(update_user_meta($user_id , 'lxp_school_id', $_POST['school_id']));
	}

    public static function get_student()
	{
        $users =  get_user_by('id', $_GET['id']
         );
         return wp_send_json_success($users);
	}

    public static function update_student()
	{
        $user_data = array(
            'ID' => $_POST['id'],
            'user_login' => $_POST['login_name'],
            'first_name' => $_POST['first_name'],
            'last_name' =>$_POST['last_name'],
            'user_email' =>$_POST['user_email'],
            'display_name' =>$_POST['first_name'] . ' ' .$_POST['last_name'],
         );
		return wp_send_json_success(wp_update_user($user_data));
	}
}

?>