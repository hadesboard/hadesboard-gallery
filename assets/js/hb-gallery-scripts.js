jQuery(document).ready(function ($) {
  // Initialize Masonry after all images have loaded
  var $grid = $(".hadesboard-gallery").masonry({
    itemSelector: ".hadesboard-gallery-item",
    columnWidth: ".grid-sizer",
    percentPosition: true,
  });

  // Layout Masonry after each image loads
  $grid.imagesLoaded().progress(function () {
    $grid.masonry("layout");
  });

  $(".hadesboard-gallery-item").on("mouseenter", function () {
    const video = $(this).find("video")[0]; // Select the DOM element from the jQuery object
    if (video) {
      video.play();
      // Add an event listener to loop the video when it ends
      video.addEventListener("ended", function () {
        video.play();
      });
    }
  });

  $(".hadesboard-gallery-item").on("mouseleave", function () {
    const video = $(this).find("video")[0]; // Select the DOM element from the jQuery object
    if (video) {
      video.pause();
      // Remove the event listener when the mouse leaves
      video.removeEventListener("ended", function () {
        video.play();
      });
    }
  });

  // Open modal when clicking on .open-modal link
  $(".open-modal").on("click", function (e) {
    e.preventDefault();
    var galleryId = $(this).data("gallery-id");
    fetchGalleryItem(galleryId);
    $("#hadesboardModal").css("display", "block");
  });

  // Close modal when clicking on close button or outside modal content
  $(".close, .hb-modal").on("click", function () {
    $("#hadesboardModal").css("display", "none");
  });

  // Prevent modal from closing when clicking inside modal content
  $(".modal-content").on("click", function (e) {
    e.stopPropagation();
  });

  // Function to fetch gallery item details via AJAX
  function fetchGalleryItem(galleryId) {
    $.ajax({
      url: hbg_ajax.ajax_url, // WordPress AJAX URL (automatically defined in WordPress admin)
      type: "POST",
      data: {
        action: "get_gallery_item", // AJAX action name
        post_id: galleryId, // Send the post ID to retrieve specific content
      },
      success: function (response) {
        $(".modal-body").html(response.data);
      },
      error: function (error) {
        console.error("Error fetching gallery item:", error);
      },
    });
  }
});
