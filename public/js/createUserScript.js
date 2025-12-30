(function(){
    const roleSelect = document.getElementById('role');
    const studentFields = document.getElementById('studentFields');
    const teacherFields = document.getElementById('teacherFields');

    const studentRequiredInputs = studentFields.querySelectorAll('input');
    const teacherRequiredInputs = teacherFields.querySelectorAll('input');
    function setRequired(elements, required) {
        elements.forEach(el => {
            if (required) {
                el.setAttribute('required', 'required');
            } else {
                el.removeAttribute('required');
            }
        });
    }
    function updateVisibility() {
        const role = roleSelect.value;

        if (role === 'admin') {
            studentFields.style.display = 'none';
            teacherFields.style.display = 'none';

            setRequired(studentRequiredInputs, false);
            setRequired(teacherRequiredInputs, false);

        } else if (role === 'student') {
            studentFields.style.display = '';
            teacherFields.style.display = 'none';

            setRequired(studentRequiredInputs, true);
            setRequired(teacherRequiredInputs, false);

        } else if (role === 'teacher') {
            studentFields.style.display = 'none';
            teacherFields.style.display = '';

            setRequired(studentRequiredInputs, false);
            setRequired(teacherRequiredInputs, true);
        }
    }

    roleSelect.addEventListener('change', updateVisibility);
    // initial
    updateVisibility();
})();
