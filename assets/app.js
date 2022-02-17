/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// start the Stimulus application
import './bootstrap';

window.boostrap = require('bootstrap');

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("[data-toggle-password]").forEach((btn) => {
        btn.addEventListener("click", (event) => {
            const target = document.querySelector(event.currentTarget.getAttribute("data-toggle-password"));

            if (target.getAttribute("type") === "password") {
                target.setAttribute("type", "text")
                event.currentTarget.querySelector("[data-eye='show']").classList.remove("d-none")
                event.currentTarget.querySelector("[data-eye='slash']").classList.add("d-none")
            } else {
                event.currentTarget.querySelector("[data-eye='show']").classList.add("d-none")
                event.currentTarget.querySelector("[data-eye='slash']").classList.remove("d-none")
                target.setAttribute("type", "password")
            }
        })
    })
});
