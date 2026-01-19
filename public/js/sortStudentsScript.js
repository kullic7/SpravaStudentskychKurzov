function filterStudents() {
    const input = document.getElementById("studentSearch");
    const filter = input.value.toLowerCase();

    const table = document.getElementById("studentsTable");
    const rows = table.tBodies[0].rows;

    for (let row of rows) {
        // berieme iba základné stĺpce (bez kurzov)
        const searchableText = [
            row.cells[0].innerText, // meno
            row.cells[1].innerText, // priezvisko
            row.cells[2].innerText, // email
            row.cells[3].innerText, // štud. číslo
            row.cells[4].innerText  // ročník
        ].join(" ").toLowerCase();

        row.style.display = searchableText.includes(filter) ? "" : "none";
    }
}