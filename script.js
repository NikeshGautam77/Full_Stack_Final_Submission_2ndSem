// ---------------- TABS ----------------

// Highlight active tab
function highlightTab(tabId, colorClass) {
  const allTabs = ['veg-tab', 'nonveg-tab', 'drinks-tab', 'dessert-tab'];
  allTabs.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.classList.remove('active-tab', 'green', 'red', 'blue', 'purple');
  });
  const activeTab = document.getElementById(tabId);
  if (activeTab) activeTab.classList.add('active-tab', colorClass);
}

// Show sections
function showVegItems() {
  hideAllTabs();
  const el = document.getElementById('vegcontents');
  if (el) el.classList.add('active');
  highlightTab('veg-tab', 'red');
}

function showNonVegItems() {
  hideAllTabs();
  const el = document.getElementById('nonvegcontents');
  if (el) el.classList.add('active');
  highlightTab('nonveg-tab', 'green');
}

function showDrinks() {
  hideAllTabs();
  const el = document.getElementById('drinkcontents');
  if (el) el.classList.add('active');
  highlightTab('drinks-tab', 'blue');
}

function showDessert() {
  hideAllTabs();
  const el = document.getElementById('dessertcontents');
  if (el) el.classList.add('active');
  highlightTab('dessert-tab', 'purple');
}

// Hide all tabs
function hideAllTabs() {
  ['vegcontents','nonvegcontents','drinkcontents','dessertcontents'].forEach(id=>{
    const el = document.getElementById(id);
    if (el) el.classList.remove('active');
  });
}

// Default load
window.onload = function () {
  showVegItems();
};

// ---------------- FILTER ----------------
function filterMenu() {
  const filterValue = document.getElementById('filter').value;
  const activeSection = document.querySelector('.tab-content.active');
  if (!activeSection) return;

  const menuCards = Array.from(activeSection.querySelectorAll('.menu-card'));
  const menuGrid = activeSection.querySelector('.menu-grid');

  menuCards.sort((a, b) => {
    const priceA = parseFloat(a.querySelector('p').textContent.replace('Rs. ', ''));
    const priceB = parseFloat(b.querySelector('p').textContent.replace('Rs. ', ''));
    switch(filterValue) {
      case 'price-asc': return priceA - priceB;
      case 'price-desc': return priceB - priceA;
      default: return 0;
    }
  });

  menuGrid.innerHTML = '';
  menuCards.forEach(card => menuGrid.appendChild(card));
}

// ---------------- CART ----------------
let cart = [];

function addToCart(name, price) {
  const existingItem = cart.find(item => item.name === name);
  if (existingItem) {
    existingItem.quantity++;
  } else {
    cart.push({ name, price, quantity: 1 });
  }
  renderCart();
  updateCartCount();
}

function renderCart() {
  const cartList = document.getElementById("cart-items");
  cartList.innerHTML = "";
  cart.forEach(item => {
    const li = document.createElement("li");
    li.innerHTML = `
      <strong>${item.name}</strong> - Rs. ${item.price} x ${item.quantity}
      <button onclick="updateQuantity('${item.name}', -1)">-</button>
      <button onclick="updateQuantity('${item.name}', 1)">+</button>
      <button onclick="removeItem('${item.name}')">Remove</button>
    `;
    cartList.appendChild(li);
  });
  updateTotal();
}

function updateQuantity(name, delta) {
  const item = cart.find(i => i.name === name);
  if (!item) return;
  item.quantity += delta;
  if (item.quantity <= 0) cart = cart.filter(i => i.name !== name);
  renderCart();
  updateCartCount();
}

function removeItem(name) {
  cart = cart.filter(item => item.name !== name);
  renderCart();
  updateCartCount();
}

function updateTotal() {
  const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
  document.getElementById("cart-total").textContent = `Total: Rs. ${total}`;
}

function updateCartCount() {
  const count = cart.reduce((total, item) => total + item.quantity, 0);
  document.getElementById("cart-count").textContent = count;
}

function resetCart() {
  cart = [];
  renderCart();
  updateCartCount();
}

// ---------------- MODALS ----------------
function openCartModal() {
  document.getElementById("cart-modal").classList.add("active");
}
function closeCartModal() {
  document.getElementById("cart-modal").classList.remove("active");
}
function closeOrderModal() {
  document.getElementById('order-modal').style.display = 'none';
}

// ---------------- ORDER FLOW ----------------
function finalOrder() {
  if (cart.length === 0) {
    alert('Your cart is empty!');
    return;
  }
  const summaryList = document.getElementById('order-summary-list');
  const summaryTotal = document.getElementById('order-summary-total');
  summaryList.innerHTML = '';
  let total = 0;
  const cartData = [];

  cart.forEach(item => {
    const li = document.createElement('li');
    li.textContent = `${item.name} - Rs. ${item.price} x ${item.quantity}`;
    summaryList.appendChild(li);
    total += item.price * item.quantity;
    cartData.push({ name: item.name, price: item.price, qty: item.quantity });
  });

  summaryTotal.textContent = `Total: Rs. ${total}`;
  const jsonStr = JSON.stringify(cartData);
  const hidden = document.getElementById('cart-json');
  if (hidden) hidden.value = jsonStr;

  document.getElementById('order-modal').style.display = 'flex';
}

function submitOrder(event) {
  event.preventDefault();
  if (cart.length === 0) {
    alert('Your cart is empty!');
    return false;
  }
  const cartData = cart.map(item => ({
    name: item.name,
    price: item.price,
    qty: item.quantity
  }));
  const jsonStr = JSON.stringify(cartData);
  const hidden = document.getElementById('cart-json');
  if (hidden) hidden.value = jsonStr;

  const form = document.getElementById('checkout-form');
  if (form) form.submit();
}

// FOR ORDERS BUTTON
function goToMyOrders() {
  window.location.href = "my_orders.php";
}

// ---------------- AJAX SEARCH ----------------
function ajaxSearch() {
  const name = document.getElementById("searchName").value;
  const category = document.getElementById("searchCategory").value;
  const minPrice = document.getElementById("searchMinPrice").value;
  const maxPrice = document.getElementById("searchMaxPrice").value;

  const xhr = new XMLHttpRequest();
  xhr.open("POST", "search.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onload = function() {
    if (xhr.status === 200) {
      document.getElementById("searchResults").innerHTML = xhr.responseText;
    } else {
      document.getElementById("searchResults").innerHTML = "<p>Error loading results.</p>";
    }
  };

  xhr.send(
    "name=" + encodeURIComponent(name) +
    "&category=" + encodeURIComponent(category) +
    "&minPrice=" + encodeURIComponent(minPrice) +
    "&maxPrice=" + encodeURIComponent(maxPrice)
  );
}

// Trigger Ajax search when pressing Enter in any search field
document.addEventListener("DOMContentLoaded", function() {
  const inputs = [
    document.getElementById("searchName"),
    document.getElementById("searchCategory"),
    document.getElementById("searchMinPrice"),
    document.getElementById("searchMaxPrice")
  ];

  inputs.forEach(input => {
    if (input) {
      input.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
          e.preventDefault(); // prevent form submission
          ajaxSearch();       // call your Ajax search
        }
      });
    }
  });
});