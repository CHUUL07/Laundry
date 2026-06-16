/**
 * Laundry-IN — Mobile Sidebar Toggle
 */
document.addEventListener("DOMContentLoaded", function () {
  const hamburger = document.getElementById("sidebar-toggle");
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("sidebar-overlay");

  function openSidebar() {
    sidebar && sidebar.classList.add("open");
    overlay && overlay.classList.add("visible");
    document.body.style.overflow = "hidden";
  }

  function closeSidebar() {
    sidebar && sidebar.classList.remove("open");
    overlay && overlay.classList.remove("visible");
    document.body.style.overflow = "";
  }

  function toggleSidebar() {
    if (sidebar && sidebar.classList.contains("open")) {
      closeSidebar();
    } else {
      openSidebar();
    }
  }

  hamburger && hamburger.addEventListener("click", toggleSidebar);
  overlay && overlay.addEventListener("click", closeSidebar);

  // Close on nav item click (mobile UX)
  const navItems = document.querySelectorAll(".sidebar-nav-item");
  navItems.forEach((item) => item.addEventListener("click", closeSidebar));
});
