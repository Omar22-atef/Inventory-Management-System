const addPopup = document.querySelector(".add-product-popup");
    const editPopup = document.querySelector(".edit-product-popup");
    const addBtn = document.querySelector(".btn-add-product"); 
    const closeBtns = document.querySelectorAll(".close-popup");
    const toast = document.getElementById("successToast");

    const addForm = document.querySelector(".add-product-popup form");
    const editForm = document.querySelector(".edit-product-popup form");

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
        showToast("Product Added Successfully!");
    });

    function openEditPopup(row) {
        currentRow = row;
        const cells = row.querySelectorAll("td");

        document.querySelector("#edit-product-name").value = cells[1].innerText;
        document.querySelector("#edit-category").value = cells[2].innerText;
        document.querySelector("#edit-stock-quantity").value = cells[3].innerText;
        document.querySelector("#edit-price").value = cells[4].innerText.replace("$", "");

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

        cells[1].innerText = document.querySelector("#edit-product-name").value;
        cells[2].innerText = document.querySelector("#edit-category").value;
        cells[3].innerText = document.querySelector("#edit-stock-quantity").value;
        cells[4].innerText = "$" + document.querySelector("#edit-price").value;

        editPopup.classList.remove("active");
        showToast("Product Updated Successfully!");
    });

    window.addEventListener("click", function(e) {
        if (e.target === addPopup || e.target === editPopup) {
            addPopup.classList.remove("active");
            editPopup.classList.remove("active");
        }
    });

const deletePopup = document.querySelector(".delete-product-popup");
const deleteCloseBtn = deletePopup.querySelector(".close-popup");
const cancelDeleteBtn = deletePopup.querySelector(".cancel-delete");
const confirmDeleteBtn = deletePopup.querySelector(".confirm-delete");
const deleteProductName = document.getElementById("delete-product-name");

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

        showToast("Product Deleted Successfully!");
        closeDeletePopup();
    }
});

deletePopup.addEventListener("click", function (e) {
    if (e.target === deletePopup) {
        closeDeletePopup();
    }
});

const searchInput = document.getElementById("product-search");
const tableRows = document.querySelectorAll("table tbody tr");
const tableBody = document.querySelector("table tbody");

let noResultsRow = document.createElement("tr");
noResultsRow.innerHTML = `<td colspan="6" class="text-center fw-bold text-danger">No Products Found</td>`;
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