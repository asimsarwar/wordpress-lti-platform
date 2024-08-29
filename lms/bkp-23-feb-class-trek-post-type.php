<?php

/**
 * Class TL_TREK_Post_Type
 * 
 * @author Waqar Muneer
 * @version 1.0
 */

class TL_TREK_Post_Type extends TL_Post_Type
{
   /**
    * @var null
    */
   protected static $_instance = null;

   /**
    * @var string
    */
   protected $_post_type = TL_TREK_CPT;

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

      $located = locate_template( 'single-tl_trek.php' );
      if(empty($located)){
         add_filter( 'single_template', function ( $page_template, $type ) {
            global $post;
            if ( $post->post_type == TL_TREK_CPT ) {
                  $page_template = dirname( __FILE__ ) . '/templates/trek/single-tl_trek.php';
            }
            return $page_template;
         },20, 2);
      }

      global $wpdb;
      $wpdb->query("CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "trek_sections(
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			trek_id bigint(20) default NULL,
            title varchar(255) default NULL,
            type varchar(255) default NULL,
            content longtext default NULL,
            link varchar(255) default NULL,
			PRIMARY KEY (id)
		)");

      $labels           =     array(
         'name'               => _x('LXP TREK', 'Post Type General Name', 'tinylms'),
         'singular_name'      => _x('LXP TREK', 'Post Type Singular Name', 'tinylms'),
         'menu_name'          => __('LXP TREKs', 'tinylms'),
         'parent_item_colon'  => __('Parent Item:', 'tinylms'),
         'all_items'          => __('LXP TREKs', 'tinylms'),
         'view_item'          => __('View LXP TREK', 'tinylms'),
         'add_new_item'       => __('Add New LXP TREK', 'tinylms'),
         'add_new'            => __('Add New', 'tinylms'),
         'edit_item'          => __('Edit LXP TREK', 'tinylms'),
         'update_item'        => __('Update LXP TREK', 'tinylms'),
         'search_items'       => __('Search LXP TREK', 'tinylms'),
         'not_found'          => sprintf(__('You haven\'t had any TREK yet. Click <a href="%s">Add new</a> to start', 'tinylms'), admin_url('post-new.php?post_type=tl_trek')),
         'not_found_in_trash' => __('No LXP TREK found in Trash', 'tinylms'),
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
            'slug'       => 'trek',
            'with_front' => false
         ),
         'show_in_rest'       => false,
         'rest_base'          => 'tl_trek',
         'supports' => array('title', 'editor', 'author', 'thumbnail')
      );
      return $args;
   }


   public function add_meta_boxes()
   {
      $this->add_meta_box([
         'trek-course-options-class',      // Unique ID
         esc_html__('Course ', 'course'),    // Title
         array(self::instance(), 'options_metabox_html'),   // Callback function
         $this->_post_type,         // Admin page (or post type)
         'side',         // Context
         'default',         // Priority
         'show_in_rest' => true,
      ]);

      $this->add_meta_box([
         'trek-sections-class',      // Unique ID
         esc_html__('Manage Teacher Instruction section', ',manage_teacher_instruction_section'),    // Title
         array(self::instance(), 'trek_sections_metabox_html'),   // Callback function
         $this->_post_type,         // Admin page (or post type)
         'side',         // Context
         'default',         // Priority
         'show_in_rest' => true,
      ]);


      $this->add_meta_box([
         'trek-options-class',      // Unique ID
         esc_html__('TREK Sections', 'trek-Sections'),    // Title
         array(self::instance(), 'trek_options_metabox_html'),   // Callback function
         $this->_post_type,         // Admin page (or post type)
         'side',         // Context
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

   public function options_metabox_html($post = null)
   {
      $args = array(
         'post_type' => 'tl_course',
         'orderby'    => 'ID',
         'post_status' => 'publish,draft',
         'order'    => 'DESC',
         'posts_per_page' => -1
      );
      $courses = get_posts($args);
      $selectedCourse =  isset($_GET['courseid']) ? $_GET['courseid'] : get_post_meta($post->ID, 'tl_course_id', true);
      $output = '  <h4>Select Course</h4>';
     
     $output .= '<select name="tl_course_id" id="course_select_options" style="margin-top:-10px"> ';
      foreach ($courses as $course) {
         if ($selectedCourse == $course->ID) {
            $selected = "selected";
         } else {
            $selected = "";
         }
         $output .= '<option value="' . $course->ID . '" ' . $selected . ' >' . $course->post_title . ' </option>';
      }
      $output .= '</select>';
      echo $output;
      ?>
  
       <?php
      }

      public function trek_sections_metabox_html($post = null)
      {

         global $wpdb;

         $output = '  <h3 id="section-title"> Add new Teacher Instruction section </h3>';

         $query = "SELECT * FROM " . $wpdb->prefix . "trek_sections WHERE trek_id=" . $post->ID;

         $output .= '<div id="appendme" class="container" >';

         $playlists =  json_decode(get_post_meta(get_post_meta($post->ID, 'tl_course_id', true), "lxp_sections", true));

         $options =  $wpdb->get_results($query);

         foreach ($options as $key => $value) {
            $selectOption = '';
            $selectOption .= "<select name='title[]' class='option-title-input' > ";
       
             foreach ($playlists as $playlist) {
                if ($playlist == $value->title) {
                   $selected = "selected";
                } else {
                   $selected = "";
                }
                $selectOption .= '<option value="' . $playlist . '" ' . $selected . ' >' .  $playlist. ' </option>';
             }
             $selectOption .= '</select>';
            if ($value->type == "content") {
               $append =   "<b>Content</b> <textarea  class='ckeditor'  rows='12' cols='50' name='option_content[]' /> " . stripslashes($value->content) . "  </textarea> ";
            } else {
               $append = "<b>Link</b> </br><input type='text'  value='" . $value->link . "'  name='option_content[]' style='width:50%'/> ";
            }
            $output .=  "<div class='row option-body' style='display:none' id='option-body-" . $key . "'> <b>Section</b> <br>". $selectOption."<button type='button' class='button button-primary btnSave'>Save</button>  <button type='button' class='button button-danger btnRemove'>Remove</button> <br>" . $append . "  <input type='hidden' name='option-type[]' value='" . $value->type . "'><br><hr> </div>";
         }

         $output .=  '</div>';

         echo $output;
         ?>
  
       <?php
      }

      public function trek_options_metabox_html($post = null)
      {
         $screen = isset($_GET['action']) && $_GET['action'] === "edit" ? "edit" : "add";
         $removeBtn = $screen === "add" ? '<span class="chip-close">&times;</span>' : '';
         
         global $wpdb;
         $query = "SELECT * FROM " . $wpdb->prefix . "trek_sections WHERE trek_id=" . $post->ID;
         $options =  $wpdb->get_results($query);
         $output = "<div id='option-chips' tota-chips=" . count($options) . ">";
         if (count($options) == 0) {
            $output .= "<div id='chips-alternate'>No section avialable. Create one using form given below.</div>";
         }
         foreach ($options as $key => $value) {
            $output .=   '<div class="chip" identifier="' . $key . '" option-body-id="option-body-' . $key . '" > <span id="chip-title-' . $key . '"> ' . $value->title . ' </span> <span class="dashicons dashicons-edit edit-trek-options"></span> ' . $removeBtn . ' </div>';
         }
         
         echo $output;
         ?>
  
       <?php
      }

      public function save_tl_post($post_id = null)
      {
         if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_type']) && 'tl_trek' == $_POST['post_type']) {
            global $wpdb;
            if ($_POST['tl_course_id'] != get_post_meta($post_id, 'tl_course_id', true)) {
               update_post_meta($post_id, 'lti_course_id', "");
            }
            update_post_meta($post_id, 'tl_course_id', $_POST['tl_course_id']);
            $wpdb->query("DELETE FROM " . $wpdb->prefix . "trek_sections WHERE trek_id =" . $post_id);
            foreach ($_POST["title"] as $key => $value) {
               if ($_POST["option-type"][$key] == "content") {
                  if(!empty($_POST["option_content"][$key])){
                     $wpdb->insert($wpdb->prefix . 'trek_sections', array(
                        'trek_id' => $post_id,
                        'title' => $value,
                        'type' => $_POST["option-type"][$key],
                        'content' => $_POST["option_content"][$key],
                     ));
                  }
               } else {
                  $wpdb->insert($wpdb->prefix . 'trek_sections', array(
                     'trek_id' => $post_id,
                     'title' => $value,
                     'type' => $_POST["option-type"][$key],
                     'link' => $_POST["option_content"][$key],
                  ));
               }
            }
         }
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
