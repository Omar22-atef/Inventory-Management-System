const addPopup = document.querySelector(".add-supplier-popup");
    const editPopup = document.querySelector(".edit-supplier-popup");
    const addBtn = document.querySelector(".btn-add-supplier"); 
    const closeBtns = document.querySelectorAll(".close-popup");
    const toast = document.getElementById("successToast");

    const addForm = document.querySelector(".add-supplier-popup form");
    const editForm = document.querySelector(".edit-supplier-popup form");

    let currentRow; 

    addBtn.addEventListener("click", () => {
        addPopup.classList.add("active");
    });

    closeBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            addPopup.classList.remove("active");
            editPopup.classList.remove("active");
        });
    });

    function showToast(message) {
        toast.innerText = message;
        toast.classList.add("show");

        setTimeout(() => {
            toast.classList.remove("show");
        }, 2500);
    }

    addForm.addEventListener("submit", function(e) {
        e.preventDefault();

        addPopup.classList.remove("active");
        addForm.reset();
        showToast("Supplier Added Successfully!");
    });

    function openEditPopup(row) {
        currentRow = row;
        const cells = row.querySelectorAll("td");

        document.querySelector("#edit-supplier-name").value = cells[1].innerText;
        document.querySelector("#edit-email").value = cells[2].innerText;
        document.querySelector("#edit-Address").value = cells[3].innerText;

        editPopup.classList.add("active");
    }

    document.querySelectorAll(".btn-edit").forEach((btn, index) => {
        btn.addEventListener("click", () => {
            const row = btn.closest("tr");
            openEditPopup(row);
        });
    });

    editForm.addEventListener("submit", function(e) {
        e.preventDefault();

        const cells = currentRow.querySelectorAll("td");

        cells[1].innerText = document.querySelector("#edit-supplier-name").value;
        cells[2].innerText = document.querySelector("#edit-email").value;
        cells[3].innerText = document.querySelector("#edit-Address").value;

        editPopup.classList.remove("active");
        showToast("Supplier Updated Successfully!");
    });

    window.addEventListener("click", function(e) {
        if (e.target === addPopup || e.target === editPopup) {
            addPopup.classList.remove("active");
            editPopup.classList.remove("active");
        }
    });

const deletePopup = document.querySelector(".delete-supplier-popup");
const deleteCloseBtn = deletePopup.querySelector(".close-popup");
const cancelDeleteBtn = deletePopup.querySelector(".cancel-delete");
const confirmDeleteBtn = deletePopup.querySelector(".confirm-delete");
const deleteProductName = document.getElementById("delete-supplier-name");

let selectedRow = null; 

document.addEventListener("click", function (e) {
    if (e.target.classList.contains("delete-btn")) {
        
        selectedRow = e.target.closest("tr");

        const name = selectedRow.querySelector("td:nth-child(2)").innerText;
        deleteProductName.innerText = `"${name}"`;

        deletePopup.classList.add("active");
    }
});

function closeDeletePopup() {
    deletePopup.classList.remove("active");
    deleteProductName.innerText = "";
}

deleteCloseBtn.addEventListener("click", closeDeletePopup);

cancelDeleteBtn.addEventListener("click", closeDeletePopup);

confirmDeleteBtn.addEventListener("click", function () {
    if (selectedRow) {
        selectedRow.remove();
        selectedRow = null;

        showToast("Supplier Deleted Successfully!");
        closeDeletePopup();
    }
});

deletePopup.addEventListener("click", function (e) {
    if (e.target === deletePopup) {
        closeDeletePopup();
    }
});

const searchInput = document.getElementById("supplier-search");
const tableRows = document.querySelectorAll("table tbody tr");
const tableBody = document.querySelector("table tbody");

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