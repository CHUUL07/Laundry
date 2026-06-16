/**
 * Laundry-IN — Delete Confirmation Modal
 *
 * Usage: Add data-modal-target="delete-modal" data-service-name="Name"
 *        data-form-action="/layanan/delete/1" to the delete button.
 */
document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("delete-modal");
  const backdrop = document.getElementById("modal-backdrop");
  const cancelBtn = document.getElementById("modal-cancel");
  const confirmBtn = document.getElementById("modal-confirm");
  const serviceNameEl = document.getElementById("modal-service-name");
  const deleteForm = document.getElementById("delete-form");

  function openModal(serviceName, formAction) {
    if (serviceNameEl) serviceNameEl.textContent = serviceName;
    if (deleteForm) deleteForm.setAttribute("action", formAction);
    backdrop && backdrop.classList.add("open");
  }

  function closeModal() {
    backdrop && backdrop.classList.remove("open");
  }

  // Attach to all delete trigger buttons
  document.querySelectorAll("[data-delete-trigger]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      openModal(
        this.getAttribute("data-service-name"),
        this.getAttribute("data-form-action"),
      );
    });
  });

  cancelBtn && cancelBtn.addEventListener("click", closeModal);
  backdrop &&
    backdrop.addEventListener("click", function (e) {
      if (e.target === backdrop) closeModal();
    });

  // Close on Escape key
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") closeModal();
  });
});
