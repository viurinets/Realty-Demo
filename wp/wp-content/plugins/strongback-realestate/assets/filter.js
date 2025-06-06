jQuery(function($){
  $('#sb-filter-form').on('submit', function(e){
    e.preventDefault();
    var data = $(this).serializeArray();
    data.push({ name: 'action', value: 'sb_search' });
    $('#sb-filter-results').html('<p>Loading…</p>');
    $.post(sb_vars.ajax_url, data, function(resp){
      if (!resp.success) {
        $('#sb-filter-results').html('<p>Error occurred.</p>');
        return;
      }

      var html = '', d = resp.data;

      if (d.items.length) {
        d.items.forEach(function(item){
          html += '<div class="sb-item">';
          if (item.image) {
            html += '<img src="' + item.image + '" alt="">';
          }
          html += '<h3><a href="' + item.link + '">' + item.title + '</a></h3>';
          html += '<p>' + item.excerpt + '</p>';
          html += '</div>';
        });

        if (d.max_pages > 1) {
          html += '<div class="sb-pagination">';
          for (var i = 1; i <= d.max_pages; i++) {
            html += '<button class="page-btn" data-page="' + i + '">' + i + '</button>';
          }
          html += '</div>';
        }

      } else {
        html = '<p>No results found.</p>';
      }

      $('#sb-filter-results').html(html);
    });
  });

  $(document).on('click', '.page-btn', function(){
    var page = $(this).data('page');
    var $form = $('#sb-filter-form');
    $form.find('input[name="paged"]').remove();
    $('<input>')
      .attr({ type: 'hidden', name: 'paged', value: page })
      .appendTo($form);
    $form.submit();
  });
});
