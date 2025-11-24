let products = JSON.parse(localStorage.getItem("products")) || [];

function displayProducts() {
    const table = document.getElementById("productTable");
    table.innerHTML = "";

    products.forEach((p, i) => {
        const row = document.createElement("tr");

        if (p.qty < 10) row.classList.add("low-stock");

        row.innerHTML = `
            <td>${p.name}</td>
            <td>${p.qty}</td>
            <td>
                <button onclick="editProduct(${i})">Edit</button>
                <button onclick="deleteProduct(${i})">Delete</button>
            </td>
        `;
        table.appendChild(row);
    });

    localStorage.setItem("products", JSON.stringify(products));
}
displayProducts();

document.getElementById("productForm").onsubmit = function (e) {
    e.preventDefault();
    const name = document.getElementById("productName").value;
    const qty = document.getElementById("productQty").value;

    products.push({ name, qty: Number(qty) });
    displayProducts();
};

function deleteProduct(index) {
    products.splice(index, 1);
    displayProducts();
}

function editProduct(index) {
    let newQty = prompt("Enter new quantity:");
    products[index].qty = Number(newQty);
    displayProducts();
}
