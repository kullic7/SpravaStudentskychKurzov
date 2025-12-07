// Profile page JavaScript
// Handles AJAX submit of the profile form and displays success/errors

// Simple helper to escape HTML in messages
function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('profileForm');
    if (!form) return;
    const alertBox = document.getElementById('profileAlert');
    const submitBtn = document.getElementById('profileSubmit');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (alertBox) alertBox.innerHTML = '';
        submitBtn.disabled = true;

        try {
            const formData = new FormData(form);

            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok) {
                if (alertBox) alertBox.innerHTML = '<div class="alert alert-danger">Server error</div>';
            } else if (data.success) {
                if (alertBox) alertBox.innerHTML = '<div class="alert alert-success">' + escapeHtml(data.message || 'Profil uložený') + '</div>';
                // update visible name in header if element exists
                const userNameElem = document.getElementById('userName');
                if (userNameElem && data.user) {
                    userNameElem.textContent = (data.user.firstName || '') + ' ' + (data.user.lastName || '');
                }
            } else {
                const errs = data.errors || ['Neznáma chyba'];
                let html = '<div class="alert alert-danger"><ul class="mb-0">';
                for (const err of errs) {
                    html += '<li>' + escapeHtml(err) + '</li>';
                }
                html += '</ul></div>';
                if (alertBox) alertBox.innerHTML = html;
            }

        } catch (err) {
            console.error('AJAX error', err);
            if (alertBox) alertBox.innerHTML = '<div class="alert alert-danger">Chyba pri ukladaní. Skúste to neskôr.</div>';
        } finally {
            submitBtn.disabled = false;
        }
    });
});

