(function ($) {
  $(function () {
    var url = ajax_url.url + '?action=search_user'
    var a_url = admin_url.url
    $('#uncanny-at-plugin-search-input').autocomplete({
      source: url,
      delay: 500,
      minLength: 3,
      focus: function (event, ui) {
        $('#uncanny-at-plugin-search-input').val(ui.item.label)
        return false
      },
      select: function (event, ui) {
        location.href = a_url + '&user_id=' + ui.item.user_id
        return false
      }
    })

    $('#uncanny-at-plugin-search-input-topic').autocomplete({
      source: url,
      delay: 500,
      minLength: 3,
      focus: function (event, ui) {
        $('#uncanny-at-plugin-search-input-topic').val(ui.item.label)
        return false
      },
      select: function (event, ui) {
        location.href = a_url + '&user_id=' + ui.item.user_id + '&topics=yes' 
        return false
      }
    })
  })
})(jQuery)

jQuery(document).ready(function ($) {
  $('.datepicker').datepicker()

  /***************************************/
  /***************************************/
  /******* STUDENTS CERTIFICATIONS *******/
  /***************************************/
  /***************************************/

  $('.tftable input').change(function () {
    var field_val = $(this).val()
    var user_id = $('#user_id').val()
    var meta_key = $(this).attr('name')
    var url = ajax_url.url + '?action=update_certification'
    $.ajax({
      beforeSend: function () {
        // show gif here, eg:
        $('#save_msg').show().html('Saving...')
      },
      global: false,
      method: 'get',
      data: {user_id: user_id, meta_key: meta_key, meta_value: field_val},
      url: url
    }).done(function (msg) {
      /*$('html, body').animate({
        scrollTop: $("#tftable").offset().top - 300
      }, 1500);*/
      $('#save_msg').html(msg).show()
      var t = setTimeout(function () {
        $('#save_msg').fadeOut()
      }, 1500)
    })
  })

  $('.tftable select').change(function () {
    var field_val = $(this).val()
    var user_id = $('#user_id').val()
    var meta_key = $(this).attr('name')
    var umeta = $(this).attr('data-umeta')
    var url = ajax_url.url + '?action=update_certification'
    $.ajax({
      beforeSend: function () {
        // show gif here, eg:
        $('#save_msg').show().html('Saving...')
      },
      global: false,
      method: 'get',
      data: {user_id: user_id, meta_key: meta_key, meta_value: field_val, is_select: 'yes', umeta: umeta},
      url: url
    }).done(function (msg) {
      /*$('html, body').animate({
        scrollTop: $("#tftable").offset().top - 300
      }, 1500);*/
      $('#save_msg').html(msg).show()
      var t = setTimeout(function () {
        $('#save_msg').fadeOut()
      }, 1500)
    })
  })

  $('.tftable textarea').change(function () {
    var field_val = $(this).val()
    var user_id = $('#user_id').val()
    var meta_key = $(this).attr('name')
    var url = ajax_url.url + '?action=update_certification'
    $.ajax({
      beforeSend: function () {
        // show gif here, eg:
        $('#save_msg').show().html('Saving...')
      },
      global: false,
      method: 'get',
      data: {user_id: user_id, meta_key: meta_key, meta_value: field_val, is_textarea: 'yes'},
      url: url
    }).done(function (msg) {
      /*$('html, body').animate({
        scrollTop: $("#tftable").offset().top - 300
      }, 1500);*/
      $('#save_msg').html(msg).show()
      $(this).val('')
      var t = setTimeout(function () {
        $('#save_msg').fadeOut()
      }, 1500)
      var url = ajax_url.url + '?action=fetch_certification_notes'
      $.ajax({
        method: 'get',
        data: {user_id: user_id, meta_key: meta_key},
        url: url
      }).done(function (msg) {
        $('#' + meta_key + '_html').html(msg)
        $('#' + meta_key).val('')
      })
    })
  })

  /***************************************/
  /***************************************/
  /************ TALLY TABLE **************/
  /***************************************/
  /***************************************/

  $('.tftable_tally input, .tftable_tally select').change(function () {
    var field_val = $(this).val()
    var user_id = $('#user_id').val()
    var meta_key = $(this).attr('name')
    var url = ajax_url.url + '?action=update_tally'
    var certificate_id = $(this).attr('data-certificate')
    var term_id = $(this).attr('data-term')
    $.ajax({
      beforeSend: function () {
        // show gif here, eg:
        $('#save_msg_tally').show().html('Saving...')
      },
      global: false,
      method: 'get',
      data: {
        user_id: user_id,
        meta_key: meta_key,
        meta_value: field_val,
        certificate_id: certificate_id,
        term_id: term_id
      },
      url: url
    }).done(function (msg) {
      /*$('html, body').animate({
        scrollTop: $("#tftable_tally").offset().top - 300
      }, 1500);*/
      $('#save_msg_tally').html(msg).show()
      var t = setTimeout(function () {
        $('#save_msg_tally').fadeOut()
      }, 1500)
    })
  })
  $('.tftable_tally textarea').change(function () {
    var field_val = $(this).val()
    var user_id = $('#user_id').val()
    var meta_key = $(this).attr('name')
    var certificate_id = $(this).attr('data-certificate')
    var term_id = $(this).attr('data-term')
    var url = ajax_url.url + '?action=update_tally'
    $.ajax({
      beforeSend: function () {
        // show gif here, eg:
        $('#save_msg_tally').show().html('Saving...')
      },
      global: false,
      method: 'get',
      data: {
        user_id: user_id,
        meta_key: meta_key,
        meta_value: field_val,
        is_textarea: 'yes',
        certificate_id: certificate_id,
        term_id: term_id
      },
      url: url
    }).done(function (msg) {
      /*$('html, body').animate({
        scrollTop: $("#tftable_tally").offset().top - 300
      }, 1500);*/
      $(this).val('')
      $('#save_msg_tally').html(msg).show()
      var t = setTimeout(function () {
        $('#save_msg_tally').fadeOut()
      }, 1500)
      var url = ajax_url.url + '?action=fetch_tally_notes'
      $.ajax({
        method: 'get',
        data: {
          user_id: user_id,
          meta_key: meta_key
        },
        url: url
      }).done(function (msg) {
        $('#' + meta_key + '_html').html(msg)
        $('#' + meta_key).val('')
      })
    })
  })

  /***************************************/
  /***************************************/
  /********* COURSE RECORDS **************/
  /***************************************/
  /***************************************/

  $('.tftable-records input, .tftable-records select').change(function () {
    var field_val = $(this).val()
    var user_id = $('#user_id').val()
    var meta_key = $(this).attr('name')
    var course_id = $(this).attr('data-course')
    var url = ajax_url.url + '?action=update_records'
    $.ajax({
      beforeSend: function () {
        // show gif here, eg:
        $('#save_msg_records').show().html('Saving...')
      },
      global: false,
      method: 'get',
      data: {user_id: user_id, meta_key: meta_key, meta_value: field_val, course_id: course_id},
      url: url
    }).done(function (msg) {
      /*$('html, body').animate({
        scrollTop: $("#tftable-records").offset().top - 300
      }, 1500);*/
      $('#save_msg_records').html(msg).show()
      var t = setTimeout(function () {
        $('#save_msg_records').fadeOut()
      }, 1500)
    })
  })

  $('.tftable-records textarea').change(function () {
    var field_val = $(this).val()
    var user_id = $('#user_id').val()
    var meta_key = $(this).attr('name')
    var course_id = $(this).attr('data-course')
    var url = ajax_url.url + '?action=update_records'
    $.ajax({
      beforeSend: function () {
        // show gif here, eg:
        $('#save_msg_records').show().html('Saving...')
      },
      global: false,
      method: 'get',
      data: {
        user_id: user_id,
        meta_key: meta_key,
        meta_value: field_val,
        course_id: course_id,
        is_textarea: 'yes'
      },
      url: url
    }).done(function (msg) {
      /*$('html, body').animate({
        scrollTop: $("#save_msg_records").offset().top - 300
      }, 1500);*/
      $('#save_msg_records').html(msg).show()
      $(this).val('')
      var t = setTimeout(function () {
        $('#save_msg_records').fadeOut()
      }, 1500)
      var url = ajax_url.url + '?action=fetch_record_notes'
      $.ajax({
        method: 'get',
        data: {user_id: user_id, meta_key: meta_key},
        url: url
      }).done(function (msg) {
        $('#' + meta_key + '_html').html(msg)
        $('#' + meta_key).val('')
      })
    })
  })
})

/*******************/
/***DELETE RECORD***/
/******************/

$('.delete-ceu-record').click(function () {
  var $this = $(this);
  var user_id = $(this).data('user')
  var course_id = $(this).data('course')
  var time = $(this).data('time')
  var url = ajax_url.url + '?action=delete_ceu_record'
  $.ajax({
    beforeSend: function () {
      // show gif here, eg:
      $('#save_msg_records').show().html('Deleting...')
    },
    global: false,
    method: 'get',
    data: {user_id: user_id, course_id: course_id, time: time},
    url: url
  }).done(function (msg) {
    /*$('html, body').animate({
      scrollTop: $("#tftable-records").offset().top - 300
    }, 1500);*/
    $('#save_msg_records').html('Record Deleted').show()
    var t = setTimeout(function () {
      $('#save_msg_records').fadeOut()
    }, 1500)
    $this.parent().parent().fadeOut()

  })
})
