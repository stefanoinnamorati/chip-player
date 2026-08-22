(function ($) {
  "use strict";

  var i18n = window.chipPlayerAdmin || {};

  $(document).on("click", "[data-chip-audio-pick], [data-pepa-audio-pick]", function (event) {
    event.preventDefault();
    var frame = wp.media({
      title: i18n.title || "Choose audio file",
      library: { type: "audio" },
      button: { text: i18n.button || "Use this file" },
      multiple: false,
    });
    frame.on("select", function () {
      var att = frame.state().get("selection").first().toJSON();
      $("#chip_audio_id, #pepa_audio_id").val(att.id);
      $("#chip_audio_url, #pepa_audio_url").val(att.url);
      $("[data-chip-audio-name], [data-pepa-audio-name]").text(att.filename || att.title || att.url);
    });
    frame.open();
  });

  $(document).on("click", "[data-chip-audio-clear], [data-pepa-audio-clear]", function (event) {
    event.preventDefault();
    $("#chip_audio_id, #pepa_audio_id").val("0");
    $("#chip_audio_url, #pepa_audio_url").val("");
    $("[data-chip-audio-name], [data-pepa-audio-name]").text(i18n.none || "No file");
  });

  $(document).on("click", "[data-chip-cover-pick]", function (event) {
    event.preventDefault();
    var frame = wp.media({
      title: i18n.image || "Choose cover image",
      library: { type: "image" },
      button: { text: i18n.button || "Use this file" },
      multiple: false,
    });
    frame.on("select", function () {
      var att = frame.state().get("selection").first().toJSON();
      $("#chip_player_cover").val(att.id);
    });
    frame.open();
  });

  $(document).on("click", "[data-chip-cover-clear]", function (event) {
    event.preventDefault();
    $("#chip_player_cover").val("0");
  });
})(jQuery);
