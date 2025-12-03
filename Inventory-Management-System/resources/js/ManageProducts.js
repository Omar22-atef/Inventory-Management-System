document.addEventListener("DOMContentLoaded", () => {

    const addPopup = document.querySelector(".add-product-popup");
    const editPopup = document.querySelector(".edit-product-popup");
    const deletePopup = document.querySelector(".delete-product-popup");

    const addBtn = document.querySelector(".btn-add-product");
    const closeBtns = document.querySelectorAll(".close-popup");

    const addForm = document.querySelector("#addProductForm");
    const editForm = document.querySelector("#editProductForm");

    const tableBody = document.querySelector("table tbody");
    const searchInput = document.getElementById("product-search");

    const deleteProductName = document.getElementById("delete-product-name");
    const confirmDeleteBtn = document.querySelector(".confirm-delete");

    let currentRow = null;
    let selectedRow = null;
    let deleteProductId = null;

    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    // Bootstrap Toast
    const toastEl = document.querySelector('.toast');
    const toast = toastEl ? new bootstrap.Toast(toastEl) : null;
    function showToast(message, type = "success") {
        if (!toast) return;
        const toastBody = document.querySelector(".toast-body");
        toastBody.textContent = message;
        toastEl.classList.remove("text-bg-danger", "text-bg-success");
        toastEl.classList.add(type === "error" ? "text-bg-danger" : "text-bg-success");
        toast.show();
    }

    // enable outside click close
    function enableOutsideClose(popup) {
        if (!popup) return;
        popup.addEventListener("click", e => {
            if (e.target === popup) popup.classList.remove("active");
        });
    }
    enableOutsideClose(addPopup);
    enableOutsideClose(editPopup);
    enableOutsideClose(deletePopup);

    // OPEN ADD
    if (addBtn) addBtn.addEventListener("click", () => addPopup.classList.add("active"));

    // ADD PRODUCT
    addForm.addEventListener("submit", e => {
        e.preventDefault();
        const formData = new FormData(addForm);

        fetch("/products", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrf,
                "Accept": "application/json"
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            // in case controller returns resource wrapped or errors
            const product = data.data ?? data;

            const newRow = document.createElement("tr");
            newRow.innerHTML = `
                <td>${product.id}</td>
                <td>${product.name}</td>
                <td>${product.quantity}</td>
                <td>${product.price}</td>
                <td>${product.reorder_threshold ?? 'N/A'}</td>
                <td>${product.supplier?.name ?? 'N/A'}</td>
                <td>${product.category?.name ?? 'N/A'}</td>
                <td>
                    <button class="btn btn-sm btn-warning btn-edit"
                        data-id="${product.id}"
                        data-name="${product.name}"
                        data-qty="${product.quantity}"
                        data-price="${product.price}"
                        data-reth="${product.reorder_threshold}"
                        data-supplier="${product.supplier_id ?? ''}"
                        data-category="${product.category_id ?? ''}">
                        Edit
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="${product.id}" data-name="${product.name}">Delete</button>
                </td>
            `;
            tableBody.appendChild(newRow);
            addPopup.classList.remove("active");
            addForm.reset();
            showToast("Product Added Successfully!");
        })
        .catch(err => {
            console.error(err);
            showToast("Error adding product", "error");
        });
    });

    // OPEN EDIT - delegation
    document.addEventListener("click", e => {
        if (e.target.classList.contains("btn-edit")) {
            const btn = e.target;
            currentRow = btn.closest("tr");

            document.querySelector("#edit-product-id").value = btn.dataset.id;
            document.querySelector("#edit-product-name").value = btn.dataset.name;
            document.querySelector("#edit-stock-quantity").value = btn.dataset.qty;
            // read the short data attr 'reth'
            document.querySelector("#edit-reorder-threshold").value = btn.dataset.reth ?? '';
            document.querySelector("#edit-price").value = btn.dataset.price;
            document.querySelector("#edit-supplier-id").value = btn.dataset.supplier ?? '';
            document.querySelector("#edit-category-id").value = btn.dataset.category ?? '';

            editPopup.classList.add("active");
        }
    });

    // UPDATE
    editForm.addEventListener("submit", e => {
        e.preventDefault();
        const id = document.querySelector("#edit-product-id").value;
        const formData = new FormData(editForm);
        formData.append('_method', 'PUT'); // method spoof

        fetch(`/products/${id}`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrf,
                "Accept": "application/json"
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            const product = data.data ?? data;
            // update row cells
            if (currentRow) {
                const cells = currentRow.querySelectorAll("td");
                cells[1].innerText = product.name;
                cells[2].innerText = product.quantity;
                cells[3].innerText = product.price;
                cells[4].innerText = product.reorder_threshold ?? 'N/A';
                // update button data attrs
                const editBtn = currentRow.querySelector(".btn-edit");
                if (editBtn) {
                    editBtn.dataset.name = product.name;
                    editBtn.dataset.qty = product.quantity;
                    editBtn.dataset.price = product.price;
                    editBtn.dataset.reth = product.reorder_threshold ?? '';
                    editBtn.dataset.supplier = product.supplier_id ?? '';
                    editBtn.dataset.category = product.category_id ?? '';
                }
            }
            editPopup.classList.remove("active");
            showToast("Product Updated Successfully!");
        })
        .catch(err => {
            console.error(err);
            showToast("Error updating product", "error");
        });
    });

    // OPEN DELETE
    document.addEventListener("click", e => {
        if (e.target.classList.contains("delete-btn")) {
            selectedRow = e.target.closest("tr");
            deleteProductId = e.target.dataset.id;
            deleteProductName.innerText = `"${e.target.dataset.name}"`;
            deletePopup.classList.add("active");
        }
    });

    // CONFIRM DELETE
    confirmDeleteBtn.addEventListener("click", () => {
        if (!deleteProductId) return;
        fetch(`/products/${deleteProductId}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": csrf,
                "Accept": "application/json"
            }
        })
        .then(res => res.json())
        .then(() => {
            if (selectedRow) selectedRow.remove();
            deletePopup.classList.remove("active");
            showToast("Product Deleted Successfully!");
        })
        .catch(err => {
            console.error(err);
            showToast("Error deleting product", "error");
        });
    });

    // CLOSE POPUPS
    closeBtns.forEach(btn => btn.addEventListener("click", () => {
        addPopup.classList.remove("active");
        editPopup.classList.remove("active");
        deletePopup.classList.remove("active");
    }));

    // SEARCH
    const noResultsRow = document.createElement("tr");
    noResultsRow.innerHTML = `<td colspan="8" class="text-center fw-bold text-danger">No Products Found</td>`;
    noResultsRow.style.display = "none";
    tableBody.appendChild(noResultsRow);

    function getRows() { return document.querySelectorAll("table tbody tr"); }

    searchInput.addEventListener("keyup", () => {
        const value = searchInput.value.toLowerCase();
        const rows = getRows();
        let visibleCount = 0;
        rows.forEach(row => {
            if (row.innerText.toLowerCase().includes(value)) {
                row.style.display = "";
                visibleCount++;
            } else {
                row.style.display = "none";
            }
        });
        noResultsRow.style.display = visibleCount === 0 ? "" : "none";
    });

});
