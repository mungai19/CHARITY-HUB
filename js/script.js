
document.addEventListener("DOMContentLoaded", function () {

    // Save and display records for all management forms

    document.querySelectorAll(".record-form").forEach(function(form) {

        const storageKey = form.dataset.key;
        const tableBody = document.getElementById(storageKey);

        if (!tableBody) return;

        function displayRecords() {

            const records = JSON.parse(
                localStorage.getItem(storageKey) || "[]"
            );

            tableBody.replaceChildren();

            if (records.length === 0) {
                const row = document.createElement("tr");
                const cell = document.createElement("td");

                cell.colSpan = form.querySelectorAll(
                    "input, select, textarea"
                ).length + 1;

                cell.textContent = "No records available yet.";
                row.appendChild(cell);
                tableBody.appendChild(row);

                return;
            }

            records.forEach(function(record, index) {

                const row = document.createElement("tr");

                Object.values(record).forEach(function(value) {
                    const cell = document.createElement("td");
                    cell.textContent = value;
                    row.appendChild(cell);
                });

                const actionCell = document.createElement("td");
                const deleteButton = document.createElement("button");

                deleteButton.type = "button";
                deleteButton.textContent = "Delete";

                deleteButton.addEventListener("click", function() {

                    if (!confirm("Are you sure you want to delete this record?")) {
                        return;
                    }

                    records.splice(index, 1);

                    localStorage.setItem(
                        storageKey,
                        JSON.stringify(records)
                    );

                    displayRecords();
                });

                actionCell.appendChild(deleteButton);
                row.appendChild(actionCell);
                tableBody.appendChild(row);
            });
        }

        form.addEventListener("submit", function(event) {

            event.preventDefault();

            const formData = new FormData(form);
            const record = {};

            formData.forEach(function(value, key) {
                record[key] = value.toString().trim();
            });

            const records = JSON.parse(
                localStorage.getItem(storageKey) || "[]"
            );

            records.push(record);

            localStorage.setItem(
                storageKey,
                JSON.stringify(records)
            );

            form.reset();
            displayRecords();

            alert("Record saved successfully!");
        });

        displayRecords();
    });


    // Save organization settings

    const settingsForm = document.getElementById("settingsForm");

    if (settingsForm) {

        const fields = [
            "orgName",
            "orgEmail",
            "orgPhone",
            "orgAddress"
        ];

        const savedSettings = JSON.parse(
            localStorage.getItem("charity_settings") || "{}"
        );

        fields.forEach(function(id) {
            const field = document.getElementById(id);

            if (savedSettings[id]) {
                field.value = savedSettings[id];
            }
        });

        settingsForm.addEventListener("submit", function(event) {

            event.preventDefault();

            const settings = {};

            fields.forEach(function(id) {
                settings[id] = document.getElementById(id).value;
            });

            localStorage.setItem(
                "charity_settings",
                JSON.stringify(settings)
            );

            document.getElementById("settingsMessage").textContent =
                "Organization settings saved successfully!";

        });
    }

});
