var LtiPlatformText = '';
var LtiPlatformProps = null;

(function (wp) {
  var LtiPlatformIcon = wp.element.createElement(wp.primitives.SVG, {
    xmlns: "http://www.w3.org/2000/svg",
    viewBox: "0 0 24 24"
  }, wp.element.createElement(wp.primitives.Path, {
    d: "M6 14H4V6h2V4H2v12h4M7.1 17h2.1l3.7-14h-2.1M14 4v2h2v8h-2v2h4V4"
  }));

  var LtiPlatformButton = function (props) {
    return wp.element.createElement(
      wp.blockEditor.RichTextToolbarButton, {
      icon: LtiPlatformIcon,
      title: 'LTI tool',
      onClick: function () {
        if (typeof props.value.start === 'undefined') {
          props.value.start = props.value.text.length;
          props.value.end = props.value.text.length;
        }
        LtiPlatformText = '';
        if (props.value.end > props.value.start) {
          LtiPlatformText = props.value.text.substr(props.value.start, props.value.end - props.value.start);
        }
        LtiPlatformProps = props;
        jQuery('.lti-platform-modal').addClass("active");
      },
    }
    );
  }
  wp.richText.registerFormatType(
    'lti-platform-format/insert-tool', {
    title: 'LTI tool',
    tagName: 'ltiplatformtool',
    className: null,
    edit: LtiPlatformButton,
  }
  );
})(window.wp);

(function ($) {
  $(document).ready(function () {
    var totalChips = $("#option-chips").attr('tota-chips');
    function deeplink() {
      var urlParams = new URLSearchParams(window.location.search);
      window.open('../?lti-platform&deeplink&post=' + encodeURIComponent(urlParams.get('post')) + '&tool=' + encodeURIComponent($("input[name='tool']:checked").val()), '_blank', 'width=1000,height=800');
      $('.lti-platform-modal').removeClass('active');
    }
    $.get('../?lti-platform&tools', function (response) {
      $('#wpwrap').append(response);
      $('.lti-platform-tool').on('change', function () {
        $('#lti-platform-select').prop('disabled', false);
      });
      $('#lti-platform-select').on('click', function () {
        $.get('../?lti-platform&usecontentitem&tool=' + encodeURIComponent($("input[name='tool']:checked").val()), function (response) {
          if (response.useContentItem) {
            deeplink();
          } else {
            if (!window.LtiPlatformText) {
              window.LtiPlatformText = $('input[name="tool"]:checked').attr('toolname');
            }
            if ($("#preview_lit_connections").length) {
              deeplink();
            }
            var id = Math.random().toString(16).substr(2, 8);
            window.LtiPlatformProps.onChange(window.wp.richText.insert(window.LtiPlatformProps.value, '[lti-platform tool=' + $("input[name='tool']:checked").val() + ' id=' + id + ']' + window.LtiPlatformText + '[/lti-platform]'));
            window.LtiPlatformProps.onFocus();
            $('.lti-platform-modal').removeClass('active');
          }
        });
      });
      $('#lti-platform-cancel').on('click', function () {
        $('.lti-platform-modal').removeClass('active');
        $('#postdivrich').addClass('wp-editor-expand');
      });

      $('#preview_lit_connections').on('click', function () {
        jQuery('.lti-platform-modal').addClass("active");
        $('#postdivrich').removeClass('wp-editor-expand');
      });

    $(".tool-input-tr").on('click', function () {
        $(this).find('td input[type=radio]').prop('checked', true);
        $('#lti-platform-select').prop("disabled", false);
      });
      var i = 1;

      $(".add-trax-options").on('click', function () {
        i++;
        $('#section-title').text("Add Option");
     
        var optionType = $('#trek-option-type').find(":selected").val();
        if (optionType == "content") {

          $("#appendme").append($("<div class='row  option-body'  id='option-body-" + totalChips + "'>  <b>Section</b> <br> <select name='title[]' class='option-title-input' >  </select> <button type='button' class='button button-primary btnSave'>Save</button>  <button type='button' class='button button-danger btnRemove'>Remove</button> <br>  <b>Content</b> <textarea id='ckeditor-id-" + i + "'   name='option_content[]'  class='ckeditor'   rows='12' cols='50' >  </textarea><input type='hidden' name='option-type[]' value='content'></br><hr> </div> "));
          
          CKEDITOR.replace('ckeditor-id-' + i);
          
        } else {
   
          $("#appendme").append($("<div class='row option-body' id='option-body-" + totalChips + "'>  <b> Section </b> <br> <select name='title[]' class='option-title-input' >  </select>   <button type='button' class='button button-primary btnSave'>Save</button>  <button type='button' class='button button-danger btnRemove'>Remove</button> <br><b>Link</b><br> <input type='text'   name='option_content[]'  style='width:50%'  />   <input type='hidden' name='option-type[]' value='action'></br><hr> </div>"));
        }
      });
      function addContentForm() {
        i++;
        $('#section-title').text("Add new Teacher Instruction section");
        var options = '';
        var courseId = $('#course_select_options').find(":selected").val();
        var host = window.location.origin + '/wp-json/lms/v1/get/playlists';
        jQuery.ajax({
          type: "get",
          dataType: "json",
          url: host,
          data: { course_id: courseId },
          success: function (response) {
            for (var j = 0, len = response.length; j < len; ++j) {
              options += '<option value="' + response[j] + '">' + response[j] + '</option>';
            }
            $("#appendme").append($("<div class='row  option-body'  id='option-body-" + totalChips + "'>  <b>Section</b> <br> <select name='title[]' class='option-title-input' > " + options + "  </select> <button type='button' class='button button-primary btnSave'>Save</button>  <button type='button' class='button button-danger btnRemove'>Remove</button> <br>  <b>Content</b> <textarea id='ckeditor-id-" + i + "'   name='option_content[]'  class='ckeditor'   rows='12' cols='50' >  </textarea><input type='hidden' name='option-type[]' value='content'></br><hr> </div> "));
            CKEDITOR.replace('ckeditor-id-' + i);
          }
         
        });
      }

      $('body').on('click', '.btnRemove', function () {
        $('#section-title').text("");
        var option_body_id = $(this).parent('div.row').attr('id');
        $("[option-body-id=" + option_body_id + "]").remove();
        $(this).parent('div.row').remove();
        addContentForm();
      });

      $('body').on('click', '.btnSave', function () {
        $('#section-title').text("");
        $(this).parent('div.row').hide();
        var title = $(this).siblings('.option-title-input').val();
        var edit = $(this).parent('div').attr('option-edit');
        var option_body_id = $(this).parent('div.row').attr('id');
        var chip_titile_id = $("[option-body-id=" + option_body_id + "]").attr('identifier');
        $('#chips-alternate').text("");
        if (edit == "true") {
          $('#chip-title-' + chip_titile_id).text(title);
        } else {
          $("#option-chips").append('<div class="chip" identifier="' + totalChips + '" option-body-id="option-body-' + totalChips + '">  <span id="chip-title-' + totalChips + '"> ' + title + ' </span>  <span class="dashicons dashicons-edit edit-trek-options"></span> <span class="chip-close">&times;</span> </div>');
        }
        totalChips++;
        addContentForm();
      });

      $(".course_remove_lesson").on('click', function () {
        var deletedLessons = $("#course_removed_lessons").val();
        $(this).parent().fadeOut();
        if (deletedLessons) {
          $("#course_removed_lessons").val(deletedLessons + "," + $(this).attr('lesson_id'));
        } else {
          $("#course_removed_lessons").val($(this).attr('lesson_id'));
        }
      });
      var selectedCourse=$('#course_select_options').val();
      $("#course_select_options").on('change', function () {
        if ($('.option-body').length != 0) {
          if (confirm("All exisitng options will be removed. Are you sure you want to continue?") == true) {
            $('.chip').remove(); 
            $('.option-body').remove(); 
            selectedCourse=$(this).val(); 
            addContentForm();
         }else{
          $(this).val(selectedCourse); 
          
         }
        } 
      });

      $('body').on('click', '.edit-trek-options', function () {
        $('#section-title').text("Edit Teacher Instruction section");
        $(".option-body").each(function (i, obj) {
          obj.style.display = "none";
        });
        var option_body_id = $(this).parent('div').attr('option-body-id');
        console.log('#' + option_body_id);
        $('#' + option_body_id).attr("option-edit", "true");
        $('#' + option_body_id).show();
      });

      $('body').on('click', '.chip-close', function () {
        if (confirm("Are you sure you want to remove?") == true) {
          var option_body_id = $(this).parent('div').attr('option-body-id');
          $('#' + option_body_id).remove();
          $(this).parent('div').remove();
          addContentForm();
        }
      });
      addContentForm();
    });
  });
})(jQuery);
