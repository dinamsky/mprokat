$(document).ready(function() {
  var my_city_autoComplete = new autoComplete({
    selector: 'input[name="input_city"]',
    minChars: 1,
    offsetTop: 16,
    source: function(term, response) {
      $.ajax({
        url: "/ajax/getCityByInput",
        type: "POST",
        dataType: "json",
        data: { q: term },
        success: function(json) {
          response(json);
        }
      });
    },
    renderItem: function(item, search) {
      var res = item.split("|");
      item = res[0];
      var itemOnly = item.split(',')[0];
      var id = res[1];
      search = search.replace(/[-\/\\^$*+?.()|[\]{}]/g, "\\$&");
      var re = new RegExp("(" + search.split(" ").join("|") + ")", "gi");
      return (
        '<div class="autocomplete-suggestion city_block" data-url="' +
        res[2] +
        '" data-header="' +
        item +
        '" data-id="' +
        id +
        '" data-val = "' +
        id +
        '" >' +
        itemOnly.replace(re, "<b>$1</b>") +
        "</div>"
      );
    },
    onSelect: function(e, term, item) {
      $('.city_block[data-id="' + item.getAttribute("data-id") + '"]').click();
      // $('.city_block[data-id="'+term+'"]').click();
      $('input[name="input_city"]').val("");
    }
  });

  var my_city_autoComplete_main = new autoComplete({
    selector: 'input[name="input_city_main"]',
    source: function(term, response) {
      $.ajax({
        url: "/ajax/getCityByInput",
        type: "POST",
        dataType: "json",
        data: { q: term },
        success: function(json) {
          response(json);
        }
      });
    },
    renderItem: function(item, search) {
      var res = item.split("|");
      item = res[0];
      var itemOnly = item.split(',')[0];
      var id = res[1];
      search = search.replace(/[-\/\\^$*+?.()|[\]{}]/g, "\\$&");
      var re = new RegExp("(" + search.split(" ").join("|") + ")", "gi");
      return (
        '<div class="autocomplete-suggestion city_block_main" data-url="' +
        res[2] +
        '" data-header="' +
        item +
        '" data-id="' +
        id +
        '" data-val = "' +
        id +
        '" >' +
        itemOnly.replace(re, "<b>$1</b>") +
        "</div>"
      );
    },
    onSelect: function(e, term, item) {
      $(
        '.city_block_main[data-id="' + item.getAttribute("data-id") + '"]'
      ).click();
      // $('.city_block[data-id="'+term+'"]').click();
      $('input[name="input_city_main"]').val("");
    }
  });

  $(".city_helper_close").on("click", function() {
    $(this)
      .parents(".city_helper")
      .remove();
  });
});
