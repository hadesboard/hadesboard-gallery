jQuery(document).ready(function ($) {
  // Initialize Masonry
  var $grid = $(".hadesboard-gallery").masonry({
    itemSelector: ".hadesboard-gallery-item",
    columnWidth: ".grid-sizer",
    percentPosition: true,
  });

  // Function to check if all videos are loaded
  function allVideosLoaded() {
    var allLoaded = true;
    $(".hadesboard-gallery-item video").each(function () {
      if (this.readyState !== 4) {
        allLoaded = false;
        return false; // break out of .each loop
      }
    });
    return allLoaded;
  }

  // Function to initialize Masonry after all videos are loaded
  function initMasonryAfterVideosLoaded() {
    if (allVideosLoaded()) {
      $grid.masonry("layout");
    } else {
      $(".hadesboard-gallery-item video").one("loadeddata", function () {
        if (allVideosLoaded()) {
          $grid.masonry("layout");
        }
      });
    }
  }

  // Initial call to layout Masonry after videos are loaded
  initMasonryAfterVideosLoaded();

  // Filter functionality
  $(".filter-button").on("click", function () {
    $(".filter-button").removeClass("active");
    $(this).addClass("active");
    var filterValue = $(this).attr("data-category");
    if (filterValue === "all") {
      $(".hadesboard-gallery-item").show();
    } else {
      $(".hadesboard-gallery-item").hide();
      $(
        '.hadesboard-gallery-item[data-category*="' + filterValue + '"]'
      ).show();
    }
    $grid.masonry("layout");
  });

  $(".hadesboard-gallery-item").on("mouseenter", function () {
    const video = $(this).find("video")[0];
    if (video) {
      video.play();
      video.addEventListener("ended", function () {
        video.play();
      });
    }
  });

  $(".hadesboard-gallery-item").on("mouseleave", function () {
    const video = $(this).find("video")[0];
    if (video) {
      video.pause();
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
    $("body").css("overflow", "hidden");
  });

  // Close modal when clicking on close button or outside modal content
  $(".close, .hb-modal").on("click", function () {
    $("#hadesboardModal").css("display", "none");
    $("body").css("overflow", "auto");
  });

  // Prevent modal from closing when clicking inside modal content
  $(".modal-content, .modal-navigation").on("click", function (e) {
    e.stopPropagation();
  });

  // Function to fetch gallery item details via AJAX
  function fetchGalleryItem(galleryId) {
    // Show loader
    $("#modalLoader").show();
    $(".modal-body, .like-section").hide();

    $.ajax({
      url: hbg_ajax.ajax_url,
      type: "POST",
      data: {
        action: "get_gallery_item",
        post_id: galleryId,
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
            buttons: ["zoom", "slideShow", "fullScreen", "thumbs", "close"],
            animationEffect: "zoom-in-out",
            transitionEffect: "slide",
          });
          var likeCount = $("#likeCount b");
          var likeButton = $("#likeButton");
          likeButton.attr("data-post-id", response.data.post_id);
          var likedPosts = getLikedPosts();
          if (likedPosts.indexOf(response.data.post_id.toString()) >= 0) {
            likeButton.addClass("liked");
          } else {
            likeButton.removeClass("liked");
          }
          likeCount.text(response.data.like_count);
          // Hide loader and show content
          $("#modalLoader").hide();
          $(".modal-body, .like-section").show();
        }
      },
      error: function (error) {
        console.error("Error fetching gallery item:", error);
        // Hide loader even on error
        $("#modalLoader").hide();
        $(".modal-body, .like-section").show();
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

  var likeButton = $("#likeButton");
  var likeCount = $("#likeCount b");

  likeButton.on("click", function () {
    var postId = $(this).attr("data-post-id");
    $.ajax({
      url: hbg_ajax.ajax_url,
      type: "POST",
      data: {
        action: "toggle_like_gallery_item",
        post_id: postId,
      },
      success: function (response) {
        if (response.success) {
          likeCount.text(response.data.like_count);
          if (response.data.liked) {
            likeButton.addClass("liked");
            setLikedPost(postId);
          } else {
            likeButton.removeClass("liked");
            unsetLikedPost(postId);
          }
        } else {
          alert(response.data);
        }
      },
      error: function () {
        alert("An error occurred. Please try again.");
      },
    });
  });

  function getLikedPosts() {
    var likedPosts = [];
    var likedPostsCookie = document.cookie
      .split("; ")
      .find((row) => row.startsWith("liked_posts="));
    if (likedPostsCookie) {
      likedPosts = JSON.parse(
        decodeURIComponent(likedPostsCookie.split("=")[1])
      );
    }
    return likedPosts;
  }

  function setLikedPost(postId) {
    var likedPosts = getLikedPosts();
    if (!likedPosts.includes(postId)) {
      likedPosts.push(postId);
    }
    document.cookie =
      "liked_posts=" +
      encodeURIComponent(JSON.stringify(likedPosts)) +
      "; path=/; max-age=" +
      10 * 365 * 24 * 60 * 60; // 10 years
  }

  function unsetLikedPost(postId) {
    var likedPosts = getLikedPosts();
    likedPosts = likedPosts.filter(function (id) {
      return id != postId;
    });
    document.cookie =
      "liked_posts=" +
      encodeURIComponent(JSON.stringify(likedPosts)) +
      "; path=/; max-age=" +
      10 * 365 * 24 * 60 * 60; // 10 years
  }
});
