document.addEventListener("DOMContentLoaded", function () {

    const accountMenus = document.querySelectorAll(".account-menu");

    document.addEventListener("click", function (event) {
        accountMenus.forEach(function (accountMenu) {
            if (accountMenu.open && !accountMenu.contains(event.target)) {
                accountMenu.open = false;
            }
        });
    });

    /* Mobile Menu */

    const menuBtn = document.getElementById("mobileMenuBtn");
    const nav = document.getElementById("mainNav");

    if (menuBtn && nav) {

        menuBtn.addEventListener("click", function () {

            nav.classList.toggle("show");

            const icon = menuBtn.querySelector("i");

            if (nav.classList.contains("show")) {
                icon.classList.remove("fa-bars");
                icon.classList.add("fa-xmark");
            } else {
                icon.classList.remove("fa-xmark");
                icon.classList.add("fa-bars");
            }

        });

    }


    /* FAQ */

    document.querySelectorAll(".faq-question").forEach(function (question) {

        question.addEventListener("click", function () {

            const item = this.parentElement;

            document.querySelectorAll(".faq-item").forEach(function (faq) {

                if (faq !== item) {
                    faq.classList.remove("open");
                }

            });

            item.classList.toggle("open");

        });

    });


    /* Toast */

    window.showToast = function (message = "Action completed successfully.") {

        const toast = document.getElementById("toast");

        if (!toast) return;

        toast.querySelector("span").textContent = message;

        toast.classList.add("show");

        setTimeout(function () {
            toast.classList.remove("show");
        }, 2500);

    };


    /* Demo buttons */

    document.querySelectorAll("[data-toast]").forEach(function (button) {

        button.addEventListener("click", function (e) {

            e.preventDefault();

            showToast(
                this.dataset.toast || "Action completed successfully."
            );

        });

    });


    /* Quantity */

    document.querySelectorAll(".qty-btn").forEach(function (button) {

        button.addEventListener("click", function () {

            const input = this.parentElement.querySelector(".qty-input");

            if (!input) return;

            let value = parseInt(input.value) || 1;

            if (this.dataset.action === "plus") {
                value++;
            }

            if (this.dataset.action === "minus" && value > 1) {
                value--;
            }

            input.value = value;

        });

    });

});