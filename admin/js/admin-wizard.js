// admin/js/admin-wizard.js
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('eventWizardModal');
    const openBtn = document.getElementById('openWizardBtn');
    const closeBtn = document.getElementById('closeWizardBtn');
    
    if (!modal) return;

    const nextBtn = document.getElementById('nextStepBtn');
    const prevBtn = document.getElementById('prevStepBtn');
    
    const step1 = document.getElementById('wizardStep1');
    const step2 = document.getElementById('wizardStep2');
    const pill1 = document.getElementById('pillStep1');
    const pill2 = document.getElementById('pillStep2');

    const packagesContainer = document.getElementById('packagesContainer');
    const addPackageBtn = document.getElementById('addPackageBtn');

    // Open & Close Modal
    if (openBtn) openBtn.addEventListener('click', () => modal.classList.add('active'));
    const closeModal = () => modal.classList.remove('active');
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    window.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

    // Step 1 Validation
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
                if (fileExt !== 'png' && fileExt !== 'jpg' && fileExt !== 'jpeg') {
                    alert('Invalid poster format! Only .png and .jpg files are allowed.');
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('Poster file is too large! Maximum allowed size is 5MB.');
                    return;
                }
            } else {
                alert('Please upload an event poster image (.png or .jpg).');
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

    // Dynamic Package Tier Addition
    let packageCount = 1;
    if (addPackageBtn && packagesContainer) {
        addPackageBtn.addEventListener('click', () => {
            packageCount++;
            const packageCard = document.createElement('div');
            packageCard.className = 'package-card-item';
            packageCard.style.cssText = 'background: var(--admin-bg); padding: 16px; border-radius: 14px; border: 1px solid var(--admin-border); margin-bottom: 16px; position: relative;';
            
            packageCard.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px;">
                    <strong style="font-size:13px; color:var(--admin-purple);">Package Tier #${packageCount}</strong>
                    <button type="button" class="remove-pkg-btn" style="background:none; border:none; color:#ef4444; font-weight:800; cursor:pointer; font-size:12px;">✕ Remove Tier</button>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Package Name *</label>
                        <input type="text" name="package_name[]" class="form-control" placeholder="e.g. VIP Backstage Pass" required>
                    </div>
                    <div class="form-group">
                        <label>Ticket Price (LKR) *</label>
                        <input type="number" step="0.01" name="ticket_price[]" class="form-control" placeholder="7500.00" value="0.00" required>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Package Benefits / Description</label>
                    <textarea name="benefits[]" class="form-control" rows="2" placeholder="e.g. VIP deck access, complimentary drinks"></textarea>
                </div>
            `;

            packagesContainer.appendChild(packageCard);

            // Handle Row Removal
            packageCard.querySelector('.remove-pkg-btn').addEventListener('click', () => {
                packageCard.remove();
            });
        });
    }
});