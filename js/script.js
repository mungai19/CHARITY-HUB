
// CHARITY HUB - Dashboard JavaScript

// Get the sidebar
const sidebar = document.getElementById("sidebar");

// Get the menu button
const menuToggle = document.getElementById("menuToggle");

// Get the notification box
const notification = document.getElementById("notification");

// Open and close the sidebar on small screens
menuToggle.addEventListener("click", function () {
    sidebar.classList.toggle("open");
});

// Display a message on the screen
function showNotification(message) {
    notification.textContent = message;
    notification.style.display = "block";

    setTimeout(function () {
        notification.style.display = "none";
    }, 3000);
}

// Handle sidebar navigation
const navigationLinks = document.querySelectorAll(".nav-link");

navigationLinks.forEach(function (link) {

    link.addEventListener("click", function (event) {

        event.preventDefault();

        // Remove active style from all links
        navigationLinks.forEach(function (item) {
            item.classList.remove("active");
        });

        // Highlight the selected link
        this.classList.add("active");

        // Get the selected page name
        const pageName = this.dataset.page;

        if (pageName === "Dashboard") {
            showNotification("You are viewing the Dashboard.");
        } else {
            showNotification(
                pageName + " page will be developed in the next stage."
            );
        }

        // Update the heading
        document.getElementById("pageTitle").textContent = pageName;

        // Close mobile menu after selection
        sidebar.classList.remove("open");
    });

});

// View all donations button
document.getElementById("viewDonations").addEventListener(
    "click",
    function () {
        showNotification(
            "The full Donations Management page is coming next."
        );
    }
);

console.log("CHARITY HUB dashboard loaded successfully!");
