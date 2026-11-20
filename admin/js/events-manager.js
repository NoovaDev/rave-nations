// admin/js/events-manager.js
document.addEventListener('DOMContentLoaded', () => {
    const escapeHtml = (text) => {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    // =====================================================
    // 1. EVENT EDIT & DELETE
    // =====================================================
    const editModal = document.getElementById('editEventModal');
    const closeEditModalBtn = document.getElementById('closeEditEventModalBtn');

    if (closeEditModalBtn && editModal) {
        closeEditModalBtn.addEventListener('click', () => editModal.classList.remove('active'));
    }

    const openEditEventModal = async (eventId) => {
        try {
            const res = await fetch(`events.php?ajax_action=get_event&event_id=${eventId}`);
            const event = await res.json();

            if (event && event.id) {
                document.getElementById('edit_event_id').value = event.id;
                document.getElementById('edit_event_name').value = event.event_name;
                document.getElementById('edit_event_date').value = event.event_date;
                document.getElementById('edit_event_time').value = event.event_time;
                document.getElementById('edit_event_location').value = event.location;
                document.getElementById('edit_event_status').value = event.status;
                document.getElementById('edit_event_description').value = event.description || '';
                editModal.classList.add('active');
            } else {
                alert('Failed to load event details.');
            }
        } catch (err) {
            alert('Error communicating with server.');
        }
    };

    document.querySelectorAll('.edit-event-btn').forEach(btn => {
        btn.addEventListener('click', () => openEditEventModal(btn.getAttribute('data-event-id')));
    });

    const urlParams = new URLSearchParams(window.location.search);
    const editIdParam = urlParams.get('edit_id');
    if (editIdParam && editModal) {
        openEditEventModal(editIdParam);
    }

    document.querySelectorAll('.delete-event-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const eventId = btn.getAttribute('data-event-id');
            const eventName = btn.getAttribute('data-event-name');

            if (!confirm(`Are you sure you want to delete "${eventName}"? All associated ticket passes will be removed permanently.`)) {
                return;
            }

            const formData = new FormData();
            formData.append('event_id', eventId);

            try {
                const res = await fetch('events.php?ajax_action=delete_event', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    const row = document.getElementById(`event-row-${eventId}`);
                    if (row) row.remove();
                } else {
                    alert('Failed to delete event: ' + (data.error || 'Unknown error'));
                }
            } catch (err) {
                alert('Error communicating with server.');
            }
        });
    });

    // =====================================================
    // 2. MAIN TICKET PASSES MANAGEMENT
    // =====================================================
    const manageModal = document.getElementById('managePackagesModal');
    const closeManageBtn = document.getElementById('closeManageModalBtn');
    const manageModalTitle = document.getElementById('manageModalTitle');
    const manageEventIdInput = document.getElementById('manageEventIdInput');
    const managePkgIdInput = document.getElementById('managePkgIdInput');
    const packageFormAction = document.getElementById('packageFormAction');
    const existingPackagesList = document.getElementById('existingPackagesList');

    const pkgForm = document.getElementById('packageForm');
    const formSectionTitle = document.getElementById('formSectionTitle');
    const nameInput = document.getElementById('pkg_name_input');
    const priceInput = document.getElementById('pkg_price_input');
    const benefitsInput = document.getElementById('pkg_benefits_input');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const saveSubmitBtn = document.getElementById('savePkgSubmitBtn');

    let activeEventId = null;

    if (closeManageBtn) {
        closeManageBtn.addEventListener('click', () => manageModal.classList.remove('active'));
    }

    const resetPkgForm = () => {
        packageFormAction.value = 'add_package';
        managePkgIdInput.value = '';
        formSectionTitle.textContent = '+ Add New Pass Tier';
        saveSubmitBtn.textContent = 'Add Ticket Pass';
        cancelEditBtn.style.display = 'none';
        nameInput.value = '';
        priceInput.value = '0.00';
        benefitsInput.value = '';
    };

    if (cancelEditBtn) cancelEditBtn.addEventListener('click', resetPkgForm);

    const loadPackages = async (eventId) => {
        existingPackagesList.innerHTML = '<div style="color:var(--admin-text-muted); font-size:13px; text-align:center; padding:16px;">Loading ticket passes...</div>';
        try {
            const res = await fetch(`events.php?ajax_action=get_packages&event_id=${eventId}`);
            const packages = await res.json();

            if (packages.length > 0) {
                existingPackagesList.innerHTML = packages.map(pkg => `
                    <div class="pkg-card" id="pkg-card-${pkg.id}">
                        <div class="pkg-card-header">
                            <span class="pkg-card-title">${escapeHtml(pkg.package_name)}</span>
                            <span class="pkg-card-price">LKR ${parseFloat(pkg.ticket_price).toLocaleString(undefined, { minimumFractionDigits: 2 })}</span>
                        </div>
                        <div class="pkg-card-benefits">${escapeHtml(pkg.benefits || 'No specific benefits listed.')}</div>
                        <div class="pkg-card-actions">
                            <button type="button" class="btn-pkg-action btn-pkg-edit"
                                data-id="${pkg.id}"
                                data-name="${escapeHtml(pkg.package_name)}"
                                data-price="${pkg.ticket_price}"
                                data-benefits="${escapeHtml(pkg.benefits || '')}"
                                onclick="triggerEditPackage(this)">Edit</button>
                            <button type="button" class="btn-pkg-action btn-pkg-delete" onclick="deletePackage(${pkg.id})">Delete</button>
                        </div>
                    </div>
                `).join('');
            } else {
                existingPackagesList.innerHTML = '<div style="color:var(--admin-text-muted); font-size:13px; text-align:center; padding:16px;">No ticket passes created yet.</div>';
            }
        } catch (err) {
            existingPackagesList.innerHTML = '<div style="color:#ef4444; font-size:13px; text-align:center; padding:16px;">Failed to load passes.</div>';
        }
    };

    document.querySelectorAll('.manage-packages-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            activeEventId = btn.getAttribute('data-event-id');
            const eventName = btn.getAttribute('data-event-name');
            manageModalTitle.textContent = `Manage Ticket Passes: ${eventName} (#${activeEventId})`;
            manageEventIdInput.value = activeEventId;
            resetPkgForm();
            manageModal.classList.add('active');
            loadPackages(activeEventId);
        });
    });

    window.triggerEditPackage = (btn) => {
        const id = btn.getAttribute('data-id');
        packageFormAction.value = 'update_package';
        managePkgIdInput.value = id;
        formSectionTitle.textContent = `Edit Pass Tier (#${id})`;
        saveSubmitBtn.textContent = 'Update Ticket Pass';
        cancelEditBtn.style.display = 'inline-block';
        nameInput.value = btn.getAttribute('data-name');
        priceInput.value = btn.getAttribute('data-price');
        benefitsInput.value = btn.getAttribute('data-benefits');
    };

    window.deletePackage = async (id) => {
        if (!confirm('Are you sure you want to delete this pass tier?')) return;
        const formData = new FormData();
        formData.append('package_id', id);

        const res = await fetch('events.php?ajax_action=delete_package', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            loadPackages(activeEventId);
        } else {
            alert('Failed to delete package.');
        }
    };

    if (pkgForm) {
        pkgForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const isUpdate = packageFormAction.value === 'update_package';
            if (!isUpdate) {
                pkgForm.submit();
                return;
            }

            try {
                const res = await fetch('events.php?ajax_action=update_package', {
                    method: 'POST',
                    body: new FormData(pkgForm)
                });
                const data = await res.json();
                if (data.success) {
                    resetPkgForm();
                    loadPackages(activeEventId);
                } else {
                    alert('Failed to update package: ' + (data.error || 'Unknown error'));
                }
            } catch (err) {
                alert('Error communicating with server.');
            }
        });
    }
});