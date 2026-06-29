/**
 * Laundry-IN — Landing Page Interactions
 */
document.addEventListener("DOMContentLoaded", function () {
  const header = document.getElementById("landing-header");
  const headerHeight = header?.offsetHeight || 64;

  // ===== Mobile Hamburger Toggle =====
  const hamburger = document.getElementById("landing-hamburger");
  const mobileNav = document.getElementById("landing-mobile-nav");

  if (hamburger && mobileNav) {
    hamburger.addEventListener("click", function () {
      mobileNav.classList.toggle("open");
      const icon = hamburger.querySelector("i");
      if (mobileNav.classList.contains("open")) {
        icon.className = "ph-bold ph-x";
      } else {
        icon.className = "ph-bold ph-list";
      }
    });

    mobileNav.querySelectorAll(".landing-nav-link").forEach(function (link) {
      link.addEventListener("click", function () {
        mobileNav.classList.remove("open");
        const icon = hamburger.querySelector("i");
        icon.className = "ph-bold ph-list";
      });
    });
  }

  // ===== Update Active Nav Link =====
  function setActiveNav(sectionId) {
    document.querySelectorAll(".landing-nav-link").forEach(function (link) {
      link.classList.remove("active");
      if (link.getAttribute("href")?.includes("#" + sectionId)) {
        link.classList.add("active");
      }
    });
  }

  // ===== Intersection Observer for scroll-based active nav =====
  const sections = ["hero", "layanan", "tentang"];
  const observer = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          setActiveNav(entry.target.id);
        }
      });
    },
    {
      rootMargin: "-" + headerHeight + "px 0px -40% 0px",
      threshold: 0,
    },
  );

  sections.forEach(function (id) {
    var el = document.getElementById(id);
    if (el) observer.observe(el);
  });

  // ===== Smooth Scroll for anchor links + update active =====
  document
    .querySelectorAll('a[href^="/laundry-in/#"]')
    .forEach(function (anchor) {
      anchor.addEventListener("click", function (e) {
        e.preventDefault();
        var targetId = this.getAttribute("href").split("#")[1];
        var target = document.getElementById(targetId);
        if (target) {
          setActiveNav(targetId);
          window.scrollTo({
            top: target.offsetTop - headerHeight,
            behavior: "smooth",
          });
        }
      });
    });

  // ===== Shrink header on scroll =====
  if (header) {
    window.addEventListener("scroll", function () {
      if (window.scrollY > 60) {
        header.style.boxShadow = "var(--shadow-md)";
      } else {
        header.style.boxShadow = "none";
      }
    });
  }
});
