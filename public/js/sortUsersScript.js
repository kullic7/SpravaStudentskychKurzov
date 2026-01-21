let sortDirection = 1; // 1 = asc, -1 = desc

function sortTableByColumn(columnIndex) {
    const table = document.getElementById("usersTable");
    const tbody = table.tBodies[0];
    const rows = Array.from(tbody.rows);

    rows.sort((a, b) => {
        const aText = a.cells[columnIndex].innerText.trim().toLowerCase();
        const bText = b.cells[columnIndex].innerText.trim().toLowerCase();

        return aText.localeCompare(bText) * sortDirection;
    });

    sortDirection *= -1;

    rows.forEach(row => tbody.appendChild(row));
}
function filterUsers() {
    const input = document.getElementById("userSearch");
    const filter = input.value.toLowerCase();

    const table = document.getElementById("usersTable");
    //vsetky tr v tbodies
    const rows = table.tBodies[0].rows;

    for (let row of rows) {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    }
}