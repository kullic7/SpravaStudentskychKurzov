// In-place AJAX grade saving
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form.grade-ajax-form');
    forms.forEach(form => {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const status = form.querySelector('.save-status');
            const errorEl = form.querySelector('.save-error');
            const input = form.querySelector('input[name="grade"]');
            const enrollmentId = form.querySelector('input[name="enrollmentId"]')?.value;

            if (!enrollmentId) return;

            // validate input
            const value = input.value.trim().toUpperCase();
            if (value !== '' && !/^(A|B|C|D|E|F)$/.test(value)) {
                errorEl.textContent = 'Neplatná známka (povolené: A–F)';
                errorEl.style.display = 'inline';
                return;
            }

            // prepare body
            const body = new URLSearchParams();
            body.append('enrollmentId', enrollmentId);
            body.append('grade', value);

            // UI state
            btn.disabled = true;
            status.style.display = 'none';
            errorEl.style.display = 'none';

            try {
                const resp = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: body.toString()
                });

                const data = await resp.json();
                if (data && data.success) {
                    // update input to reflect stored grade (null -> empty)
                    input.value = data.grade === null || data.grade === undefined ? '' : data.grade;
                    status.style.display = 'inline';
                    // hide status after 1.5s
                    setTimeout(() => { status.style.display = 'none'; }, 1500);
                } else {
                    const msg = (data && data.message) ? data.message : 'Chyba';
                    errorEl.textContent = msg;
                    errorEl.style.display = 'inline';
                    setTimeout(() => { errorEl.style.display = 'none'; }, 3000);
                }
            } catch (err) {
                errorEl.textContent = 'Chyba komunikácie';
                errorEl.style.display = 'inline';
                setTimeout(() => { errorEl.style.display = 'none'; }, 3000);
            } finally {
                btn.disabled = false;
            }
        });
    });
});