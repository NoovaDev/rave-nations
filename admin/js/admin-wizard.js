// admin/js/admin-wizard.js
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('eventWizardModal');
    if (!modal) return;

    const openBtn = document.getElementById('openWizardBtn');
    const closeBtn = document.getElementById('closeWizardBtn');
    const nextBtn = document.getElementById('nextStepBtn');
    const prevBtn = document.getElementById('prevStepBtn');

    const step1 = document.getElementById('wizardStep1');
    const step2 = document.getElementById('wizardStep2');
    const pill1 = document.getElementById('pillStep1');
    const pill2 = document.getElementById('pillStep2');

    const openModal = () => modal.classList.add('active');
    const closeModal = () => modal.classList.remove('active');

    if (openBtn) openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    window.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('open_wizard') === '1') {
        openModal();
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            const eventName = document.getElementById('event_name').value.trim();
            const eventDate = document.getElementById('event_date').value;
            const eventLocation = document.getElementById('event_location').value.trim();
            const posterInput = document.getElementById('event_poster');

            if (!eventName || !eventDate || !eventLocation) {
                alert('Please fill out all required fields in Step 1.');
                return;
            }

            if (posterInput.files && posterInput.files[0]) {
                const file = posterInput.files[0];
                const fileExt = file.name.split('.').pop().toLowerCase();
                if (!['png', 'jpg', 'jpeg', 'webp'].includes(fileExt)) {
                    alert('Invalid poster format! Only .png, .jpg, and .webp files are allowed.');
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('Poster file is too large! Maximum allowed size is 5MB.');
                    return;
                }
            } else {
                alert('Please upload an event poster image.');
                return;
            }

            step1.classList.remove('active');
            step2.classList.add('active');
            pill1.classList.remove('active');
            pill2.classList.add('active');
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            step2.classList.remove('active');
            step1.classList.add('active');
            pill2.classList.remove('active');
            pill1.classList.add('active');
        });
    }
});