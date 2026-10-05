function openModal(id) {

    document.getElementById(id).classList.remove('hidden');

}

function closeModal(id) {

    document.getElementById(id).classList.add('hidden');

}

window.openModal = openModal;
window.closeModal = closeModal;