
(function(){
    const roleSelect = document.getElementById('role');
    const studentFields = document.getElementById('studentFields');
    const teacherFields = document.getElementById('teacherFields');

    function updateVisibility() {
    const role = roleSelect.value;
    if (role === 'admin') {
    studentFields.style.display = 'none';
    teacherFields.style.display = 'none';
    } else if (role === 'student') {
    studentFields.style.display = '';
    teacherFields.style.display = 'none';
    } else if (role === 'teacher') {
    studentFields.style.display = 'none';
    teacherFields.style.display = '';
    }
    }

    roleSelect.addEventListener('change', updateVisibility);
    // initial
    updateVisibility();
})();
