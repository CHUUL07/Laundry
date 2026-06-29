/**
 * Laundry-IN — Dark/Light Mode Toggle
 * Applies theme on page load and handles toggle clicks.
 */
(function () {
  const html = document.documentElement;
  const saved = localStorage.getItem("laundry-in-theme");
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;

  // Apply saved or system theme before paint (prevents flash)
  if (saved === "dark" || (!saved && prefersDark)) {
    html.classList.add("dark");
  }

  document.addEventListener("DOMContentLoaded", function () {
    const toggle = document.getElementById("theme-toggle");
    const iconSun = document.getElementById("icon-sun");
    const iconMoon = document.getElementById("icon-moon");

    function updateIcons() {
      if (html.classList.contains("dark")) {
        iconSun && iconSun.classList.remove("hidden");
        iconMoon && iconMoon.classList.add("hidden");
      } else {
        iconSun && iconSun.classList.add("hidden");
        iconMoon && iconMoon.classList.remove("hidden");
      }
    }

    updateIcons();

    toggle &&
      toggle.addEventListener("click", function () {
        const isDark = html.classList.toggle("dark");
        localStorage.setItem("laundry-in-theme", isDark ? "dark" : "light");
        updateIcons();
      });
  });
})();
