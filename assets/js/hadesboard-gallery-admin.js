jQuery(document).ready(function ($) {
  // Combined Gallery
  $("#add_gallery").on("click", function (e) {
    e.preventDefault();
    var galleryFrame;
    if (galleryFrame) {
      galleryFrame.open();
      return;
    }

    galleryFrame = wp.media({
      title: "Select Media",
      button: { text: "Add to Gallery" },
      multiple: true,
    });

    galleryFrame.on("select", function () {
      var attachments = galleryFrame
        .state()
        .get("selection")
        .map(function (attachment) {
          attachment = attachment.toJSON();
          var mime_type = attachment.mime.split("/")[0];
          return {
            id: attachment.id,
            url:
              mime_type === "image"
                ? attachment.sizes.thumbnail.url
                : attachment.url,
            mime_type: mime_type,
          };
        });

      var galleryField = $("#gallery");
      var galleryContainer = $("#gallery_container ul.gallery");
      var currentGallery = galleryField.val()
        ? galleryField.val().split(",")
        : [];

      attachments.forEach(function (attachment) {
        if (!currentGallery.includes(attachment.id.toString())) {
          currentGallery.push(attachment.id);
          if (attachment.mime_type === "image") {
            galleryContainer.append(
              '<li class="image" data-id="' +
                attachment.id +
                '"><img src="' +
                attachment.url +
                '" /><span class="remove">x</span></li>'
            );
          } else if (attachment.mime_type === "video") {
            galleryContainer.append(
              '<li class="video" data-id="' +
                attachment.id +
                '"><video src="' +
                attachment.url +
                '" controls></video><span class="remove">x</span></li>'
            );
          }
        }
      });

      galleryField.val(currentGallery.join(","));
    });

    galleryFrame.open();
  });

  // Set Image Cover
  $("#add_image_cover").on("click", function (e) {
    e.preventDefault();
    var imageCoverFrame;
    if (imageCoverFrame) {
      imageCoverFrame.open();
      return;
    }

    imageCoverFrame = wp.media({
      title: "Select Image Cover",
      button: { text: "Set as Cover" },
      multiple: false,
    });

    imageCoverFrame.on("select", function () {
      var attachment = imageCoverFrame
        .state()
        .get("selection")
        .first()
        .toJSON();
      $("#image_cover").val(attachment.id);
      $("#image_cover_preview")
        .attr("src", attachment.sizes.thumbnail.url)
        .show();
      $("#remove_image_cover").show();
    });

    imageCoverFrame.open();
  });

  // Remove Image Cover
  $("#remove_image_cover").on("click", function (e) {
    e.preventDefault();
    $("#image_cover").val("");
    $("#image_cover_preview").hide();
    $(this).hide();
  });

  // Set Video Cover
  $("#add_video_cover").on("click", function (e) {
    e.preventDefault();
    var videoCoverFrame;
    if (videoCoverFrame) {
      videoCoverFrame.open();
      return;
    }

    videoCoverFrame = wp.media({
      title: "Select Video Cover",
      button: { text: "Set as Cover" },
      library: { type: "video" },
      multiple: false,
    });

    videoCoverFrame.on("select", function () {
      var attachment = videoCoverFrame
        .state()
        .get("selection")
        .first()
        .toJSON();
      $("#video_cover").val(attachment.id);
      $("#video_cover_preview").attr("src", attachment.url).show();
      $("#remove_video_cover").show();
    });

    videoCoverFrame.open();
  });

  // Remove Video Cover
  $("#remove_video_cover").on("click", function (e) {
    e.preventDefault();
    $("#video_cover").val("");
    $("#video_cover_preview").hide();
    $(this).hide();
  });

  // Remove media from gallery
  $("body").on("click", ".gallery .remove", function () {
    var parent = $(this).closest("li");
    var id = parent.data("id").toString();
    parent.remove();
    var galleryField = parent.closest(".inside").find('input[type="hidden"]');
    var gallery = galleryField.val().split(",");
    gallery = gallery.filter(function (item) {
      return item !== id;
    });
    galleryField.val(gallery.join(","));
  });

  // Make gallery sortable
  $(".gallery").sortable({
    update: function (event, ui) {
      var sortedIDs = $(this).sortable("toArray", { attribute: "data-id" });
      $(this)
        .closest(".inside")
        .find('input[type="hidden"]')
        .val(sortedIDs.join(","));
    },
  });
});
