var LtiPlatformText = '';
var LtiPlatformProps = null;
var currentSectionState = "create";
var currentsectionId = 0;

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

      $(".course_remove_lesson").on('click', function () {
        var deletedLessons = $("#course_removed_lessons").val();
        $(this).parent().fadeOut();
        if (deletedLessons) {
          $("#course_removed_lessons").val(deletedLessons + "," + $(this).attr('lesson_id'));
        } else {
          $("#course_removed_lessons").val($(this).attr('lesson_id'));
        }
      });

      $('body').on('click', '.chip-close', function () {
        if (confirm("Are you sure you want to remove?") == true) {
          var sectionId = $(this).parent('div').attr('identifier');
          var host = window.location.origin + '/wp-json/lms/v1/delete/trek/section';
          jQuery.ajax({
            type: "post",
            dataType: "json",
            url: host,
            data: { section_id: sectionId },
            success: function (response) {
              if (window.currentSectionState == "edit" && window.currentsectionId == sectionId) {
                var url = window.location.href;
                if (url.indexOf("&action=edit") >= 0) {
                  $('#playlist-select-area').css("display","inline");
                }
                $('#section-title').text("Add New Section");
                $('#btnSaveSection').text("Create");
                CKEDITOR.instances['ck-editor-id'].setData('');
                $('#option-title-select-box').val('');
                window.currentSectionState = "create";
                window.currentsectionId = 0;
              }
              $("[identifier=" + sectionId + "]").remove();
              appendCoursePlaylistSelectOptions();
            }
          });
        }
      });

      appendCoursePlaylistSelectOptions();
      function appendCoursePlaylistSelectOptions(selctedOption = null) {
        var options = '';
        var courseId = $('#course_select_options').find(":selected").val();
        var postID = $('#post_ID').val();
        var host = window.location.origin + '/wp-json/lms/v1/get/playlists';
        jQuery.ajax({
          type: "get",
          dataType: "json",
          url: host,
          data: { course_id: courseId, post_id: postID },
          success: function (response) {
            if (response.length == 0 && selctedOption == null) {
              options += '<option> No Section Available </option>';
            }else{
              options += '<option>---Select Section---</option>';
            }
            for (var j = 0, len = response.length; j < len; ++j) {
              options += '<option value="' + response[j] + '">' + response[j] + '</option>';
            }
            if (selctedOption != null) {
              options += selctedOption;
            }

            $("#option-title-select-box").html(options);
          }
        });
      }

      $('body').on('click', '#btnSaveSection', function () {

        var title = $('#option-title-select-box').val();

        if (title.indexOf("No Section Available") >= 0 || title.indexOf("---Select Section---") >= 0) {
          alert("No section selected");
          return;
        }
        var content = CKEDITOR.instances['ck-editor-id'].getData();
        var postID = $('#post_ID').val();
        var host = window.location.origin + '/wp-json/lms/v1/store/trek/section';
        $("[identifier=" + window.currentsectionId + "]").find('.edit-trek-options').removeClass("active-edit-trek-option");
        $("[identifier=" + window.currentsectionId + "]").find('.chip-close').removeClass("active-chip-close");
        $("[identifier=" + window.currentsectionId + "]").removeClass("edit-playlist-chip");
        $("[identifier=" + window.currentsectionId + "]").find('.edit-trek-options').css("visibility","visible");
        $("[identifier=" + window.currentsectionId + "]").find('.chip-close').css("visibility","visible");
        jQuery.ajax({
          type: "post",
          dataType: "json",
          url: host,
          data: { title: title, content: content, post_id: postID, section_id: window.currentsectionId },
          success: function (recordId) {
            if(recordId == 0){
              alert('Please enter post "Title" and "Description" first.');
            }else{
              appendCoursePlaylistSelectOptions();
              if (window.currentSectionState == "edit") {
                $('#chip-title-' + window.currentsectionId).text();
              } else {
                $("#option-chips").append('<div class="playlist-chip" identifier="' + recordId + '">  <span id="chip-title-' + recordId + '"> ' + title + ' </span>  <span class="edit-trek-options"><span style="margin-top:5px" class="dashicons dashicons-edit"></span> </span> <span type="button" class="chip-close"><span style="margin-top:5px" class="dashicons dashicons-no"></span> </span> </div>');
              }
              $('#playlist-select-area').css("display","inline");
              window.currentsectionId = 0;
              window.currentSectionState = "create";
              CKEDITOR.instances['ck-editor-id'].setData('');
              $('#option-title-select-box').val('');
              $('#section-title').text("Add New Section");
              $('#btnSaveSection').text("Create");
              $('#chips-alternate').text("");
              $('#btnCancelUpdate').css("display","none");
            }
          }
        });
      });
      $('body').on('click', '#btnCancelUpdate', function () {
        appendCoursePlaylistSelectOptions();
        $("[identifier=" + window.currentsectionId + "]").find('.edit-trek-options').removeClass("active-edit-trek-option");
        $("[identifier=" + window.currentsectionId + "]").find('.chip-close').removeClass("active-chip-close");
        $("[identifier=" + window.currentsectionId + "]").removeClass("edit-playlist-chip");
        $("[identifier=" + window.currentsectionId + "]").find('.edit-trek-options').css("visibility","visible");
        $("[identifier=" + window.currentsectionId + "]").find('.chip-close').css("visibility","visible");
        window.currentsectionId = 0;
        window.currentSectionState = "create";
        CKEDITOR.instances['ck-editor-id'].setData('');
        $('#option-title-select-box').val('');
        $('#section-title').text("Add New Section");
        $('#btnSaveSection').text("Create");
        $('#chips-alternate').text("");
        $('#btnCancelUpdate').css("display","none");
        $('#playlist-select-area').css("display","inline");

      });
      $('body').on('click', '.edit-trek-options', function () {
        var url = window.location.href;
        $('#playlist-select-area').css("display","none");
        window.currentSectionState = "edit";
        $('#btnSaveSection').text("Update");
        $('#btnCancelUpdate').css("display","inline-block");
        $("[identifier=" + window.currentsectionId + "]").find('.edit-trek-options').removeClass("active-edit-trek-option");
        $("[identifier=" + window.currentsectionId + "]").find('.chip-close').removeClass("active-chip-close");
        $("[identifier=" + window.currentsectionId + "]").removeClass("edit-playlist-chip");
        $("[identifier=" + window.currentsectionId + "]").find('.edit-trek-options').css("visibility","visible");
        $("[identifier=" + window.currentsectionId + "]").find('.chip-close').css("visibility","visible");
        var sectionId = $(this).parent('div').attr('identifier');
        // $("[identifier=" + sectionId + "]").find('.edit-trek-options').addClass("active-edit-trek-option");
        // $("[identifier=" + sectionId + "]").find('.chip-close').addClass("active-chip-close");
        $("[identifier=" + sectionId + "]").find('.edit-trek-options').css("visibility","hidden");
        $("[identifier=" + sectionId + "]").find('.chip-close').css("visibility","hidden");
        $("[identifier=" + sectionId + "]").addClass("edit-playlist-chip");
        window.currentsectionId = sectionId;
        var host = window.location.origin + '/wp-json/lms/v1/get/trek/section';
        jQuery.ajax({
          type: "get",
          dataType: "json",
          url: host,
          data: { section_id: sectionId },
          success: function (response) {
            $('#section-title').text("Edit \"" + response[0].title + "\" Section");
            CKEDITOR.instances['ck-editor-id'].setData(response[0].content);
            if ($("#option-title-select-box option[value='" + response[0].title + "']").length == 0) {
              option = '<option selected value="' + response[0].title + '">' + response[0].title + '</option>';
              appendCoursePlaylistSelectOptions(option);
            }
          }
        });
      });

      $("#course_select_options").on('change', function () {
        appendCoursePlaylistSelectOptions();
      });

    });
  });
})(jQuery);

