document.addEventListener("DOMContentLoaded", function () {
    // Confirm before deleting an option
    document.querySelectorAll(".delete-option").forEach((button) => {
        button.addEventListener("click", function (event) {
            if (!confirm("Are you sure you want to delete this option?")) {
                event.preventDefault();
            }
        });
    });

    // Basic validation to prevent empty fields
    const form = document.querySelector("form");
    if (form) {
        form.addEventListener("submit", function (event) {
            const inputs = form.querySelectorAll("input[required]");
            for (const input of inputs) {
                if (!input.value.trim()) {
                    alert("Please fill out all required fields.");
                    event.preventDefault();
                    return;
                }
            }
        });
    }
});
