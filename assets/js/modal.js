/**
 * Laundry-IN — Delete Confirmation Modal
 *
 * Usage: Add data-delete-trigger, data-item-name="Item Name",
 *        and data-form-action="/path/delete/1" to the delete button.
 */
document.addEventListener("DOMContentLoaded", function () {
  const backdrop = document.getElementById("modal-backdrop");
  const cancelBtn = document.getElementById("modal-cancel");
  const itemNameEl = document.getElementById("modal-item-name");
  const deleteForm = document.getElementById("delete-form");

  function openModal(itemName, formAction) {
    if (itemNameEl) itemNameEl.textContent = itemName;
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
        this.getAttribute("data-item-name"),
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
