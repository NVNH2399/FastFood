document.addEventListener("DOMContentLoaded", function () {
  var slideIndex = 1;
  showSlides(slideIndex);

  // Nút điều hướng slide
  window.plusSlides = function (n) {
    showSlides((slideIndex += n));
  };

  function showSlides(n) {
    var slides = document.getElementsByClassName("slide");
    if (n > slides.length) {
      slideIndex = 1;
    }
    if (n < 1) {
      slideIndex = slides.length;
    }
    for (var i = 0; i < slides.length; i++) {
      slides[i].style.display = "none"; // Ẩn tất cả các slide
    }
    slides[slideIndex - 1].style.display = "block"; // Hiển thị slide hiện tại
  }

  // Tự động chuyển slide sau mỗi 4 giây
  setInterval(function () {
    plusSlides(1); // Chuyển đến slide tiếp theo
  }, 4000);
});

let slideIndex = 1;
showSlides(slideIndex);

// Next/previous controls
function plusSlides(n) {
  showSlides(slideIndex += n);
}

// Thumbnail image controls
function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
  let i;
  let slides = document.getElementsByClassName("slide");
  let dots = document.getElementsByClassName("dot");

  if (n > slides.length) { slideIndex = 1 }
  if (n < 1) { slideIndex = slides.length }

  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
  }
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }

  slides[slideIndex - 1].style.display = "block";
  dots[slideIndex - 1].className += " active";
}

// Auto slide
setInterval(function () {
  plusSlides(1);
}, 5000); // Chuyển slide mỗi 5 giây