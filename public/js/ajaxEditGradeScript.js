document.addEventListener('DOMContentLoaded', () => {
    const VALID_GRADE = /^(A|B|C|D|E|FX)$/;

    document.querySelectorAll('form.grade-ajax-form').forEach(form => {
        const btn = form.querySelector('button[type="submit"]');
        const status = form.querySelector('.save-status');
        const errorEl = form.querySelector('.save-error');
        const input = form.querySelector('input[name="grade"]');
        const enrollmentId = form.querySelector('input[name="enrollmentId"]')?.value;

        if (!enrollmentId) return;

        form.addEventListener('submit', async e => {
            e.preventDefault();

            const grade = input.value.trim().toUpperCase();
            if (grade && !VALID_GRADE.test(grade)) {
                showError(errorEl, 'Neplatná známka: A, B, C, D, E alebo FX.');
                return;
            }

            setLoading(btn, status, errorEl, true);

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: new URLSearchParams({
                        enrollmentId,
                        grade
                    })
                });

                const data = await res.json();

                if (data?.success) {
                    input.value = data.grade ?? '';
                    showStatus(status);
                } else {
                    showError(errorEl, data?.message || 'Chyba pri ukladaní.');
                }
            } catch {
                showError(errorEl, 'Chyba komunikácie so serverom.');
            } finally {
                btn.disabled = false;
            }
        });
    });

    function showError(el, msg) {
        el.textContent = msg;
        el.style.display = 'inline';
        setTimeout(() => el.style.display = 'none', 3000);
    }

    function showStatus(el) {
        el.style.display = 'inline';
        setTimeout(() => el.style.display = 'none', 1500);
    }

    function setLoading(btn, status, errorEl, loading) {
        btn.disabled = loading;
        status.style.display = 'none';
        errorEl.style.display = 'none';
    }
});
