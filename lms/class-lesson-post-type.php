<?php

/**
 * Class TL_Lesson_Post_Type
 * 
 * @author Waqar Muneer
 * @version 1.0
 */

 class TL_Lesson_Post_Type extends TL_Post_Type {
    /**
    * @var null
    */
   protected static $_instance = null;

   /**
    * @var string
    */
   protected $_post_type = TL_LESSON_CPT;

   /**
    * Get Instance
    */
   public static function instance() {
      if (is_null(self::$_instance)) {
         self::$_instance = new self();
      }

      return self::$_instance;
   }

   /**
    * Constructor
    */
   public function __construct() {
      parent::__construct();
   }

   /**
    * Register lesson post type.
    */
   public function args_register_post_type() : array {
      $labels           = array(
         'name'               => _x( 'Lessons', 'Post Type General Name', 'tinylms' ),
         'singular_name'      => _x( 'Lesson', 'Post Type Singular Name', 'tinylms' ),
         'menu_name'          => __( 'Lessons', 'tinylms' ),
         'parent_item_colon'  => __( 'Parent Item:', 'tinylms' ),
         'all_items'          => __( 'Lessons', 'tinylms' ),
         'view_item'          => __( 'View Lesson', 'tinylms' ),
         'add_new_item'       => __( 'Add New Lesson', 'tinylms' ),
         'add_new'            => __( 'Add New', 'tinylms' ),
         'edit_item'          => __( 'Edit Lesson', 'tinylms' ),
         'update_item'        => __( 'Update Lesson', 'tinylms' ),
         'search_items'       => __( 'Search Lessons', 'tinylms' ),
         'not_found'          => sprintf( __( 'You haven\'t had any lessons yet. Click <a href="%s">Add new</a> to start', 'tinylms' ), admin_url( 'post-new.php?post_type=tl_lesson' ) ),
         'not_found_in_trash' => __( 'No lesson found in Trash', 'tinylms' ),
      );
      
      $args = array(
         'labels'             => $labels,
         'public'             => true,
         'query_var'          => true,
         'publicly_queryable' => true,
         'show_ui'            => true,
         'has_archive'        => true,
         'show_in_menu'       => 'tiny_lms',
         'show_in_admin_bar'  => true,
         'show_in_nav_menus'  => true,
         'rewrite'            => array(
            'slug'       => 'tl/lessons',
            'with_front' => false
         )
      );

      return $args;
   }

   public function add_meta_boxes() {
      $this->options_metabox();
   }

   public function options_metabox() {
      $this->add_meta_box([
         'lesson-options-class',      // Unique ID
         esc_html__('Lesson Options', 'lesson-options'),    // Title
         array(self::instance(), 'options_metabox_html'),   // Callback function
         $this->_post_type,       // Admin page (or post type)
         'side',         // Context
         'default'         // Priority
      ]);
   }

   public function options_metabox_html($post = null)
   {
      $args = array(
         'post_type'=> 'tl_course',
         'orderby'    => 'ID',
         'post_status' => 'publish,draft',
         'order'    => 'DESC',
         'posts_per_page' => -1 
         );
      $courses = get_posts( $args );
      $selectedCourse =  isset($_GET['courseid']) ? $_GET['courseid'] : get_post_meta($post->ID, 'tl_course_id', true);
      $output = '  <h4>Select Course</h4>';
      $output .= '<select name="tl_course_id" style="margin-top:-10px"> 
               <option disabled selected>Select a course</option>';
      foreach($courses as $course){
         if($selectedCourse == $course->ID){
            $selected = "selected";
           }else{
            $selected = "";
           }
            $output .= '<option value="'.$course->ID .'" '.$selected.' >'. $course->post_title .' </option>';
      }
      $output .= '</select>';
      echo $output ;
      ?>
      <h4 >LTI Deep Linking</h4>
      <div style="width: 100%;margin-top:-10px">
         <input type="text" required id="lti_tool_url" name="lti_tool_url" value="<?php echo get_post_meta($post->ID, 'lti_tool_url', true)?>" style="width: 100%;" />
         <input type="hidden" id="lti_tool_code" name="lti_tool_code" value="<?php echo get_post_meta($post->ID, 'lti_tool_code', true) ?>" style="width: 100%;" />
         <input type="hidden" id="lti_content_title" name="lti_content_title" value="<?php echo get_post_meta($post->ID, 'lti_content_title', true) ?>" style="width: 100%;" />
         <input type="hidden" id="lti_custom_attr" name="lti_custom_attr" value="<?php echo get_post_meta($post->ID, 'lti_custom_attr', true) ?>" style="width: 100%;" />
         <input type="hidden" id="lti_post_attr_id" name="lti_post_attr_id" value="<?php echo get_post_meta($post->ID, 'lti_post_attr_id', true) ?>" style="width: 100%;" />
      </div>
      <div id="preview_lit_connections" style="width: 100%;display: inline-block;margin-top: 10px;">
         <div class="preview button" href="#">Select Content<span class="screen-reader-text"> (opens in a new tab)</span></div>
      </div>
      <?php
   }

   public function save_tl_post($post_id = null)
   {
       if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_type']) && 'tl_lesson' == $_POST['post_type']) {
               update_post_meta($post_id, 'lti_tool_url',$_POST['lti_tool_url']);
               update_post_meta($post_id, 'lti_tool_code', $_POST['lti_tool_code']);
               update_post_meta($post_id, 'lti_content_title', $_POST['lti_content_title']);
               update_post_meta($post_id, 'lti_custom_attr', $_POST['lti_custom_attr']);
               update_post_meta($post_id, 'lti_post_attr_id', $_POST['lti_post_attr_id']);
               update_post_meta($post_id, 'tl_course_id', $_POST['tl_course_id']);
       }
   }

   public function tl_post_content($more_link_text = null, $strip_teaser = false)
   {
       $post = get_post();
       if (isset($post->post_type) && $post->post_type == "tl_lesson") {
           $content = get_post_meta($post->ID);
           $attrId =  isset($content['lti_post_attr_id'][0]) ? $content['lti_post_attr_id'][0] : "";
           $title =  isset($content['lti_content_title'][0]) ? $content['lti_content_title'][0] : "";
           $toolCode =  isset($content['lti_tool_code'][0]) ?$content['lti_tool_code'][0] : "";
           $customAttr =  isset($content['lti_custom_attr'][0]) ? $content['lti_custom_attr'][0] : "";
           $toolUrl =  isset($content['lti_tool_url'][0]) ? $content['lti_tool_url'][0] : "";
           $plugin_name = LTI_Platform::get_plugin_name();
           $content = '<p>' . $post->post_content . '</p>';
           $content.= '<p> [' . $plugin_name . ' tool=' . $toolCode . ' id=' . $attrId . ' title=\"' . $title . '\" url=' . $toolUrl . ' custom=' . $customAttr . ']' . "". '[/' . $plugin_name . ']  </p>';
       } else {
           $content = get_the_content($more_link_text, $strip_teaser);
           return  $content;
       }
       return $content;
   }

 }