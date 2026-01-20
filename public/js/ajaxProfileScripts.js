document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('profileForm');
    const alertBox = document.getElementById('profileAlert');
    const submitBtn = document.getElementById('profileSubmit');

    if (!form || !alertBox || !submitBtn) return;

    const escapeHtml = str =>
        String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

    const showAlert = (type, html) => {
        alertBox.innerHTML = `<div class="alert alert-${type}">${html}</div>`;
    };

    form.addEventListener('submit', async e => {
        e.preventDefault();
        alertBox.innerHTML = '';
        submitBtn.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form)
            });

            const data = await response.json();

            if (data.success) {
                showAlert(
                    'success',
                    escapeHtml(data.message || 'Profil uložený')
                );

                form.reset();
                return;
            }

            const errors = (data.errors || ['Neznáma chyba'])
                .map(e => `<li>${escapeHtml(e)}</li>`)
                .join('');

            showAlert('danger', `<ul class="mb-0">${errors}</ul>`);

        } catch (err) {
            console.error(err);
            showAlert('danger', 'Chyba pri ukladaní. Skúste to neskôr.');
        } finally {
            submitBtn.disabled = false;
        }
    });
});