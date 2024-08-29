<?php

/**
 * Class TL_Teacher_Post_Type
 * 
 * @author Waqar Muneer
 * @version 1.0
 */
require_once(ABSPATH . 'wp-admin/includes/file.php');
class TL_Teacher_Post_Type extends TL_Post_Type
{
   /**
    * @var null
    */
   protected static $_instance = null;

   /**
    * @var string
    */
   protected $_post_type = TL_TEACHER_CPT;

   /**
    * Get Instance
    */
   public static function instance()
   {
      if (is_null(self::$_instance)) {
         self::$_instance = new self();
      }

      return self::$_instance;
   }

   /**
    * Constructor
    */
   public function __construct()
   {
      parent::__construct();
   }

   /**
    * Register course post type.
    */
   public function args_register_post_type(): array
   {

      // $located = locate_template('single-tl_trek.php');
      // if (empty($located)) {
      //    add_filter('single_template', function ($page_template, $type) {
      //       global $post;
      //       if ($post->post_type == TL_TREK_CPT) {
      //          $page_template = dirname(__FILE__) . '/templates/trek/single-tl_trek.php';
      //       }
      //       return $page_template;
      //    }, 20, 2);
      // }

      $labels           =     array(
         'name'               => _x('LXP Teacher', 'Post Type General Name', 'tinylms'),
         'singular_name'      => _x('LXP Teacher', 'Post Type Singular Name', 'tinylms'),
         'menu_name'          => __('LXP Teachers', 'tinylms'),
         'parent_item_colon'  => __('Parent Item:', 'tinylms'),
         'all_items'          => __('LXP Teachers', 'tinylms'),
         'view_item'          => __('View LXP Teacher', 'tinylms'),
         'add_new_item'       => __('Add New LXP Teacher', 'tinylms'),
         'add_new'            => __('Add New', 'tinylms'),
         'edit_item'          => __('Edit LXP Teacher', 'tinylms'),
         'update_item'        => __('Update LXP Teacher', 'tinylms'),
         'search_items'       => __('Search LXP Teacher', 'tinylms'),
         'not_found'          => sprintf(__('You haven\'t had any Teacher yet. Click <a href="%s">Add new</a> to start', 'tinylms'), admin_url('post-new.php?post_type=tl_teacher')),
         'not_found_in_trash' => __('No LXP Teacher found in Trash', 'tinylms'),
      );

      $args = array(
         'labels'             => $labels,
         'public'             => true,
         'query_var'          => true,
         'publicly_queryable' => true,
         'show_ui'            => true,
         'has_archive'        => true,
         'show_in_menu'       => true,
         'show_in_admin_bar'  => true,
         'show_in_nav_menus'  => true,
         'rewrite'            => array(
            'slug'       => 'teacher',
            'with_front' => false
         ),
         'show_in_rest'       => true,
         'rest_base'          => 'tl_teacher',
         'supports' => array('title', 'editor', 'thumbnail'),
         // 'capabilities' => array(
         //     'edit_post' => 'edit_lxp_teacher',
         //     'publish_posts' => 'publish_lxp_teachers',
         //     'read_post' => 'read_lxp_teacher',
         //     'read_private_posts' => 'read_private_lxp_teachers',
         //     'delete_post' => 'delete_lxp_teacher',
         //     'delete_posts' => 'delete_lxp_teachers',
         //     'create_posts' => 'create_lxp_teachers',
         //     'create_post' => 'create_lxp_teacher'
         // )
      );
      add_theme_support('post-thumbnails');
      $this->logout_inactive_user();
      return $args;
   }

   // logout teacher user
   public function logout_inactive_user()
   {
      // get current logged in user and check if it is 'lxp_teacher' role
      $user = wp_get_current_user();
      if (in_array('lxp_teacher', (array) $user->roles) ) {
         // get post type 'tl_teacher' by 'lxp_teacher_admin_id' post metadata which is equal to current user id
         $args = array(
            'post_type' => 'tl_teacher',
            'meta_key' => 'lxp_teacher_admin_id',
            'meta_value' => $user->ID,
            'number' => -1
         );
         $teachers = get_posts($args);
         $teacher = count($teachers) > 0 ? $teachers[0] : null;
         // is $teacher 'settings_active' metadata is not equal to 'false'.
         $isInActive = get_post_meta($teacher->ID, 'settings_active', true) === 'false';

         if ($isInActive) {
            // logout user
            wp_logout();
            // redirect to home page
            wp_redirect(home_url());
            exit;
         }
      }
   }
   

   public function add_meta_boxes()
   {
      $this->add_meta_box([
         'teacher-school-id',      // Unique ID
         esc_html__('Teacher School ', 'teacher'),    // Title
         array(self::instance(), 'lxp_teacher_school_metabox_html'),   // Callback function
         $this->_post_type,         // Admin page (or post type)
         'advanced',         // Context
         'default',         // Priority
         'show_in_rest' => true,
      ]);

      $this->add_meta_box([
         'teacher-admin-id',      // Unique ID
         esc_html__('Teacher Admin ', 'teacher'),    // Title
         array(self::instance(), 'lxp_teacher_admin_metabox_html'),   // Callback function
         $this->_post_type,         // Admin page (or post type)
         'advanced',         // Context
         'default',         // Priority
         'show_in_rest' => true,
      ]);

      $this->add_meta_box([
         'teacher-users-id',      // Unique ID
         esc_html__('Teacher Users ', 'teacher'),    // Title
         array(self::instance(), 'lxp_teacher_users_metabox_html'),   // Callback function
         $this->_post_type,         // Admin page (or post type)
         'advanced',         // Context
         'default',         // Priority
         'show_in_rest' => true,
      ]);
   }

   function post_meta_request_params($args, $request)
   {
      $args += array(
         'meta_key'   => $request['meta_key'],
         'meta_value' => $request['meta_value'],
         'meta_query' => $request['meta_query'],
      );
      return $args;
   }

   public function lxp_teacher_school_metabox_html($post = null)
   {
      $args = array(
         'post_type' => 'tl_school',
         'orderby'    => 'ID',
         'post_status' => 'publish',
         'order'    => 'DESC',
         'posts_per_page' => -1
      );
      $schools = get_posts($args);
      $selectedSchool =  get_post_meta($post->ID, 'lxp_teacher_school_id', true);
      $output = '  <h4>Select School</h4>';

      $output .= '<select name="lxp_teacher_school_id" id="course_select_options" style="margin-top:-10px"> ';
      $output .= '<option value="0">Select School</option>';
      foreach ($schools as $school) {
         if ($selectedSchool == $school->ID) {
            $selected = "selected";
         } else {
            $selected = "";
         }
         $output .= '<option value="' . $school->ID . '" ' . $selected . ' >' . $school->post_title . ' </option>';
      }
      $output .= '</select>';
      echo $output;
   }

   public function lxp_teacher_admin_metabox_html($post = null)
   {
      $args = array(
         'role' => 'lxp_teacher_admin',
         'order' => 'ASC'
      );
      $teacherAdmins = get_users($args);
      
      $users = get_users(
         array(
            'role' => 'lxp_teacher_admin',
            'meta_key' => 'lxp_teacher_admin_id',
            'meta_value' => $post->ID,
            'number' => -1
         )
      );

      $selectedAdmin = isset($users[0]) ? $users[0]->ID : "";
      $output = '<h4>Select Teacher Admin</h4>';

      $output .= '<select name="lxp_teacher_admin_id"  style="margin-top:-10px"> ';
      $output .= '<option value="0">Select An Admin</option>';
      foreach ($teacherAdmins as $admin) {
         if ($selectedAdmin == $admin->ID) {
            $selected = "selected";
         } else {
            $selected = "";
         }
         $output .= '<option value="' . $admin->ID . '" ' . $selected . ' >' . $admin->display_name . ' </option>';
      }
      $output .= '</select>';
      echo $output;
   }

   public function lxp_teacher_users_metabox_html($post = null)
   {
      $users = get_users(
         array(
            'meta_key' => 'lxp_teacher_id',
            'meta_value' => $post->ID,
            'number' => -1
         )
      );

      $html = '<p class="description">';
      $html .= 'Bulk Upload Users.';
      $html .= '</p>';
      $html .= '<input type="file" id="teacher_users" name="teacher_users" value=""  />';
      echo $html;

      foreach ($users as $user) {
         echo '<p>  '.$user->display_name .' ('. $user->roles[0] .')
          &nbsp;
          <span class="dashicons dashicons-trash" id="teacher_remove_lxp_user" lxp_user_id="' . $user->ID . '"></span>
          </p>';
      }
   }


   public function update_edit_form()
   {
      echo ' enctype="multipart/form-data"';
   }

   public function save_tl_post($post_id = null)
   {
      /*
      if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_type']) && 'tl_teacher' == $_POST['post_type']) {
         if ($_POST['lxp_teacher_school_id'] != 0) {
            update_post_meta($post_id, 'lxp_teacher_school_id', $_POST['lxp_teacher_school_id']);
         } else {
            update_post_meta($post_id, 'lxp_teacher_school_id', '');
         }

         $users = get_users(
            array(
               'role' => 'lxp_teacher_admin',
               'meta_key' => 'lxp_teacher_admin_id',
               'meta_value' => $post_id,
               'number' => -1
            )
         );
    
         if(isset($users[0]->ID)){
            delete_user_meta($users[0]->ID, 'lxp_teacher_admin_id');
         }
         if ($_POST['lxp_teacher_admin_id'] != 0) {
            update_user_meta($_POST['lxp_teacher_admin_id'], 'lxp_teacher_admin_id', $post_id);
         }

         $filename = $_FILES["teacher_users"]["tmp_name"];
         if ($_FILES["teacher_users"]["size"] > 0) {
            $file = fopen($filename, "r");
            $i = 0;
            while (($getData = fgetcsv($file, 10000, ",")) !== FALSE) {
               if ($i == 0) {
                  if( strtolower(trim($getData[0]))  != "user name" ||strtolower(trim($getData[1]))  != "first name" || strtolower(trim($getData[2]))  != "last name" || strtolower(trim($getData[3]))  != "email" || strtolower(trim($getData[4]))  != "password" || strtolower(trim($getData[5])) != "type" ){
                     break;
                  }
                  $i++;
                  continue;
               }
               if ($getData[5] == "Teacher") {
                  $role = "lxp_teacher";
               } else {
                  $role = "lxp_student";
               }
               $user_data = array(
                  'user_login' => $getData[0],
                  'first_name' => $getData[1],
                  'last_name' => $getData[2],
                  'user_email' => $getData[3],
                  'display_name' => $getData[1] . ' ' . $getData[2],
                  'user_pass' => $getData[4],
                  'role' => $role
               );
               $user_id  = wp_insert_user($user_data);
               update_user_meta($user_id, 'lxp_teacher_id', $post_id);
            }

            fclose($file);
         }
      }
      */
   }


   public function insert_post_api($post, $request)
   {
   }

   function modify_list_row_actions($actions, $post)
   {
      return $actions;
   }

   public  function grade_view()
   {
   }

   public  function grade_book_view()
   {
   }

   public function register_views()
   {
   }
}
