console.log("PASSWORD JS LOADED");

import '../css/app.css';
import './bootstrap';

import Chart from 'chart.js/auto';

import './password-strength';

window.Chart = Chart;

console.log("APP JS LOADED");
console.log(typeof window.openModal);
console.log(window.Chart);

// =======================
// Open / Close Modals
// =======================

window.openModal = function(id) {
    document.getElementById(id).classList.remove('hidden');
}

window.closeModal = function(id) {
    document.getElementById(id).classList.add('hidden');
}

// =======================
// Delete Account Confirmation
// =======================

document.addEventListener("DOMContentLoaded", () => {

    const input = document.getElementById("delete-confirm-input");
    const button = document.getElementById("delete-account-button");

    if (!input || !button) return;

    input.addEventListener("input", () => {

        // Auto convert to uppercase
        input.value = input.value.toUpperCase();

        // Enable button only if value is DELETE
        button.disabled = input.value !== "DELETE";

    });

});