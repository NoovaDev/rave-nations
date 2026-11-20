
/**
 * Rave Nations - Staff Management
 * File: admin/js/staff-manager.js
 */

document.addEventListener('DOMContentLoaded', () => {

    // ==========================================
    // MODAL ELEMENTS
    // ==========================================

    const createModal = document.getElementById('createStaffModal');
    const openCreateBtn = document.getElementById('openCreateStaffBtn');
    const closeCreateBtn = document.getElementById('closeCreateStaffBtn');

    const editModal = document.getElementById('editStaffModal');
    const closeEditBtn = document.getElementById('closeEditStaffBtn');

    const editForm = document.querySelector('#editStaffModal form');


    // ==========================================
    // CREATE STAFF MODAL
    // ==========================================

    if (openCreateBtn && createModal) {
        openCreateBtn.addEventListener('click', () => {
            createModal.classList.add('active');
        });
    }

    if (closeCreateBtn && createModal) {
        closeCreateBtn.addEventListener('click', () => {
            createModal.classList.remove('active');
        });
    }


    // ==========================================
    // EDIT STAFF MODAL
    // ==========================================

    if (closeEditBtn && editModal) {
        closeEditBtn.addEventListener('click', () => {
            editModal.classList.remove('active');
        });
    }


    // Close modal when clicking outside
    window.addEventListener('click', (e) => {

        if (createModal && e.target === createModal) {
            createModal.classList.remove('active');
        }

        if (editModal && e.target === editModal) {
            editModal.classList.remove('active');
        }

    });


    // ==========================================
    // LIVE SEARCH & ROLE FILTER
    // ==========================================

    const searchInput = document.getElementById('staffSearchInput');
    const roleFilter = document.getElementById('staffRoleFilter');

    const staffRows = document.querySelectorAll('.staff-table-row');

    function filterTable() {

        const query = searchInput
            ? searchInput.value.trim().toLowerCase()
            : '';

        const selectedRole = roleFilter
            ? roleFilter.value
            : 'all';

        staffRows.forEach(row => {

            const rowRole = row.getAttribute('data-role') || '';

            const rowText = (
                row.getAttribute('data-search') || ''
            ).toLowerCase();

            const matchesRole =
                selectedRole === 'all' ||
                rowRole === selectedRole;

            const matchesSearch =
                query === '' ||
                rowText.includes(query);

            row.style.display =
                (matchesRole && matchesSearch) ? '' : 'none';

        });

    }

    if (searchInput) {
        searchInput.addEventListener('input', filterTable);
    }

    if (roleFilter) {
        roleFilter.addEventListener('change', filterTable);
    }


    // ==========================================
    // HELPER: SHOW STAFF EDIT MODAL
    // ==========================================

    function setFieldValue(id, value) {

        const field = document.getElementById(id);

        if (field) {
            field.value = value ?? '';
        }

    }


    // ==========================================
    // EDIT STAFF - FETCH EXISTING DATA
    // ==========================================

    document.querySelectorAll('.edit-staff-btn').forEach(btn => {

        btn.addEventListener('click', async () => {

            const id = btn.getAttribute('data-id');

            if (!id) {
                alert('Invalid staff ID.');
                return;
            }

            btn.disabled = true;

            try {

                const res = await fetch(
                    `staff.php?ajax_action=get_staff&id=${encodeURIComponent(id)}`,
                    {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                );

                if (!res.ok) {
                    throw new Error(
                        `Server error: ${res.status}`
                    );
                }

                const staff = await res.json();

                if (!staff || !staff.id) {
                    throw new Error(
                        'Staff account not found.'
                    );
                }

                // Populate staff details
                setFieldValue('edit_staff_id', staff.id);

                setFieldValue(
                    'edit_full_name',
                    staff.full_name
                );

                setFieldValue(
                    'edit_username',
                    staff.username
                );

                setFieldValue(
                    'edit_email',
                    staff.email
                );

                // IMPORTANT: Populate role dropdown
                const roleDropdown =
                    document.getElementById('edit_role');

                if (roleDropdown) {

                    const allowedRoles = [
                        'admin',
                        'support_agent',
                        'gate_keeper'
                    ];

                    const staffRole = String(
                        staff.role || ''
                    ).trim();

                    if (allowedRoles.includes(staffRole)) {

                        roleDropdown.value = staffRole;

                    } else {

                        console.warn(
                            'Unknown staff role:',
                            staffRole
                        );

                        roleDropdown.value = '';
                    }
                }

                // Populate account status
                setFieldValue(
                    'edit_status',
                    staff.status
                );

                // Always clear password field
                setFieldValue(
                    'edit_new_password',
                    ''
                );

                // Open modal
                if (editModal) {
                    editModal.classList.add('active');
                }

            } catch (err) {

                console.error(
                    'Load staff error:',
                    err
                );

                alert(
                    err.message ||
                    'Failed to load staff details.'
                );

            } finally {

                btn.disabled = false;

            }

        });

    });


    // ==========================================
    // EDIT STAFF - SAVE CHANGES
    // ==========================================

    if (editForm) {

        editForm.addEventListener('submit', async (e) => {

            e.preventDefault();

            const submitBtn = editForm.querySelector(
                'button[type="submit"]'
            );

            if (submitBtn && submitBtn.disabled) {
                return;
            }

            // Collect form data
            const formData = new FormData(editForm);

            const staffId = formData.get('staff_id');
            const selectedRole = formData.get('role');

            // Validate staff ID
            if (!staffId) {
                alert('Staff ID is missing.');
                return;
            }

            // Validate role
            const allowedRoles = [
                'admin',
                'support_agent',
                'gate_keeper'
            ];

            if (!allowedRoles.includes(selectedRole)) {
                alert('Please select a valid staff role.');
                return;
            }

            try {

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Saving...';
                }

                const res = await fetch('staff.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'text/html'
                    }
                });

                // Read PHP response
                const responseText = await res.text();

                if (!res.ok) {
                    throw new Error(
                        `Server error (${res.status}).`
                    );
                }

                // Parse returned HTML
                const parser = new DOMParser();

                const doc = parser.parseFromString(
                    responseText,
                    'text/html'
                );

                // Find PHP success/error messages
                const errorMessage = doc.querySelector(
                    '.rn-admin-main [style*="239, 68, 68"]'
                );

                const successMessage = doc.querySelector(
                    '.rn-admin-main [style*="16, 185, 129"]'
                );

                // Handle PHP validation/database error
                if (errorMessage) {

                    alert(
                        errorMessage.textContent.trim()
                    );

                    return;
                }

                // Handle successful update
                if (successMessage) {

                    alert(
                        successMessage.textContent.trim()
                    );

                    window.location.reload();

                    return;
                }

                // Unexpected response
                console.error(
                    'Unexpected PHP response:',
                    responseText
                );

                alert(
                    'The server response could not be verified. Please check your PHP code.'
                );

            } catch (err) {

                console.error(
                    'Update staff error:',
                    err
                );

                alert(
                    err.message ||
                    'Server error while updating staff.'
                );

            } finally {

                if (submitBtn) {

                    submitBtn.disabled = false;

                    submitBtn.textContent = 'Save Changes';

                }

            }

        });

    }


    // ==========================================
    // TOGGLE STAFF STATUS
    // ==========================================

    document.querySelectorAll('.toggle-staff-btn').forEach(btn => {

        btn.addEventListener('click', async () => {

            const id = btn.getAttribute('data-id');

            if (!id) {
                return;
            }

            const confirmed = confirm(
                'Are you sure you want to change this staff account status?'
            );

            if (!confirmed) {
                return;
            }

            const formData = new FormData();

            formData.append('id', id);

            try {

                btn.disabled = true;

                const res = await fetch(
                    'staff.php?ajax_action=toggle_status',
                    {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                );

                const data = await res.json();

                if (!res.ok || !data.success) {

                    throw new Error(
                        data.error ||
                        'Could not update staff status.'
                    );

                }

                window.location.reload();

            } catch (err) {

                console.error(
                    'Toggle status error:',
                    err
                );

                alert(
                    err.message ||
                    'Error communicating with server.'
                );

                btn.disabled = false;

            }

        });

    });


    // ==========================================
    // DELETE STAFF
    // ==========================================

    document.querySelectorAll('.delete-staff-btn').forEach(btn => {

        btn.addEventListener('click', async () => {

            const id = btn.getAttribute('data-id');

            const name = btn.getAttribute('data-name');

            if (!id) {
                alert('Invalid staff ID.');
                return;
            }

            const confirmed = confirm(
                `Are you sure you want to permanently delete staff account "${name}"?`
            );

            if (!confirmed) {
                return;
            }

            const formData = new FormData();

            formData.append('id', id);

            try {

                btn.disabled = true;

                const res = await fetch(
                    'staff.php?ajax_action=delete_staff',
                    {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                );

                const data = await res.json();

                if (!res.ok || !data.success) {

                    throw new Error(
                        data.error ||
                        'Failed to delete staff member.'
                    );

                }

                // Remove deleted row immediately
                const row = document.getElementById(
                    `staff-row-${id}`
                );

                if (row) {
                    row.remove();
                }

                // Refresh page to update statistics
                window.location.reload();

            } catch (err) {

                console.error(
                    'Delete staff error:',
                    err
                );

                alert(
                    err.message ||
                    'Error communicating with server.'
                );

                btn.disabled = false;

            }

        });

    });


    console.log(
        'Rave Nations Staff Manager initialized successfully.'
    );

});
