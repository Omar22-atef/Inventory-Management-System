document.addEventListener("DOMContentLoaded", () => {
    // DOM Elements
    const addPopup = document.querySelector(".add-supplier-popup");
    const editPopup = document.querySelector(".edit-supplier-popup");
    const deletePopup = document.querySelector(".delete-supplier-popup");
    const addBtn = document.querySelector(".btn-add-supplier");
    const closeBtns = document.querySelectorAll(".close-popup");
    const addForm = document.querySelector("#addSupplierForm");
    const editForm = document.querySelector("#editSupplierForm");
    const tableBody = document.querySelector("table tbody");
    const searchInput = document.getElementById("supplier-search");
    let tableRows = document.querySelectorAll("table tbody tr");
    const deleteSupplierName = document.getElementById("delete-supplier-name");
    const confirmDeleteBtn = document.querySelector(".confirm-delete");

    let currentRow = null;
    let selectedRow = null;
    let deleteSupplierId = null;

    // Initialize Bootstrap Toast
    const toastEl = document.querySelector('.toast');
    const toast = new bootstrap.Toast(toastEl);

    // Toast notification function
    function showToast(message, type = 'success') {
        const toastBody = document.querySelector('.toast-body');
        toastBody.textContent = message;

        if (type === 'error') {
            toastEl.classList.remove('text-bg-success');
            toastEl.classList.add('text-bg-danger');
        } else {
            toastEl.classList.remove('text-bg-danger');
            toastEl.classList.add('text-bg-success');
        }

        toast.show();
    }

    // Get CSRF token
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    // ------------------------
    //  ADD SUPPLIER
    // ------------------------
    addBtn.addEventListener("click", () => {
        addPopup.classList.add("active");
    });

    addForm.addEventListener("submit", function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch("/suppliers", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": getCsrfToken(),
                "Accept": "application/json"
            },
            body: formData
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => { throw err; });
            }
            return res.json();
        })
        .then(data => {
            // Add new row to table
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>${data.id}</td>
                <td>${data.name}</td>
                <td>${data.email}</td>
                <td>${data.phone}</td>
                <td>${data.address}</td>
                <td>
                    <button class="btn btn-sm btn-warning btn-edit"
                        data-id="${data.id}"
                        data-name="${data.name}"
                        data-email="${data.email}"
                        data-phone="${data.phone}"
                        data-address="${data.address}">
                        Edit
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn"
                        data-id="${data.id}"
                        data-name="${data.name}">
                        Delete
                    </button>
                </td>
            `;
            tableBody.appendChild(newRow);

            addPopup.classList.remove("active");
            addForm.reset();
            showToast("Supplier Added Successfully!");

            // Update tableRows for search functionality
            tableRows = document.querySelectorAll("table tbody tr");
        })
        .catch(err => {
            showToast(err.message || "Error adding supplier", 'error');
            console.error(err);
        });
    });

    // ------------------------
    //  EDIT SUPPLIER (Event Delegation)
    // ------------------------
    document.addEventListener("click", function(e) {
        if (e.target.classList.contains("btn-edit")) {
            const row = e.target.closest("tr");
            openEditPopup(row, e.target);
        }
    });

    function openEditPopup(row, editButton) {
        currentRow = row;

        // Get data from button's data attributes (more reliable than innerText)
        document.querySelector("#edit-supplier-id").value = editButton.dataset.id;
        document.querySelector("#edit-supplier-name").value = editButton.dataset.name;
        document.querySelector("#edit-email").value = editButton.dataset.email;
        document.querySelector("#edit-phone").value = editButton.dataset.phone;
        document.querySelector("#edit-address").value = editButton.dataset.address;

        editPopup.classList.add("active");
    }

    editForm.addEventListener("submit", function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const supplierId = document.querySelector("#edit-supplier-id").value;

        fetch(`/suppliers/${supplierId}`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": getCsrfToken(),
                "Accept": "application/json",
                "X-HTTP-Method-Override": "PUT"
            },
            body: formData
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => { throw err; });
            }
            return res.json();
        })
        .then(data => {
            // Update the row in the table
            const cells = currentRow.querySelectorAll("td");
            cells[1].innerText = data.name;
            cells[2].innerText = data.email;
            cells[3].innerText = data.phone;
            cells[4].innerText = data.address;

            // Update the edit button's data attributes
            const editBtn = currentRow.querySelector('.btn-edit');
            editBtn.dataset.name = data.name;
            editBtn.dataset.email = data.email;
            editBtn.dataset.phone = data.phone;
            editBtn.dataset.address = data.address;

            editPopup.classList.remove("active");
            showToast("Supplier Updated Successfully!");
        })
        .catch(err => {
            showToast(err.message || "Error updating supplier", 'error');
            console.error(err);
        });
    });

    // ------------------------
    //  DELETE SUPPLIER (Event Delegation)
    // ------------------------
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("delete-btn")) {
            selectedRow = e.target.closest("tr");
            deleteSupplierId = e.target.dataset.id;

            const name = e.target.dataset.name;
            deleteSupplierName.innerText = `"${name}"`;
            confirmDeleteBtn.setAttribute('data-id', deleteSupplierId);

            deletePopup.classList.add("active");
        }
    });

    function closeDeletePopup() {
        deletePopup.classList.remove("active");
        deleteSupplierName.innerText = "";
        selectedRow = null;
        deleteSupplierId = null;
    }

    deletePopup.querySelector(".close-popup").addEventListener("click", closeDeletePopup);
    deletePopup.querySelector(".cancel-delete").addEventListener("click", closeDeletePopup);

    confirmDeleteBtn.addEventListener("click", function () {
        if (!deleteSupplierId) return;

        fetch(`/suppliers/${deleteSupplierId}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": getCsrfToken(),
                "Accept": "application/json"
            }
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => { throw err; });
            }
            return res.json();
        })
        .then(data => {
            selectedRow.remove();
            showToast("Supplier Deleted Successfully!");
            closeDeletePopup();

            // Update tableRows for search functionality
            tableRows = document.querySelectorAll("table tbody tr");
        })
        .catch(err => {
            showToast(err.message || "Error deleting supplier", 'error');
            console.error(err);
        });
    });

    deletePopup.addEventListener("click", function (e) {
        if (e.target === deletePopup) {
            closeDeletePopup();
        }
    });

    // ------------------------
    //  CLOSE POPUPS
    // ------------------------
    closeBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            addPopup.classList.remove("active");
            editPopup.classList.remove("active");
            deletePopup.classList.remove("active");
        });
    });

    // Close popups when clicking outside
    [addPopup, editPopup, deletePopup].forEach(popup => {
        if (popup) {
            popup.addEventListener("click", function(e) {
                if (e.target === this) {
                    this.classList.remove("active");
                }
            });
        }
    });

    // ------------------------
    //  SEARCH FUNCTIONALITY
    // ------------------------
    let noResultsRow = document.createElement("tr");
    noResultsRow.innerHTML = `<td colspan="6" class="text-center fw-bold text-danger">No Suppliers Found</td>`;
    noResultsRow.style.display = "none";
    tableBody.appendChild(noResultsRow);

    searchInput.addEventListener("keyup", function () {
        let searchValue = searchInput.value.toLowerCase();
        let visibleRows = 0;

        tableRows.forEach(row => {
            let text = row.innerText.toLowerCase();
            if (text.includes(searchValue)) {
                row.style.display = "";
                visibleRows++;
            } else {
                row.style.display = "none";
            }
        });

        noResultsRow.style.display = visibleRows === 0 ? "" : "none";
    });
});
