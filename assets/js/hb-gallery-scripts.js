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

  var currentGalleryId;

  // Open modal when clicking on .open-modal link
  $(".open-modal").on("click", function (e) {
    e.preventDefault();
    currentGalleryId = $(this).data("gallery-id");
    fetchGalleryItem(currentGalleryId);
    $("#hadesboardModal").css("display", "block");
  });

  // Close modal when clicking on close button or outside modal content
  $(".close, .hb-modal").on("click", function () {
    $("#hadesboardModal").css("display", "none");
  });

  // Prevent modal from closing when clicking inside modal content
  $(".modal-content, .modal-navigation").on("click", function (e) {
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
        if (response.success) {
          $(".modal-body").html(response.data.html);
          updateNavigationButtons(
            response.data.prev_post_id,
            response.data.next_post_id
          );
          $("[data-fancybox]").fancybox({
            loop: true,
            buttons: [
              "zoom",
              "share",
              "slideShow",
              "fullScreen",
              "download",
              "thumbs",
              "close",
            ],
            animationEffect: "zoom-in-out",
            transitionEffect: "slide",
          });
        }
      },
      error: function (error) {
        console.error("Error fetching gallery item:", error);
      },
    });
  }

  function updateNavigationButtons(prevPostId, nextPostId) {
    var $prevButton = $("#prevButton");
    var $nextButton = $("#nextButton");

    if (prevPostId) {
      $prevButton.attr("disabled", false);
      $prevButton.data("gallery-id", prevPostId);
    } else {
      $prevButton.attr("disabled", true);
    }

    if (nextPostId) {
      $nextButton.attr("disabled", false);
      $nextButton.data("gallery-id", nextPostId);
    } else {
      $nextButton.attr("disabled", true);
    }
  }

  // Previous button click handler
  $("#prevButton").on("click", function () {
    var prevId = $(this).data("gallery-id");
    if (prevId) {
      currentGalleryId = prevId;
      fetchGalleryItem(currentGalleryId);
    }
  });

  // Next button click handler
  $("#nextButton").on("click", function () {
    var nextId = $(this).data("gallery-id");
    if (nextId) {
      currentGalleryId = nextId;
      fetchGalleryItem(currentGalleryId);
    }
  });

  $("[data-fancybox]").fancybox({
    loop: true,
    buttons: [
      "zoom",
      "share",
      "slideShow",
      "fullScreen",
      "download",
      "thumbs",
      "close",
    ],
    animationEffect: "zoom-in-out",
    transitionEffect: "slide",
  });
});
